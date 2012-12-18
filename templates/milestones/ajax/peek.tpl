<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmMilestone">
<input type="hidden" name="c" value="milestones">
<input type="hidden" name="a" value="saveMilestone">
{if !empty($view_id)}<input type="hidden" name="view_id" value="{$view_id}">{/if}
{if !empty($milestone) && !empty($milestone->id)}<input type="hidden" name="id" value="{$milestone->id}">{/if}
{if !empty($link_context)}
<input type="hidden" name="link_context" value="{$link_context}">
<input type="hidden" name="link_context_id" value="{$link_context_id}">
{/if}
<input type="hidden" name="do_delete" value="0">

<fieldset class="peek">
	<legend>{'common.properties'|devblocks_translate}</legend>

	<table cellspacing="0" cellpadding="2" border="0" width="98%">
		<tr>
			<td width="1%" nowrap="nowrap" valign="top"><b>{$translate->_('milestone.name')}</b></td>
			<td width="99%" valign="top">
				<input type="text" name="name" value="{$project->name}" style="width:98%;">
			</td>
		</tr>
		<tr>
			<td width="1%" nowrap="nowrap" valign="top"><b>{$translate->_('milestone.project')}</b></td>
			<td width="99%" valign="top">
				<input type="text" name="project_name" value="{DAO_Project::get($milestone->project_id)->name}" style="width:98%;">
			</td>
		</tr>
		
		{* Watchers *}
		<tr>
			<td width="0%" nowrap="nowrap" valign="top" align="right">{$translate->_('common.watchers')|capitalize}: </td>
			<td width="100%">
				{if empty($model->id)}
					<button type="button" class="chooser_watcher"><span class="cerb-sprite sprite-view"></span></button>
					<ul class="chooser-container bubbles" style="display:block;"></ul>
				{else}
					{$object_watchers = DAO_ContextLink::getContextLinks('wgm.contexts.milestone', array($model->id), CerberusContexts::CONTEXT_WORKER)}
					{include file="devblocks:cerberusweb.core::internal/watchers/context_follow_button.tpl" context='wgm.contexts.milestone' context_id=$model->id full=true}
				{/if}
			</td>
		</tr>

	</table>
</fieldset>

{if !empty($custom_fields)}
<fieldset class="peek">
	<legend>{'common.custom_fields'|devblocks_translate}</legend>
	{include file="devblocks:cerberusweb.core::internal/custom_fields/bulk/form.tpl" bulk=false}
</fieldset>
{/if}

{* Comment *}
{if !empty($last_comment)}
	{include file="devblocks:cerberusweb.core::internal/comments/comment.tpl" readonly=true comment=$last_comment}
{/if}

<fieldset class="peek">
	<legend>{'common.comment'|devblocks_translate|capitalize}</legend>
	<textarea name="comment" rows="5" cols="45" style="width:98%;"></textarea>
	<div class="notify" style="display:none;">
		<b>{'common.notify_watchers_and'|devblocks_translate}:</b>
		<button type="button" class="chooser_notify_worker"><span class="cerb-sprite sprite-view"></span></button>
		<ul class="chooser-container bubbles" style="display:block;"></ul>
	</div>
</fieldset>

<button type="button" onclick="genericAjaxPopupPostCloseReloadView(null,'frmMilestone','{$view_id}', false, 'call_save');"><span class="cerb-sprite2 sprite-tick-circle"></span> {$translate->_('common.save_changes')|capitalize}</button>
{if $model->id && ($active_worker->is_superuser || $active_worker->id == $model->worker_id)}<button type="button" onclick="if(confirm('Permanently delete this milestone?')) { this.form.do_delete.value='1';genericAjaxPopupPostCloseReloadView(null,'frmMilestone','{$view_id}'); } "><span class="cerb-sprite2 sprite-minus-circle"></span> {$translate->_('common.delete')|capitalize}</button>{/if}

{if !empty($model->id)}
<div style="float:right;">
	<a href="{devblocks_url}c=profiles&type=call&id={$model->id}-{$model->subject|devblocks_permalink}{/devblocks_url}">view full record</a>
</div>
<br clear="all">
{/if}
</form>

<script type="text/javascript">
	$popup = genericAjaxPopupFetch('peek');
	$popup.one('popup_open', function(event,ui) {
		$(this).dialog('option','title',"{$translate->_('milestone.add')}");
		
		$(this).find('button.chooser_watcher').each(function() {
			ajax.chooser(this,'cerberusweb.contexts.worker','add_watcher_ids', { autocomplete:true });
		});
        
        function projectAutoComplete(sel, options) {
    		var url = DevblocksAppPath+'ajax.php?c=projects&a=getProjectAutoCompletions';
    		if(null == options) options = { };

    		if(null == options.minLength)
    			options.minLength = 2;
		
    		if(null == options.autoFocus)
    			options.autoFocus = true;
		
    		if(null != options.multiple && options.multiple) {
    			options.source = function (request, response) {
    				// From the last comma (if exists)
    				var pos = request.term.lastIndexOf(',');
    				if(-1 != pos) {
    					// Split at the comma and trim
    					request.term = $.trim(request.term.substring(pos+1));
    				}
				
    				if(0==request.term.length)
    					return;
				
    				$.ajax({
    					url: url,
    					dataType: "json",
    					data: request,
    					success: function(data) {
    						response(data);
    					}
    				});
    			}
    			options.select = function(event, ui) {
    				var value = $(this).val();
    				var pos = value.lastIndexOf(',');
    				if(-1 != pos) {
    					$(this).val(value.substring(0,pos)+', '+ui.item.value+', ');
    				} else {
    					$(this).val(ui.item.value+', ');
    				}
    				return false;
    			}
			
    			options.focus = function(event, ui) {
    				// Don't replace the textbox value
    				return false;
    			}
			
    		} else {
    			options.source = url;
    		}
		
    		$(sel).autocomplete(options);
        }
        
        projectAutoComplete('#frmMilestone input:text[name=project_name]');
        
		$(this).find('textarea[name=comment]').keyup(function() {
			if($(this).val().length > 0) {
				$(this).next('DIV.notify').show();
			} else {
				$(this).next('DIV.notify').hide();
			}
		});
	});
    
	$('#frmMilestone button.chooser_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','worker_id', { autocomplete:true });
	});
	$('#frmMilestone  button.chooser_notify_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','notify_worker_ids', { autocomplete:true });
	});
</script>