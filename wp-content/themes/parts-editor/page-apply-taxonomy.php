<?php /* Template Name: Apply Taxonomy */ ?>

<?php get_template_part('templates/page', 'header'); ?>


<?php

	$page = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
    // number of tags to show per-page
    $per_page = 10;
    $offset = ( $page-1 ) * $per_page;
    $args = array( 'number' => $per_page, 'offset' => $offset, 'hide_empty' => 0 );

    $taxonomy = 'fz_original_family';
	$tax_terms = get_terms( $taxonomy, $args );

	echo '<ul>';
	foreach ($tax_terms as $tax_term) {
		echo '<li>' . '<a href="' . esc_attr(get_term_link($tax_term, $taxonomy)) . '" title="' . sprintf( __( "View all posts in %s" ), $tax_term->name ) . '" ' . '>' . $tax_term->name.'</a></li>';
	}
	echo '</ul>';

	$total_terms = wp_count_terms($taxonomy);
    $pages = ceil($total_terms/$per_page);

    

    // if there's more than one page
    if( $pages > 1 ):
        echo '<ul>';

        for ($pagecount=1; $pagecount <= $pages; $pagecount++):
            echo '<li><a href="'.get_permalink().'page/'.$pagecount.'/">'.$pagecount.'</a></li>';
        endfor;

        echo '</ul>';

        // link to show all
        echo '<a href="'.get_permalink().'?showall=true">show all</a>';
    endif;
?>