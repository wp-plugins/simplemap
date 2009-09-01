<?php  
include "../includes/connect-db.php";

// Get parameters from URL
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$radius = $_GET["radius"];
$namequery = $_GET['namequery'];

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);


$namequery = str_replace('&', '', $namequery);
$namequery = str_replace("\'", '', $namequery);
$namequery = str_replace('.', '', $namequery);
$namequery = str_ireplace('saint', 'st', $namequery);
$namequery = trim($namequery);

$storename = Array();
$usename = 0;
$names = mysql_query("SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, special FROM $table");
while ($row = @mysql_fetch_assoc($names)) {
	$name_no_quotes = str_replace("&", ' ', str_replace("'", '', $row['name']));
	$name_quotes_to_spaces = str_replace("&", ' ', str_replace("'", ' ', $row['name']));
	if ((stripos($name_no_quotes, $namequery) !== false || stripos($name_quotes_to_spaces, $namequery) !== false) && array_search($row['name'], $storename) === false) {
		$storename[] = addslashes($row['name']);
		$usename = 1;
	}
}

if ($usename == 1) {
	$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, special FROM $table WHERE name = '".$storename[0]."'";
	if (count($storename) > 1) {
		foreach ($storename as $name) {
			if ($name != $storename[0])
				$query .= " OR name = '".$name."'";
		}
	}
}
else {
	// Search the rows in the markers table
	$query = sprintf("SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, special, ( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM $table HAVING distance < '%s' ORDER BY distance LIMIT 0 , 20",
		mysql_real_escape_string($center_lat),
		mysql_real_escape_string($center_lng),
		mysql_real_escape_string($center_lat),
		mysql_real_escape_string($radius));
}
  
$result = mysql_query($query);

if (!$result) {
  die("Invalid query: " . mysql_error());
}

header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
while ($row = mysql_fetch_assoc($result)){
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
  $newnode->setAttribute("category", stripslashes($row['category']));
  $newnode->setAttribute("special", $row['special']);
}

echo $dom->saveXML();
?>
