<?php

function fz_add_tax_to_post() {

	if( isset($_POST['post_id']) && isset($_POST['tax_id'])){

		$taxonomy = 'fz_taxonomy2';
		$post_id = $_POST['post_id'];
		$tax_id = $_POST['tax_id'];

		// get all existing terms from post
		$terms = wp_get_post_terms( $post_id, $taxonomy, array("fields" => "ids") );

		// if $tax_id is in $terms
		if( has_term( $tax_id, $taxonomy, $post_id ) )
    		$terms = array_diff($terms, array($tax_id)); //term löschen
    	else
    		array_push($terms, $tax_id);

  		wp_set_post_terms( $post_id, $terms, $taxonomy, false ); //false = replace tags

  		// update tags of fzps with the samel _family_
  		
  		//1. query the taxonomy of the current fzp
  		$family = wp_get_post_terms( $post_id, 'fz_original_family', array("fields" => "ids") );

  		//2. get all other posts linked to that family
		$args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'fz_original_family',
					'field' => 'id',
					'terms' => $family
				)
			)
		);
		$query = new WP_Query( $args );

		// set their tags too!
		foreach( $query->posts as $member){
			wp_set_post_terms( $member->ID, $terms, $taxonomy, false ); 
		}

		echo hey_top_parents('fz_taxonomy2', $post_id);
		 
		die(); //important?

	} // end if
}
add_action('wp_ajax_fz_add_tax_to_post', 'fz_add_tax_to_post');

function fz_remove_tax_from_post() {

	if( isset($_POST['post_id']) && isset($_POST['tax_id'])){

		$post_id = $_POST['post_id'];
		$tax_id = $_POST['tax_id'];

  		wp_set_post_terms( $post_id, $tax_id, 'fz_taxonomy2', false ); //true = append taxs instead of overwrite

  		//return updated terms
  		$taxonomy = wp_get_post_terms( $post_id, 'fz_taxonomy2' ); 

                foreach( $taxonomy as $tax ){
                    echo "<span class='part-tax'>".$tax->name."</span>";    
                }   
		 
		die(); //important?

	} // end if
}
add_action('wp_ajax_fz_remove_tax_from_post', 'fz_remove_tax_from_post');

?>