<?php get_template_part('templates/page', 'header'); ?>

<?php

// loop through all -lbr files in /assets
foreach( glob('/Users/Hansi/Sites/fz-parts/assets/{*.lbr}', GLOB_BRACE) as $lib_file) 
{ 
	// load xml
	$lbr = simplexml_load_file( $lib_file );

	// packages branch
	$devicesets = $lbr->drawing->library->devicesets->deviceset;

	// loop packages
	foreach( $devicesets as $deviceset )
	{

		// store variables
		$partName = $deviceset['name'];
		$partDescription = $deviceset->description;
		$partLbrFile = basename($lib_file); // remove path - just filename.lbr
		$partPackages = array();


		// check if a post with the given title already exists
		$query = $wpdb->prepare(
	        'SELECT ID FROM ' . $wpdb->posts . '
	        WHERE post_title = %s
	        AND post_status = \'publish\'
	        AND post_type = \'fz_part\'',
	        $partName
	    );

	    $wpdb->query( $query );

	    if ( $wpdb->num_rows ) {
	    	// query has results – do not insert
	        printf("Part already exists: <b>%s</b><br/>", $partName);
	    } 
	    else {
			// create post object
			$fz_part = array(
				'post_type' => 'fz_part',
				'post_title' => $partName,
				'post_content' => $partDescription,
				'post_status' => 'publish',
				'post_author' => 1
			);

			// insert the post into the database
	  		$post_id = wp_insert_post( $fz_part );

			// apply the lbr-file category
			wp_set_post_terms( $post_id, $partLbrFile, 'fz_lbr');

			// loop through packages		
			foreach( $deviceset->devices->device as $device){
				// packages (dump "name", it's beliebig and replacable)
				$package = (string) $device['package'];
				
				// store in array
				array_push( $partPackages, $package );
			}

			// set terms
			wp_set_post_terms( $post_id, $partPackages, 'fz_lbr_packages');
				
			
			echo "<hr>";

			//exit;

			printf("Part inserted: <b>%s</b>", $partName);
		

		}

	}

	// next file
	echo "<h1>$lib_file done.</h1> next<br>";
}


?>
