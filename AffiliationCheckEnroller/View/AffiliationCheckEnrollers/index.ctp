<?php
/**
 * COmanage Registry Affiliation Check Enroller Index View
 *
 * This file should be removed when converting for v4.0.0
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.3.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

  // Add breadcrumbs
  $this->Html->addCrumb(_txt('ct.affiliation_check_enrollers.pl'));

  // Add page title
  $params = array();
  $params['title'] = _txt('ct.affiliation_check_enrollers.pl');

  // Add top links
  $params['topLinks'] = array();

  if($permissions['add']) {
    $params['topLinks'][] = $this->Html->link(
      _txt('op.add-a', array(_txt('ct.affiliation_check_enrollers.1'))),
      array(
        'plugin' => 'affiliation_check_enroller',
        'controller' => 'affiliation_check_enrollers',
        'action' => 'add'
      ),
      array('class' => 'addbutton')
    );
  }

  print $this->element("pageTitleAndButtons", $params);

?>

<div class="table-container">
  <table id="affiliation_check_enrollers">
    <thead>
      <tr>
        <th><?php print $this->Paginator->sort('env_var', _txt('pl.affiliationcheckenroller.env_var')); ?></th>
        <th><?php print $this->Paginator->sort('co_enrollment_flow_id', _txt('ct.co_enrollment_flows.1')); ?></th>
        <th><?php print _txt('fd.actions'); ?></th>
      </tr>
    </thead>

    <tbody>
      <?php $i = 0; ?>
      <?php foreach ($affiliation_check_enrollers as $c): ?>
      <tr class="line<?php print ($i % 2)+1; ?>">
        <td>
          <?php
            print $this->Html->link(
              $c['AffiliationCheckEnroller']['env_var'],
              array(
                'plugin' => 'affiliation_check_enroller',
                'controller' => 'affiliation_check_enrollers',
                'action' => 'edit',
                $c['AffiliationCheckEnroller']['id']
              )
            );
          ?>
        </td>
        <td>
          <?php
            print $this->Html->link(
              $vv_enrollment_flows[ $c['AffiliationCheckEnroller']['co_enrollment_flow_id'] ],
              array(
                'plugin' => null,
                'controller' => 'co_enrollment_flows',
                'action' => 'edit',
                $c['AffiliationCheckEnroller']['co_enrollment_flow_id']
              )
            );
          ?>
        </td>
        <td>
          <?php
            if($permissions['edit']) {
              print $this->Html->link(
                _txt('op.edit'),
                array(
                  'plugin' => 'affiliation_check_enroller',
                  'controller' => 'affiliation_check_enrollers',
                  'action' => 'edit',
                  $c['AffiliationCheckEnroller']['id']
                ),
                array('class' => 'editbutton')
              ) . "\n";
            }
            
            if($permissions['delete']) {
              print '<button type="button" class="deletebutton" title="' . _txt('op.delete')
                . '" onclick="javascript:js_confirm_generic(\''
                . _txt('js.remove') . '\',\''    // dialog body text
                . $this->Html->url(              // dialog confirm URL
                  array(
                    'plugin' => 'affiliation_check_enroller',
                    'controller' => 'affiliation_check_enrollers',
                    'action' => 'delete',
                    $c['AffiliationCheckEnroller']['id']
                  )
                ) . '\',\''
                . _txt('op.remove') . '\',\''    // dialog confirm button
                . _txt('op.cancel') . '\',\''    // dialog cancel button
                . _txt('op.remove') . '\',[\''   // dialog title
                . filter_var(_jtxt($c['AffiliationCheckEnroller']['env_var']),FILTER_SANITIZE_STRING)  // dialog body text replacement strings
                . '\']);">'
                . _txt('op.delete')
                . '</button>';
            }
          ?>
          <?php ; ?>
        </td>
      </tr>
      <?php $i++; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php
  print $this->element("pagination");
