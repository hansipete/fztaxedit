<?php get_template_part('templates/page', 'header'); ?>

<?php
	// modify query
	query_posts( 'post_type=fz_part' );
?>

<table border="1" width="100%">
	<tr>
		<th>Part</th>
		<th>.lbr pack</th>
		<th>Standard Package</th>
		<th>Attributes</th>
		<th>Tags</th>
		<!-- <th>Library</th> -->
	</tr>

<?php 	$i=0; 

		while ( have_posts() ) : the_post(); 
		
		$class = ($i++ == 0) ? ' class="selected-row"' : '';
		?>
		
		<tr<?php echo $class; ?>>
  			<td><?php the_title(); ?></td>
  			<td>Power supply / management > Mkrocontrollers > Sensor networks</td>
  			<td>Sparkfun-AnalogIC.lbr</td>





  			
    		<td class="lbr_packages">
    			<?php 
    				$packages = wp_get_object_terms( $post->ID, 'fz_lbr_packages' );

    				foreach($packages as $package){
    					printf('<span style="font-size:0.7em"class=".tag-link-%s">%s</span><br>', $package->term_id, $package->name);
    				}
    			?>
    		</td>
    		<td>
    			<?php 
    			
						$fz_gcode_svn_baseurl = "http://fritzing.googlecode.com/svn/trunk/fritzing/parts/svg/user/pcb/";

    					$lbr_name = wp_get_post_terms( $post->ID, 'fz_lbr', array("fields" => "names"));
    					$lbr_name = str_replace('.lbr', '', strtolower($lbr_name[0]));
    					$lbr_package = $packages[0]->name;

    					$svg_file = $lbr_name . '_' . strtolower($lbr_package) . '_' . 'pcb' . '.svg';

    			?>

    			<!-- <img src="assets/svg.php?url=<?php echo $fz_gcode_svn_baseurl.$svg_file; ?>"> -->
    			<!-- <object data="assets/svg.php?url=<?php echo $fz_gcode_svn_baseurl.$svg_file; ?>" type="image/svg+xml"> -->

    			<?php

    				$delete_shit = array('GND', 'PAD', 'GROUND');
    				$std_pack_prefix = array('DIL', 'DIP', 'TSSOP', 'SOIC', 'SO', 'SOP', 'SOT', );

    				foreach($packages as $package){

    					// output only until first occurance of another character than A-Z
	 					//$name = preg_replace('/[^A-Za-z]+/','', $package->name);
	 					$name = preg_replace('/[^A-Za-z]/','', $package->name);

	 					printf('<span style="font-size:0.9em">%s</span><br>', $name);
	 				}
    			?>
    		</td>
    		<td>-</td>
    		<td>-</td>
    		<!-- <td><?php echo get_the_term_list( $post->ID, 'fz_lbr'); ?></td> -->
  		</tr>

<?php endwhile; ?>

</table>

<?php wp_pagenavi(); ?>