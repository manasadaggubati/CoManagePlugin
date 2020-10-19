<?php
/**
 * COmanage Registry Affiliation Check Enroller Fields
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.3.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

  // Determine if fields are editable
  $e = false;
  
  if(($this->action == "add" && $permissions['add']) || ($this->action == "edit" && $permissions['edit']))
    $e = true;
    
  // We shouldn't get here if we don't have at least read permission, but check just in case
  
  if(!$e && !$permissions['view'])
    return(false);

  // Add breadcrumbs
  $this->Html->addCrumb(_txt('ct.affiliation_check_enrollers.pl'), array('plugin' => 'affiliation_check_enroller', 'controller' => 'affiliation_check_enrollers', 'action' => 'index'));
  $crumbTxt = _txt('op.' . $this->action . '-a', array(_txt('ct.affiliation_check_enrollers.pl')));
  $this->Html->addCrumb($crumbTxt);
    
?>
<ul id="<?php print $this->action; ?>_affiliation_check_enrollers" class="fields form-list">
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print $this->Form->label('co_enrollment_flow_id', _txt('ct.co_enrollment_flows.1')); ?>
        <span class="required">*</span>
      </div>
    </div>
    <div class="field-info">
      <?php
        $attrs = array();
        $attrs['value'] = isset($affiliation_check_enrollers[0]['AffiliationCheckEnroller']['co_enrollment_flow_id'])
                          ? $affiliation_check_enrollers[0]['AffiliationCheckEnroller']['co_enrollment_flow_id']
                          : null;
        $attrs['empty'] = true;
        
        print $this->Form->select('co_enrollment_flow_id',
                                  $vv_enrollment_flows,
                                  $attrs);
        
        if($this->Form->isFieldError('co_enrollment_flow_id')) {
          print $this->Form->error('co_enrollment_flow_id');
        }
      ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print $this->Form->label('env_var', _txt('pl.affiliationcheckenroller.env_var')); ?>
        <span class="required">*</span>
      </div>
    </div>
    <div class="field-info">
      <?php print $this->Form->input('env_var', array('class' => 'focusFirst')); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print $this->Form->label('match_regex', _txt('pl.affiliationcheckenroller.match_regex')); ?>
        <span class="required">*</span>
      </div>
    </div>
    <div class="field-info">
      <?php print $this->Form->input('match_regex'); ?>
    </div>
  </li>
  <li>
    <div class="field-name">
      <div class="field-title">
        <?php print $this->Form->label('redirect_url', _txt('pl.affiliationcheckenroller.redirect_url')); ?>
      </div>
    </div>
    <div class="field-info">
      <?php print $this->Form->input('redirect_url'); ?>
    </div>
  </li>
  <?php if($e): ?>
    <li class="fields-submit">
      <div class="field-name">
        <span class="required"><?php print _txt('fd.req'); ?></span>
      </div>
      <div class="field-info">
        <?php print $this->Form->submit($submit_label); ?>
      </div>
    </li>
  <?php endif; ?>
</ul>
