{if !$no_go_reason}
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <p>Click send to send  to {$displayName|escape} in their preferred language of
    <em>{$language|escape}</em> to email address {$email}|escape}</p>
    {if array_key_exists('template', $form)}
        {$form.template.label} {$form.template.html}
    {/if}

  <hr>
    {if $subject}
      <h2>Message preview</h2>
      <div id="thank_you_subject">{$subject}</div>
      <div id="thank_you_message">{$message}</div>
    {/if}

    {* FOOTER *}
  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/if}

{if $no_go_reason}
  <p>An email cannot be sent because of {$no_go_reason|escape}</p>
{/if}
<script type="text/javascript">
    {literal}
    CRM.$(function($) {
      "use strict";
      CRM.loadPreview = function (preferred_language, contributionID, contactID, template) {
        CRM.api4("WorkflowMessage", "render", {
          "workflow": template,
          "language": preferred_language,
          "values": {"contributionID": contributionID, "contactID": contactID}
        }).then(function (results) {
          CRM.$("#thank_you_subject").text(results[0]["subject"]);
          CRM.$("#thank_you_message").html(results[0]["html"]);
        })
      }
    });
    {/literal}
</script>
