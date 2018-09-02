<?php
/**
 * COmanage Registry Identifier Hasher Listener
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

App::uses('CakeEventListener', 'Event');

class IdentifierHasherListener implements CakeEventListener {
  /**
   * Define our listener(s)
   *
   * @since  COmanage Registry v3.2.0
   * @return Array Array of events and associated function names
   */
    
  public function implementedEvents() {
    return array(
      'Model.afterDelete' => 'deleteIdentifier',
      'Model.afterSave'   => 'shortenIdentifier'
    );
  }
  
  /**
   * Handle an Identifier Deleted event by removing the associated short identifier.
   *
   * @since  COmanage Registry v3.2.0
   * @param  CakeEvent $event Cake Event
   * @return Boolean True on success
   */
  
  public function deleteIdentifier(CakeEvent $event) {
    // Toss the associated record
    
    $subject = $event->subject();
    
    // The only thing we have for the record is the ID, so we just have to take
    // it on faith that we're not colliding with another identifier.
    
    $shortId = 'EXT' . $subject->id;
    
    $Identifier = ClassRegistry::init('Identifier');
    
    $args = array();
    $args['Identifier.identifier LIKE '] = '%:' . $shortId;
    $args['Identifier.type'] = 'shortorgid';
    
    // Make sure callbacks run
    $Identifier->deleteAll($args, false, true);
    
    return true;
  }
  
  /**
   * Handle the actual synchronization (shortening) of an identifier.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Identifier $Identifier Identifier class instantiation
   * @param  Array      $id         Identifier record
   * 
   */
  
  protected function syncIdentifier($Identifier, $id) {
    $longId = $id['identifier'];
    $shortId = "EXT" . $id['id'];
    $concatId = $longId . ":" . $shortId;
    
    // We need the corresponding CO Person ID
    $CoOrgIdentityLink = ClassRegistry::init('CoOrgIdentityLink');
    
    $args = array();
    $args['conditions']['CoOrgIdentityLink.org_identity_id'] = $id['org_identity_id'];
    $args['contain'] = 'CoPerson';
    
    $link = $CoOrgIdentityLink->find('first', $args);
    
    if(!$link || empty($link['CoOrgIdentityLink']['co_person_id'])) {
      // No CO Person ID, nothing to do
      return true;
    }
    
    // For now we hardcode the CO we're interested in, though ultimately it
    // would be better to enable on a per-CO basis, eg
    //  https://bugs.internet2.edu/jira/browse/CO-1646
    if($link['CoPerson']['co_id'] != 2) {
      // If CO is not 2, don't do anything
      return true;
    }
    
    // Is there already an identifier associated with this $shortId?
    $args = array();
    $args['conditions']['Identifier.identifier LIKE'] = '%:' . $shortId;
    $args['conditions']['Identifier.co_person_id'] = $link['CoOrgIdentityLink']['co_person_id'];
    $args['contain'] = false;
    
    $curId = $Identifier->find('first', $args);
    
    if(!empty($curId)) {
      if($curId['Identifier']['identifier'] == $concatId) {
        // Nothing to do, identifier already exists
        return true;
      }
    }
    
    $newId = array(
      'Identifier' => array(
        'identifier'           => $concatId,
        'co_person_id'         => $link['CoOrgIdentityLink']['co_person_id'],
        'type'                 => 'shortorgid',
        'status'               => StatusEnum::Active,
        'login'                => false
      )
    );
    
    if(!empty($curId['Identifier']['id'])) {
      // Update the existing record
      $newId['Identifier']['id'] = $curId['Identifier']['id'];
    }
    
    // Make sure the validation rules are set for extended types, which isn't
    // set when we get here via CoOrgIdentityLink (since it does not extend
    // MVPAController).
    
    $vrule = $Identifier->validate['type']['content']['rule'];
    $vrule[1]['coid'] = $link['CoPerson']['co_id'];
    $Identifier->validator()->getField('type')->getRule('content')->rule = $vrule;    
    
    $Identifier->clear();
    $Identifier->save($newId);    
    
    return true;
  }
  
  /**
   * Handle an Identifier Saved event by creating or updating the associated
   * short identifier.
   *
   * @since  COmanage Registry v3.2.0
   * @param  CakeEvent $event Cake Event
   * @return Boolean True on success
   */
  
  public function shortenIdentifier(CakeEvent $event) {
    // We don't hash the identifiers, since we could end up with a collision
    // (albeit unlikely), and many algorithms generate > 20 character strings.
    // So instead we simply create an identifier of the form EXT#, where # is
    // the ID of the Identifier. ie, EXT275 is the shortened identifier of
    // cm_identifiers:id=275 We then write out the new identifier (which we'll
    // skip because we're only hashing login identifiers) as EXT#:identifier
    // for population into LDAP and provisioning to RN.
    
    $subject = $event->subject();
    
    if($subject->name == 'Identifier') {
      $identifier = $subject->data['Identifier'];
      
      if(!empty($identifier['org_identity_id'])
         && isset($identifier['login']) && $identifier['login']
         && !empty($identifier['identifier'])) {
        $Identifier = ClassRegistry::init('Identifier');
        
        $this->syncIdentifier($Identifier, $identifier);
      }
    } elseif($subject->name == 'CoOrgIdentityLink') {
      // If an Org Identity is linked to a CO Person (which might happen eg
      // after an OIS record is created and pipelined to a CO Person), we need
      // to check for any identifiers to hash.
      
      $link = $subject->data['CoOrgIdentityLink'];
      
      if(!empty($link['org_identity_id'])) {
        // Look for Identifiers attached to the Org Identity
        
        $args = array();
        $args['conditions']['Identifier.org_identity_id'] = $link['org_identity_id'];
        $args['conditions']['Identifier.login'] = true;
        $args['contain'] = false;
        
        $Identifier = ClassRegistry::init('Identifier');
        
        $ids = $Identifier->find('all', $args);
        
        foreach($ids as $id) {
          $this->syncIdentifier($Identifier, $id['Identifier']);
        }
      }
    }
    
    // Return true to keep the event flowing
    return true;
  }
}