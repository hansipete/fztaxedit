<h3>Bins</h3>
<div>
	<?php
			$args = array(
			  'taxonomy'     => 'fz_bins',
			  'orderby'      => 'name',
			  'show_count'   => 0,
			  'pad_counts'   => 0,
			  'hierarchical' => 1,
			  'title_li'	 => '',
			  'hide_empty'	 => 0
			);
	?>
	<ul>
		<?php wp_list_categories( $args ); ?>
	</ul>
	<label for="newtax">New bin</label><input type="text" name="newtax">
</div>	