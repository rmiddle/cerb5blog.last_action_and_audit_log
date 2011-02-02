
<table cellpadding="0" cellspacing="0" border="0">
  <tr>
  </tr>
</table>

<form action="{devblocks_url}{/devblocks_url}" method="POST" id="configActivity" name="configActivity" >
<input type="hidden" name="c" value="config">
<input type="hidden" name="a" value="handleTabAction">
<input type="hidden" name="tab" value="cerb5blog.last_action_and_audit_log.config.tab">
<input type="hidden" name="action" value="saveCerb5BlogAuditLog">

<h2>{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.audit.log')}</h2><br>

{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.audit.comment')}<br>
<label><input type="radio" name="al_comment_enabled" value="1" {if $al_comment_enabled}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="al_comment_enabled" value="0" {if !$al_comment_enabled}checked="checked"{/if}> {$translate->_('cerb5blog.last_action_and_audit_log.no')}</label>
<br>

{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.audit.merge.new_ticket')}<br>
<label><input type="radio" name="al_merge_enabled" value="1" {if $al_merge_enabled}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="al_merge_enabled" value="0" {if !$al_merge_enabled}checked="checked"{/if}> {$translate->_('cerb5blog.last_action_and_audit_log.no')}</label>
<br>

<br>
<br>
<h2>{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.ticket.update.field')}</h2><br>

{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.update.comment')}<br>
<label><input type="radio" name="uf_comment_enabled" value="1" {if $uf_comment_enabled}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="uf_comment_enabled" value="0" {if !$uf_comment_enabled}checked="checked"{/if}> {$translate->_('cerb5blog.last_action_and_audit_log.no')}</label>
<br>

{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.update.merge')}<br>
<label><input type="radio" name="uf_merge_enabled" value="1" {if $uf_merge_enabled}checked="checked"{/if}> {$translate->_('common.yes')|capitalize}</label>
<label><input type="radio" name="uf_merge_enabled" value="0" {if !$uf_merge_enabled}checked="checked"{/if}> {$translate->_('cerb5blog.last_action_and_audit_log.no')}</label>
<br>

{*
<br>
<br>
<h2>{$translate->_('cerb5blog.last_action_and_audit_log.config.tab.ticket.custom.field')}</h2><br>
*}
<br>
<br>
<button type="button" id="btnSubmit" onclick="genericAjaxPost('configActivity', 'feedback');"><span class="cerb-sprite sprite-check"></span> {$translate->_('common.save_changes')|capitalize}</button>
</form>
<br>
<br>
<div id="feedback" style="background-color:rgb(255,255,255);"></div>
