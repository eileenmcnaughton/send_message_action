<?php

require_once 'send_message_action.civix.php';
// phpcs:disable
use CRM_SendMessageAction_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function send_message_action_civicrm_config(&$config): void {
  _send_message_action_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function send_message_action_civicrm_install(): void {
  _send_message_action_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function send_message_action_civicrm_enable(): void {
  _send_message_action_civix_civicrm_enable();
}

function send_message_action_civicrm_links(string $op, ?string $objectName, $objectID, array &$links, ?int &$mask, array &$values) {
  if ($objectName === 'Contribution') {
    $links[] = [
      'title' => ts('Send workflow template'),
      'label' => ts('Send workflow template'),
      'name' => 'send_workflow',
      'url' => 'civicrm/task/send_message',
      'qs' => 'reset=1&id=%%id%%&entity=contribution',
      'weight' => 200,
      'is_single_mode' => TRUE,
      'bit' => 403,
    ];
  }
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function send_message_action_civicrm_preProcess($formName, &$form): void {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function send_message_action_civicrm_navigationMenu(&$menu): void {
//  _send_message_action_civix_insert_navigation_menu($menu, 'Mailings', [
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ]);
//  _send_message_action_civix_navigationMenu($menu);
//}
