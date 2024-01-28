<?php

use Civi\Api4\WorkflowMessage;
use CRM_SendMessageAction_ExtensionUtil as E;
class CRM_SendMessageAction_Form_Task_SendWorkflowMessage extends CRM_Core_Form {
use CRM_Contribute_Form_ContributeFormTrait;
use CRM_Contact_Form_ContactFormTrait;

  /**
   * Build basic form.
   *
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public function buildQuickForm(): void {
    if (!$this->getContactValue('email_primary.email') || $this->getContactValue('email_primary.on_hold')) {
      $this->assign('no_go_reason', E::ts('A usable email is required'));
    }
    $this->assign('no_go_reason');
    $this->assign('displayName', $this->getContactValue('display_name'));
    $this->assign('email', $this->getContactValue('email_primary.email'));

    $preferredLanguage = $this->getLanguage();
    $preferredLanguageString = CRM_Core_PseudoConstant::getLabel('CRM_Contact_BAO_Contact', 'preferred_language', $preferredLanguage);
    $this->assign('language', $preferredLanguageString ?? $preferredLanguage);
    $this->add('select',  'template', E::ts('Template'), $this->getAvailableTemplates(), TRUE, [
      'onClick' => 'CRM.loadPreview("' . $this->getLanguage() . '", ' . $this->getContributionID() . ',' . $this->getContactID() . ' CRM.$(this).val())',
    ]);
    $message = $this->renderMessage();
    $this->assign('subject', $message['subject']);
    $this->assign('message', $message['html']);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Send'),
        'isDefault' => TRUE,
      ],
    ]);
    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    parent::buildQuickForm();
  }

  /**
   * Submit form.
   *
   * @throws \CRM_Core_Exception
   */
  public function postProcess() {

    try {
      $message = $this->renderMessage();
      $send = [
        'subject' => $message['subject'],
        'html' => $message['html'],
        'toName' => $this->getContactValue('display_name'),
        'toEmail' => $this->getContactValue('email_primary.email'),
      ];
      [, $send['from']] = CRM_Core_BAO_Domain::getNameAndEmail();
      CRM_Utils_Mail::send($send);
      CRM_Core_Session::setStatus('Message sent', E::ts('Message Sent'), 'success');
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Message failed with error ' . $e->getMessage());
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  public function getContactID(): ?int {
    if ($this->getContributionID()) {
      return $this->getContributionValue('contact_id');
    }
    return NULL;
  }

  /**
   * @return int|mixed|string|null
   * @throws \CRM_Core_Exception
   */
  protected function getContributionID(): ?int {
    if ($this->getEntity() === 'contribution') {
      return (int) CRM_Utils_Request::retrieve('id', 'Integer', $this);
    }
    return NULL;
  }

  protected function getEntity(): string {
    return CRM_Utils_Request::retrieve('entity', 'String', $this, TRUE);
  }

  /**
   * @return void
   */
  protected function getWorkflowTemplate(): string {
    if ($this->isFormBuilt()) {
      return $this->getSubmittedValue('template');
    }
    // grab the first one as that will be the default.
    $templates = $this->getAvailableTemplates();
    return array_key_first($templates);
  }

  /**
   * @return mixed|string
   * @throws \CRM_Core_Exception
   */
  protected function getLanguage() {
    return $this->getContactValue('preferred_language') ?: 'en_US';
  }

  /**
   * @return array
   */
  public function getAvailableTemplates(): array {
    return [
      'contribution_invoice_receipt' => E::ts('Invoice'),
      'contribution_offline_receipt' => E::ts('Offline Contribution Receipt'),
      'contribution_online_receipt' => E::ts('Online Contribution Receipt'),
      'payment_or_refund_notification' => E::ts('Payment or refund notification'),
    ];
  }

  /**
   * @return array|null
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function renderMessage(): ?array {
    return WorkflowMessage::render()
      ->setWorkflow($this->getWorkflowTemplate())
      ->setLanguage($this->getLanguage())
      ->setValues(['contributionID' => $this->getContributionID(), 'contactID' => $this->getContactID()])
      ->execute()
      ->first();
  }

}
