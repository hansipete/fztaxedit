<?php

function fz_apply_tax_to_family() {

	if( isset($_POST['family_id']) && isset($_POST['term_id'])){

		$taxonomy = 'fz_taxonomy2';
		$family_id = $_POST['family_id'];
		$term_id = $_POST['term_id'];

		// get parts linked to family
		$part_ids = getPartIdsByFamilyId($family_id);

		// update tax in every part
		foreach($part_ids as $part_id){

			// get all existing terms from part
			$terms = wp_get_post_terms( $part_id, $taxonomy, array("fields" => "ids") );

			// if $term_id is in $terms
			if( has_term( $term_id, $taxonomy, $part_id ) )
	    		$terms = array_diff($terms, array($term_id)); //term löschen
	    	else
	    		array_push($terms, $term_id);

	    	// update post
	  		wp_set_post_terms( $part_id, $terms, $taxonomy, false ); //false = replace tags
		}
		
		die();

	} // end if
}
add_action('wp_ajax_fz_apply_tax_to_family', 'fz_apply_tax_to_family');

function fz_remove_tax_from_family() {

	if( isset($_POST['family_id']) && isset($_POST['term_id'])){

		$family_id = $_POST['family_id'];
		$term_id = $_POST['term_id'];

  		wp_set_post_terms( $family_id, $term_id, 'fz_taxonomy2', false ); //true = append taxs instead of overwrite

  		//return updated terms
  		$taxonomy = wp_get_post_terms( $family_id, 'fz_taxonomy2' ); 

                foreach( $taxonomy as $tax ){
                    echo "<span class='part-tax'>".$tax->name."</span>";    
                }   
		 
		die(); //important?

	} // end if
}
add_action('wp_ajax_fz_remove_tax_from_family', 'fz_remove_tax_from_family');

?>