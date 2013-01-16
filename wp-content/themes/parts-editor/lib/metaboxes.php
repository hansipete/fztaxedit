<?php

// This file shows a demo for register meta boxes for ALL custom post types

add_action( 'admin_init', 'fz_register_meta_boxes' );

function fz_register_meta_boxes()
{
	if ( ! class_exists( 'RW_Meta_Box' ) )
		return;

	$prefix     = 'fzp';
	$meta_boxes = array();

	// 1st meta box
	$meta_boxes[] = array(
		'id'    => 'fileinfo',
		'title' => 'File Information',
		'pages' => array('fz_fzp'),

		'fields' => array(
			array(
				'name' => 'Family',
				'id'   => $prefix . 'Family',
				'type' => 'text',
			),
			array(
				'name' => 'Author',
				'id'   => $prefix . 'Author',
				'type' => 'text',
			),
			array(
				'name' => 'Bin',
				'id'   => $prefix . 'Bin',
				'type' => 'text',
			),
			array(
				'name' => 'Date',
				'id'   => $prefix . 'Date',
				'type' => 'text',
			),
			array(
				'name' => 'File',
				'id'   => $prefix . 'File',
				'type' => 'text',
			),
			array(
				'name' => 'Fritzing Version',
				'id'   => $prefix . 'FritzingVersion',
				'type' => 'text',
			),
			array(
				'name' => 'Url',
				'id'   => $prefix . 'Url',
				'type' => 'text',
			),
			array(
				'name' => 'Version',
				'id'   => $prefix . 'Version',
				'type' => 'text',
			),
			array(
				'name' => 'Module ID',
				'id'   => $prefix . 'ModuleId',
				'type' => 'text',
			)
		)
	);
	// Other meta boxes go here

	foreach ( $meta_boxes as $meta_box )
	{
		new RW_Meta_Box( $meta_box );
	}

	// append my custom metaboxes

	// family
	

}