<?php
/**
 * COmanage Registry Affiliation Checker Provisioner Plugin Language File
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.3.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */
  
global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_affiliation_check_enroller_texts['en_US'] = array(
  // Titles, per-controller
  'ct.affiliation_check_enrollers.1'  => 'Affiliation Check Enroller',
  'ct.affiliation_check_enrollers.pl' => 'Affiliation Check Enrollers',
  
  // Error messages
  'er.affiliationcheckenroller.failed' => 'Check for "%1$s" failed (value: %2$s)',
  'er.affiliationcheckenroller.msg' => 'Check for "%1$s" failed (value: %2$s)',
  
  // Plugin texts
  'pl.affiliationcheckenroller.env_var' => 'Environment Variable Name',
  'pl.affiliationcheckenroller.match_regex' => 'Matching Regular Expression',
  'pl.affiliationcheckenroller.passed' => 'Check for "%1$s" passed (value: %2$s)',
  'pl.affiliationcheckenroller.redirect_url' => 'Redirect URL'
);
