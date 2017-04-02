<?php
/**
 * @package Import Posts from Google Sheets
 * @version 1.0.3
 */
/*
Plugin Name: Import Posts from Google Sheets
Plugin URI: http://thenorthernfoundry.com/import-posts-google-sheets-wordpress/
Description: Use a google sheet to import data into posts or custom post type, supports ACF custom fields too. 
Author: Adam Jackson, Patrick Karjala
Version: 1.0.3
Author URI: http://thenorthernfoundry.com/
*/

// PAK 20170330 Is this a good idea?
ini_set( 'memory_limit', -1 );

/**
 * Add the admin menu entry for the plugin.
 */
function g_sheets_plugin_create_menu() {

	//create new top-level menu
	add_menu_page( 'Google Sheets', 'Google Sheets Importer', 'manage_options', 'g_sheets_plugin_settings_page', 'g_sheets_plugin_settings_page' );
	
	//call register settings function
	add_action( 'admin_init', 'register_g_sheets_plugin_settings' );
}
add_action( 'admin_menu', 'g_sheets_plugin_create_menu' );


/**
 * Registers all stored settings for the plugin.
 */
function register_g_sheets_plugin_settings() {
	//register our settings
	register_setting( 'g-sheets-plugin-settings-group', 'sheets_url' );
	register_setting( 'g-sheets-plugin-settings-group', 'cpt_slug' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_title' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_content' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_acf_group' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_custom_fields' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_categories' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_delete_posts' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_custom_slugs' );
	register_setting( 'g-sheets-plugin-settings-group', 'cpt_status' );
	register_setting( 'g-sheets-plugin-settings-group', 'cpt_taxonomy' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_tax_list' );
	//Images stuff
	register_setting( 'g-sheets-plugin-settings-group', 'g_featured_image_path' );
	register_setting( 'g-sheets-plugin-settings-group', 'g_featured_image_folder' );
}

/**
 * Main Import Button & Options
 **/
function g_sheets_plugin_settings_page() {

	// Only allow users who have the manage_options capability access to this plugin.
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'You are not allowed access this content.' );
		return;
	}

	$spreadsheet_url = get_option( 'sheets_url' );
	?>

	<div class="wrap">

	<h1>Google Sheets Importer</h1>
	<i><a href="http://thenorthernfoundry.com" target="_blank" >By The Northern Foundry</a>; updates by <a href="https://dcdc.coe.hawaii.edu/" target="_blank">DCDC</a></i>

	<p>
	To start, please publish a spreadsheet in Google Sheets: <a target="_blank" href="https://support.google.com/docs/answer/37579?hl=en">Publish a spreadsheet</a>
	</p>

	<hr>

	<h1>Sheet Configuration</h1>

	<form method="post" action="options.php">
		<?php //wp_nonce_field( 'g-sheets-import-nonce' ); ?>
	    <?php settings_fields( 'g-sheets-plugin-settings-group' ); ?>
	    <?php do_settings_sections( 'g-sheets-plugin-settings-group' ); ?>
	    <table class="form-table">
	        <tr valign="top">
	        <th scope="row">Google Sheets URL</th>
	        <td><input style="width:100%;" type="text" name="sheets_url" value="<?php echo esc_attr( get_option( 'sheets_url' ) ); ?>" /></td>
	        </tr>
	        
	        <?php if($spreadsheet_url):?>

	        <tr valign="top">
	        <th scope="row">Post Type</th>
	        <i>Note: Enter 'post' for normal blog posts, or enter a custom post type slug you have created</i>
	        <td><input type="text" name="cpt_slug" placeholder="eg post, products, portfolio" value="<?php echo esc_attr( get_option( 'cpt_slug' ) ); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Custom Taxonomy</th>
	        <i>Enter the 'slug' of your custom taxonomy if you are using one</i>
	        <td><input type="text" name="cpt_taxonomy" value="<?php echo esc_attr( get_option( 'cpt_taxonomy' ) ); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Posts Status</th>
	        <i>eg. publish, draft or pending</i>
	        <td><input type="text" name="cpt_status" placeholder="eg publish, draft or pending" value="<?php echo esc_attr( get_option( 'cpt_status' ) ); ?>" /></td>
	        </tr>

	   		<?php endif;?>
	   
		</table>

		<hr>

		<?php if( $spreadsheet_url ): ?>

		<h1>Mandetory Field Mapping</h1>
	    <i>Add the actual column name from your google sheet as it appears (case sensitive)</i>
		 
		<table class="form-table">
		 		
		 	<tr valign="top">
	        <th scope="row">Post title</th>
	        <td><input type="text" name="g_title" value="<?php echo esc_attr( get_option( 'g_title') ); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Post Content</th>
	        <td><input type="text" name="g_content" value="<?php echo esc_attr( get_option( 'g_content') ); ?>" /></td>
	        </tr>

		</table>

		<hr>

		<h1>Other Mapping</h1>
		<i>Map the columns from your Google Spreadsheet</i>
		</br>
		<p>
		DON'T FORGET TO CREATE YOUR CUSTOM FIELDS AND/OR CUSTOM TAXONOMIES FIRST - I recommend using <a target="_blank" href="https://www.advancedcustomfields.com/">ACF (Advanced Custom Fields)</a> and <a href="https://en-gb.wordpress.org/plugins/custom-post-type-ui/">Custom Post Type UI</a>
		</p>
		</br>

		<?php // Preview Google Sheet Headers
		include_once plugin_dir_path( __FILE__ ) . 'inc/g_sheets_headers.php';
		?>		 

		<table class="form-table">
		 		
		 	<tr valign="top">
	        <th scope="row">Map Google Spreadsheet Columns</th>
	        <td><input style="width:100%;" type="text" name="g_custom_fields" value="<?php echo esc_attr( get_option( 'g_custom_fields') ); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">To these Custom Fields (enter slug)</th>
	        <td><input style="width:100%;" type="text" name="g_custom_slugs" value="<?php echo esc_attr( get_option( 'g_custom_slugs') ); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Map Categories to use these Google Spreadsheet columns</th>
	        <td><input style="width:100%;" type="text" name="g_categories" value="<?php echo esc_attr( get_option( 'g_categories') ); ?>" /></td>
	        </tr>

	        <tr valign="top">
	        <th scope="row">Map Custom Taxonomies to use these Google Spreadsheet columns</th>
	        <td><input style="width:100%;" type="text" name="g_tax_list" value="<?php echo esc_attr( get_option( 'g_tax_list') ); ?>" /></td>
	        </tr>

		</table>
		 
		<hr>

		<h1>Upload Images</h1>
		
		<table class="form-table">
		 	<tr valign="top">
		        <th scope="row">Images Field</th>
		        <td>
			        <b>Reference the field in your Google Sheet that contains your image URLS.</b>
			        <p>Note: make sure the image file names match the path stated below. for example if you have a "/" in your google sheet infront of your image file names your page should not include one. If you don't, include the "/" in the "Path to your images folder" </p>
					<input style="width:100%;" type="text" name="g_featured_image_path" value="<?php echo esc_attr( get_option( 'g_featured_image_path') ); ?>" />
				</td>
	        </tr>
		 	<tr valign="top">
		        <th scope="row">Path to your images folder </th>
		        <td>
			        <b>Simple, just enter the path to your images folder (relative to the root of your site eg. www.yoursite.com/upload_images/)</b></br>
			        <p>Note: Always include the "/" infront</p>
			        <input style="width:100%;" type="text" placeholder="/upload_images/" name="g_featured_image_folder" value="<?php echo esc_attr( get_option( 'g_featured_image_folder') ); ?>" />
		        </td>
	        </tr>
		</table>

<!-- 		<h1>Bulk Delete Posts</h1>
		<i>Especially for testing purposes USE WITH CAUTION</i>

		<table class="form-table">
				
			<tr valign="top">
			<th scope="row">Number of Posts to delete</th>
			<td><input type="text" name="g_delete_posts" value="<?php //echo esc_attr( get_option( 'g_delete_posts') ); ?>" /></td>
			</tr>

		</table> -->

		


	 	<?php // Delete Posts
		//include_once plugin_dir_path( __FILE__ ).'inc/delete_posts.php';
		?>

		<?php // Delete Posts
		//include_once plugin_dir_path( __FILE__ ).'inc/delete_media.php';
		?>

		</br>

	 	<?php 
	 	endif; // Only display once spreadsheet address is entered
	    
	    submit_button(); 
	    ?>

	</form>

	<hr>
	<?php
	if( $spreadsheet_url ) {
		include_once plugin_dir_path( __FILE__ ).'inc/processing.php';
	} else {
		echo '<h3>Enter a Google Sheets share URL and \'SAVE\' to get configuration options<h3>';
	} // Only display once spreadsheet address is entered ?>

</div>

<?php
}