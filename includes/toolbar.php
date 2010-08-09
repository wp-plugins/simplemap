<table class="sm-toolbar" cellspacing="0" cellpadding="0" border="0"><tr>
	<td class="sm-page-title">
		<h2><?php _e($sm_page_title, 'SimpleMap'); ?></h2>
	</td>
	<td class="sm-toolbar-item">
		<a href="http://simplemap-plugin.com" target="_blank" title="<?php _e('Go to the SimpleMap Home Page', 'SimpleMap'); ?>"><?php _e('SimpleMap Home Page', 'SimpleMap'); ?></a>
	</td>
	<td class="sm-toolbar-item">
		<a href="https://simplemap.tenderapp.com/home" target="_blank" title="<?php _e('Go to the SimpleMap Support Forums', 'SimpleMap'); ?>"><?php _e('Support Forums', 'SimpleMap'); ?></a>
	</td>
	<td class="sm-toolbar-item">
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_s-xclick">
		<input type="hidden" name="hosted_button_id" value="DTJBYXGQFSW64">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
	</td>
</tr></table>

<?php
	if ( !isset( $options['api_key'] ) || $options['api_key'] == '')
		echo '<div class="error"><p>'.__('You must enter an API key for your domain.', 'SimpleMap').' <a href="'.get_bloginfo('wpurl').'/wp-admin/admin.php?page=simplemap/simplemap.php">'.__('Enter a key on the General Options page.', 'SimpleMap').'</a></p></div>';
		
	if (get_option('simplemap_cats_using_ids') == 'false' || !get_option('simplemap_cats_using_ids')) {
		echo '<div class="error"><p>';
		echo __('You must update your database to enable the new category functionality.', 'SimpleMap');
		echo ' '.__('To update the database:', 'SimpleMap').'</p>';
		echo '<ol><li><a href="'.$this->plugin_url.'actions/csv-process.php?action=export">';
		echo __('Click here FIRST to download a backup of your database.', 'SimpleMap').'</a></li>';
		echo '<li><a href="'.$this->plugin_url.'actions/category-update.php?redirect='.urlencode($current_uri).'">';
		echo __('Then click here to update your categories.', 'SimpleMap').'</a></ol></div>';
	}
?>