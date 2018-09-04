<?php
/*
 */
add_action( 'wp_enqueue_scripts', 'storefront_child_enqueue_styles' );
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
