<?php

/*
	Lost / Found external helpers
*/

function my_remove_post_term( $post_id, $term, $taxonomy ) {
 
	if ( ! is_numeric( $term ) ) {
		$term = get_term( $term, $taxonomy );
		if ( ! $term || is_wp_error( $term ) )
			return false;
		$term_id = $term->term_id;
	} else {
		$term_id = $term;
	}
 
	// Get the existing terms and only keep the ones we don't want removed
	$new_terms = array();
	$current_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
 
	foreach ( $current_terms as $current_term ) {
		if ( $current_term != $term_id )
			$new_terms[] = intval( $current_term );
	}
 
	return wp_set_object_terms( $post_id, $new_terms, $taxonomy );
}

?>