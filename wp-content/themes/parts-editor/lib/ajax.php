<?php

function fz_add_tax_to_post() {

	if( isset($_POST['post_id']) && isset($_POST['tax_id'])){

		$post_id = $_POST['post_id'];
		$tax_id = $_POST['tax_id'];

  		wp_set_post_terms( $post_id, $tax_id, 'fz_bins', true ); //true = append taxs instead of overwrite

  		//return updated terms
  		$taxonomy = wp_get_post_terms( $post_id, 'fz_bins' ); 

                foreach( $taxonomy as $tax ){
                    echo "<span class='part-tax'>".$tax->name."</span>";    
                }   
		 
		die(); //important?

	} // end if
}

add_action('wp_ajax_fz_add_tax_to_post', 'fz_add_tax_to_post');
add_action('wp_ajax_nopriv_fz_add_tax_to_post', 'fz_add_tax_to_post');//for users that are not logged in.

?>