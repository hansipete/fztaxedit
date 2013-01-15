<?php get_template_part('templates/page', 'header'); ?>
<pre>
<?php

$original_bin = "core";

// loop through all -fzp files in /pdb/core
foreach( glob('/Users/Hansi/Sites/fz-parts/pdb/'.$original_bin.'/{*.fzp}', GLOB_BRACE) as $fzp_file) 
{ 
	// FOR EACH FILE…
	
	// …load xml
	$fzp = simplexml_load_file( $fzp_file );

	// store fzp data in variables for easy access
	$fzpFile = $original_bin . "/" . basename($fzp_file);
	$fzpFritzingVersion = (string) $fzp['fritzingVersion'];
	$fzpModuleId = (string) $fzp['moduleId'];
	$fzpVersion = (string) $fzp->version;
	$fzpDate = (string) $fzp->date;
	$fzpAuthor = (string) $fzp->author;
	$fzpDescription = (string) $fzp->description;
	$fzpTitle = (string) $fzp->title;
	$fzpLabel = (string) $fzp->label;
	$fzpUrl = (string) $fzp->url;
	$fzpTags = (Array) $fzp->tags->tag;
	$fzpProperties = $fzp->properties;
	$fzpFamily = (string) $fzpProperties->xpath('property[@name="family"]')[0]; //store seperately - its unique!


	// check if a post with the given moduleId already exists
	$query = $wpdb->prepare(
		"SELECT * FROM $wpdb->posts AS posts, $wpdb->postmeta AS postmeta
		 WHERE posts.post_type = 'fz_fzp'
		 AND postmeta.meta_key = 'fzpModuleId'
		 AND postmeta.meta_value = '%s'", 
		 $fzpModuleId
	);

	$wpdb->query( $query );

    if ( $wpdb->num_rows ) {
    	// query has results – do not insert
        printf("Part already exists (Matching .fzp Module ID): <b>%s</b><br/>", $fzpModuleId);
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
		add_post_meta( $post_id, 'fzpBin', $original_bin);
		add_post_meta( $post_id, 'fzpFile', $fzpFile);
		add_post_meta( $post_id, 'fzpFritzingVersion', $fzpFritzingVersion);
		add_post_meta( $post_id, 'fzpModuleId', $fzpModuleId);
		add_post_meta( $post_id, 'fzpVersion ', $fzpVersion);
		add_post_meta( $post_id, 'fzpDate', $fzpDate);
		add_post_meta( $post_id, 'fzpAuthor', $fzpAuthor);
		add_post_meta( $post_id, 'fzpUrl', $fzpUrl);
		add_post_meta( $post_id, 'fzpFamily' , $fzpFamily);

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

		printf("Part inserted: <b>%s</b>", $fzpTitle);
	
	}

	// next file
	echo "<em style='color: green;'>$fzp_file done.</em><hr>";
}


?>
