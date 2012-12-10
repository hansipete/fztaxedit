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

// custom loop for grouping fzp

function query_group_fzp( ){
    global $wpdb, $paged, $max_num_pages, $current_date;

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $post_per_page = intval(get_query_var('posts_per_page'));
    $offset = ($paged - 1)*$post_per_page;

    /* Custom sql here. I left out the important bits and deleted the body 
     as it will be specific when you have your own. */
    $sql = "
        SELECT *
        FROM {$wpdb->posts}
        WHERE post_type = 'fz_fzp'
        AND post_status = 'publish'
        GROUP BY {$wpdb->posts}.post_title 
        ORDER BY {$wpdb->posts}.post_date DESC";
        //LIMIT ".$offset.", ".$post_per_page."; ";   

    $sql_result = $wpdb->get_results( $sql, OBJECT);

    /* Determine the total of results found to calculate the max_num_pages
     for next_posts_link navigation */
    $sql_posts_total = $wpdb->get_var( "SELECT FOUND_ROWS();" );
    $max_num_pages = ceil($sql_posts_total / $post_per_page);

    return $sql_result;
}