<?php  
include "../includes/connect-db.php";

// Get parameters from URL
$center_lat	= $_GET["lat"];
$center_lng	= $_GET["lng"];
$radius		= $_GET["radius"];
$namequery	= $_GET['namequery'];
$limit		= (int) $_GET['limit'];

if ( isset( $_GET['categories'] ) && $_GET['categories'] != '' )
	$categories = explode( ',', $_GET['categories'] );
else
	$categories = null;

//$query2 = null;
//$cat_id_matches = null;
//$query2_add = '';
//$category_text_2 = ' ';

// Start XML file, create parent node
$dom 		= new DOMDocument( "1.0" );
$node 		= $dom->createElement( "markers" );
$parnode 	= $dom->appendChild( $node );

// Set finite limit based on option set in General Options (or 100 if no limit)
$limittext = '';
if ( $limit != 0 )
	$limittext = " LIMIT 0, $limit";
else
	$limittext = " LIMIT 0, 100";

$namequery = trim( urldecode( $namequery ) );

$usename = 0;

$textsearch = mysql_query( "SELECT id FROM $table WHERE MATCH(name, description, tags) AGAINST('$namequery*' IN BOOLEAN MODE)" );
if ( $textsearch ) {
	while ( $row = mysql_fetch_array( $textsearch ) ) {
		$usename = 1;
	}
}

if ( $usename == 0 ) {
	$textsearch2 = mysql_query( "SELECT id FROM $table WHERE name LIKE '%$namequery%' OR description LIKE '%$namequery%' OR tags LIKE '%$namequery%'" );
	if ( $textsearch2 ) {
		while ( $row = mysql_fetch_array( $textsearch2 ) ) {
			$usename = 2;
		}
	}
}

/*
$textsearch2 = mysql_query("SELECT id FROM $cat_table WHERE MATCH(name) AGAINST('$namequery*' IN BOOLEAN MODE)");
if ($textsearch2) {
	while ($row = mysql_fetch_array($textsearch2)) {
		$category_text_2 .= "category = '".$row['id']."' OR ";
	}
	$category_text_2 = substr($category_text_2, 0, -4).' OR ';
}
*/

if ( $usename == 1 || $usename == 2 ) {
	$category_text = ' ';
	if ( $categories ) {
		foreach ( $categories as $category ) {
			$category_text .= "category = '" . $category . "' OR ";
		}
		$category_text = substr( $category_text, 0, -4 ) . ' AND ';
	}
	// NOTE: This query caused category names to be searched, but not any of the text in the locations table
	//$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special, MATCH(name, description, tags) AGAINST('$namequery') AS score FROM $table WHERE".$category_text.$category_text_2."MATCH(name, description, tags) AGAINST('$namequery*' IN BOOLEAN MODE) ORDER BY score DESC".$limittext;
	
	// This will search the locations table but not the category names - if the FULLTEXT search returned any results
	if ( $usename == 1 )
		$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special, MATCH(name, description, tags) AGAINST('$namequery') AS score FROM $table WHERE".$category_text."MATCH(name, description, tags) AGAINST('$namequery*' IN BOOLEAN MODE) ORDER BY score DESC".$limittext;
	
	// This will search the locations table but not the category names - if the FULLTEXT search returned NO results but the LIKE search did
	if ( $usename == 2 )
		$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special FROM $table WHERE".$category_text."name LIKE '%$namequery%' OR description LIKE '%$namequery%' OR tags LIKE '%$namequery%'".$limittext;
	
	// This will add the category table search onto the end of the previous results list
	/*
$cat_id_matches = mysql_query("SELECT id FROM $cat_table WHERE MATCH(name) AGAINST('$namequery*' IN BOOLEAN MODE)");
	if ($cat_id_matches) {
		while ($row = mysql_fetch_assoc($cat_id_matches)) {
			$query2_add .= " category = '".$row['id']."' OR";
		}
		$query2_add = substr($query2_add, 0, -3);
		//echo '$query2_add:'.$query2_add."<br />\n";
		if ($query2_add != ' ' && $category_text != ' ')
			$query2 = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special FROM $table WHERE".$category_text.$query2_add." ORDER BY score DESC".$limittext;
	}
*/
} else {
	// If there were no text matches found in either search method (name, description, and tags only)
	$category_text = ' ';
	if ( $categories ) {
		$category_text .= 'WHERE ';
		foreach ( $categories as $category ) {
			$category_text .= "category = '" . $category . "' OR ";
		}
		$category_text = substr( $category_text, 0, -3 );
	}
	
	// Search the rows in the markers table
	if ( $radius == 'infinite' ) {
		$query = "SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special FROM $table".$category_text."ORDER BY name".$limittext;
	} else {
		$query = sprintf("SELECT name, address, address2, city, state, zip, country, lat, lng, phone, fax, url, description, category, tags, special, ( 3959 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM $table".$category_text."HAVING distance < '%s' ORDER BY distance".$limittext,
			mysql_real_escape_string($center_lat),
			mysql_real_escape_string($center_lng),
			mysql_real_escape_string($center_lat),
			mysql_real_escape_string($radius));
	}
}

if ( !$result = mysql_query( $query ) ) {
  die( "Invalid query: " . mysql_error() . "<br />\n" . $query );
}

// For the category search add-on
/*
if ($query2) {
	$result2 = mysql_query($query2);
	if (!$result2)
		die("Invalid query: " . mysql_error() . "<br />\n" . $query2);
}
*/

header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each
while ( $row = mysql_fetch_assoc( $result ) ) {
	
	$category_name = '';
	$cats = mysql_query( "SELECT name FROM $cat_table WHERE id = '" . $row['category'] . "'" );
	if ( $cats ) {
		while ( $cat = mysql_fetch_array( $cats ) ) {
			$category_name = $cat['name'];
		}
	}

	$node = $dom->createElement( "marker", nl2br( stripslashes( $row['description'] ) ) );
	$newnode = $parnode->appendChild( $node );
	$newnode->setAttribute( "name", stripslashes( $row['name'] ) );
	$newnode->setAttribute( "address", stripslashes( $row['address'] ) );
	$newnode->setAttribute( "address2", stripslashes( $row['address2'] ) );
	$newnode->setAttribute( "city", stripslashes( $row['city'] ) );
	$newnode->setAttribute( "state", stripslashes( $row['state'] ) );
	$newnode->setAttribute( "zip", stripslashes( $row['zip'] ) );
	$newnode->setAttribute( "country", stripslashes( $row['country'] ) );
	$newnode->setAttribute( "lat", $row['lat'] );
	$newnode->setAttribute( "lng", $row['lng'] );
	$newnode->setAttribute( "distance", $row['distance'] );
	$newnode->setAttribute( "phone", stripslashes( $row['phone'] ) );
	$newnode->setAttribute( "fax", stripslashes( $row['fax'] ) );
	$newnode->setAttribute( "url", stripslashes( $row['url'] ) );
	$newnode->setAttribute( "category", stripslashes( $category_name ) );
	$newnode->setAttribute( "tags", stripslashes( $row['tags'] ) );
	$newnode->setAttribute( "special", $row['special'] );
}

// For the category search add-on
/*
if ($result2) {
	while ($row = mysql_fetch_assoc($result2)) {
		
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
}
*/

echo $dom->saveXML();
echo "<!-- Query: $query -->\n";
echo "<!-- Query2: $query2 -->\n";
echo "<!-- Usename: $usename -->\n";
?>
