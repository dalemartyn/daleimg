<?php
/**
 * Custom menu bar.
 *
 * @package Sockman
 */

/**
 * Remove WP logo menu and Comments from menu bar.
 *
 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
 */
function sockman_remove_menu_bar_nodes( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );

	$wp_admin_bar->remove_node( 'comments' );
}

add_action( 'admin_bar_menu', 'sockman_remove_menu_bar_nodes', 999 );


/**
 * Get the css for the custom admin bar.
 *
 * @param string $color The hex code for the color.
 * @param string $string The name to add to the bar.
 * @return $css
 */
function sockman_color_admin_bar_css( $color, $string ) {

	return "
	#wpadminbar {
		background-color: {$color};
	}

	#wpadminbar #wp-admin-bar-site-name > .ab-item::after {
		content: '{$string}';
	}

	#wpadminbar.mobile .quicklinks .ab-icon:before,
	#wpadminbar.mobile .quicklinks .ab-item:before {
		color: rgba(255, 255, 255, .6);
	}";
}

/**
 * Add the admin bar css as an inline style.
 *
 * `wp_add_inline_style` adds extra styles after an already included stylesheet is loaded.
 *
 * @return void
 */
function sockman_admin_bar_customisations() {

	if ( defined( 'WP_ENV' ) && 'staging' === WP_ENV ) {
		$css = sockman_color_admin_bar_css( '#4caf50', ' – Staging Site' );

		wp_add_inline_style( 'admin-bar', $custom_css );

	} elseif ( defined( 'WP_ENV' ) && 'development' === WP_ENV ) {
		$css = sockman_color_admin_bar_css( '#ff5252', ' – Dev Site' );
		wp_add_inline_style( 'admin-bar', $css );
	}
}

add_action( 'wp_enqueue_scripts', 'sockman_admin_bar_customisations', 20 );
add_action( 'admin_enqueue_scripts', 'sockman_admin_bar_customisations' );
