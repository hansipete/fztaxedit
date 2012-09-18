<?php

add_action( 'init', 'fz_register_post_types' );

function fz_register_post_types() {

	register_post_type( 'fz_part',
		array(
			'labels' => array(
				'name' => __( 'Parts' ),
				'singular_name' => __( 'Part' )
			),
		'public' => true,
		'has_archive' => true,
		)
	);

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
			'rewrite' => array( 'slug' => 'package' )
		)
	);

}

?>