<?php

class Cerb5BlogLastActionAndAuditLogConfigTab extends Extension_ConfigTab {
	const ID = 'cerb5blog.last_action_and_audit_log.config.tab';
	const AL_COMMENT_ENABLED = 'al_comment_enabled';
	const UF_COMMENT_ENABLED = 'uf_comment_enabled';
	const AL_MERGE_ENABLED = 'al_merge_enabled';
	const UF_MERGE_ENABLED = 'uf_merge_enabled';

  function __construct($manifest) {
      parent::__construct($manifest);
  }

	function showTab() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->cache_lifetime = "0";

		$al_comment_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','al_comment_enabled', 0));
		$tpl->assign('al_comment_enabled', $al_comment_enabled);

		$uf_comment_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','uf_comment_enabled', 0));
		$tpl->assign('uf_comment_enabled', $uf_comment_enabled);

		$al_merge_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','al_merge_enabled', 0));
		$tpl->assign('al_merge_enabled', $al_merge_enabled);

		$uf_merge_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','uf_merge_enabled', 0));
		$tpl->assign('uf_merge_enabled', $uf_merge_enabled);

		@$address = DAO_Address::get($address_id);
		@$worker_id = DAO_Worker::lookupAgentEmail($address->email);
		$tpl->display('file:' . $tpl_path . 'config.tpl');
	}

	function saveCerb5BlogAuditLogAction() {
		$settings = DevblocksPlatform::getPluginSettingsService();
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl_path = dirname(dirname(__FILE__)) . '/templates/';
		$tpl->cache_lifetime = "0";

		@$al_comment_enabled = DevblocksPlatform::importGPC($_REQUEST['al_comment_enabled'],'integer',0);
		@$uf_comment_enabled = DevblocksPlatform::importGPC($_POST['uf_comment_enabled'],'integer',0);
		@$al_merge_enabled = DevblocksPlatform::importGPC($_REQUEST['al_merge_enabled'],'integer',0);
		@$uf_merge_enabled = DevblocksPlatform::importGPC($_POST['uf_merge_enabled'],'integer',0);
		
		$settings->set('cerb5blog.last_action_and_audit_log','al_comment_enabled', $al_comment_enabled);
		$settings->set('cerb5blog.last_action_and_audit_log','uf_comment_enabled', $uf_comment_enabled);
		$settings->set('cerb5blog.last_action_and_audit_log','al_merge_enabled', $al_merge_enabled);
		$settings->set('cerb5blog.last_action_and_audit_log','uf_merge_enabled', $uf_merge_enabled);

		$tpl->display('file:' . $tpl_path . 'config_success.tpl');
	}
};

class Cerb5BlogLastActionAndAuditLogEventListener extends DevblocksEventListenerExtension {
    const ID = 'cerb5blog.last_action_and_audit_log.listeners';
    function __construct($manifest) {
        parent::__construct($manifest);
    }

    /**
     * @param Model_DevblocksEvent $event
     */
    function handleEvent(Model_DevblocksEvent $event) {
        switch($event->id) {
            case 'comment.action.create':
              $this->newTicketComment($event);
              break;

            case 'dao.ticket.update':
            	break;

            case 'ticket.reply.inbound':
            	break;

            case 'ticket.reply.outbound':
            	break;

            case 'ticket.action.merge':
              $this->mergeTicket($event);
            	break;
        }
    }

  private function newTicketComment($event) {
		DevblocksPlatform::getExtensions('cerberusweb.ticket.tab', true);
		// ticket_comment.id
		@$comment_id = $event->params['comment_id'];
		// Event context
		@$context = $event->params['context'];
		// ticket.id if context == ticket.
		@$$ticket_id = $event->params['context_id'];
		@$address_id = $event->params['address_id'];
		// text of actual comment.
		@$comment_text = $event->params['comment'];

        if(CerberusContexts::CONTEXT_TICKET != $context)
            return;
            
        if(empty($ticket_id) || empty($address_id) || empty($comment_text))
			return;

		$settings = DevblocksPlatform::getPluginSettingsService();
		
		$al_merge_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','al_merge_enabled', 0));

		if (class_exists('DAO_TicketAuditLog',true)):
			$al_comment_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','al_comment_enabled', 0));
			if($al_comment_enabled) {
				@$address = DAO_Address::get($address_id);
				@$worker_id = DAO_Worker::lookupAgentEmail($address->email);
				$fields = array(
					DAO_TicketAuditLog::TICKET_ID => $ticket_id,
					DAO_TicketAuditLog::WORKER_ID => $worker_id,
					DAO_TicketAuditLog::CHANGE_DATE => time(),
					DAO_TicketAuditLog::CHANGE_FIELD => "cerb5blog.last_action_and_audit_log.type.comment",
					DAO_TicketAuditLog::CHANGE_VALUE => substr($comment_text,0,128),
				);
				$log_id = DAO_TicketAuditLog::create($fields);
				unset($fields);
			}
		endif;

		$uf_comment_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','uf_comment_enabled', 0));
		if($uf_comment_enabled) {
			$change_fields[DAO_Ticket::UPDATED_DATE] = time();
			DAO_Ticket::update($ticket_id, $change_fields);
			unset($change_fields);
		}
	}


	private function mergeTicket($event) {
		// Listen for ticket merges and update our internal ticket_id records

		@$new_ticket_id = $event->params['new_ticket_id'];
		@$old_ticket_ids = $event->params['old_ticket_ids'];

		$translate = DevblocksPlatform::getTranslationService();

		if(empty($new_ticket_id) || empty($old_ticket_ids))
			return;

		$settings = DevblocksPlatform::getPluginSettingsService();
		$al_merge_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','al_merge_enabled', 0));
		$uf_merge_enabled = intval($settings->get('cerb5blog.last_action_and_audit_log','uf_merge_enabled', 0));

		if(!($al_merge_enabled || $uf_merge_enabled))
			return;

		$active_worker = CerberusApplication::getActiveWorker();
		$worker_id = $active_worker->id;

		if (class_exists('DAO_TicketAuditLog',true)):
			if($al_merge_enabled) {
				foreach($old_ticket_ids as $old_id) {
					$old_ticket = DAO_Ticket::get($old_id);
					$translate_str = $translate->_('cerb5blog.last_action_and_audit_log.post.merge.new_ticket');
					$translated = sprintf($translate_str,$old_id, $old_ticket->mask);

					$fields = array(
						DAO_TicketAuditLog::TICKET_ID => $new_ticket_id,
						DAO_TicketAuditLog::WORKER_ID => $worker_id,
						DAO_TicketAuditLog::CHANGE_DATE => time(),
						DAO_TicketAuditLog::CHANGE_FIELD => "cerb5blog.last_action_and_audit_log.type.merge",
						DAO_TicketAuditLog::CHANGE_VALUE => substr($translated,0,128),
					);
					$log_id = DAO_TicketAuditLog::create($fields);
				}
				unset($fields);
			}
		endif;

		if($uf_merge_enabled) {
			$new_change_fields[DAO_Ticket::UPDATED_DATE] = time();
			DAO_Ticket::update($new_ticket_id, $new_change_fields);
			unset($new_change_fields);
		}
	}

};
