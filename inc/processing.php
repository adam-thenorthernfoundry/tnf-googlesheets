<?php

	 /**
	 * Create and insert posts from CSV files
	 **/

	global $wpdb;
	global $post;

	// Options Vars
	$spreadsheet_url = get_option('sheets_url');
	$cptslug = get_option('cpt_slug');
	
	$g_title = get_option('g_title');
	$g_content = get_option('g_content');
	$cpt_status = get_option('cpt_status');
	$cpt_taxonomy = get_option('cpt_taxonomy');
	$g_tax_list = get_option('g_tax_list');
	$g_categories = get_option('g_categories');
	$g_featured_image_path = get_option('g_featured_image_path');
	$g_featured_image_folder = get_option('g_featured_image_folder');


	echo '<h1>Import</h1>';
	echo '<div class="upload-form">';
	echo '<p>To insert the posts into the database, hit the button.</p><br>';
	echo '<a class="button button-primary" style="padding:1em 3em;border-color:green; background:green; height:auto;" href="'.$_SERVER["REQUEST_URI"].'&insert_'.$cptslug.'">Import Google Sheet</a>';
	echo '</div>';


	// Process Custom Meta Biatch!

	$custom_fields_str = get_option('g_custom_fields');
	$custom_fields = explode(",",$custom_fields_str);

	$g_custom_slugs_str = get_option('g_custom_slugs');
	$g_custom_slugs = explode(",",$g_custom_slugs_str);

	//$custom_fields_str = preg_replace("[^A-Za-z0-9]", "", $custom_fields_str);
	//$custom_fields_str = strtolower($custom_fields_str);

    //print_r($custom_fields_str);

	

	$q_cs_result = array_combine($custom_fields, $g_custom_slugs);

	//print_r($q_cs_result);

	// Process Cats

	if($g_categories){
	$g_cats_str = get_option('g_categories');
	$g_cats = explode(",",$g_cats_str);
	//print_r($g_cats);
	}

	if($g_tax_list){
	$g_tax_str = get_option('g_tax_list');
	$g_tax = explode(",",$g_tax_str);	
	//print_r($g_tax);
	}



	// I'd recommend replacing this with your own code to make sure
	//  the post creation _only_ happens when you want it to.
	if ( ! isset( $_GET["insert_".$cptslug] ) ) {
		return;
	}

	/* Post Insertion for Wordpress */

	$csv = array_map('str_getcsv', file($spreadsheet_url));
	array_walk($csv, function(&$a) use ($csv) {
	      $a = array_combine($csv[0], $a);
	    });
	    array_shift($csv); # remove column header


	//print_r($csv);

	
	$args = array(
			  'post_type' => $cptslug,
			  'post_status' => 'publish',
			  'posts_per_page' => -1,
	);

		 
	$all_posts = get_posts( $args );

	// Check if the posts exist

	$postcheck = array();
	foreach ($all_posts as $p) {  
		$postcheck[] .= $p->post_title;
	}

	//print_r($postcheck);


	echo "<div class='updated'>";


	foreach ($csv as $c ) {

	if(!in_array($c[$g_title], $postcheck)) {

	 echo $c[$g_title].' - Inserted into - '.$cptslug.' Status: '.$cpt_status;
	 
	 echo '</br>';  

	 // Create Default WP Catgeories
	if ($g_categories) {
	$categories = array();
	foreach ($g_cats as $gdc) {
	$categories[] .= wp_create_category($c[$gdc], 0);
	}
	}
	
	// Create the Fucking Post...

	global $user_ID;
	$new_post = array(
	    'post_title' => $c[$g_title],
	    'post_content' => $c[$g_content],
	    'post_status' => $cpt_status,
	    'post_date' => date('Y-m-d H:i:s'),
	    'post_author' => $user_ID,
	    'post_type' => $cptslug,
	    'post_category' => $categories
		
	);
	$post_id = wp_insert_post($new_post);

	// Create Taxonomy

	if ($g_tax_list) {

	foreach ($g_tax as $gt) {
	wp_set_object_terms($post_id, $c[$gt], $cpt_taxonomy, true);
	}

	}
	 // Add Catgeories

	// Add Custom Fields
	foreach ($q_cs_result as $fd => $fv) {
    add_post_meta($post_id, $fv, $c[$fd], true);

	}



	// Import to Featured Image
	$url = home_url();

	// example image
	$image = $url.$g_featured_image_folder.$c[$g_featured_image_path];



	// magic sideload image returns an HTML image, not an ID
	$media = media_sideload_image($image, $post_id);

	// therefore we must find it so we can set it as featured ID
	if(!empty($media) && !is_wp_error($media)){
	    $args = array(
	        'post_type' => 'attachment',
	        'posts_per_page' => 1,
	        'post_status' => 'any',
	        'post_parent' => $post_id
	    );

	    // reference new image to set as featured
	    $attachments = get_posts($args);

	    if(isset($attachments) && is_array($attachments)){
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


	} else {

	echo '<span style="color:red;">Already exist - '.$c[$g_title].'</span>';
	echo '</br>';  


	}

	}

	echo '<br>';
	echo '<span style="color:green;">Done! Finito! Complete!</span>';


	echo '</div>';

	//print_r($postcheck);

 
?>