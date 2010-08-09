<?php
/*
SimpleMap Plugin
category-process.php: Adds/edits/deletes a category from the database
*/

import_request_variables('pg', 'bcl_');

include "../includes/connect-db.php";
include "../includes/sminc.php";

if ( $bcl_action == 'delete' ) {
	// Delete the category and return to the requesting script
	$query = "DELETE FROM " . $cat_table . " WHERE id = '$bcl_del_id'";
	$result = mysql_query( $query ) or die ( mysql_error() );
	header( "Location: {$_SERVER['HTTP_REFERER']}" );
	exit();

} else if ( $bcl_action == 'delete_all' ) {
	// Delete all categories and return to the requesting script
	$query = "DELETE FROM ".$cat_table;
	$result = mysql_query($query) or die (mysql_error());
	header("Location: {$_SERVER['HTTP_REFERER']}");
	exit();
	
} else {

	if ( $bcl_action == 'edit' || $bcl_action == 'inline-save') {
	
		// Fires when we are editing or doing an inline save for categories.
		$query = "UPDATE $cat_table SET name = '$bcl_store_name' WHERE id = '$bcl_store_id'";
		
		if ( !$result = mysql_query($query) ) {
			die( "Invalid query: " . mysql_error() . "<br />\nQuery: " . $query . "<br />\n" );
		} else { 
			$bcl_store_name = stripslashes( $bcl_store_name );
			?>
			<tr id='post-<?php echo $bcl_store_id; ?>' class='<?php echo $bcl_altclass; ?>author-self status-publish iedit' valign="top">
				<!-- <th scope="row" class="check-column"><input type="checkbox" name="post[]" value="1" /></th> -->
					<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $bcl_store_id; ?></span></strong></td>
				<td class="post-title column-title"><strong><span class="row-title row_name"><?php echo $bcl_store_name; ?></span></strong>
					<div class="row-actions">
					<span class='inline hide-if-no-js'><a href="#" class="editinline" title="Edit this category inline">Quick Edit</a> | </span>
					<span class='delete'><a class='submitdelete' title='Delete this category' href='../wp-content/plugins/simplemap/actions/category-process?action=delete&amp;del_id=<?php echo $bcl_store_id; ?>' onclick="javascript:return confirm('Do you really want to delete \'<?php echo addslashes($bcl_store_name); ?>\'?');">Delete</a></span>
				</div>
					<div class="hidden" id="inline_<?php echo $bcl_store_id; ?>">
					<div class="store_id"><?php echo $bcl_store_id; ?></div>
					<div class="altclass"><?php echo $bcl_altclass; ?></div>
					<div class="store_name"><?php echo $bcl_store_name; ?></div></div>
				</td>
			</tr>
			<?php
		}
	} else if ( $bcl_action == 'add' ) {
		// We're adding a new category
		$query = "INSERT INTO $cat_table SET name = '$bcl_new_store_name'";
		
		if ( !$result = mysql_query( $query ) ) {
			die("Invalid query: " . mysql_error() . "<br />\nQuery: " . $query . "<br />\n");
		} else {
			$urlname = urlencode(stripslashes( $bcl_store_name ) );
			header( "Location: {$_SERVER['HTTP_REFERER']}&added=$urlname" );
			exit();
		}
	}
}
?>