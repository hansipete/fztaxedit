<?php
/*
Template Name: Show structure of bin
*/
?>

<?php get_template_part('templates/page', 'header'); ?>

<?php
	
	// …load xml
	$bin = simplexml_load_file('/Users/Hansi/Sites/fritzing2/bins/core.fzb');

	$title = (string) $bin->title;
	$instances = $bin->instances;

	echo "<div class=\"alert alert-success\">Imported data from /bin/core.fzb</div>";

	echo "<h2>$title</h2>";

	foreach( $instances->instance as $instance ){
		$moduleIdRef = $instance['moduleIdRef'];
		$modelIndex = $instance['modelIndex'];
		$path = $instance['path'];
		$file = basename($path);

		if ($moduleIdRef == '__spacer__') {
			// print headline for group
			echo "";

			//start new table
			echo "<table class=\"table table-bordered\">
			<caption>
				<h3>$path</h3>
			</caption>
			<thead>
    			<tr>
					<th>Package</th>    			
					<th>Model Index</th>
					<th>moduleIdRef</th>
				</tr>
			</thead>
			<tbody>";
		} else {
			//add row
			echo "<tr>
				<td title='$path'>{$file}</td>
				<td>{$modelIndex}</td>
				<td>{$moduleIdRef}</td>				
			  </tr>";
		}
		
	}

	echo "	</tbody>
		</table>";

?>