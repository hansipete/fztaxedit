<?php /* Template Name: Bin - Group - Family - Index */ ?>

<?php get_template_part('templates/page', 'header'); ?>

<?php

	$taxonomy = 'fz_taxonomy2';
	$familyTaxonomy = 'fz_original_family';

	//get bins (= first level in taxonomy)
	$bins = get_terms_by_parent($taxonomy, 0);

	print_r($bins);

	//for every bin
	foreach ( $bins as $bin ) {

		//get groups by bin_id
		$groups = get_term_children( $bin->term_id, $taxonomy );

		//output title of bin
		echo "<div class='well'>
				<h2>{$bin->name}</h2>";

		//loop groups get parts
		foreach ( $groups as $group ) {
			
			//init group term values
			$group = get_term_by( 'id', $group, $taxonomy );

			// output group title
			echo "<h4>{$group->name}</h4>";

			//get the parts of the group
			$args = array(
						'post_type' => 'fz_fzp',
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field' => 'id',
								'terms' => $group->term_id
							)
						)
					);
			$query = new WP_Query( $args );

			//store all ids in an array
			$all_parts_ids = array();
			foreach($query->posts as $part)
				array_push($all_parts_ids, $part->ID);

			//get all original family tags from ids
			$part_families_ids = wp_get_object_terms($all_parts_ids, $familyTaxonomy, array('fields'=>'ids'));

			$part_families_ids = array_unique($part_families_ids); //remove duplettes

			foreach($part_families_ids as $family_id){
				$family = get_term_by('id', $family_id, $familyTaxonomy);
				
				echo "<span class=\"label label-info\">{$family->name} <small>{$family->count}</small></span> ";
			}

		}

		echo "</div>";

	}
?>