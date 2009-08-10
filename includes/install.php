<?php
$plugin_folder = $this->plugin_url;
global $wpdb;
$simplemap_db_version = '1.0';
//$installed_ver = get_option('db_version');
//only run installation if the table doesn't exist
if($wpdb->get_var("show tables like '".$this->table."'") != $this->table) {

	//*****************************************************************************************
	// Create the sql - You will need to edit this to include the columns you need
	// Using the dbdelta function to allow the table to be updated if this is an update.
	// Read the limitations of the dbdelta function here: http://codex.wordpress.org/Creating_Tables_with_Plugins
	// remember to update the version number every time you want to make a change.
	//*****************************************************************************************
	$sql = "CREATE TABLE " . $this->table . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	name varchar(64) collate utf8_unicode_ci NOT NULL,
	address varchar(64) collate utf8_unicode_ci NOT NULL,
	address2 varchar(64) collate utf8_unicode_ci default NULL,
	city varchar(64) collate utf8_unicode_ci NOT NULL,
	state varchar(64) collate utf8_unicode_ci NOT NULL,
	zip varchar(10) collate utf8_unicode_ci default NULL,
	phone varchar(14) collate utf8_unicode_ci default NULL,
	fax varchar(14) collate utf8_unicode_ci default NULL,
	url varchar(64) collate utf8_unicode_ci default NULL,
	special tinyint(1) NOT NULL default '0',
	lat float(10,6) default NULL,
	lng float(10,6) default NULL,
	dateUpdated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY id (id)
	);";

	require_once(ABSPATH . "wp-admin/upgrade-functions.php");
	dbDelta($sql);
	//add a database version number for future upgrade purposes
	add_option("simplemap_db_version", $simplemap_db_version);
}
?>