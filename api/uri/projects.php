<?php

class WgmProjectsPage extends CerberusPageExtension {
    
	function saveProjectAction() {
		$active_worker = CerberusApplication::getActiveWorker();
        
		// Make sure we're an active worker
		if(empty($active_worker) || empty($active_worker->id))
			return;
        
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);
		@$name = DevblocksPlatform::importGPC($_POST['name'],'string','');

		$fields = array(
			DAO_Project::NAME => $name,
            // DAO_Project::UPDATED_DATE => time(),
		);
        
		if(empty($id)) {
            // $fields[DAO_Project::CREATED_DATE] = time();
			DAO_Project::create($fields);
		} else {
			DAO_Project::update($id, $fields);
		}
	}
	
	function getProjectAutoCompletionsAction() {
		@$starts_with = DevblocksPlatform::importGPC($_REQUEST['term'],'string','');
		@$callback = DevblocksPlatform::importGPC($_REQUEST['callback'],'string','');
		
		list($projects,$null) = DAO_Project::search(
			array(),
			array(
				new DevblocksSearchCriteria(SearchFields_Project::NAME,DevblocksSearchCriteria::OPER_LIKE, $starts_with. '*'),
			),
			25,
		    0,
			SearchFields_Project::NAME,
		    true,
		    false
		);

		$list = array();

		foreach($projects AS $val){
			$list[] = $val[SearchFields_Project::NAME];
		}
		
		echo sprintf("%s%s%s",
			!empty($callback) ? ($callback.'(') : '',
			json_encode($list),
			!empty($callback) ? (')') : ''
		);
		exit;
	}
	
}