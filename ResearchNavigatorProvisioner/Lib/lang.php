<?php
/**
 * COmanage Registry Research Navigator Provisioner Plugin Language File
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */
  
global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_research_navigator_provisioner_texts['en_US'] = array(
  // Titles, per-controller
  'ct.co_research_navigator_provisioner_targets.1'  => 'Research Navigator Provisioner Target',
  'ct.co_research_navigator_provisioner_targets.pl' => 'Research Navigator Provisioner Targets',
  
  // Error messages
  'er.researchnavigatorprovisioner.attr' => 'Required attribute %1$s not found in record',
  'er.researchnavigatorprovisioner.cfg.identifier' => 'No identifier type specified',
  
  // Plugin texts
  'pl.researchnavigatorprovisioner.recordid'   => 'Record Identifier Type'
);
