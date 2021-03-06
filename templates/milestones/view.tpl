{$view_context = 'wgm.contexts.milestone'}
{$view_fields = $view->getColumnsAvailable()}
{assign var=results value=$view->getData()}
{assign var=total value=$results[1]}
{assign var=data value=$results[0]}
<table cellpadding="0" cellspacing="0" border="0" class="worklist" width="100%">
	<tr>
		<td nowrap="nowrap"><span class="title">{$view->name}</span></td>
		<td nowrap="nowrap" align="right">
			<a href="javascript:;" title="{'common.add'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxPopup('peek','c=internal&a=showPeekPopup&context={$view_context}&context_id=0&view_id={$view->id}',null,false,'500');"><span class="cerb-sprite2 sprite-plus-circle-frame"></span></a>
			<a href="javascript:;" title="{'common.search'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxPopup('search','c=internal&a=viewShowQuickSearchPopup&view_id={$view->id}',this,false,'400');"><span class="cerb-sprite2 sprite-document-search-result"></span></a>
			<a href="javascript:;" title="{'common.customize'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxGet('customize{$view->id}','c=internal&a=viewCustomize&id={$view->id}');toggleDiv('customize{$view->id}','block');"><span class="cerb-sprite2 sprite-gear"></span></a>
			<a href="javascript:;" title="Subtotals" class="subtotals minimal"><span class="cerb-sprite2 sprite-application-sidebar-list"></span></a>
            <a href="javascript:;" title="{$translate->_('common.import')|capitalize}" onclick="genericAjaxPopup('import','c=invoices&a=showImportPopup&context={$view_context}&view_id={$view->id}',null,false,'500');"><span class="cerb-sprite2 sprite-application-import"></span></a>
			<a href="javascript:;" title="{$translate->_('common.export')|capitalize}" onclick="genericAjaxGet('{$view->id}_tips','c=internal&a=viewShowExport&id={$view->id}');toggleDiv('{$view->id}_tips','block');"><span class="cerb-sprite2 sprite-application-export"></span></a>
			<a href="javascript:;" title="{$translate->_('common.copy')|capitalize}" onclick="genericAjaxGet('{$view->id}_tips','c=internal&a=viewShowCopy&view_id={$view->id}');toggleDiv('{$view->id}_tips','block');"><span class="cerb-sprite2 sprite-applications"></span></a>
			<a href="javascript:;" title="{'common.refresh'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewRefresh&id={$view->id}');"><span class="cerb-sprite2 sprite-arrow-circle-135-left"></span></a>
			<input type="checkbox" class="select-all">
		</td>
	</tr>
</table>

<div id="{$view->id}_tips" class="block" style="display:none;margin:10px;padding:5px;">Analyzing...</div>
<form id="customize{$view->id}" name="customize{$view->id}" action="#" onsubmit="return false;" style="display:none;"></form>
<form id="viewForm{$view->id}" name="viewForm{$view->id}" action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="view_id" value="{$view->id}">
<input type="hidden" name="context_id" value="wgm.contexts.milestone">
<input type="hidden" name="id" value="{$view->id}">
<input type="hidden" name="c" value="milestones">
<input type="hidden" name="a" value="">
<input type="hidden" name="explore_from" value="0">
<table cellpadding="1" cellspacing="0" border="0" width="100%" class="worklistBody">

	{* Column Headers *}
	<tr>
		{foreach from=$view->view_columns item=header name=headers}
			{* start table header, insert column title and link *}
			<th>
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewSortBy&id={$view->id}&sortBy={$header}');">{$view_fields.$header->db_label|capitalize}</a>
			
			{* add arrow if sorting by this column, finish table header tag *}
			{if $header==$view->renderSortBy}
				{if $view->renderSortAsc}
					<span class="cerb-sprite sprite-sort_ascending"></span>
				{else}
					<span class="cerb-sprite sprite-sort_descending"></span>
				{/if}
			{/if}
			</th>
		{/foreach}
	</tr>

	{* Column Data *}
	{foreach from=$data item=result key=idx name=results}

	{if $smarty.foreach.results.iteration % 2}
		{assign var=tableRowClass value="even"}
	{else}
		{assign var=tableRowClass value="odd"}
	{/if}
	<tbody style="cursor:pointer">
		<tr class="{$tableRowClass}">
            <td style="display:none;"><input type="checkbox" name="p_id[]" value="{$result.p_id}" style="display:none;"></td>
		{foreach from=$view->view_columns item=column name=columns}
			{if substr($column,0,3)=="cf_"}
				{include file="devblocks:cerberusweb.core::internal/custom_fields/view/cell_renderer.tpl"}
			{elseif $column=="p_id"}
				<td><input type="checkbox" name="row_id[]" value="{$result.o_id}" style="display:none;">{$result.p_id}</td>
			{elseif $column=="p_name"}
				<td>

					<a href="javascript:;" onclick="genericAjaxPopup('peek','c=internal&a=showPeekPopup&context={$view_context}&context_id={$result.p_id}&view_id={$view->id}', null, false, '600');">{$result.p_name}</a>
					<!--<button type="button" class="peek" style="visibility:hidden;padding:1px;margin:0px 5px;" onclick="genericAjaxPopup('peek','c=billing&a=handleTabAction&tab=products.tab.billing.osellot&action=showProductPanel&view_id={$view->id}&id={$result.p_id}', null, false, '600');"><span class="cerb-sprite2 sprite-document-search-result" style="margin-left:2px" title="{$translate->_('views.peek')}"></span></button>-->
				</td>
			{elseif $column=="p_recurring"}
				<td>{if $column.p_recurring == 1}Yes{else}No{/if}</td>
			{elseif $column=="p_taxable"}
				<td>{if $column.p_taxable == 1}Yes{else}No{/if}</td>
			{elseif $column=="p_price"}
				<td>{number_format($result.p_price, 2)}</td>
			{elseif $column=="p_price_setup"}
				<td>{number_format($result.p_price_setup, 2)}</td>
			{else}
				<td>{$result.$column}</td>
			{/if}
		{/foreach}
		</tr>
	</tbody>
	{/foreach}
	
	
</table>

			{math assign=fromRow equation="(x*y)+1" x=$view->renderPage y=$view->renderLimit}
			{math assign=toRow equation="(x-1)+y" x=$fromRow y=$view->renderLimit}
			{math assign=nextPage equation="x+1" x=$view->renderPage}
			{math assign=prevPage equation="x-1" x=$view->renderPage}
			{math assign=lastPage equation="ceil(x/y)-1" x=$total y=$view->renderLimit}
			
			{* Sanity checks *}
			{if $toRow > $total}{assign var=toRow value=$total}{/if}
			{if $fromRow > $toRow}{assign var=fromRow value=$toRow}{/if}
			
			{if $view->renderPage > 0}
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page=0');">&lt;&lt;</a>
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$prevPage}');">&lt;{$translate->_('common.previous_short')|capitalize}</a>
			{/if}
			({'views.showing_from_to'|devblocks_translate:$fromRow:$toRow:$total})
			{if $toRow < $total}
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$nextPage}');">{$translate->_('common.next')|capitalize}&gt;</a>
				<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$lastPage}');">&gt;&gt;</a>
			{/if}

</form>

{include file="devblocks:cerberusweb.core::internal/views/view_common_jquery_ui.tpl"}