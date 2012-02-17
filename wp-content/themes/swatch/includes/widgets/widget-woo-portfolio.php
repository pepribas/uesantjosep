<?php
/*-----------------------------------------------------------------------------------

CLASS INFORMATION

Description: A custom WooThemes portfolio widget.
Date Created: 2011-09-01.
Last Modified: 2011-09-01.
Author: WooThemes.
Since: 1.1.0


TABLE OF CONTENTS

- var $woo_widget_cssclass
- var $woo_widget_description
- var $woo_widget_idbase
- var $woo_widget_title

- function (constructor)
- function widget ()
- function update ()
- function form ()

- Register the widget on `widgets_init`.

-----------------------------------------------------------------------------------*/

class Woo_Widget_Portfolio extends WP_Widget {

	/*----------------------------------------
	  Variable Declarations.
	  ----------------------------------------
	  
	  * Variables to setup the widget.
	----------------------------------------*/

	var $woo_widget_cssclass;
	var $woo_widget_description;
	var $woo_widget_idbase;
	var $woo_widget_title;

	/*----------------------------------------
	  Constructor.
	  ----------------------------------------
	  
	  * The constructor. Sets up the widget.
	----------------------------------------*/
	
	function Woo_Widget_Portfolio () {
	
		/* Widget variable settings. */
		$this->woo_widget_cssclass = 'widget-portfolio-snapshot';
		$this->woo_widget_description = __( 'This is a WooThemes standardized portfolio widget.', 'woothemes' );
		$this->woo_widget_idbase = 'woo_portfolio';
		$this->woo_widget_title = __('Woo - Portfolio', 'woothemes' );
		
		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->woo_widget_cssclass, 'description' => $this->woo_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->woo_widget_idbase );

		/* Create the widget. */
		$this->WP_Widget( $this->woo_widget_idbase, $this->woo_widget_title, $widget_ops, $control_ops );
		
	} // End Constructor

	/*----------------------------------------
	  widget()
	  ----------------------------------------
	  
	  * Displays the widget on the frontend.
	----------------------------------------*/

	function widget( $args, $instance ) {
		
		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('woo_home_portfolio_title', $instance['title'], $instance, $this->id_base );
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );
		
		$limit = $instance['limit'];
		
		$orderby = 'menu_order';
		$order = 'asc';
		
		if ( isset( $instance['orderby'] ) ) {
			$orderby = $instance['orderby'];
		}
		
		if ( isset( $instance['order'] ) ) {
			$order = $instance['order'];
		}
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title != '' ) {
		
			echo $before_title . $title . $after_title;
		
		} // End IF Statement
		
		/* Widget content. */
		
		// Add actions for plugins/themes to hook onto.
		do_action( $this->woo_widget_cssclass . '_top' );
		
		// Load widget content here.
		global $woo_options;

		$query_args = array(
						'post_type' => 'portfolio', 
						'posts_per_page' => $limit, 
						'orderby' => $orderby,
						'order' => $order
						);

		query_posts( $query_args );
		
		if ( have_posts() ) {
			while ( have_posts() ) { the_post();
				echo '<div id="portfolio-item-id-' . get_the_ID() . '" class="' . join( ' ', get_post_class( 'portfolio-item' ) ) . '">' . "\n";
				echo '<div class="portfolio-image drop-shadow lifted">' . woo_image( 'return=true&key=portfolio-image&width=240&height=150' ) . '</div><!--/.portfolio-image-->' . "\n";
				the_title( '<h3><a href="' . get_permalink( get_the_ID() ) . '">', '</a></h3>' . "\n" );
				echo get_the_term_list( get_the_ID(), 'portfolio-gallery', '<span class="portfolio-galleries">', ', ', '</span><!--/.portfolio-galleries-->' . "\n" );
				echo '</div>' . "\n";
			}
		}
		
		wp_reset_query();
	
		// Add actions for plugins/themes to hook onto.
		do_action( $this->woo_widget_cssclass . '_bottom' );

		/* After widget (defined by themes). */
		echo $after_widget;

	} // End widget()

	/*----------------------------------------
	  update()
	  ----------------------------------------
	
	* Function to update the settings from
	* the form() function.
	
	* Params:
	* - Array $new_instance
	* - Array $old_instance
	----------------------------------------*/
	
	function update ( $new_instance, $old_instance ) {
		
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		
		if ( ! is_numeric( $new_instance['limit'] ) || $new_instance['limit'] == '' ) { $new_instance['limit'] = 3; } // Set a default limit value.
		
		$instance['limit'] = intval( strip_tags( $new_instance['limit'] ) );
		
		/* The select boxes are returning a text value, so we escape them. */
		$instance['orderby'] = esc_attr( $new_instance['orderby'] );

		$instance['order'] = esc_attr( $new_instance['order'] );
		
		return $instance;
		
	} // End update()

   /*----------------------------------------
	 form()
	 ----------------------------------------
	  
	  * The form on the widget control in the
	  * widget administration area.
	  
	  * Make use of the get_field_id() and 
	  * get_field_name() function when creating
	  * your form elements. This handles the confusing stuff.
	  
	  * Params:
	  * - Array $instance
	----------------------------------------*/

   function form( $instance ) {       
   
		/* Set up some default widget settings. */
		$defaults = array(
						'title' => __( 'Portfolio Snapshots', 'woothemes' ), 
						'limit' => 3, 
						'orderby' => 'menu_order', 
						'order' => 'asc'
					);
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$orderby_options = array(
								'none' => __( 'No Order', 'woothemes' ), 
								'ID' => __( 'ID', 'woothemes' ), 
								'title' => __( 'Title', 'woothemes' ), 
								'date' => __( 'Date', 'woothemes' ), 
								'modified' => __( 'Last Modified Date', 'woothemes' ),  
								'rand' => __( 'Random', 'woothemes' ), 
								'comment_count' => __( 'Comment Count', 'woothemes' ), 
								'menu_order' => __( 'Menu Order', 'woothemes' )
								);
								
		$order_options = array(
								'desc' => __( 'Descending', 'woothemes' ), 
								'asc' => __( 'Ascending', 'woothemes' )
								);
?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):', 'woothemes' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>"  value="<?php echo $instance['title']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" />
		</p>
		<!-- Widget Limit: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit (default: 3):', 'woothemes' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'limit' ); ?>"  value="<?php echo $instance['limit']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" />
		</p>
		<!-- Widget Order By: Select Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Order By:', 'woothemes' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'orderby' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>">
				<?php
					foreach ( $orderby_options as $k => $v ) {
				?>
				<option value="<?php echo $k; ?>"<?php selected( $instance['orderby'], $k ); ?>><?php echo $v; ?></option>
				<?php
					}
				?>     
			</select>
		</p>
		<!-- Widget Order: Select Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order Direction:', 'woothemes' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'order' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>">
				<?php
					foreach ( $order_options as $k => $v ) {
				?>
				<option value="<?php echo $k; ?>"<?php selected( $instance['order'], $k ); ?>><?php echo $v; ?></option>
				<?php
					}
				?>     
			</select>
		</p>
<?php
	} // End form()
	
} // End Class

/*----------------------------------------
  Register the widget on `widgets_init`.
  ----------------------------------------
  
  * Registers this widget.
----------------------------------------*/

add_action( 'widgets_init', create_function( '', 'return register_widget("Woo_Widget_Portfolio");' ), 1 ); 
?>