<?php
/**
 * COmanage Registry CO Research Navigator Provisioner Target Model
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

App::uses("CoProvisionerPluginTarget", "Model");

class CoResearchNavigatorProvisionerTarget extends CoProvisionerPluginTarget {
  // Define class name for cake
  public $name = "CoResearchNavigatorProvisionerTarget";
  
  // Add behaviors
  public $actsAs = array('Containable');
  
  // Association rules from this model to other models
  public $belongsTo = array("CoProvisioningTarget");
  
  // Default display field for cake generated views
  public $displayField = "server_id";
  
  // Request OAuth2 servers
  public $cmServerType = ServerEnum::SqlServer;
  
  // Validation rules for table elements
  public $validate = array(
    'co_provisioning_target_id' => array(
      'rule' => 'numeric',
      'required' => true
    ),
    'server_id' => array(
      'rule' => 'numeric',
      'required' => true
    ),
    'record_id_type' => array(
      'rule' => 'notBlank',
      'required' => true,
      'allowEmpty' => false
    )
  );
  
  /**
   * Provision for the specified CO Person.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Array CO Provisioning Target data
   * @param  ProvisioningActionEnum Registry transaction type triggering provisioning
   * @param  Array Provisioning data, populated with ['CoPerson'] or ['CoGroup']
   * @return Boolean True on success
   * @throws RuntimeException
   * @throws InvalidArgumentException
   */
  
  public function provision($coProvisioningTargetData, $op, $provisioningData) {
    // First determine what to do
    $deletePerson = false;
    $syncPerson = false;

    switch($op) {
      case ProvisioningActionEnum::CoPersonAdded:
      case ProvisioningActionEnum::CoPersonEnteredGracePeriod:
      case ProvisioningActionEnum::CoPersonExpired:
      case ProvisioningActionEnum::CoPersonPetitionProvisioned:
      case ProvisioningActionEnum::CoPersonPipelineProvisioned:
      case ProvisioningActionEnum::CoPersonReprovisionRequested:
      case ProvisioningActionEnum::CoPersonUnexpired:
      case ProvisioningActionEnum::CoPersonUpdated:
        $syncPerson = true;
        break;
      case ProvisioningActionEnum::CoPersonDeleted:
        // XXX under what circumstances do we delete a person?
        // We don't do anything here because typically we don't have any useful
        // information to process, and we've probably deprovisioned due to
        // status change/group membership loss/etc.
        break;
      default:
        // Ignore all other actions. Note group membership changes
        // are typically handled as CoPersonUpdated events.
        return true;
        break;
    }
    
    if($syncPerson) {
      // Find the target identifier
      if(empty($coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['record_id_type'])) {
        throw new InvalidArgumentException('er.researchnavigatorprovisioner.cfg.identifier');
      }
      
      $idType = $coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['record_id_type'];
      
      $identifier = Hash::extract($provisioningData, 'Identifier.{n}[type=' . $idType . ']');
      
      if(empty($identifier)) {
        throw new InvalidArgumentException('er.researchnavigatorprovisioner.attr', 
                                           array($coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['record_id_type']));
      }
      
      // Just let any exceptions bubble up the stack
      $this->CoProvisioningTarget->Co->Server->SqlServer->connect($coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['server_id'], "rnav");
      
      $ComanagePerson = new Model(array(
        'table' => 'comanage_people',
        'name'  => 'ComanagePerson',
        'ds'    => 'rnav'
      ));
      
      $data = array(
        'ComanagePerson' => array(
          'lastmodifieddate'  => DboSource::expression('NOW()'),
        )
      );
      
      // Do we already have a record for this ID?
      $currecid = $ComanagePerson->field('id', array('ComanagePerson.kerberosid' => $identifier[0]['identifier']));
      
      if($currecid) {
        $data['ComanagePerson']['id'] = $currecid;
      } else {
        $data['ComanagePerson']['createddate'] = DboSource::expression('NOW()');
      }
      
      $data['ComanagePerson']['kerberosid'] = $identifier[0]['identifier'];
      
      if(!empty($provisioningData['PrimaryName']['given'])) {
        $data['ComanagePerson']['firstname'] = $provisioningData['PrimaryName']['given'];
      } else {
        throw new RuntimeException(_txt('er.researchnavigatorprovisioner.attr', array(_txt('fd.name.given'))));
      }
      if(!empty($provisioningData['PrimaryName']['middle'])) {
        $data['ComanagePerson']['middlename'] = $provisioningData['PrimaryName']['middle'];
      }
      if(!empty($provisioningData['PrimaryName']['family'])) {
        $data['ComanagePerson']['lastname'] = $provisioningData['PrimaryName']['family'];
      } else {
        throw new RuntimeException(_txt('er.researchnavigatorprovisioner.attr', array(_txt('fd.name.family'))));
      }
      
      if(!empty($provisioningData['EmailAddress'])) {
        // XXX Which email address? For now just pick the first
        
        if(!empty($provisioningData['EmailAddress'][0]['mail'])) {
          // Look for the first address of any type
          $data['ComanagePerson']['email'] = $provisioningData['EmailAddress'][0]['mail'];
        }
      }
      
      if(!empty($provisioningData['CoPersonRole'][0])) {
        // XXX Which role? For now just pick the first.This can be coerced
        // by setting CoPersonRole.ordr.
        if(!empty($provisioningData['CoPersonRole'][0]['o'])) {
          $data['ComanagePerson']['institution'] = $provisioningData['CoPersonRole'][0]['o'];
        }
        
        if(!empty($provisioningData['CoPersonRole'][0]['title'])) {
          $data['ComanagePerson']['title'] = $provisioningData['CoPersonRole'][0]['title'];
        }
      }
      
      $ComanagePerson->save($data);
    }
    
    return true;
  }
}
