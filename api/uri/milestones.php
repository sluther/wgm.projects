<?php

class WgmMilestonesPage extends CerberusPageExtension {

	function saveMilestoneAction() {
		$active_worker = CerberusApplication::getActiveWorker();

		// Make sure we're an active worker
		if(empty($active_worker) || empty($active_worker->id))
			return;

		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		@$do_delete = DevblocksPlatform::importGPC($_REQUEST['do_delete'],'integer',0);
		@$name = DevblocksPlatform::importGPC($_REQUEST['name'],'string','');
        @$project_name = DevblocksPlatform::importGPC($_REQUEST['project_name'], 'string', '');
        
        $project = DAO_Project::getByName($project_name);
        
		$fields = array(
			DAO_Milestone::NAME => $name,
            DAO_Milestone::PROJECT_ID => $project->id
            // DAO_Milestone::UPDATED_DATE => time(),
		);

		if(empty($id)) {
            // $fields[DAO_Milestone::CREATED_DATE] = time();
			DAO_Milestone::create($fields);
		} else {
			DAO_Milestone::update($id, $fields);
		}
		var_dump($fields);
	}

}