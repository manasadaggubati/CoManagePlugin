<?php
/**
 * COmanage Registry Research Navigator Provisioner Model
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

class ResearchNavigatorProvisioner extends AppModel {
  // Required by COmanage Plugins
  public $cmPluginType = "provisioner";

  // Document foreign keys
  public $cmPluginHasMany = array();
  
  /**
   * Expose menu items.
   * 
   * @ since COmanage Registry v3.2.0
   * @ return Array with menu location type as key and array of labels, controllers, actions as values.
   */
  
  public function cmPluginMenus() {
  	return array();
  }
}