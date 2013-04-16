<?php /* Template Name: Apply Taxonomy */ ?>

<input type="checkbox" name="lonely-only"> Show lonely families only

<?php get_template_part('templates/page', 'header'); ?>

<div id="families" class="row">

<?php

	$page = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
    // number of tags to show per-page
    $per_page = 20;
    $offset = ( $page-1 ) * $per_page;
    $args = array( 'number' => $per_page, 'offset' => $offset, 'hide_empty' => 0 );

    $taxonomy = 'fz_original_family';
	$tax_terms = get_terms( $taxonomy, $args );

	foreach ($tax_terms as $tax_term) {
		
		$family = $tax_term->name;
		$family_id = $tax_term->term_id;
		
		//get the parts of the group
		$part_ids = getPartIdsByFamilyId($family_id);
		$part_count = count($part_ids);
		$repres_id = null;

		//get their representative
		foreach($part_ids as $part_id){
			$repres = get_post_meta( $part_id, 'representative', true );
			
			if( !empty($repres) )
				$repres_id = $part_id;
		}

		echo "<div class='span6 thumbnail family family-{$family_id}'>
				<h4>$family <small>($part_count parts)</small></h4>
				
				<dl class='dl-horizontal linked-parts'>
				  <dt>Linked Parts</dt>";

		// echo parts
		foreach($part_ids as $part_id){
			$title = get_the_title($part_id);

			echo "<dd>$title</dd>";
		}

		echo "</dl>";

		the_applied_taxononmies($part_id);
		
		echo "</div>";
	}

	$total_terms = wp_count_terms($taxonomy);
    $pages = ceil($total_terms/$per_page);

    // if there's more than one page
    if( $pages > 1 ):
        echo '<div class="pagination pagination-centered">
    		  	<ul>';

        for ($pagecount=1; $pagecount <= $pages; $pagecount++):
        	$disabled = ($page==$pagecount) ? ' btn-primary disabled' : '';
        	$permalink = get_permalink($post->ID);
            echo "<li class=\"{$disabled}\"><a href=\"{$permalink}page/{$pagecount}/\">{$pagecount}</a></li>";
        endfor;

        echo '	</ul>
			  </div>';      
    endif;
?>
</div>