<?php

/**
 * Create and insert posts from CSV files
 */

global $wpdb;
global $post;

// Options Vars
$spreadsheet_url = get_option( 'sheets_url' );
$cptslug = get_option( 'cpt_slug' );

?>
<h1>Import</h1>
<div class="upload-form">
	<p>To insert the posts into the database, hit the button.</p>
	<br />
	<a class="button button-primary" style="padding:1em 3em;border-color:green; background:green; height:auto;" href="<?php echo $_SERVER["REQUEST_URI"] . '&insert_' . $cptslug ?>">Import Google Sheet</a>
</div>
<?php


// I'd recommend replacing this with your own code to make sure
// the post creation _only_ happens when you want it to.
if ( !isset( $_GET["insert_" . $cptslug] ) ) {
	return;
}

// Gather the rest of the option information.
$g_title = get_option( 'g_title' );
$g_content = get_option( 'g_content' );
$cpt_status = get_option( 'cpt_status' );
$cpt_taxonomy = get_option( 'cpt_taxonomy' );
$g_tax_list = get_option( 'g_tax_list' );
$g_categories = get_option( 'g_categories' );
$g_featured_image_path = get_option( 'g_featured_image_path' );
$g_featured_image_folder = get_option( 'g_featured_image_folder' );

// Process Custom Meta!
$custom_fields = explode( ",", get_option( 'g_custom_fields' ) );
$g_custom_slugs = explode( ",", get_option( 'g_custom_slugs' ) );
$q_cs_result = array_combine( $custom_fields, $g_custom_slugs );

// print_r($q_cs_result);
// echo "<br /><br />";

// Process Categories and Taxonomies.
if ( $g_categories ) {
	$g_cats = explode( ",", $g_categories );
	//print_r($g_cats);
}

if ( $g_tax_list ) {
	$g_tax = explode( ",", $g_tax_list );
	//print_r($g_tax);
}


/* Post Insertion for Wordpress */
$csv = array();

// Parse the CSV data from the Google Sheet into an Array.
// NOTE:  This SHOULD handle newlines in a cell without issue.
if ( ( $handle = fopen( $spreadsheet_url, "r" ) ) !== FALSE ) {
    while ( ( $line = fgetcsv( $handle ) ) !== FALSE ) {
    	array_push( $csv, $line );
    }
    fclose( $handle );
}


// Take the Header row for the spreadsheet and use those values as the key for
// each array entry.
array_walk( $csv, function( &$a ) use ( $csv ) {
	if ( count( $a ) === count( $csv[0] ) ) {
		$a = array_combine( $csv[0], $a );
	}
});


// Remove the Column Headers; NOTE:  This should be an OPTION.
array_shift( $csv );

// print_r( $csv );
// echo "<br /><br />";


// Get the full list of posts to compare against; we want all published and drafted posts so we don't override duplicates.
$all_posts = get_posts( 
	array(
		'post_type' => $cptslug,
		'post_status' => 'publish, draft',
		'posts_per_page' => -1
	)
);

// Check if the posts exist

$postcheck = array();
foreach ( $all_posts as $p ) {  
	$postcheck[] .= $p->post_title;
}

// print_r( $postcheck );

// Temporary return so that we can see test values to be inserted without performing the insert.
// return;

?>

<div class="updated">

<?php
foreach ( $csv as $c ) {

	if ( !in_array( $c[$g_title], $postcheck ) ) {

		echo $c[$g_title]  .' - Inserted into - ' . $cptslug . ' Status: ' . $cpt_status;

		echo '</br>';  

		// Create Default WP Catgeories
		if ( $g_categories ) {
			$categories = array();
			foreach ( $g_cats as $gdc ) {
				$categories[] .= wp_create_category( $c[$gdc], 0 );
			}
		}
	
		// Create the Post...
		global $user_ID;
		if ( isset( $categories ) ) {
			$new_post = array(
			    'post_title' => $c[$g_title],
			    'post_content' => $c[$g_content],
			    'post_status' => $cpt_status,
			    'post_date' => date('Y-m-d H:i:s'),
			    'post_author' => $user_ID,
			    'post_type' => $cptslug,
			    'post_category' => $categories
		    );
		} else {
			$new_post = array(
			    'post_title' => $c[$g_title],
			    'post_content' => $c[$g_content],
			    'post_status' => $cpt_status,
			    'post_date' => date('Y-m-d H:i:s'),
			    'post_author' => $user_ID,
			    'post_type' => $cptslug
			);
		}
		
		$post_id = wp_insert_post( $new_post );

		// Create Taxonomy
		if ( $g_tax_list ) {
			foreach ( $g_tax as $gt ) {
				wp_set_object_terms( $post_id, $c[$gt], $cpt_taxonomy, true );
			}
		}
	 	// Add Catgeories

		// Add Custom Fields
		foreach ( $q_cs_result as $fd => $fv ) {
	    	add_post_meta( $post_id, $fv, $c[$fd], true );
		}

		// Import to Featured Image
		$url = home_url();

		// example image
		$image = $url . $g_featured_image_folder . $c[$g_featured_image_path];

		// magic sideload image returns an HTML image, not an ID
		$media = media_sideload_image( $image, $post_id );

		// therefore we must find it so we can set it as featured ID
		if ( !empty( $media ) && !is_wp_error( $media ) ) {
		    $args = array(
		        'post_type' => 'attachment',
		        'posts_per_page' => 1,
		        'post_status' => 'any',
		        'post_parent' => $post_id
		    );

		    // reference new image to set as featured
		    $attachments = get_posts($args);

		    if ( isset($attachments) && is_array($attachments)){
		        foreach($attachments as $attachment){
		            // grab source of full size images (so no 300x150 nonsense in path)
		            $image = wp_get_attachment_image_src($attachment->ID, 'full');
		            // determine if in the $media image we created, the string of the URL exists
		            if(strpos($media, $image[0]) !== false){
		                // if so, we found our image. set it as thumbnail
		                set_post_thumbnail($post_id, $attachment->ID);
		                // only want one image
		                break;
		            }
		        }
		    }
		}


	} else { // The item already exists as a post somewhere.

	echo '<span style="color:red;">Already exist - ' . $c[$g_title] . '</span>';
	echo '</br>';  

	}

}

//print_r($postcheck);
?>
	<br />
	<span style="color:green;">Done! Finito! Complete!</span>
</div> <!-- .updated -->
