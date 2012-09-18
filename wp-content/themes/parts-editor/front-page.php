<?php get_template_part('templates/page', 'header'); ?>

<pre>
<?php

// loop through all -lbr files in /assets
foreach( glob('/Users/Hansi/Sites/fz-parts/assets/{*.lbr}', GLOB_BRACE) as $lib_file) 
{ 
	// load xml
	$lbr = simplexml_load_file( $lib_file );

	// packages branch
	//$packages = $lbr->drawing->library->packages->package; 
	$devicesets = $lbr->drawing->library->devicesets->deviceset;

/*
<deviceset name="ACS712">
		<description>Hall-effect-based isolated linear current sensor</description>
		<gates>
			<gate name="G$1" symbol="ACS712" x="0" y="0"/>
		</gates>
		<devices>
			<device name="" package="SO08">
				<connects>
					<connect gate="G$1" pin="FILT" pad="6"/>
					<connect gate="G$1" pin="GND" pad="5"/>
					<connect gate="G$1" pin="IP+@1" pad="1"/>
					<connect gate="G$1" pin="IP+@2" pad="2"/>
					<connect gate="G$1" pin="IP-@3" pad="3"/>
					<connect gate="G$1" pin="IP-@4" pad="4"/>
					<connect gate="G$1" pin="VCC" pad="8"/>
					<connect gate="G$1" pin="VOUT" pad="7"/>
				</connects>
				<technologies>
					<technology name=""/>
				</technologies>
			</device>
		</devices>
	</deviceset>
*/

	// loop packages
	foreach( $devicesets as $deviceset )
	{

		// store variables
		$partName = $deviceset['name'];
		$partDescription = $deviceset->description;
		$partLbrFile = basename($lib_file); // remove path - just filename.lbr


		// check if a post with the given title already exists
		$query = $wpdb->prepare(
	        'SELECT ID FROM ' . $wpdb->posts . '
	        WHERE post_title = %s
	        AND post_type = \'fz_part\'',
	        $partName
	    );

	    $wpdb->query( $query );

	    if ( $wpdb->num_rows ) {
	        printf("Part already exists: <b>%s</b>", $partName);
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
			wp_set_post_terms( $post_id, $partLbrFile, 'fz_lbr', false);

			printf("Part inserted: <b>%s</b>", $partName);
		}

		//echo $partName . "<br>";

		// packages
		$partPackage = $deviceset->devices->device;
		foreach( $deviceset->devices->device as $device)
			echo " - " . $device['name'] . " / " . $device['package'] . "<br>";
		echo "<hr>";

		//exit;

	}

	// next file
	echo "<h1>$lib_file done.</h1> next<br>";
}


?>
