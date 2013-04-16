<?php

/* 
	REGISTER CUSTOM POST TYPES
*/

add_action( 'init', 'fz_register_post_types' );

function fz_register_post_types() {

  $labels = array(
	'name' => __( 'FZP Files' ),
	'singular_name' => __( 'FZP Files' )
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'fzp' ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title', 'editor', 'custom-fields', 'revisions')
  );

  register_post_type('fz_fzp', $args); // this is the post type for the imported packages


  $labels = array(
	'name' => __( 'Parts' ),
	'singular_name' => __( 'Part' )
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => 'part' ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => false,
    'menu_position' => null,
    'supports' => array('title', 'editor', 'custom-fields', 'revisions'),
    'taxonomies' => array('post_tag')
  );

  //register_post_type('fz_part', $args);

}

/*
	ADMIN VIEW
*/

add_filter( 'manage_edit-fz_fzp_columns', 'fz_edit_fzp_columns' ) ;

function fz_edit_fzp_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Package Title' ),
		'fz_original_bin' => __( 'Original Bin' ),
		'fz_original_family' => __( 'Original Family' ),
		'fz_moduleId' => __( 'Module ID' ),		
		'fz_original_tags' => __( 'Original Tags' ),
		'date' => __( 'Import Date' )
	);

	return $columns;
}

add_action( 'manage_fz_fzp_posts_custom_column', 'fz_manage_fzp_columns', 10, 2 );

function fz_manage_fzp_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		case 'fz_original_family' :
			echo get_the_term_list( $post_id, 'fz_original_family');
			break;

		case 'fz_original_bin' :
			echo get_the_term_list( $post_id, 'fz_original_bin');
			break;

		case 'fz_moduleId' : 	
			echo get_post_meta( $post_id, 'fzpModuleId', true); 
			break;

		case 'fz_original_tags' :
		echo get_the_term_list( $post_id, 'fz_original_tags', null, ", ");
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
		'fz_original_bin',
		'fz_fzp',
		array(
			'label' => __( 'Bin' ),
			'rewrite' => array( 'slug' => 'fzb' )
		)
	);
	register_taxonomy(
		'fz_original_tags',
		'fz_fzp',
		array(
			'label' => __( 'Tags' ),
			'rewrite' => array( 'slug' => 'fzp-tag' )
		)
	);
	register_taxonomy(
		'fz_original_family',
		'fz_fzp',
		array(
			'label' => __( 'Family' ),
			'rewrite' => array( 'slug' => 'fzp-family' )
		)
	);


	//new taxonomies 
	register_taxonomy(
		'fz_taxonomy_2013',
		'fz_fzp',
		array(
			'label' => __( 'Taxonomy' ),
			'rewrite' => array( 'slug' => 'fzp-tax2013' ),
			'hierarchical' => true
		)
	);

}

?>