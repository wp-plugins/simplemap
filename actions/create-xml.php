<?php  
include "../includes/connect-db.php";

// Get parameters from URL
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];
$namequery = $_GET['namequery'];
$limit = (int)$_GET['limit'];
if ($_GET['categories'] != '')
	$categories = explode(',', $_GET['categories']);
else
	$categories = null;

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

// Set finite limit based on option set in General Options (or 100 if no limit)
$limittext = '';
if ($limit != 0)
	$limittext = " LIMIT 0, $limit";
else
	$limittext = " LIMIT 0, 100";

$namequery = trim($namequery);

$usename = 0;

$textsearch = mysql_query("SELECT id FROM $table WHERE MATCH(name, description, category, tags) AGAINST('$namequery*' IN BOOLEAN MODE)");
if ($textsearch) {
	while ($row = mysql_fetch_array($textsearch)) {
		$usename = 1;
	}
}

if ($usename == 1) {
	$category_text = ' ';
	if ($categories) {
		foreach ($categories as $category)
			$category_text .= "category = '".$category."' OR ";
		$category_text = substr($category_text, 0, -4).' AND ';
	}
	$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special, MATCH(name, description, category, tags) AGAINST('$namequery') AS score FROM $table WHERE".$category_text."MATCH(name, description, category, tags) AGAINST('$namequery*' IN BOOLEAN MODE) ORDER BY score DESC".$limittext;
}
else {
	$category_text = ' ';
	if ($categories) {
		$category_text .= 'WHERE ';
		foreach ($categories as $category)
			$category_text .= "category = '".$category."' OR ";
		$category_text = substr($category_text, 0, -3);
	}
	// Search the rows in the markers table
	if ($radius == 'infinite') {
		$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special FROM $table".$category_text."ORDER BY name".$limittext;
	}
	else {
		$query = sprintf("SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special, ( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM $table".$category_text."HAVING distance < '%s' ORDER BY distance".$limittext,
			mysql_real_escape_string($center_lat),
			mysql_real_escape_string($center_lng),
			mysql_real_escape_string($center_lat),
			mysql_real_escape_string($radius));
	}
}
  
$result = mysql_query($query);

if (!$result) {
  die("Invalid query: " . mysql_error());
}

header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
while ($row = mysql_fetch_assoc($result)){
	
	$category_name = '';
	$cats = mysql_query("SELECT name FROM $cat_table WHERE id = '".$row['category']."'");
	if ($cats) {
		while ($cat = mysql_fetch_array($cats)) {
			$category_name = $cat['name'];
		}
	}

	$node = $dom->createElement("marker", nl2br(stripslashes($row['description'])));
	$newnode = $parnode->appendChild($node);
	$newnode->setAttribute("name", stripslashes($row['name']));
	$newnode->setAttribute("address", stripslashes($row['address']));
	$newnode->setAttribute("address2", stripslashes($row['address2']));
	$newnode->setAttribute("city", stripslashes($row['city']));
	$newnode->setAttribute("state", stripslashes($row['state']));
	$newnode->setAttribute("zip", stripslashes($row['zip']));
	$newnode->setAttribute("lat", $row['lat']);
	$newnode->setAttribute("lng", $row['lng']);
	$newnode->setAttribute("distance", $row['distance']);
	$newnode->setAttribute("phone", stripslashes($row['phone']));
	$newnode->setAttribute("fax", stripslashes($row['fax']));
	$newnode->setAttribute("url", stripslashes($row['url']));
	$newnode->setAttribute("category", stripslashes($category_name));
	$newnode->setAttribute("tags", stripslashes($row['tags']));
	$newnode->setAttribute("special", $row['special']);
}

echo $dom->saveXML();
?>
