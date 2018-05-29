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
      case ProvisioningActionEnum::CoPersonDeleted:
        // We can get CoPersonDeleted under a few different circumstances, including
        // if the person is removed from the Provisioning Group.
        // XXX Currently this will just set isenabled=false, but should the entire
        // row (ever) be removed?
        $syncPerson = true;
        break;
      default:
        // Ignore all other actions. Note group membership changes
        // are typically handled as CoPersonUpdated events.
        return true;
        break;
    }
    
    if($syncPerson) {
      // Research Navigator wants Org Identity attributes, but ProvisionerBehavior doesn't
      // currently support it (CO-1394). So we need to pull the Org Identity records manually.
      // NOTE: This means a change to an Org Identity record will NOT trigger provisioning.
      
      $args = array();
      $args['conditions']['CoOrgIdentityLink.co_person_id'] = $provisioningData['CoPerson']['id'];
      $args['contain'] = array(
        'OrgIdentity' => array(
          'PrimaryName',
          'EmailAddress',
          'Identifier'
        )
      );
      
      $orgIdentities = $this->CoProvisioningTarget->Co->CoPerson->CoOrgIdentityLink->find('all', $args);
      
      // Just let any exceptions bubble up the stack
      $this->CoProvisioningTarget->Co->Server->SqlServer->connect($coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['server_id'], "rnav");
      
      $ComanagePerson = new Model(array(
        'table' => 'comanage_people',
        'name'  => 'ComanagePerson',
        'ds'    => 'rnav'
      ));
      
      // We manage one row per Org Identity, due to the constraints of the RN data format
      
      foreach($orgIdentities as $orgId) {
        $ComanagePerson->clear();
        
        $data = array(
          'ComanagePerson' => array(
            // We can assume NOW() for the last modified date since presumably
            // something changed to trigger provisioning
            'lastmodifieddate'  => DboSource::expression('NOW()'),
          )
        );
        
        // Find the login identifier for this record, we'll use that as the key
        $identifier = Hash::extract($orgId, 'OrgIdentity.Identifier.{n}[login=true]');
        
        if(empty($identifier)) {
          // No login identifier, skip this identity
          continue;
        }
        
        $data['ComanagePerson']['kerberosid'] = $identifier[0]['identifier'];
        
        // Do we already have a record for this ID?
        $currecid = $ComanagePerson->field('id', array('ComanagePerson.kerberosid' => $identifier[0]['identifier']));
        
        if($currecid) {
          $data['ComanagePerson']['id'] = $currecid;
        }
        
        // This shouldn't change after the initial record is created, but we'll sync on update anyway
        $data['ComanagePerson']['createddate'] = $orgId['OrgIdentity']['created'];
        
        if(!empty($orgId['OrgIdentity']['PrimaryName']['given'])) {
          $data['ComanagePerson']['firstname'] = $orgId['OrgIdentity']['PrimaryName']['given'];
        } else {
          // No given name, skip this identity
          continue;
        }
        if(!empty($provisioningData['PrimaryName']['middle'])) {
          $data['ComanagePerson']['middlename'] = $orgId['OrgIdentity']['PrimaryName']['middle'];
        }
        if(!empty($provisioningData['PrimaryName']['family'])) {
          $data['ComanagePerson']['lastname'] = $orgId['OrgIdentity']['PrimaryName']['family'];
        } else {
          // No last name, skip this identity
          continue;
        }
        
        if(!empty($orgId['OrgIdentity']['o'])) {
          $data['ComanagePerson']['institution'] = $orgId['OrgIdentity']['o'];
        }
        
        if(!empty($orgId['OrgIdentity']['EmailAddress'][0]['mail'])) {
          // For now we just pick the first address in the unlikely event there
          // is more than one
          
          $data['ComanagePerson']['email'] = $orgId['OrgIdentity']['EmailAddress'][0]['mail'];
        }
        
        if(!empty($provisioningData['CoPerson']['status'])
           && in_array($provisioningData['CoPerson']['status'],
                       array(StatusEnum::Active, StatusEnum::GracePeriod))) {
          $data['ComanagePerson']['isenabled'] = true;
        } else {
          $data['ComanagePerson']['isenabled'] = false;
        }
        
        // For affiliation records, see if the OrgIdentity has it populated, otherwise
        // look at the CO Person Role
        
        $affil = null;
        
        if(!empty($orgId['OrgIdentity']['affiliation'])) {
          $affil = $orgId['OrgIdentity']['affiliation'];
        } elseif(!empty($provisioningData['CoPersonRole'][0]['affiliation'])) {
          $affil = $provisioningData['CoPersonRole'][0]['affiliation'];
        }
        
        if($affil) {
          $data['ComanagePerson']['isemployee'] = false;
          $data['ComanagePerson']['isfaculty'] = false;
          
          switch($affil) {
            case AffiliationEnum::Employee:
            case AffiliationEnum::Staff:
              $data['ComanagePerson']['isemployee'] = true;
              break;
            case AffiliationEnum::Faculty:
              $data['ComanagePerson']['isfaculty'] = true;
              $data['ComanagePerson']['isemployee'] = true;
              break;
            default:
              break;
          }
        }
        
        // Temporary implementation pending further requirements
        if(!empty($provisioningData['CoPersonRole'][0])) {
          // XXX Depending on how renewals work, we may need to look for first
          // active role instead of first role
          
          if(!empty($provisioningData['CoPersonRole'][0]['title'])) {
            $data['ComanagePerson']['title'] = $provisioningData['CoPersonRole'][0]['title'];
          }
        }
        
        // Find the platform identifier
        if(empty($coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['record_id_type'])) {
          throw new InvalidArgumentException('er.researchnavigatorprovisioner.cfg.identifier');
        }
        
        $idType = $coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['record_id_type'];
        
        $identifier = Hash::extract($provisioningData, 'Identifier.{n}[type=' . $idType . ']');
        
        if(empty($identifier)) {
          throw new InvalidArgumentException('er.researchnavigatorprovisioner.attr', 
                                             array($coProvisioningTargetData['CoResearchNavigatorProvisionerTarget']['record_id_type']));
        }
        
        $data['ComanagePerson']['employeeid'] = $identifier[0]['identifier'];
        
        $ComanagePerson->save($data);
      }
    }
    
    return true;
  }
}
