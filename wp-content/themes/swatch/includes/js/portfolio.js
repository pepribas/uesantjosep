jQuery(document).ready(function($){

/*-----------------------------------------------------------------------------------*/
/* PrettyPhoto (lightbox) */
/*-----------------------------------------------------------------------------------*/

	$("a[rel^='lightbox']").prettyPhoto({ social_tools: false });

/*-----------------------------------------------------------------------------------*/
/* Portfolio thumbnail hover effect */
/*-----------------------------------------------------------------------------------*/

	jQuery('#portfolio img, .widget-portfolio-snapshot img').mouseover(function() {
		jQuery(this).stop().fadeTo(300, 0.5);
	});
	jQuery('#portfolio img, .widget-portfolio-snapshot img').mouseout(function() {
		jQuery(this).stop().fadeTo(400, 1.0);
	});

/*-----------------------------------------------------------------------------------*/
/* Portfolio tag toggle on page load, based on hash in URL */
/*-----------------------------------------------------------------------------------*/

	if ( jQuery( '.port-cat a' ).length ) {
		var currentHash = '';
		currentHash = window.location.hash;
		
		// If we have a hash, begin the logic.
		if ( currentHash != '' ) {
			currentHash = currentHash.replace( '#', '' );
			
			if ( jQuery( '#portfolio .' + currentHash ).length ) {
			
				// Select the appropriate item in the category menu.
				jQuery( '.port-cat a.current' ).removeClass( 'current' );
				jQuery( '.port-cat a[rel="' + currentHash + '"]' ).addClass( 'current' );
				
				// Show only the items we want to show.
				jQuery( '#portfolio .post' ).hide();
				jQuery( '#portfolio .' + currentHash ).fadeIn( 400 );
			
			}
		}

	}

/*-----------------------------------------------------------------------------------*/
/* Portfolio tag sorting */
/*-----------------------------------------------------------------------------------*/
								
	jQuery('.port-cat a').click(function(evt){
		var clicked_cat = jQuery(this).attr('rel');
		
		jQuery( '.port-cat a.current' ).removeClass( 'current' );
		jQuery( this ).addClass( 'current' );
		
		// Move the "fix" DIV tag appropriately.
		var itemSelector = '.portfolio-item';
		if ( clicked_cat != 'all' ) {
			itemSelector = '.' + clicked_cat;
		}
		
		var perRow = 3;
		if ( jQuery( 'body' ).hasClass( 'two-col-left' ) || jQuery( 'body' ).hasClass( 'two-col-right' ) ) {
			perRow = 2;
		}
		
		woo_move_clearfix( itemSelector, '.portfolio-items', perRow );
		
		if(clicked_cat == 'all'){
			jQuery('#portfolio .post').hide().fadeIn(200);
		} else {
			jQuery('#portfolio .post').hide();
			jQuery('#portfolio .' + clicked_cat).fadeIn(400);
		 }
		//eq_heights();
		evt.preventDefault();
	})	

	// Thanks @johnturner, I owe you a beer!
	/*
	var postMaxHeight = 0;
	jQuery("#portfolio .post").each(function (i) {
		 var elHeight = jQuery(this).height();
		 
		 if(parseInt(elHeight) > postMaxHeight){
			 postMaxHeight = parseInt(elHeight);
		 }
	});
	jQuery("#portfolio .post").each(function (i) {
		jQuery(this).css('height',postMaxHeight+'px');
	});
	*/
														
});

/**
 * woo_move_clearfix function.
 *
 * @description Move the "fix" DIV tag according to the number of items per row.
 * @access public
 * @param string itemSelector
 * @param string containerSelector
 * @param int perRow
 * @return void
 */
function woo_move_clearfix ( itemSelector, containerSelector, perRow ) {
	jQuery( containerSelector ).find( '.fix' ).remove();
	var count = 1;
	jQuery( containerSelector + ' ' + itemSelector ).each( function ( i ) {
		count++;
		if ( count % ( perRow + 1 ) == 0 && i > 0 ) {
			jQuery( this ).after( '<div class="fix"></div>' );
			
			count = 1;
		}
	});
} // End woo_move_clearfix()