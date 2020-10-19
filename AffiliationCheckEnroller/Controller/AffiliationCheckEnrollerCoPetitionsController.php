<?php
/**
 * COmanage Registry Affiliation Check Enroller Controller
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.3.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

App::uses('CoPetitionsController', 'Controller');

class AffiliationCheckEnrollerCoPetitionsController extends CoPetitionsController {
  // Class name, used by Cake
  public $name = "AffiliationCheckEnrollerCoPetitions";

  public $uses = array("CoPetition",
                       "AffiliationCheckEnroller.AffiliationCheckEnroller");
  
  /**
   * Start a new CO Petition
   *
   * @since  COmanage Registry v3.3.0
   */
  
  protected function execute_plugin_collectIdentifier($id, $onFinish) {
    // Because this plugin is written for 3.3.0, configuration is global to the platform.
    // Configuration parsing will need to be rewritten (somewhat) for v4.0.0.
    
    // We need to determine what the current Enrollment Flow is
    
    $efId = $this->CoPetition->field('co_enrollment_flow_id', array('CoPetition.id' => $id));
    
    if(!$efId) {
      throw new RuntimeException(_txt('er.notfound', array(_txt('ct.co_enrollment_flows.1'), $id)));
    }
    
    // Do we have any checks to perform?
    
    $args = array();
    $args['conditions']['AffiliationCheckEnroller.co_enrollment_flow_id'] = $efId;
    $args['contain'] = false;
    
    $checks = $this->AffiliationCheckEnroller->find('all', $args);
    
    foreach($checks as $check) {
      // First pull the value for the specified variable
      
      $val = getenv($check['AffiliationCheckEnroller']['env_var']);
      
      $matched = preg_match($check['AffiliationCheckEnroller']['match_regex'], $val);
      
      // Record petition history regardless of outcome
      
      $pAction = PetitionActionEnum::CommentAdded;
      $pTxt = _txt('pl.affiliationcheckenroller.passed', array($check['AffiliationCheckEnroller']['env_var'], $val));
      
      if(!$matched) {
        $pAction = PetitionActionEnum::EligibilityFailed;
        $pTxt = _txt('er.affiliationcheckenroller.failed', array($check['AffiliationCheckEnroller']['env_var'], $val));
      }
      
      $this->CoPetition->CoPetitionHistoryRecord->record($id,
                                                         $this->Session->read('Auth.User.co_person_id'),
                                                         $pAction,
                                                         $pTxt);
      
      if(!$matched) {
        // Flag the petition as denied
        
        $this->CoPetition->updateStatus($id,
                                        PetitionStatusEnum::Denied,
                                        $this->Session->read('Auth.User.co_person_id'));
        
        // If there is a redirect URL send the enrollee there, otherwise set a
        // default flash message and redirect to root
        
        if(!empty($check['AffiliationCheckEnroller']['redirect_url'])) {
          $this->redirect($check['AffiliationCheckEnroller']['redirect_url']);
        } else {
          $this->Flash->set(_txt('er.affiliationcheckenroller.msg', array($check['AffiliationCheckEnroller']['env_var'], $val)), array('key' => 'error'));
          $this->redirect('/');
        }
      }
      // else this check succeeded, continue
    }
    
    // The step is done
    $this->redirect($onFinish);
  }
}
