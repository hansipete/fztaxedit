<?php get_template_part('templates/page', 'header'); ?>
<?php

$absolute_path = '/Users/Hansi/Sites/fzp-2013/fritzing-read-only/fritzing/pdb/user/';

// grab all fzp files from directory - store in array
$files_array = glob( $absolute_path.'{*.fzp}', GLOB_BRACE);

// if array size 0 - no files found - output error
if( sizeof($files_array) == 0 ){
	wp_die('No fzp files found in given directory: ' . $absolute_path);
}

// loop through all -fzp files
foreach( $files_array as $fzp_file) 
{ 
	// FOR EACH FILE…
	
	// …load xml
	$fzp = simplexml_load_file( $fzp_file );

	// store fzp data in variables for easy access
	$fzpFile = basename($fzp_file);
	$fzpModuleId = (string) $fzp['moduleId'];
	$fzpDate = (string) $fzp->date;
	$fzpAuthor = (string) $fzp->author;
	$fzpDescription = (string) $fzp->description;
	$fzpTitle = (string) $fzp->title;
	$fzpLabel = (string) $fzp->label;
	$fzpUrl = (string) $fzp->url;
	$fzpTags = (Array) $fzp->tags->tag;
	$fzpProperties = $fzp->properties;
	$fzpFamily = (string) current( $fzpProperties->xpath('property[@name="family"]') ); //store seperately - its unique!

	// there are no "bins" fpr the sparkfun .fzp
	// derive them from the original .lbr
	$fzpModuleIdArray = explode('-', $fzpModuleId);
	$original_bin = $fzpModuleIdArray[1];

	$args = array(
        'post_type' => 'fz_fzp',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'fzpModuleId',
        'meta_value' => $fzpModuleId
    );

	$result = new WP_Query( $args );

    if ( $result->post_count > 0 ) {

    	$existing_guid = $result->posts[0]->guid;
    	
    	// query has results – do not insert
        echo "<p>Part already exists (Matching ModuleID): <a href='{$existing_guid}'>{$fzpModuleId}</a></p>";
    } 
    else {
		// create post object
		$fz_fzp = array(
			'post_type' => 'fz_fzp',
			'post_title' => $fzpTitle,
			'post_content' => $fzpDescription,
			'post_status' => 'publish',
			'post_author' => 1
		);

		// insert the post into the database
  		$post_id = wp_insert_post( $fz_fzp );

		// store post meta
		add_post_meta( $post_id, 'fzpFile', $fzpFile);
		add_post_meta( $post_id, 'fzpBin', $original_bin);
		add_post_meta( $post_id, 'fzpModuleId', $fzpModuleId);
		add_post_meta( $post_id, 'fzpFritzingVersion', 'n/a');
		add_post_meta( $post_id, 'fzpDate', $fzpDate);
		add_post_meta( $post_id, 'fzpAuthor', $fzpAuthor);
		add_post_meta( $post_id, 'fzpUrl', $fzpUrl);

		// loop through properties, store post meta
		$fzpPropertiesArray = array();

		foreach ($fzpProperties->property as $property) {
			$name = (string) $property['name'];
			$value = (string) $property;

			add_post_meta( $post_id, $name, $value);
		}

		// store family
		wp_set_post_terms( $post_id, $fzpFamily, 'fz_original_family');

		// store bin
		wp_set_post_terms( $post_id, $original_bin, 'fz_original_bin');		

		// tags, store tax
		wp_set_post_terms( $post_id, $fzpTags, 'fz_original_tags');

		echo "Part inserted: <b>$fzpTitle</b><br>";
	
	}

}


?>
