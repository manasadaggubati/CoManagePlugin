<?php
/**
 * COmanage Registry Affiliation Check Enrollers Controller
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.3.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

App::uses("StandardController", "Controller");

class AffiliationCheckEnrollersController extends StandardController {
  // Class name, used by Cake
  public $name = "AffiliationCheckEnrollers";
    
  // Establish pagination parameters for HTML views
  public $paginate = array(
    'limit' => 25,
    'order' => array(
      'env_var' => 'asc'
    )
  );
  
  public $view_contains = array();
  
  public $edit_contains = array();
  
  public $delete_contains = array();
  
  public $uses = array('AffiliationCheckEnroller.AffiliationCheckEnroller', 
                       'CoEnrollmentFlow');
  
  /**
   * Callback after controller methods are invoked but before views are rendered.
   *
   * @since  COmanage Registry v3.3.0
   */
    
  public function beforeRender() {
    parent::beforeRender();
    
    $args = array();
    $args['conditions'] = array(
      'status' => TemplateableStatusEnum::Active
    );
    $args['fields'] = array('id', 'name');
    $args['order'] = array('name ASC');
    
    $this->set('vv_enrollment_flows', $this->CoEnrollmentFlow->find('list', $args));
  }
  
  /**
   * Authorization for this Controller, called by Auth component
   * - precondition: Session.Auth holds data used for authz decisions
   * - postcondition: $permissions set with calculated permissions
   *
   * @since  COmanage Registry v3.3.0
   * @return Array Permissions
   */
  
  function isAuthorized() {
    $roles = $this->Role->calculateCMRoles();
    
    // Construct the permission set for this user, which will also be passed to the view.
    $p = array();
    
    // Determine what operations this user can perform
    
    // Add a new Affiliation Check?
    $p['add'] = $roles['cmadmin'];
    
    // Delete an Affiliation Check?
    $p['delete'] = $roles['cmadmin'];
    
    // Edit an existing Affiliation Check?
    $p['edit'] = $roles['cmadmin'];
    
    // View all existing Affiliation Check?
    $p['index'] = $roles['cmadmin'];
    
    // View an existing Affiliation Check?
    $p['view'] = $roles['cmadmin'];
    
    $this->set('permissions', $p);
    return $p[$this->action];
  }
}
