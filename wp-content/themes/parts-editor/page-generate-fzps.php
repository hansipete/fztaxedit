<?php 
	
	/* Template Name: Generate part .fzps */
	
	get_template_part('templates/page', 'header');

	//define output path for fzp files
	$output_path = ABSPATH . 'output/pdb/';
	$input_path = ABSPATH . 'fritzing/pdb/';
	$original_bin_path = 'core/';

	//standard fzp header
	$header = '<?xml version="1.0" encoding="UTF-8"?>';
	
	//query all fz_fzp
	$query = new WP_Query('post_type=fz_fzp&s=MMA8452Q&posts_per_page=1'); //get süpecicif result

	echo "<pre>";

	while( $query->have_posts() ) : $query->the_post();

		echo $post->post_title;

		//load original fzp file
		$original_fzp = get_post_meta( $post->ID, "fzpFile", true);

		//check original bin (core vs. user path)
		$bin_list = wp_get_post_terms($post->ID, 'fz_original_bin', array("fields" => "names"));
		$original_bin = current($bin_list);

		//if bin is assigned - was not core / change to user/
		if(!empty($original_bin))
			$original_bin_path = 'user/';

		//new dom, import old fzp data
		$fzp = simplexml_load_file( $input_path . $original_bin_path . $original_fzp );
		
		// INJECT FAMILY PROPERTY
		$family_node = $fzp->properties->xpath('property[@name="family"]');

		$xml = dom_import_simplexml( current($family_node) );
		$xml->nodeValue = "FICKI!"; 

		//$family = (string) current($fzp->properties->xpath('property[@name="family"]'));

		$dom = new DOMDocument('1.0');
		$xml = $dom->importNode($xml, true);
		$xml = $dom->appendChild($xml);
		
		//nice formatting
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		//make bin dir
		$bin_path = $output_path . $original_bin;

		if( !is_dir($bin_path) )
			mkdir( $bin_path, 0777 );

		//Save XML to file - remove this and following line if save not desired
		$fzp_size = $dom->save( $output_path . $original_fzp );

		echo "Wrote <b>{$original_fzp}</b> ({$fzp_size} bytes)<br>\n";

	endwhile;

	echo "<b class=\"success\">All done.</b>";
?>