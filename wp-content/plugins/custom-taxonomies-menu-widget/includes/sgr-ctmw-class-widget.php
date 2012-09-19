<?php
/**
 * SGR_Widget_Custom_Taxonomies_Menu class
 *
 * @author Ade WALKER  (email : info@studiograsshopper.ch)
 * @copyright Copyright 2010-2012
 * @package custom_taxonomies_menu_widget
 * @version 1.2.2
 *
 * Defines widget class and registers widget
 * Any helper functions outside the class, but used by the class, are also defined here
 *
 * @since 1.2
 */

 
/**
 * Prevent direct access to this file
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( _( 'Sorry, you are not allowed to access this file directly.' ) );
}



/**
 * Our widget class
 *
 *
 * @since 1.0
 */
class SGR_Widget_Custom_Taxonomies_Menu extends WP_Widget {

	function __construct() {
		
		$id_base = 'sgr-custom-taxonomies-menu';
		
		$widget_ops = array(
			'classname' => $id_base,
			'description' => __( 'Display navigation for your custom taxonomies.', SGR_CTMW_DOMAIN )
			);
			
		$control_ops = array(
			'id_base' => $id_base,
			'width'   => 505,
			'height'  => 350,
		);
		
		$this->WP_Widget( $id_base, __( 'Custom Taxonomies Menu Widget', SGR_CTMW_DOMAIN), $widget_ops, $control_ops );
	}
	
	
	/**
	 * Get all custom taxonomies on this install 
	 *
	 * @since 1.2
	 *
	 * @return Object of all custom taxonomies if there are any, or false if no taxonomies
	 */
	function taxonomies() {
	 
		$args = array(
  			'public'   => true,
  			'_builtin' => false
			);
			
		$output = 'objects'; // or names
		$operator = 'and'; // 'and' or 'or'
		$custom_taxonomies = get_taxonomies( $args, $output, $operator );
		
		return $custom_taxonomies;
	}
	
	
	/**
	 * Default widget args
	 *
	 * Sets defaults and merges them with current $instance settings
	 *
	 * Apart from 'title', all these args are the same as those used by wp_list_categories
	 * which we will need for populating the menu
	 *
	 * Note: 'include' is a placeholder for the array of selected taxonomy terms
	 * which will be passed to wp_list_categories for each taxonomy
	 *
	 * Additional settings are dynamically created by the class:
	 * $instance['include_'.$custom_taxonomy->name], array for each taxonomy, containing its selected terms
	 * $instance['known_'.$custom_taxonomy->name], array for each taxonomy, containing all terms when widget form is saved
	 * $instance['show_tax_'.$custom_taxonomy->name], string, "true" if tax name is checked in the widget form
	 *
	 * @since 1.2
	 *
	 * @param $instance, current $instance settings
	 * @return $instance, object of widget defaults merged with current $instance settings
	 */
	function defaults( $instance ) {
	 
		$instance = wp_parse_args( (array)$instance, array(
			'title' => '',				// string	Widget title to be displayed
			'include' => array(),		// array	Placeholder for the selected terms used in wp_list_categories
			'order' => '',				// string	ASC or DESC
			'orderby' => '',			// string	name, id, slug, count, term_group
			'show_count' => '',			// on/off
			'show_tax_title' => '',		// on/off
			'show_hierarchical' => '',	// on/off
			'hide_empty' => '',			// on/off
		) );
		
		return $instance;
	}
	
	/**
	 * Echo the custom taxonomies menu
	 *
	 * @since 1.0
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Display arguments including before_title, after_title, before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget
	 */
	function widget( $args, $instance ) {
		
		extract( $args );
		
		// Get defaults
		$instance = $this->defaults( $instance );
		
		echo $before_widget;
		
		if ( $instance['title'] ) echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;
			
		// Get taxonomies
		$custom_taxonomies = $this->taxonomies(); 
		
		// If no custom taxonomies exist...
		if( !$custom_taxonomies ) {
			echo "\n" . printf( '<p>%s</p>', __( 'There are no registered custom taxonomies.', SGR_CTMW_DOMAIN ) ) . "\n";
  			echo $after_widget;
  			return;
  		}
  			
  		// Loop through each taxonomy and display its terms using wp_list_categories
  		foreach ( $custom_taxonomies as $custom_taxonomy ) {
  				
  			if( isset( $instance['show_tax_' . $custom_taxonomy->name] ) && $instance['show_tax_' . $custom_taxonomy->name] == "true" ) {
  				
  				// Need to check whether any new terms have been added since the widget form was last saved
  				// Get all terms that currently exist now, for this custom taxonomy
				$current_terms = get_terms( $custom_taxonomy->name, array( 'hide_empty' => 0 ) );
  				
  				// Get all terms (checked and unchecked) that were present in the widget form when the widget form was last saved
  				if( isset( $instance['known_' . $custom_taxonomy->name] ) ) {
  					
  					$known_terms = $instance['known_' . $custom_taxonomy->name];
  					
  				} else {
  					
  					// Deal with upgraded plugin situation, or new install, and widget form not yet saved.
  					// Prevents PHP error
  					$known_terms = array();
  				}
  				
  				// Loop through the terms and look for newly added ones
  				foreach ( $current_terms as $current_term ) {
  					
  					// Do we have a new term added since the widget form was last saved?
  					if( ! in_array( $current_term->term_id, $known_terms ) ) {
  						
  						// Add any new terms to the $instance['include_' . $custom_taxonomy->name] array
  						$instance['include_' . $custom_taxonomy->name][] = $current_term->term_id;
  					}
  				}
  				
  				// We're good to go, let's build the menu
  				$args_list = array(
  					'taxonomy' => $custom_taxonomy->name, // Registered tax name
  					'title_li' => $instance['show_tax_title'] ? $custom_taxonomy->labels->name : '', // Tax nice name
  					'include' => implode( ',', ( array )$instance['include_' . $custom_taxonomy->name] ), // Selected terms
  					'orderby' => $instance['orderby'],
  					'show_count' => $instance['show_count'],
  					'order' => $instance['order'],
  					'echo' => '0',
					'hierarchical' => $instance['show_hierarchical'] ? true : false,
					'hide_empty' => $instance['hide_empty'] ? true : false,
  				 	);
  					 
  				$list = wp_list_categories( $args_list );
  				
  				echo "\n" . '<ul>' . "\n";
  				
  				echo $list;
  				  				
  				echo "\n" . '</ul>' . "\n";
  			}
   		}     
				
		echo $after_widget;
	}

	/**
	 * Update instance options when form is saved.
	 *
	 * First, we run some sanitisation on user input
	 * Second, we loop through all custom taxonomies and:
	 * - if show_tax is unchecked, do nothing and skip to next taxonomy, or
	 * - if show_tax is checked, loop through its terms and add them to the 'known_'.$custom_taxonomy array
	 *
	 * The 'known_'.$custom_taxonomy array is saved and used on output to check for newly added terms
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance New settings for this instance as input by the user via form()
	 * @param array $old_instance Old settings for this instance
	 * @return array Settings to save or bool false to cancel saving
	 */
	function update( $new_instance, $old_instance ) {
		
		// Sanitise
		$new_instance['title'] = esc_attr( $new_instance['title'] );
		
		// Get all custom taxonomies
		$custom_taxonomies = $this->taxonomies();
		
		foreach( $custom_taxonomies as $custom_taxonomy ) {
		
			if( ! $new_instance['show_tax_' . $custom_taxonomy->name] )
				continue;
			
			// Get all terms (regardless of whether checked or not)
			$current_terms = get_terms( $custom_taxonomy->name, array( 'hide_empty' => 0 ) );
  				
  			// Loop through all terms (checked and unchecked)
  			foreach ( $current_terms as $current_term ) {
  				
  				// Store them in the ['known_' . $custom_taxonomy->name] array for later use on output of widget
  				$new_instance['known_' . $custom_taxonomy->name][] = $current_term->term_id;
  			}
  		}
		return $new_instance;
	}


	function form( $instance ) { 
		
		// Load plugin textdomain
		sgr_ctmw_load_textdomain();
		
		// Get all custom taxonomies - shame we have to do this again, but we need it a few times below
		$custom_taxonomies = $this->taxonomies();
		
		if( !$custom_taxonomies ) {
			echo __( 'There are no custom taxonomies registered.', SGR_CTMW_DOMAIN );
			return;	
		}
		
		// Get parsed defaults - prevents PHP undefined index warnings
		$instance = $this->defaults( $instance );
		
		
		// Empty fallback (default)
		//
		// The idea here is that all term checkboxes will be pre-checked on first use of widget
		// Note: if all terms for a taxonomy are unchecked by user, this code will automatically re-check all terms
		// Therefore, to hide a taxonomy, user must uncheck the taxonomy, not all of the taxonomy's terms. Make sense?
		foreach( $custom_taxonomies as $custom_taxonomy ) {
		
  			// Get all terms that currently exist right now, for this custom taxonomy
			$current_terms = get_terms( $custom_taxonomy->name, array( 'hide_empty' => 0 ) );
			
			// If all terms are unchecked, we need to re-check them
			if( empty( $instance['include_' . $custom_taxonomy->name] ) ) {

				foreach( $current_terms as $current_term ) {
				
					// Check all terms in this taxonomy
					$instance['include_' . $custom_taxonomy->name][] = $current_term->term_id;
				}
			}
			
			// Populate the 'show_tax' taxonomy checkboxes to prevent PHP undefined index warnings
			if( empty( $instance['show_tax_' . $custom_taxonomy->name] ) ) {
				
				// This is temporary, only "true" will be saved by the form
				$instance['show_tax_' . $custom_taxonomy->name] = "false";
			}
			
			// Deal with any new terms added since last save
			// If show_tax is false, $instance['known_' . $custom_taxonomy->name] will be empty
  			if ( ! empty ( $instance['known_' . $custom_taxonomy->name] ) ) {
  				
  				// Loop through the terms and look for newly added ones
  				foreach ( $current_terms as $current_term ) {
  					
  					// Do we have a new term added since the widget form was last saved?
  					if( ! in_array( $current_term->term_id, $instance['known_' . $custom_taxonomy->name] ) ) {
  						
  						// Add any new terms to the $instance['include_' . $custom_taxonomy->name] array
  						$instance['include_' . $custom_taxonomy->name][] = $current_term->term_id;
  					}
  				}
			}
		}
		?>
		
		<div class="custom-taxonomies-menu-top">
			
			<p><?php _e( 'This widget produces a custom taxonomy navigation menu, ideal for use in sidebars.', SGR_CTMW_DOMAIN ); ?></p>
			<p>
				<a href="<?php echo SGR_CTMW_HOME; ?>"><?php _e( 'Plugin homepage', SGR_CTMW_DOMAIN ); ?></a> |
				<a href="<?php echo SGR_CTMW_HOME; ?>"><?php _e( 'FAQ', SGR_CTMW_DOMAIN ); ?></a> |
				<?php printf( __( 'version %s', SGR_CTMW_DOMAIN ), SGR_CTMW_VER ); ?>
			</p>
		
		</div>
		
		<div class="custom-taxonomies-menu-column">
		
			<div class="custom-taxonomies-menu-column-inner">
		
				<h4><?php _e( 'Configuration options', SGR_CTMW_DOMAIN ); ?></h4>
				<p>
					<label for="<?php echo $this->get_field_id( 'title' ); ?>">
					<?php _e( 'Menu Title', SGR_CTMW_DOMAIN ); ?>:
					</label>
					<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:95%;" />
				</p>
		
				<p><?php _e( 'Choose the order by which you would like to display the terms within each taxonomy', SGR_CTMW_DOMAIN ); ?>:</p>
		
				<p>
				<select name="<?php echo $this->get_field_name( 'orderby' ); ?>">
					<option style="padding-right:10px;" value="name" <?php selected('name', $instance['orderby']); ?>>Name</option>
					<option style="padding-right:10px;" value="ID" <?php selected('id', $instance['orderby']); ?>>ID</option>
					<option style="padding-right:10px;" value="slug" <?php selected('slug', $instance['orderby']); ?>>Slug</option>
					<option style="padding-right:10px;" value="count" <?php selected('count', $instance['orderby']); ?>>Count</option>
					<option style="padding-right:10px;" value="term_group" <?php selected('term_group', $instance['orderby']); ?>>Term Group</option>
				</select>
				</p>
		
				<p><?php _e( 'Choose whether to display taxonomy terms in ASCending order(default) or DESCending order', SGR_CTMW_DOMAIN ); ?>:</p>
				<p>
				<select name="<?php echo $this->get_field_name( 'order' ); ?>">
					<option style="padding-right:10px;" value="asc" <?php selected('ASC', $instance['order']); ?>>ASC (default)</option>
					<option style="padding-right:10px;" value="desc" <?php selected('DESC', $instance['order']); ?>>DESC</option>
				</select>
				</p>
		
				<p>
					<label for="<?php echo $this->get_field_id( 'show_count' ); ?>">
						<?php _e( 'Show post count?', SGR_CTMW_DOMAIN ); ?>
					</label>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'show_count' ); ?>" name="<?php echo $this->get_field_name( 'show_count' ); ?>" value="true" <?php checked( 'true', $instance['show_count'] ); ?> />
				</p>
				
				<p>
					<label for="<?php echo $this->get_field_id( 'hide_empty' ); ?>">
						<?php _e( 'Hide empty terms?', SGR_CTMW_DOMAIN ); ?>
					</label>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>" value="true" <?php checked( 'true', $instance['hide_empty'] ); ?> />
				</p>

				<p>
					<label for="<?php echo $this->get_field_id( 'show_tax_title' ); ?>">
						<?php _e( 'Show Taxonomy Title?', SGR_CTMW_DOMAIN ); ?>
					</label>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'show_tax_title' ); ?>" name="<?php echo $this->get_field_name( 'show_tax_title' ); ?>" value="true" <?php checked( 'true', $instance['show_tax_title'] ); ?> />
				</p>

				<p>
					<label for="<?php echo $this->get_field_id( 'show_hierachical' ); ?>">
						<?php _e( 'Show Terms as hierarchy?', SGR_CTMW_DOMAIN ); ?>
					</label>
					<input type="checkbox" id="<?php echo $this->get_field_id( 'show_hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'show_hierarchical' ); ?>" value="true" <?php checked( 'true', $instance['show_hierarchical'] ); ?> />
				</p>
			
			</div><!-- end .custom-taxonomies-menu-column-inner -->
		
			<div class="custom-taxonomies-menu-column-inner custom-taxonomies-menu-column-inner-bottom">
			
				<h4><?php _e( 'About the checklists', SGR_CTMW_DOMAIN ); ?></h4>
				<p><?php _e( 'By default, all taxonomies are unchecked and all terms are checked. If the taxonomy is unchecked, none of its terms will be displayed in the menu.', SGR_CTMW_DOMAIN ); ?></p>
				<p><?php _e( 'The checklists only include custom taxonomies whose register_taxonomy() "public" $arg is set to true. Note that if a taxonomy does not have any terms, it will not be displayed in the checklist.', SGR_CTMW_DOMAIN ); ?></p>
				
			</div><!-- end .custom-taxonomies-menu-column-inner -->
		
		</div><!-- end .custom-taxonomies-menu-column -->
		
		<div class="custom-taxonomies-menu-column custom-taxonomies-menu-column-right">
		
			<div class="custom-taxonomies-menu-column-inner">
		
				<h4><?php _e( 'Select taxonomies and terms', SGR_CTMW_DOMAIN ); ?></h4>

				<p><?php _e( 'Use the checklist(s) below to choose which custom taxonomies and terms you want to include in your menu. To hide a taxonomy and all its terms, uncheck the taxonomy name.', SGR_CTMW_DOMAIN ); ?></p>
		
				<?php
				// Produce a checklist of terms for each custom taxonomy
				foreach ( $custom_taxonomies as $custom_taxonomy ) :
			
					$checkboxes = '';
				
					// Need to make sure that the taxonomy has some terms. If it doesn't, skip to the next taxonomy
					// Prevents PHP index notice when tax has no terms
					if( empty( $instance['include_' . $custom_taxonomy->name] ) )
						continue;
					
					// Get checklist, sgr_taxonomy_checklist( $name, $custom_taxonomy, $selected )
					$checkboxes = sgr_taxonomy_checklist( $this->get_field_name( 'include_' . $custom_taxonomy->name ), $custom_taxonomy, $instance['include_' . $custom_taxonomy->name] );
					?>
			
					<div class="custom-taxonomies-menu-list">
					
						<p>
							<input type="checkbox" id="<?php echo $this->get_field_id( 'show_tax_' . $custom_taxonomy->name ); ?>" name="<?php echo $this->get_field_name( 'show_tax_' . $custom_taxonomy->name ); ?>" value="true" <?php checked( 'true', $instance['show_tax_'.$custom_taxonomy->name] ); ?> />
							<label for="<?php echo $this->get_field_id( 'show_tax' . $custom_taxonomy->name ); ?>" class="sgr-ctmw-tax-label"><?php echo $custom_taxonomy->label; ?></label>
						</p>
				
						<ul class="custom-taxonomies-menu-checklist">
							<?php echo $checkboxes; ?>
						</ul>
					</div>
			
				<?php
				endforeach; ?>
		
			</div><!-- end .custom-taxonomies-menu-column-inner -->
		
		</div><!-- end .custom-taxonomies-menu-column -->
		
	<?php 
	}
}


add_action('widgets_init', 'register_sgr_custom_taxonomies_menu_widget');
/**
 * Register our widget
 *
 * @since 1.0
 */
function register_sgr_custom_taxonomies_menu_widget() {
	
	register_widget('SGR_Widget_Custom_Taxonomies_Menu');
}


/**
 * Creates a taxonomy checklist based on wp_terms_checklist()
 *
 * Output buffering is used so that we can run a string replace after the checklist is created
 *
 * @since 1.0
 *
 * @param $name - string
 * @param $custom_taxonomy - array - Array object for a custom taxonomy
 * @param $selected - array - Selected terms within the taxonomy
 *
 * @return string, xhtml markup of the checklist
 */
function sgr_taxonomy_checklist($name = '', $custom_taxonomy, $selected = array()) {
	
	$name = esc_attr( $name );

	$checkboxes = '';

	ob_start();
		
	$terms_args = array ( 'taxonomy' => $custom_taxonomy->name, 'selected_cats' => $selected, 'checked_ontop' => false );
	
	// Note: 'hide empty' is false, therefore terms with no posts will appear in the checklist
	wp_terms_checklist( 0, $terms_args );
	
	// Replace standard checklist "name" attr with the one we need, ie 'include_' . $custom_taxonomy->name[]
	$checkboxes .= str_replace( 'name="tax_input['.$custom_taxonomy->name.'][]"', 'name="'.$name.'[]"', ob_get_clean() );
			
	return $checkboxes;
}