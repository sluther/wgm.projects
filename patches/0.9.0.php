<?php
$db = DevblocksPlatform::getDatabaseService();
$tables = $db->metaTables();

// project ========================
if(!isset($tables['project'])) {
	$sql = sprintf("
		CREATE TABLE IF NOT EXISTS project (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL DEFAULT '',
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// milestone ========================
if(!isset($tables['milestone'])) {
	$sql = sprintf("
		CREATE TABLE milestone (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL DEFAULT '',
            project_id INT UNSIGNED NOT NULL DEFAULT 0,
            position INT UNSIGNED NOT NULL DEFAULT 0,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}

// issue ========================
if(!isset($tables['issue'])) {
	$sql = sprintf("
		CREATE TABLE issue (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            type INT UNSIGNED NOT NULL DEFAULT 0,
            milestone_id INT UNSIGNED NOT NULL DEFAULT 0,
            is_closed INT UNSIGNED NOT NULL DEFAULT 0,
            created_date INT UNSIGNED NOT NULL DEFAULT 0,
            updated_date INT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(255) NOT NULL DEFAULT '',
            content TEXT,
			PRIMARY KEY (id)
		) ENGINE=%s;
	", APP_DB_ENGINE);
	$db->Execute($sql);
}