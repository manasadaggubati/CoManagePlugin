<?php
/**
 * COmanage Registry Affiliation Check Enroller
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.3.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

class AffiliationCheckEnroller extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "enroller";
  
  // Document foreign keys
  public $cmPluginHasMany = array();
  
  // Add behaviors
  public $actsAs = array('Containable', 'Changelog' => array('priority' => 5));
  
  // Association rules from this model to other models
// Enable for v4.0.0
//  public $belongsTo = array("CoEnrollmentFlowWedge");
  
  // Default display field for cake generated views
  public $displayField = "env_var";
  
  // Validation rules for table elements
  public $validate = array(
/* Swap this for the next one when migrating for v4.0.0
    'co_enrollment_flow_wedge_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    ),*/
    'co_enrollment_flow_id' => array(
      'rule' => 'numeric',
      'required' => true,
      'allowEmpty' => false
    ),
    'env_var' => array(
      'rule' => array('validateInput'),
      'required' => true,
			'allowEmpty' => false
    ),
    'match_regex' => array(
      'rule' => array('notEmpty'),
      'required' => true,
			'allowEmpty' => false
    ),
    'deny_enrollment' => array(
      'rule' => 'boolean',
      'required' => false,
      'allowEmpty' => true
    ),
    'redirect_url' => array(
      'rule' => array('notEmpty'),
      'required' => false,
			'allowEmpty' => true
    )
  );
  
  /**
   * Expose menu items.
   * 
   * @since COmanage Registry v3.3.0
   * @return Array with menu location type as key and array of labels, controllers, actions as values.
   */
  
  public function cmPluginMenus() {
    return array(
// drop this when migrating to 4.0.0
      "cmp" => array(_txt('ct.affiliation_check_enrollers.pl') =>
        array(
          'plugin'     => 'affiliation_check_enroller',
          'controller' => "affiliation_check_enrollers",
          'action'     => "index")
        )
    );
  }
}
