<?php 
	
	/* Template Name: Generate bins */ 
	
	get_template_part('templates/page', 'header');

	//taxonomy to use (there are a few to test different priorities)
	$taxonomy = 'fz_taxonomy2';

	//default vars
	$output_path = ABSPATH . 'output/bins/';
	$fritzingVersion = '0.1';
	$moduleId = 'KeeneAhnung';

	$header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	$module = "<module fritzingVersion=\"$fritzingVersion\" moduleId=\"$moduleId\" />";


	//get bins (= first level in taxonomy)
	$bins = get_terms_by_parent($taxonomy, 0);

	//for every bin
	foreach ( $bins as $bin ) {

		//slug for later filename
		$filename = $bin->slug;
		
		//create fzb file
		$dom = new DOMDocument();
		$dom->loadXml( $header . $module );

		//create nodes
		$title_node = $dom->createElement('title', $bin->name);
		$instances_node = $dom->createElement('instances');

		//append to root (module)
		$dom->appendChild($title_node);
		$dom->appendChild($instances_node);

		//get groups by bin_id
		$groups = get_term_children( $bin->term_id, $taxonomy );

		//output title of bin
		echo "<h2>{$bin->name} <small>({$filename}.fzb)</small></h2>";

		//loop groups get parts
		foreach ( $groups as $group ) {
			
			//init group term values
			$group = get_term_by( 'id', $group, $taxonomy );

			//create space instance with group title
			$instance_node = createBinInstanceNode($dom, '__spacer__', 0, $group->name);

			//append instance to instances
			$instances_node->appendChild($instance_node);

			echo "<table class='table table-condensed'>
					<caption><h4>{$group->name}</h4></caption>
					<thead>
						<tr>
							<th>Part package</th>
							<th>Model Index</th>
							<th>moduleId</th>
						</tr>
					</thead>
					<tbody>";

			//get the parts of the group
			$args = array(
						'post_type' => 'fz_fzp',
						'tax_query' => array(
							array(
								'taxonomy' => $taxonomy,
								'field' => 'id',
								'terms' => $group->term_id
							)
						)
					);
			$query = new WP_Query( $args );

			foreach($query->posts as $part){
				
				//get vars				
				$post_id = $part->ID;
				$moduleId = get_post_meta($post_id, 'fzpModuleId', true);
				$package = get_post_meta($post_id, 'fzpFile', true);

				//generate nodes
				$instance_node = createBinInstanceNode($dom, $moduleId, 0, $package);

				//append node
				$instances_node->appendChild($instance_node);

				//output 
				echo "<tr>
						<td>$package</td>
						<td>0</td>
						<td>$moduleId</td>
					  </tr>";

			}
								
			echo "	</tbody>
				  </table>";

		}

		//make up output
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		//save fzb
		$dom->save( $output_path . $filename . '.fzb' );

		//output success
		echo "<div class='alert alert-success'>output/bins/{$bin->slug}.fzb written successfully.</div>";
	}
	
	
?>