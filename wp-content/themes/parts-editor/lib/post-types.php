<?php

/* 
	REGISTER CUSTOM POST TYPES
*/

add_action( 'init', 'fz_register_post_types' );

function fz_register_post_types() {

	register_post_type( 'fz_part',
		array(
			'labels' => array(
				'name' => __( 'Parts' ),
				'singular_name' => __( 'Part' )
			),
		'supports' => array('title', 'editor', 'custom-fields', 'revisions', 'post-formats', 'thumbnail', 'comments'),
		'public' => true,
		'has_archive' => true,
		)
	);

}

/*
	ADMIN VIEW
*/

add_filter( 'manage_edit-fz_part_columns', 'fz_edit_part_columns' ) ;

function fz_edit_part_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Part name' ),
		'lbr' => __( 'Library' ),
		'lbr_packages' => __( 'Packages' ),
		'date' => __( 'Date' )
	);

	return $columns;
}

add_action( 'manage_fz_part_posts_custom_column', 'fz_manage_part_columns', 10, 2 );

function fz_manage_part_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		case 'lbr' :
			echo get_the_term_list( $post_id, 'fz_lbr');
			break;

		case 'lbr_packages' :
			echo get_the_term_list( $post_id, 'fz_lbr_packages', null, ', ');
			break;	

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}



add_action( 'init', 'fz_register_taxonomies' );

function fz_register_taxonomies() {
	
	// old taxonomies
	register_taxonomy(
		'fz_lbr',
		'fz_part',
		array(
			'label' => __( '.lbr' ),
			'rewrite' => array( 'slug' => 'lbr' )
		)
	);
	register_taxonomy(
		'fz_lbr_packages',
		'fz_part',
		array(
			'label' => __( '.lbr package' ),
			'rewrite' => array( 'slug' => 'lbr-package' )
		)
	);

	register_taxonomy(
		'fz_packages',
		'fz_part',
		array(
			'label' => __( 'Package' ),
			'rewrite' => array( 'slug' => 'package' ),
			'hierarchical' => true
		)
	);

	register_taxonomy(
		'fz_bins',
		'fz_part',
		array(
			'label' => __( 'Bins' ),
			'rewrite' => array( 'slug' => 'bins' ),
			'hierarchical' => true
		)
	);

	register_taxonomy(
		'fz_attributes',
		'fz_part',
		array(
			'label' => __( 'Attributes' ),
			'rewrite' => array( 'slug' => 'attributes' ),
			'hierarchical' => true
		)
	);

}

?>