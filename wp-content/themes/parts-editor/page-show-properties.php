<?php /* Template Name: Show Properties */ ?>
<?php get_template_part('templates/page', 'header'); ?>

<div id="properties" class="row">
<?php

$original_bin = "core";

$properties = array(array());

// loop through all -fzp files in /pdb/core
foreach( glob('/Users/Hansi/Sites/fz-parts/pdb/'.$original_bin.'/{*.fzp}', GLOB_BRACE) as $fzp_file) 
{ 
	// FOR EACH FILE…
	
	// …load xml
	$fzp = simplexml_load_file( $fzp_file );

	// store fzp data in variables for easy access
	$fzpProperties = $fzp->properties;

	// loop through properties, store post meta
	$fzpPropertiesArray = array();

	foreach ($fzpProperties->property as $property) {
		$name = (string) $property['name'];
		$value = (string) $property;

		// property key already in array 1. level - if not set it
		if( !isset($properties[$name]) )
			$properties[$name] = array();

		// value already in 2. level? if not - set it
		if( !in_array($value, $properties[$name]) )
			array_push($properties[$name], $value);

	}
}

foreach( $properties as $name => $values){
	echo "<div class='property span2 well'><h5>".$name."</h5>";
	echo "<ul class=\"inline\">";
	foreach ($values as $value) {
		echo "<li>$value</li>";
	}
	echo "</ul></div>";
}

?>
</div>
<script>
	jQuery('#properties').masonry({
  		itemSelector: '.property'
	});
</script>