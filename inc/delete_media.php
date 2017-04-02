<?php 

	global $wpdb;
	global $post;

	// Options Vars
	
	$number_delete = get_option('g_delete_posts');


	echo '<h1>Bulk Delete Media</h1>';
	echo '<i>Please dont use this on a production site, you would be a mentalist to do so! TESTING ONLY PLEASE</i>';
	echo '<div class="upload-form">';
	echo '</br>';
	echo '<a class="button button-error" style="padding:1em 3em;  height:auto;" href="'.$_SERVER['REQUEST_URI'].'&delete_attachment">Delete Media</a>';
	echo '</div>';

	if ( ! isset( $_GET["delete_attachment"] ) ) {
		return;
	}

	$args = array(
	'numberposts' => $number_delete,
	'post_type' => 'attachment'
	);
	$posts = get_posts( $args );
	if (is_array($posts)) {
	   foreach ($posts as $post) {
	// what you want to do;
	       wp_delete_post( $post->ID, true);
	   }

	   	   echo "<div class='updated'>";
		   echo "<p>";
	       echo "Deleted Media</br>";
	       echo "</p>";
	       echo "</div>";

	}?>