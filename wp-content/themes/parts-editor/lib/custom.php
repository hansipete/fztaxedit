<?php

// Custom functions

if( !function_exists( 'mfields_overwrite_hide_taxonomies_from_admin' ) ) {
    add_action( 'add_meta_boxes', 'mfields_hide_taxonomies_from_admin' );
    function mfields_hide_taxonomies_from_admin() {
        global $wp_taxonomies;
        $hide = array( 
            'fz_lbr'
            );
        foreach( $wp_taxonomies as $name => $taxonomy ) {
            if( in_array( $name, $hide ) ) {
                remove_meta_box( 'tagsdiv-' . $name, 'fz_part', 'side' );
                add_meta_box(
                    'mfields_taxonomy_ui_' . $name,
                    $taxonomy->label,
                    'my_custom_taxonomy_handler_function',
                    'post',
                    'side',
                    'core',
                    array( 'taxonomy' => $name )
                    );
            }
        }
    }
}