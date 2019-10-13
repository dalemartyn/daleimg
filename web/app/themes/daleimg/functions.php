<?php
/**
 * Sockman functions. Loads most stuff from the /inc folder.
 *
 * @package Sockman
 */

if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});

	add_filter('template_include', function( $template ) {
		return get_stylesheet_directory() . '/assets/dist/no-timber.html';
	});

	return;
}

Timber::$dirname = array( 'templates' );
Timber::$locations = array(
	get_stylesheet_directory() . '/assets/dist/img/',
	get_stylesheet_directory() . '/assets/dist/svg/',
);

/**
 * Load our main theme class.
 */
$sockman = require 'inc/class-site.php';
