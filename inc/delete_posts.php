<?php

global $wpdb;
global $post;

// Options Vars
$cptslug = get_option('cpt_slug');
$number_delete = get_option('g_delete_posts');

// Only allow users who have the capability to delete posts.
if( !current_user_can( 'delete_posts' ) ) {
	return;
} else {
	echo '<h1>Delete Posts</h1>';
	echo '<i>Make sure you save the number of posts you would like to delete above before hitting this button</i>';
	echo '<p>Delete all the posts you have uploaded, good for testing purposes only USE WITH CAUTION</p>';
	echo '<div class="upload-form">';
	echo '<a class="button button-error" style="padding:1em 3em;  height:auto;" href="' . $_SERVER['REQUEST_URI'] . '&delete_' . $cptslug . '">Delete Posts from '
	 . $cptslug . '</a>';
	echo '</div>';


	if ( ! isset( $_GET["delete_".$cptslug] ) ) {
		return;
	}

	$args = array(
		'numberposts' => $number_delete,
		'post_type' => $cptslug
	);
	$posts = get_posts( $args );
	if ( is_array( $posts ) ) {
		foreach ( $posts as $post ) {
			// what you want to do;
			wp_delete_post( $post->ID, true);
		}

		echo "<div class='updated'>";
		echo "<p>";
		echo "Deleted Posts</br>";
		echo "</p>";
		echo "</div>";
	}
}