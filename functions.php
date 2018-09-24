<?php



add_filter( 'woocommerce_background_image_regeneration', '__return_false' );
add_filter( 'storefront_menu_toggle_text', '__return_empty_string' );
add_filter( 'bw_email_message', 'sgm_bw_email_message', 11, 2 );
add_filter( 'woocommerce_loop_add_to_cart_link', 'sgm_add_quantity_fields', 10, 3 );


/**
 * Appends From email and contact name to the contact form message
 * 
 * Needed since easy-wp-smtp overrides the From email address.      
 * Since we're in the 'bw_email_message' filter we expect bw_replace_fields to be available.
 *  
 * @param string $message the email content
 * @param array $fields array of name value pairs
 * @return string updated message 
 */
function sgm_bw_email_message( $message, $fields ) {
	//bw_trace2();
	$extra_bits = bw_replace_fields( "<br />From: %from%<br />Name: %contact%", $fields );
	$message .= $extra_bits;
	return $message;
}


/**
 * Adds quantity field to simple products
 * 
 * @param string $html
 * @param object $product
 * @param array $args
 * @return string 
 */
function sgm_add_quantity_fields( $html, $product, $args) {
	//bw_trace2();
	//bw_backtrace();
	if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_sold_individually() ) {
		//rewrite form code for add to cart button
		$html = '<form action="' . esc_url( $product->add_to_cart_url() ) . '" class="cart" method="post" enctype="multipart/form-data">';
		$html .= woocommerce_quantity_input( array(), $product, false );
		$html .= '<button type="submit" data-quantity="1" data-product_id="' . $product->get_id() . '" class="button alt ajax_add_to_cart add_to_cart_button product_type_simple">' . esc_html( $product->add_to_cart_text() ) . '</button>';
		$html .= '</form>';
	}
	return $html;
}



/** 
 * This is no good since the colour is used for the whole of the header
 * https://wp-a2z.org/oik_hook/theme_mod_name/
 
 */
//add_filter( "theme_mod_storefront_header_background_color", "sg_header_background_color" );

function sg_header_background_color( $color ) {
	return( '#ffffff' );
}


/** 
 * No need to enqueue the child theme styles ourselves as Storefront already does this.
 */
//add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_styles' );
function storefront_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style')
    );
}

// Replace WP autop formatting
if ( ! function_exists( 'woo_remove_wpautop' ) ) {
	function woo_remove_wpautop( $content ) {
		$content = do_shortcode( shortcode_unautop( $content ) );
		$content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content );
		return $content;
	} // End woo_remove_wpautop()
}


/*-----------------------------------------------------------------------------------*/
/* 2. Boxes - box
/*-----------------------------------------------------------------------------------*/
/*

Optional arguments:
 - type: info, alert, tick, download, note
 - size: medium, large
 - style: rounded
 - border: none, full
 - icon: none OR full URL to a custom icon

*/
function woo_shortcode_box( $atts, $content = null ) {
   extract( shortcode_atts( array(	'type' => 'normal',
   									'size' => '',
   									'style' => '',
   									'border' => '',
   									'icon' => '' ), $atts ) );

    // "Toggle in a box" fix
   	$allowed_tags = wp_kses_allowed_html( 'post' );
	$allowed_tags['input'] = array( 'type' => true,
									'name' => true,
									'value' => true );

	$allowed_protocols = wp_allowed_protocols();
	$allowed_protocols[] = 'skype';

	$class = '';
   	$custom = '';
   	if ( $icon == 'none' ) {
   		$class = 'no-icon';
   		$custom = ' style="padding-left:15px;background-image:none;"';
   	} elseif ( $icon ) {
   		$class = 'custom-icon';
   		$custom = ' style="padding-left:50px;background-image:url( ' . esc_attr( esc_url( $icon ) ) . ' ); background-repeat:no-repeat; background-position:20px 45%;"';
    }
   	return '<div class="woo-sc-box ' . esc_attr( $class ) . ' ' . esc_attr( $type ) . ' ' . esc_attr( $size ) . ' ' . esc_attr( $style ) . ' ' . esc_attr( $border ) . '"' . $custom . '>' . wp_kses( do_shortcode( woo_remove_wpautop( $content ) ), $allowed_tags, $allowed_protocols ) . '</div>';
} // End woo_shortcode_box()

add_shortcode( 'box', 'woo_shortcode_box' );

/* WooCommerce: The Code Below Removes Checkout Fields */

//add_filter( 'woocommerce_checkout_fields' , 'storefront_override_checkout_fields' );

function storefront_override_checkout_fields( $fields ) {
	unset($fields['billing']['billing_company']);
	unset($fields['billing']['billing_address_2']);
	return $fields;
}
