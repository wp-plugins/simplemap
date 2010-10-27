<?php

if (file_exists("./wp-load.php")){include("./wp-load.php");}
elseif (file_exists("../wp-load.php")){include("../wp-load.php");}
elseif (file_exists("../../wp-load.php")){include("../../wp-load.php");}
elseif (file_exists("../../../wp-load.php")){include("../../../wp-load.php");}
elseif (file_exists("../../../../wp-load.php")){include("../../../../wp-load.php");}
elseif (file_exists("../../../../../wp-load.php")){include("../../../../../wp-load.php");}
elseif (file_exists("../../../../../../wp-load.php")){include("../../../../../../wp-load.php");}
elseif (file_exists("../../../../../../../wp-load.php")){include("../../../../../../../wp-load.php");}
elseif (file_exists("../../../../../../../../wp-load.php")){include("../../../../../../../../wp-load.php");}

$table = $table_prefix."simple_map";
$cat_table = $table_prefix."simple_map_cats";

$username=DB_USER;
$password=DB_PASSWORD;
$database=DB_NAME;
$host=DB_HOST;


// Opens a connection to a MySQL server
$connection = mysql_connect($host, $username, $password);
if (!$connection) {
  die("Not connected: " . mysql_error());
}

// Set the active MySQL database
$db_selected = mysql_select_db($database, $connection);
if (!$db_selected) {
  die("Can't use db: " . mysql_error());
}

mysql_query("SET CHARACTER SET utf8");
mysql_query("SET NAMES utf8");

?>