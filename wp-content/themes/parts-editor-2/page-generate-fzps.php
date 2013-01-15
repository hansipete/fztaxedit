<?php 
	
	/* Template Name: Generate part .fzps */
	
	get_template_part('templates/page', 'header');

	//define output path for fzp files
	$output_path = ABSPATH . 'output/pdb/';
	$input_path = ABSPATH . 'pdb/';

	//standard fzp header
	$header = '<?xml version="1.0" encoding="UTF-8"?>';
	
	//query all fz_fzp
	$query = new WP_Query('post_type=fz_fzp&posts_per_page=7');

	while( $query->have_posts() ) : $query->the_post();

		//load original fzp file
		$original_fzp = get_post_meta( $post->ID, "fzpFile", true);
		$original_bin = dirname($original_fzp);

		//new dom, import old fzp data
		$dom = new DOMDocument();
		$dom->load( $input_path . $original_fzp );

	// TODO: REPLACE CONTENTS
	//	replaceNodeContent($dom, "/module/description", get_the_content($post->ID));
	//	replaceNodeContent($dom, "/module/title", "ahja?".get_the_title($post->ID));


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