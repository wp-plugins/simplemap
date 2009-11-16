<?php
$plugin_folder = $this->plugin_url;
global $wpdb;
$simplemap_db_version = '1.2.1';
require_once(ABSPATH . "wp-admin/upgrade-functions.php");
$installed_ver = get_option('simplemap_db_version');

/* If table doesn't exist or database number is outdated, create or modify both tables ===================================*/
if (($wpdb->get_var("SHOW TABLES LIKE '".$this->table."'") != $this->table) || ($installed_ver != $simplemap_db_version)) {

	$sql = "CREATE TABLE " . $this->table . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	name tinytext collate utf8_unicode_ci NOT NULL,
	address varchar(64) collate utf8_unicode_ci NOT NULL,
	address2 varchar(64) collate utf8_unicode_ci default NULL,
	city varchar(64) collate utf8_unicode_ci NOT NULL,
	state varchar(64) collate utf8_unicode_ci NOT NULL,
	zip varchar(20) collate utf8_unicode_ci default NULL,
	country varchar(64) collate utf8_unicode_ci default NULL,
	phone varchar(28) collate utf8_unicode_ci default NULL,
	fax varchar(28) collate utf8_unicode_ci default NULL,
	url varchar(128) collate utf8_unicode_ci default NULL,
	description text(4096) collate utf8_unicode_ci NOT NULL,
	category mediumint(9) collate utf8_unicode_ci NOT NULL,
	tags tinytext collate utf8_unicode_ci NOT NULL,
	special tinyint(1) NOT NULL default '0',
	lat float(10,6) default NULL,
	lng float(10,6) default NULL,
	dateUpdated timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
	UNIQUE KEY id (id),
	FULLTEXT(name, description, tags)
	);";
	dbDelta($sql);
	
	$sql = "CREATE TABLE " . $this->cat_table . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	name tinytext collate utf8_unicode_ci NOT NULL,
	UNIQUE KEY id (id),
	FULLTEXT(name)
	);";
	dbDelta($sql);
	
	// database version number for future upgrade purposes
	if ($wpdb->get_var("SHOW TABLES LIKE '".$this->table."'") != $this->table) {
		add_option("simplemap_db_version", $simplemap_db_version);
		add_option("simplemap_cats_using_ids", 'true');
	}
	else if ($installed_ver != $simplemap_db_version)
		update_option("simplemap_db_version", $simplemap_db_version);
}

/* If updated tables already exist, do nothing ========================================================*/

?>