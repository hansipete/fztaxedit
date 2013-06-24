<?php

function fz_change_part_category(){

	// change term -> parent to new category id

	$taxonomy = 'fz_taxonomy_2013';

	$cat_term_id = $_POST['cat_term_id'];
	$part_term_id = $_POST['part_term_id'];

    if(!empty($cat_term_id)) {

    	wp_update_term($part_term_id, $taxonomy, array('parent' => $cat_term_id));

    } else {
        /* 
        In case of incorrect value or error you should return HTTP status != 200. 
        Response body will be shown as error message in editable form.
        */

        header('HTTP 400 Bad Request', true, 400);
        echo "Empty titles not allowed! :-)";
    }

    die();
}
add_action('wp_ajax_fz_change_part_category', 'fz_change_part_category');


function fz_check_part(){
	
	$taxonomy = 'fz_taxonomy_2013';

	$term_id = $_POST['term_id'];
	$checked = $_POST['checked'];

    if(!empty($term_id)) {

    	update_option( "part_{$term_id}_checked", $checked );

    } else {
        /* 
        In case of incorrect value or error you should return HTTP status != 200. 
        Response body will be shown as error message in editable form.
        */

        header('HTTP 400 Bad Request', true, 400);
        echo "Empty titles not allowed! :-)";
    }

    die();
}

add_action('wp_ajax_fz_check_part', 'fz_check_part');

function fz_popover_fzps(){
	
	$taxonomy = 'fz_taxonomy_2013';

	$term_id = $_POST['term_id'];

    if(!empty($term_id)) {

    	$args = array(
						'post_type' => 'fz_fzp',
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field' => 'id',
								'terms' => $term_id
							)
						)
		);
		
		$fzps = get_posts( $args );

		echo "<ul class='thumbnails'>";
  				
		foreach($fzps as $fzp){
			$package = get_post_meta($fzp->ID, 'package', true);
			$terms = wp_get_post_terms($fzp->ID, 'fz_original_bin');
			$bin = current($terms);
			if(!is_object($bin)){
				$bin->slug = 'ERROR';
			}
			$svg_url = get_bloginfo('wpurl') . '/fritzing/parts/svg/user/breadboard/' . 'sparkfun-' . $bin->slug . '_' . $package . '_breadboard.svg';
      		$excerpt = substr ($fzp->post_content, 0, 150 );

			echo "<li class='span1'>
    				<div class='thumbnail'>
      					<img data-src='$svg_url' src='$svg_url' alt=''>
      					<h3 style='font-size: 10px; line-height: 12px;'>{$fzp->post_title}</h3>
      					<p style='font-size: 9px; line-height: 10px;'>{$excerpt}</p>
    				</div>
  				</li>";
		}

		echo "</ul>";

    } else {
        /* 
        In case of incorrect value or error you should return HTTP status != 200. 
        Response body will be shown as error message in editable form.
        */

        header('HTTP 400 Bad Request', true, 400);
        echo "Empty titles not allowed! :-)";
    }

    die();
}

add_action('wp_ajax_fz_popover_fzps', 'fz_popover_fzps');



function fz_merge_parts(){
	
	$taxonomy = 'fz_taxonomy_2013';

	$target_term_id = $_POST['target_term_id']; 
	$term_id = $_POST['term_id'];

	// get term_id post ids
	// post ids: set new term on them
	// delete old unused term

    if(!empty($target_term_id)) {

    	// get all fzp linked with original term
		$args = array(
						'post_type' => 'fz_fzp',
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field' => 'id',
								'terms' => $term_id
							)
						)
		);
		
		$fzps = get_posts( $args );

		// inject new term_id
		foreach($fzps as $fzp){
			wp_set_post_terms( $fzp->ID, $target_term_id, $taxonomy);
		}

		// clear old term
		wp_delete_term($term_id, $taxonomy);

    } else {
        /* 
        In case of incorrect value or error you should return HTTP status != 200. 
        Response body will be shown as error message in editable form.
        */

        header('HTTP 400 Bad Request', true, 400);
        echo "Empty titles not allowed! :-)";
    }

    die();
}

add_action('wp_ajax_fz_merge_parts', 'fz_merge_parts');

function fz_inline_editing(){
	
	$taxonomy = 'fz_taxonomy_2013';

	$type = $_POST['type'];
    $id = $_POST['id'];
    $value = $_POST['value'];

    /*
     Check submitted value
    */
    if(!empty($id)) {

    	if( $type == 'part' ){

    		if(!empty($value))
    			wp_update_term( $id, $taxonomy, array('name' => $value) );
    		else
    			wp_delete_term( $id, $taxonomy); //delete part term if value is empty…
    			

        }
        else if( $type == 'category'){

        	if(!empty($value))
        		wp_update_term( $id, $taxonomy, array('name' => $value) );
        	else
        		wp_delete_term( $id, $taxonomy); //delete category if value is empty…

        }

    } else {
        /* 
        In case of incorrect value or error you should return HTTP status != 200. 
        Response body will be shown as error message in editable form.
        */

        header('HTTP 400 Bad Request', true, 400);
        echo "Empty titles not allowed! :-)";
    }

    die();
}

add_action('wp_ajax_fz_inline_editing', 'fz_inline_editing');



function fz_update_term_name(){

	$taxonomy = 'fz_taxonomy_2013';

	if( !empty($_POST['term_id']) && !empty($_POST['new_part_name']) ){

		wp_update_term( $_POST['term_id'], $taxonomy, array('name' => $_POST['new_part_name']) );

		$return['type'] = 'success';
	}
	else {
		$return['type'] = 'error';
	}

	return $return; 
	die();
};
add_action('wp_ajax_fz_update_term_name', 'fz_update_term_name');


function fz_new_part(){


	$taxonomy = 'fz_taxonomy_2013';

	// use selected cat id as parent by default
	$category = (int) $_POST['cat'];
	$fzp_id = (int) $_POST['fzp_id'];

	// create new part
	$part = wp_insert_term(
        $_POST['new_part_name'],
        $taxonomy, // the taxonomy
        array( 'parent'=> $category )
    );

    //need to have this (bug in wp?)
    delete_option("fz_taxonomy_2013_children");

	wp_set_post_terms( $fzp_id, $part['term_id'], $taxonomy);

	echo $part['term_id'];

	die();
};
add_action('wp_ajax_fz_new_part', 'fz_new_part');	


function fz_remove_fzp_from_part(){

	/*
		part_term_id:   $part_ul.data('part-term-id'),
        fzp_id:         $fzp_a.data('fzp-id')
    */
	
	$taxonomy = 'fz_taxonomy_2013';
	$part_term_id = $_POST['part_term_id'];
	$fzp_id = $_POST['fzp_id'];

	my_remove_post_term( $fzp_id, (int) $part_term_id, $taxonomy );

    die();
}
add_action('wp_ajax_fz_remove_fzp_from_part', 'fz_remove_fzp_from_part');	

function fz_add_fzp_to_part(){

	/*
		part_term_id:   $part_ul.data('part-term-id'),
        fzp_id:         $fzp_a.data('fzp-id')
    */
	
	$taxonomy = 'fz_taxonomy_2013';
	$part_term_id = $_POST['part_term_id'];
	$fzp_id = $_POST['fzp_id'];

	wp_set_object_terms( $fzp_id, (int) $part_term_id, $taxonomy, true); // append terms (when a part has multiple occurances)

	$result['type'] = 'success';

	return $result; 

    die();
}
add_action('wp_ajax_fz_add_fzp_to_part', 'fz_add_fzp_to_part');	


/*
	Called from edit interface. Create new categpory in bin.
*/
function fz_new_category_in_bin(){
	$taxonomy = 'fz_taxonomy_2013';
	$term_id = $_POST['term_id'];
	$category_name = $_POST['category_name'];

	$category_id = wp_insert_term(
        $category_name, // the term 
        $taxonomy, // the taxonomy
        array( 'parent'=> $term_id )
    );

    //need to have this (bug in wp?)
    delete_option("fz_taxonomy_2013_children");

    

    echo $category_id['term_id'];

    die();
}
add_action('wp_ajax_fz_new_category_in_bin', 'fz_new_category_in_bin');	

function fz_create_part_from_category(){

	$taxonomy = 'fz_taxonomy_2013';

	$term_id = $_POST['term_id'];
	$term = get_term( $term_id, $taxonomy );

	// create new part form category name
	$part_id = wp_insert_term(
        $term->name, // the term 
        $taxonomy, // the taxonomy
        array( 'parent'=> $term_id )
    );

	// query all parts linked to this term
    $my_query = new WP_Query( array(
                                     'post_type' => 'fz_fzp', 
                                     'tax_query' => array(
                                       array(
                                                           'taxonomy' => 'fz_taxonomy_2013',
                                                           'terms' => $term_id,
                                                           'field' => 'term_id'
                                       )
    )));

    while ( $my_query->have_posts() ){ $my_query->the_post();

    	//change taxonomy for every fzp
    	wp_set_object_terms( $my_query->post->ID, $part_id, $taxonomy);            	

    }

    //need to have this (bug in wp?)
    delete_option("fz_taxonomy_2013_children");

    die();
}
add_action('wp_ajax_fz_create_part_from_category', 'fz_create_part_from_category');



function fz_apply_tax_to_family() {

	$taxonomy = 'fz_taxonomy_2013';

	if( isset($_POST['family_id']) && isset($_POST['term_id'])){

		
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

		// echo updated taxonomy DOM
		the_applied_taxononmies($part_id, true);

	} // end if

	die();
}
add_action('wp_ajax_fz_apply_tax_to_family', 'fz_apply_tax_to_family');

function fz_remove_tax_from_family() {

	if( isset($_POST['family_id']) && isset($_POST['term_id'])){

		$family_id = $_POST['family_id'];
		$term_id = $_POST['term_id'];

  		wp_set_post_terms( $family_id, $term_id, 'fz_taxonomy_2013', false ); //true = append taxs instead of overwrite

  		//return updated terms
  		$taxonomy = wp_get_post_terms( $family_id, 'fz_taxonomy_2013' ); 

                foreach( $taxonomy as $tax ){
                    echo "<span class='part-tax'>".$tax->name."</span>";    
                }   
		 
		die(); //important?

	} // end if
}
add_action('wp_ajax_fz_remove_tax_from_family', 'fz_remove_tax_from_family');

?>