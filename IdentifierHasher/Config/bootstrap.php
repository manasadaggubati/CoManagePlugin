<?php
/**
 * COmanage Registry Identifier Hasher Bootstrap
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @copyright     NYU Langone Health
 * @license       Not Licensed for External Use
 */

App::uses('CakeEventManager', 'Event');
App::uses('IdentifierHasherListener', 'IdentifierHasher.Lib');
CakeEventManager::instance()->attach(new IdentifierHasherListener());