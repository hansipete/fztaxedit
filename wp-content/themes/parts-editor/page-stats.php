<?php get_template_part('templates/page', 'header'); ?>

<ul class="nav nav-tabs" id="myTab">
<?
	$parent_terms = get_terms('fz_taxonomy2', 'hide_empty=0&parent=0');
 	
 	foreach ( $parent_terms as $term ) : 
?>
	<li><a href="#<?php echo $term->slug; ?>"><?php echo $term->name; ?></a></li>
<?
	endforeach;
?>
</ul>
 
<div class="tab-content">
<? 	foreach ( $parent_terms as $term ) : ?>
		<div class="tab-pane" id="<?php echo $term->slug; ?>">
<?
$child_terms = get_terms('fz_taxonomy2', 'hide_empty=0&parent='.$term->term_id);
 		foreach ( $child_terms as $term ){

 			$term_id = $term->term_id;

 			echo "<h4>"  .$term->name . "</h4>";
echo "todo: hier jetzt die parts"

 		}
 ?>

		</div>
<?	endforeach; ?>	
</div>
 
<script>
  jQuery(function () {
    jQuery('#myTab a').click(function(){
    	jQuery(this).tab('show');
  	})
  });
</script>


<?php	

	// LIST UNTAXED ITEMS

	$query = "	SELECT * FROM wp_posts p 
					WHERE p.post_status = 'publish' 
					AND p.post_type='fz_fzp' 
					AND p.ID NOT IN (	SELECT object_id FROM wp_term_relationships tr 
										LEFT JOIN wp_term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) 
										WHERE taxonomy='fz_taxonomy2')
		";

	$myrows = $wpdb->get_results( $query );

	echo "<h4>Parts without any taxonomy2 applied:</h4>";

	foreach($myrows as $untaxed){
		echo $untaxed->post_title;
		edit_post_link("Edit", " ", "", $untaxed->ID);
		echo "<br>";
	}

?>