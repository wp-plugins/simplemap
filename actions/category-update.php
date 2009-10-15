<?php
/*
SimpleMap Plugin
category-update.php: Updates the locations table to use version 1.2 category functionality
*/

include "../includes/connect-db.php";

$categories = mysql_query("SELECT id, name FROM $cat_table ORDER BY id");

while ($category = mysql_fetch_assoc($categories)) {
	mysql_query("UPDATE $table SET category = '".$category['id']."' WHERE category = '".$category['name']."'");
}

$message = urlencode(__('Your database has been successfully updated.', 'SimpleMap'));
$redirect = urldecode($_GET['redirect']);

update_option('simplemap_cats_using_ids', 'true');
		
header("Location: $redirect&message=$message");
exit();

?>