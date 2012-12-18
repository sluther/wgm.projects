<?php

class DAO_Milestone extends C4_ORMHelper {
	const ID = 'id';
	const NAME = 'name';
	const PROJECT_ID = 'project_id';
	const POSITION = 'position';

	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "INSERT INTO milestone () VALUES ()";
		$db->Execute($sql);
		$id = $db->LastInsertId();
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($ids, $fields) {
		parent::_update($ids, 'milestone', $fields);
		
		// Log the context update
	    //DevblocksPlatform::markContextChanged('example.context', $ids);
	}
	
	static function updateWhere($fields, $where) {
		parent::_updateWhere('milestone', $fields, $where);
	}
	
	/**
	 * @param string $where
	 * @param mixed $sortBy
	 * @param mixed $sortAsc
	 * @param integer $limit
	 * @return Model_Milestone[]
	 */
	static function getWhere($where=null, $sortBy=null, $sortAsc=true, $limit=null) {
		$db = DevblocksPlatform::getDatabaseService();

		list($where_sql, $sort_sql, $limit_sql) = self::_getWhereSQL($where, $sortBy, $sortAsc, $limit);
		
		// SQL
		$sql = "SELECT id, name, project_id, position ".
			"FROM milestone ".
			$where_sql.
			$sort_sql.
			$limit_sql
		;
		$rs = $db->Execute($sql);
		
		return self::_getObjectsFromResult($rs);
	}

	/**
	 * @param integer $id
	 * @return Model_Milestone	 */
	static function get($id) {
		$objects = self::getWhere(sprintf("%s = %d",
			self::ID,
			$id
		));
		
		if(isset($objects[$id]))
			return $objects[$id];
		
		return null;
	}
	
	/**
	 * @param resource $rs
	 * @return Model_Milestone[]
	 */
	static private function _getObjectsFromResult($rs) {
		$objects = array();
		
		while($row = mysql_fetch_assoc($rs)) {
			$object = new Model_Milestone();
			$object->id = $row['id'];
			$object->name = $row['name'];
			$object->project_id = $row['project_id'];
			$object->position = $row['position'];
			$objects[$object->id] = $object;
		}
		
		mysql_free_result($rs);
		
		return $objects;
	}
	
	static function delete($ids) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($ids))
			return;
		
		$ids_list = implode(',', $ids);
		
		$db->Execute(sprintf("DELETE FROM milestone WHERE id IN (%s)", $ids_list));
		
		// Fire event
		/*
	    $eventMgr = DevblocksPlatform::getEventService();
	    $eventMgr->trigger(
	        new Model_DevblocksEvent(
	            'context.delete',
                array(
                	'context' => 'cerberusweb.contexts.',
                	'context_ids' => $ids
                )
            )
	    );
	    */
		
		return true;
	}
	
	public static function getSearchQueryComponents($columns, $params, $sortBy=null, $sortAsc=null) {
		$fields = SearchFields_Milestone::getFields();
		
		// Sanitize
		if('*'==substr($sortBy,0,1) || !isset($fields[$sortBy]))
			$sortBy=null;

        list($tables,$wheres) = parent::_parseSearchParams($params, $columns, $fields, $sortBy);
		
		$select_sql = sprintf("SELECT ".
			"milestone.id as %s, ".
			"milestone.name as %s, ".
			"milestone.project_id as %s, ".
			"milestone.position as %s ",
				SearchFields_Milestone::ID,
				SearchFields_Milestone::NAME,
				SearchFields_Milestone::PROJECT_ID,
				SearchFields_Milestone::POSITION
			);
			
		$join_sql = "FROM milestone ";
		
		// Custom field joins
		//list($select_sql, $join_sql, $has_multiple_values) = self::_appendSelectJoinSqlForCustomFieldTables(
		//	$tables,
		//	$params,
		//	'milestone.id',
		//	$select_sql,
		//	$join_sql
		//);
		$has_multiple_values = false; // [TODO] Temporary when custom fields disabled
				
		$where_sql = "".
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "WHERE 1 ");
			
		$sort_sql = (!empty($sortBy)) ? sprintf("ORDER BY %s %s ",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : " ";
	
		// Virtuals
		
		$args = array(
			'join_sql' => &$join_sql,
			'where_sql' => &$where_sql,
			'has_multiple_values' => &$has_multiple_values
		);
	
		array_walk_recursive(
			$params,
			array('DAO_Milestone', '_translateVirtualParameters'),
			$args
		);
		
		return array(
			'primary_table' => 'milestone',
			'select' => $select_sql,
			'join' => $join_sql,
			'where' => $where_sql,
			'has_multiple_values' => $has_multiple_values,
			'sort' => $sort_sql,
		);
	}
	
	private static function _translateVirtualParameters($param, $key, &$args) {
		if(!is_a($param, 'DevblocksSearchCriteria'))
			return;
			
		//$from_context = CerberusContexts::CONTEXT_EXAMPLE;
		//$from_index = 'example.id';
		
		$param_key = $param->field;
		settype($param_key, 'string');
		
		switch($param_key) {
			/*
			case SearchFields_EXAMPLE::VIRTUAL_WATCHERS:
				$args['has_multiple_values'] = true;
				self::_searchComponentsVirtualWatchers($param, $from_context, $from_index, $args['join_sql'], $args['where_sql']);
				break;
			*/
		}
	}
	
    /**
     * Enter description here...
     *
     * @param array $columns
     * @param DevblocksSearchCriteria[] $params
     * @param integer $limit
     * @param integer $page
     * @param string $sortBy
     * @param boolean $sortAsc
     * @param boolean $withCounts
     * @return array
     */
    static function search($columns, $params, $limit=10, $page=0, $sortBy=null, $sortAsc=null, $withCounts=true) {
		$db = DevblocksPlatform::getDatabaseService();
		
		// Build search queries
		$query_parts = self::getSearchQueryComponents($columns,$params,$sortBy,$sortAsc);

		$select_sql = $query_parts['select'];
		$join_sql = $query_parts['join'];
		$where_sql = $query_parts['where'];
		$has_multiple_values = $query_parts['has_multiple_values'];
		$sort_sql = $query_parts['sort'];
		
		$sql = 
			$select_sql.
			$join_sql.
			$where_sql.
			($has_multiple_values ? 'GROUP BY milestone.id ' : '').
			$sort_sql;
			
		if($limit > 0) {
    		$rs = $db->SelectLimit($sql,$limit,$page*$limit) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		} else {
		    $rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
            $total = mysql_num_rows($rs);
		}
		
		$results = array();
		$total = -1;
		
		while($row = mysql_fetch_assoc($rs)) {
			$result = array();
			foreach($row as $f => $v) {
				$result[$f] = $v;
			}
			$object_id = intval($row[SearchFields_Milestone::ID]);
			$results[$object_id] = $result;
		}

		// [JAS]: Count all
		if($withCounts) {
			$count_sql = 
				($has_multiple_values ? "SELECT COUNT(DISTINCT milestone.id) " : "SELECT COUNT(milestone.id) ").
				$join_sql.
				$where_sql;
			$total = $db->GetOne($count_sql);
		}
		
		mysql_free_result($rs);
		
		return array($results,$total);
	}

};

class SearchFields_Milestone implements IDevblocksSearchFields {
	const ID = 'm_id';
	const NAME = 'm_name';
	const PROJECT_ID = 'm_project_id';
	const POSITION = 'm_position';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		$translate = DevblocksPlatform::getTranslationService();
		
		$columns = array(
			self::ID => new DevblocksSearchField(self::ID, 'milestone', 'id', $translate->_('common.id')),
			self::NAME => new DevblocksSearchField(self::NAME, 'milestone', 'name', $translate->_('milestone.name')),
			self::PROJECT_ID => new DevblocksSearchField(self::PROJECT_ID, 'milestone', 'project_id', $translate->_('milestone.project')),
			self::POSITION => new DevblocksSearchField(self::POSITION, 'milestone', 'position', $translate->_('milestone.position')),
		);
		
		// Custom Fields
		//$fields = DAO_CustomField::getByContext(CerberusContexts::XXX);

		//if(is_array($fields))
		//foreach($fields as $field_id => $field) {
		//	$key = 'cf_'.$field_id;
		//	$columns[$key] = new DevblocksSearchField($key,$key,'field_value',$field->name,$field->type);
		//}
		
		// Sort by label (translation-conscious)
		DevblocksPlatform::sortObjects($columns, 'db_label');

		return $columns;		
	}
};

class Model_Milestone {
	public $id;
	public $name;
	public $project_id;
	public $position;
};

class View_Milestone extends C4_AbstractView implements IAbstractView_Subtotals {
	const DEFAULT_ID = 'milestone';

	function __construct() {
		$translate = DevblocksPlatform::getTranslationService();
	
		$this->id = self::DEFAULT_ID;
		// [TODO] Name the worklist view
		$this->name = $translate->_('Milestone');
		$this->renderLimit = 25;
		$this->renderSortBy = SearchFields_Milestone::ID;
		$this->renderSortAsc = true;

		$this->view_columns = array(
			SearchFields_Milestone::NAME,
			SearchFields_Milestone::PROJECT_ID,
			SearchFields_Milestone::POSITION,
		);
		// [TODO] Filter fields
		$this->addColumnsHidden(array(
			SearchFields_Milestone::ID
		));
		
		// [TODO] Filter fields
		$this->addParamsHidden(array(
		));
		
		$this->doResetCriteria();
	}

	function getData() {
		$objects = DAO_Milestone::search(
			$this->view_columns,
			$this->getParams(),
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc,
			$this->renderTotal
		);
		return $objects;
	}
	
	function getDataAsObjects($ids=null) {
		return $this->_getDataAsObjects('DAO_Milestone', $ids);
	}
	
	function getDataSample($size) {
		return $this->_doGetDataSample('DAO_Milestone', $size);
	}

	function getSubtotalFields() {
		$all_fields = $this->getParamsAvailable();
		
		$fields = array();

		if(is_array($all_fields))
		foreach($all_fields as $field_key => $field_model) {
			$pass = false;
			
			switch($field_key) {
				// Fields
//				case SearchFields_Milestone::EXAMPLE:
//					$pass = true;
//					break;
					
				// Virtuals
//				case SearchFields_Milestone::VIRTUAL_CONTEXT_LINK:
//				case SearchFields_Milestone::VIRTUAL_WATCHERS:
//					$pass = true;
//					break;
					
				// Valid custom fields
				default:
					if('cf_' == substr($field_key,0,3))
						$pass = $this->_canSubtotalCustomField($field_key);
					break;
			}
			
			if($pass)
				$fields[$field_key] = $field_model;
		}
		
		return $fields;
	}
	
	function getSubtotalCounts($column) {
		$counts = array();
		$fields = $this->getFields();

		if(!isset($fields[$column]))
			return array();
		
		switch($column) {
//			case SearchFields_Milestone::EXAMPLE_BOOL:
//				$counts = $this->_getSubtotalCountForBooleanColumn('DAO_Milestone', $column);
//				break;

//			case SearchFields_Milestone::EXAMPLE_STRING:
//				$counts = $this->_getSubtotalCountForStringColumn('DAO_Milestone', $column);
//				break;
				
//			case SearchFields_Milestone::VIRTUAL_CONTEXT_LINK:
//				$counts = $this->_getSubtotalCountForContextLinkColumn('DAO_Milestone', 'example.context', $column);
//				break;
				
//			case SearchFields_Milestone::VIRTUAL_WATCHERS:
//				$counts = $this->_getSubtotalCountForWatcherColumn('DAO_Milestone', $column);
//				break;
			
			default:
				// Custom fields
				if('cf_' == substr($column,0,3)) {
					$counts = $this->_getSubtotalCountForCustomColumn('DAO_Milestone', $column, 'milestone.id');
				}
				
				break;
		}
		
		return $counts;
	}	
	
	function render() {
		$this->_sanitize();
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);
		$tpl->assign('view', $this);

		// Custom fields
		//$custom_fields = DAO_CustomField::getByContext(CerberusContexts::XXX);
		//$tpl->assign('custom_fields', $custom_fields);

		// [TODO] Set your template path
		$tpl->assign('view_template', 'devblocks:wgm.projects::milestones/view.tpl');
		$tpl->display('devblocks:cerberusweb.core::internal/views/subtotals_and_view.tpl');
	}

	function renderCriteria($field) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->assign('id', $this->id);

		// [TODO] Move the fields into the proper data type
		switch($field) {
			case SearchFields_Milestone::ID:
			case SearchFields_Milestone::NAME:
			case SearchFields_Milestone::PROJECT_ID:
			case SearchFields_Milestone::POSITION:
			case 'placeholder_string':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__string.tpl');
				break;
				
			case 'placeholder_number':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__number.tpl');
				break;
				
			case 'placeholder_bool':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__bool.tpl');
				break;
				
			case 'placeholder_date':
				$tpl->display('devblocks:cerberusweb.core::internal/views/criteria/__date.tpl');
				break;
				
			/*
			default:
				// Custom Fields
				if('cf_' == substr($field,0,3)) {
					$this->_renderCriteriaCustomField($tpl, substr($field,3));
				} else {
					echo ' ';
				}
				break;
			*/
		}
	}

	function renderCriteriaParam($param) {
		$field = $param->field;
		$values = !is_array($param->value) ? array($param->value) : $param->value;

		switch($field) {
			default:
				parent::renderCriteriaParam($param);
				break;
		}
	}

	function renderVirtualCriteria($param) {
		$key = $param->field;
		
		$translate = DevblocksPlatform::getTranslationService();
		
		switch($key) {
		}
	}

	function getFields() {
		return SearchFields_Milestone::getFields();
	}

	function doSetCriteria($field, $oper, $value) {
		$criteria = null;

		// [TODO] Move fields into the right data type
		switch($field) {
			case SearchFields_Milestone::ID:
			case SearchFields_Milestone::NAME:
			case SearchFields_Milestone::PROJECT_ID:
			case SearchFields_Milestone::POSITION:
			case 'placeholder_string':
				$criteria = $this->_doSetCriteriaString($field, $oper, $value);
				break;
				
			case 'placeholder_number':
				$criteria = new DevblocksSearchCriteria($field,$oper,$value);
				break;
				
			case 'placeholder_date':
				$criteria = $this->_doSetCriteriaDate($field, $oper);
				break;
				
			case 'placeholder_bool':
				@$bool = DevblocksPlatform::importGPC($_REQUEST['bool'],'integer',1);
				$criteria = new DevblocksSearchCriteria($field,$oper,$bool);
				break;
				
			/*
			default:
				// Custom Fields
				if(substr($field,0,3)=='cf_') {
					$criteria = $this->_doSetCriteriaCustomField($field, substr($field,3));
				}
				break;
			*/
		}

		if(!empty($criteria)) {
			$this->addParam($criteria, $field);
			$this->renderPage = 0;
		}
	}
		
	function doBulkUpdate($filter, $do, $ids=array()) {
		@set_time_limit(600); // 10m
	
		$change_fields = array();
		$custom_fields = array();

		// Make sure we have actions
		if(empty($do))
			return;

		// Make sure we have checked items if we want a checked list
		if(0 == strcasecmp($filter,"checks") && empty($ids))
			return;
			
		if(is_array($do))
		foreach($do as $k => $v) {
			switch($k) {
				// [TODO] Implement actions
				case 'example':
					//$change_fields[DAO_Milestone::EXAMPLE] = 'some value';
					break;
				/*
				default:
					// Custom fields
					if(substr($k,0,3)=="cf_") {
						$custom_fields[substr($k,3)] = $v;
					}
					break;
				*/
			}
		}

		$pg = 0;

		if(empty($ids))
		do {
			list($objects,$null) = DAO_Milestone::search(
				array(),
				$this->getParams(),
				100,
				$pg++,
				SearchFields_Milestone::ID,
				true,
				false
			);
			$ids = array_merge($ids, array_keys($objects));
			 
		} while(!empty($objects));

		$batch_total = count($ids);
		for($x=0;$x<=$batch_total;$x+=100) {
			$batch_ids = array_slice($ids,$x,100);
			
			if(!empty($change_fields)) {
				DAO_Milestone::update($batch_ids, $change_fields);
			}

			// Custom Fields
			//self::_doBulkSetCustomFields(ChCustomFieldSource_Milestone::ID, $custom_fields, $batch_ids);
			
			unset($batch_ids);
		}

		unset($ids);
	}			
};

class Context_Milestone extends Extension_DevblocksContext implements IDevblocksContextProfile, IDevblocksContextPeek, IDevblocksContextImport {
	function authorize($context_id, Model_Worker $worker) {
		return TRUE;
	}
	
	function getRandom() {
		return DAO_Milestone::random();
	}

	function profileGetUrl($context_id) {
		if(empty($context_id))
			return '';
		
		$url_writer = DevblocksPlatform::getUrlService();
		$url = $url_writer->writeNoProxy(sprintf("c=profiles&type=milestone&id=%d", $context_id, true));
		return $url;
	}
	
	function getMeta($context_id) {
		$milestone = DAO_Milestone::get($context_id);
		
		$url = $this->profileGetUrl($context_id);
		$friendly = DevblocksPlatform::strToPermalink($milestone->name);
		
		if(!empty($friendly))
			$url .= '-' . $friendly;
		
		return array(
			'id' => $milestone->id,
			'number' => $milestone->name,
			'permalink' => $url,
		);
	}
	
	function getContext($milestone, &$token_labels, &$token_values, $prefix=null) {
		if(is_null($prefix))
			$prefix = 'Milestone:';
		
		$translate = DevblocksPlatform::getTranslationService();
		$fields = DAO_CustomField::getByContext(CerberusContexts::CONTEXT_KB_ARTICLE);
		
		// Polymorph
		if(is_numeric($milestone)) {
			$milestone = DAO_Milestone::get($milestone);
		} elseif($milestone instanceof Model_Milestone) {
			// It's what we want already.
		} else {
			$milestone = null;
		}
		/* @var $milestone Model_Milestone */
			
		// Token labels
		$token_labels = array(
			'id' => $prefix.$translate->_('common.id'),
			'name' => $prefix.$translate->_('milestone.name'),
			'record_url' => $prefix.$translate->_('common.url.record'),
		);
		
		if(is_array($fields))
		foreach($fields as $cf_id => $field) {
			$token_labels['custom_'.$cf_id] = $prefix.$field->name;
		}

		// Token values
		$token_values = array();
		
		$token_values['_context'] = CerberusContexts::CONTEXT_KB_ARTICLE;
		
		// Token values
		if(null != $milestone) {
			$token_values['_label'] = $milestone->name;
			$token_values['id'] = $milestone->id;
			$token_values['name'] = $milestone->name;
			// [TODO] Add the rest
			
			
			// URL
			$url_writer = DevblocksPlatform::getUrlService();
			$token_values['record_url'] = $url_writer->writeNoProxy(sprintf("c=profiles&type=milestone&id=%d-%s",$milestone->id, DevblocksPlatform::strToPermalink($milestone->title)), true);
		}
		
		return TRUE;
	}

	function lazyLoadContextValues($token, $dictionary) {
		if(!isset($dictionary['id']))
			return;
		
		$context = CerberusContexts::CONTEXT_KB_ARTICLE;
		$context_id = $dictionary['id'];
		
		@$is_loaded = $dictionary['_loaded'];
		$values = array();
		
		if(!$is_loaded) {
			$labels = array();
			CerberusContexts::getContext($context, $context_id, $labels, $values);
		}
		
		switch($token) {
			case 'watchers':
				$watchers = array(
					$token => CerberusContexts::getWatchers($context, $context_id, true),
				);
				$values = array_merge($values, $watchers);
				break;
				
			default:
				if(substr($token,0,7) == 'custom_') {
					$fields = $this->_lazyLoadCustomFields($context, $context_id);
					$values = array_merge($values, $fields);
				}
				break;
		}
		
		return $values;
	}	
	
	function getChooserView($view_id=null) {
		$active_worker = CerberusApplication::getActiveWorker();

		if(empty($view_id))
			$view_id = 'chooser_'.str_replace('.','_',$this->id).time().mt_rand(0,9999);
		
		// View
		$defaults = new C4_AbstractViewModel();
		$defaults->id = $view_id;
		$defaults->is_ephemeral = true;
		$defaults->class_name = $this->getViewClass();
		$view = C4_AbstractViewLoader::getView($view_id, $defaults);
//		$view->name = 'Headlines';
//		$view->view_columns = array(
//			SearchFields_CallEntry::IS_OUTGOING,
//			SearchFields_CallEntry::PHONE,
//			SearchFields_CallEntry::UPDATED_DATE,
//		);
		$view->addParams(array(
			//SearchFields_Milestone::IS_CLOSED => new DevblocksSearchCriteria(SearchFields_Milestone::IS_CLOSED,'=',0),
		), true);
		$view->renderSortBy = SearchFields_Milestone::NAME;
		$view->renderSortAsc = false;
		$view->renderLimit = 10;
		$view->renderFilters = false;
		$view->renderTemplate = 'contextlinks_chooser';
		
		C4_AbstractViewLoader::setView($view_id, $view);
		return $view;
	}
	
	function getView($context=null, $context_id=null, $options=array()) {
		$view_id = str_replace('.','_',$this->id);
		
		$defaults = new C4_AbstractViewModel();
		$defaults->id = $view_id; 
		$defaults->class_name = $this->getViewClass();
		$view = C4_AbstractViewLoader::getView($view_id, $defaults);
		
		$params_req = array();
		
		if(!empty($context) && !empty($context_id)) {
			$params_req = array(
				new DevblocksSearchCriteria(SearchFields_Milestone::CONTEXT_LINK,'=',$context),
				new DevblocksSearchCriteria(SearchFields_Milestone::CONTEXT_LINK_ID,'=',$context_id),
			);
		}
		
		$view->addParamsRequired($params_req, true);
		
		$view->renderTemplate = 'context';
		C4_AbstractViewLoader::setView($view_id, $view);
		return $view;
	}
	
	function renderPeekPopup($context_id=0, $view_id='') {
		$tpl = DevblocksPlatform::getTemplateService();
		
		if(!empty($context_id)) {
			$milestone = DAO_Milestone::get($context_id);
			$tpl->assign('milestone', $milestone);
		}
		
		if(!empty($view_id))
			$tpl->assign('view_id', $view_id);
			
		$tpl->display('devblocks:wgm.projects::milestones/ajax/peek.tpl');
	}
	
	function importGetKeys() {
		// [TODO] Translate
		
		$keys = array(
			'amount' => array(
				'label' => 'Amount',
				'type' => Model_CustomField::TYPE_NUMBER,
				'param' => SearchFields_CrmOpportunity::AMOUNT,
			),
			'closed_date' => array(
				'label' => 'Closed Date',
				'type' => Model_CustomField::TYPE_DATE,
				'param' => SearchFields_CrmOpportunity::CLOSED_DATE,
			),
			'created_date' => array(
				'label' => 'Created Date',
				'type' => Model_CustomField::TYPE_DATE,
				'param' => SearchFields_CrmOpportunity::CREATED_DATE,
			),
			'is_closed' => array(
				'label' => 'Is Closed',
				'type' => Model_CustomField::TYPE_CHECKBOX,
				'param' => SearchFields_CrmOpportunity::IS_CLOSED,
			),
			'is_won' => array(
				'label' => 'Is Won',
				'type' => Model_CustomField::TYPE_CHECKBOX,
				'param' => SearchFields_CrmOpportunity::IS_WON,
			),
			'name' => array(
				'label' => 'Name',
				'type' => Model_CustomField::TYPE_SINGLE_LINE,
				'param' => SearchFields_CrmOpportunity::NAME,
			),
			'primary_email_id' => array(
				'label' => 'Email',
				'type' => 'ctx_' . CerberusContexts::CONTEXT_ADDRESS,
				'param' => SearchFields_CrmOpportunity::PRIMARY_EMAIL_ID,
				'required' => true,
			),
			'updated_date' => array(
				'label' => 'Updated Date',
				'type' => Model_CustomField::TYPE_DATE,
				'param' => SearchFields_CrmOpportunity::UPDATED_DATE,
			),
		);
		
		$cfields = DAO_CustomField::getByContext(CerberusContexts::CONTEXT_OPPORTUNITY);
		
		foreach($cfields as $cfield_id => $cfield) {
			$keys['cf_' . $cfield_id] = array(
				'label' => $cfield->name,
				'type' => $cfield->type,
				'param' => 'cf_' . $cfield_id, 
			);
		}
		
		DevblocksPlatform::sortObjects($keys, '[label]', true);
		
		return $keys;
	}
	
	function importKeyValue($key, $value) {
		switch($key) {
		}

		return $value;
	}
	
	function importSaveObject(array $fields, array $custom_fields, array $meta) {
		// Default these fields
		if(!isset($fields[DAO_CrmOpportunity::UPDATED_DATE]))
			$fields[DAO_CrmOpportunity::UPDATED_DATE] = time();

		// If new...
		if(!isset($meta['object_id']) || empty($meta['object_id'])) {
			// Make sure we have an opp name
			if(!isset($fields[DAO_CrmOpportunity::NAME])) {
				$fields[DAO_CrmOpportunity::NAME] = 'New ' . $this->manifest->name;
			}
			
			// Default the created date to now
			if(!isset($fields[DAO_CrmOpportunity::CREATED_DATE]))
				$fields[DAO_CrmOpportunity::CREATED_DATE] = time();
			
			// Create
			$meta['object_id'] = DAO_CrmOpportunity::create($fields);
			
		} else {
			// Update
			DAO_CrmOpportunity::update($meta['object_id'], $fields);
		}
		
		// Custom fields
		if(!empty($custom_fields) && !empty($meta['object_id'])) {
			DAO_CustomFieldValue::formatAndSetFieldValues($this->manifest->id, $meta['object_id'], $custom_fields, false, true, true); //$is_blank_unset (4th)
		}
	}
};