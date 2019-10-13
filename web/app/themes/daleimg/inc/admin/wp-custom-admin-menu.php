<?php
/**
 * Custom admin menu (left hand side nav).
 *
 * @package Sockman
 */

/**
 * Remove top-level menus for editors.
 */
function sockman_remove_menu_pages() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		remove_menu_page( 'tools.php' );
		remove_menu_page( 'themes.php' );
	}
	
	remove_menu_page( 'edit-comments.php' );
	remove_menu_page( 'edit.php?post_type=page' );
	remove_menu_page( 'edit.php' );
}

add_action( 'admin_menu', 'sockman_remove_menu_pages', 999 );

/**
 * Add extra menu page niceties.
 */
function sockman_add_menu_pages() {

	if ( ! current_user_can( 'administrator' ) ) {
		add_menu_page(
			'Menus',
			'Menus',
			'edit_theme_options',
			'nav-menus.php',
			'',
			'dashicons-menu'
		);
	}

	add_menu_page(
		'Home',
		'Home Page',
		'edit_posts',
		'post.php?post=2&action=edit',
		'',
		'dashicons-admin-home'
	);

}

add_action( 'admin_menu', 'sockman_add_menu_pages', 999 );


/**
 * Add an admin seperator.
 *
 * @param int $number The seperator number.
 * @return void
 */
function sockman_add_admin_menu_separator( $number ) {

	global $menu;

	$menu[] = array(
		0	=> '',
		1	=> 'read',
		2	=> 'separator' . $number,
		3	=> '',
		4	=> 'wp-menu-separator',
	);
}


/**
 * Add our options pages.
 * One for the promo banner.
 */
function sockman_add_options_pages() {
	if ( function_exists( 'acf_add_options_page' ) ) {

		sockman_add_admin_menu_separator( 3 );
		sockman_add_admin_menu_separator( 4 );

		$contact_details = acf_add_options_page(array(
			'page_title' 	=> 'Contact details, social accounts and contact form options',
			'menu_title' 	=> 'Contact Details',
			'menu_slug' 	=> 'contact-details',
			'icon_url' 		=> 'dashicons-book',
		));

		$site_settings = acf_add_options_page(array(
			'page_title' 	=> 'Site Settings',
			'menu_title' 	=> 'Site Settings',
			'menu_slug' 	=> 'site-settings',
			'icon_url' 		=> 'dashicons-admin-generic',
		));

	} // End if().
}

add_action( 'acf/init', 'sockman_add_options_pages' );


/**
 * Let editors change the menus.
 *
 * @return void
 */
function sockman_allow_ediors_theme_access() {
	$role_object = get_role( 'editor' );

	$role_object->add_cap( 'edit_theme_options' );
}

add_action( 'init', 'sockman_allow_ediors_theme_access' );



/**
 * Remove the additional CSS section, introduced in 4.7, from the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize the customizer object.
 */
function sockman_remove_customizer_css_section( $wp_customize ) {
	$wp_customize->remove_section( 'custom_css' );
}

add_action( 'customize_register', 'sockman_remove_customizer_css_section', 15 );






/**
 * Reorder the admin menu
 *
 * @param array $menu_order the current order.
 * @return $menu_order
 */
function reorder_admin_menu( $menu_order ) {
	$m = $menu_order;

	return array(
		'index.php',						// Dashboard
		'separator1',						// --
		'post.php?post=2&action=edit',		// Home
		'upload.php',						// Media
		'separator2',						// --
		// 'edit.php?post_type=page',			// Pages
		// 'edit.php',							// Posts
		'nav-menus.php',					// Menus
		'separator3',						// --
		'edit-comments.php',				// Comments
		'contact-details',					// Contact Details
		'site-settings',					// Site settings (analytics, cookie banner)
		'separator4',						// --
		'themes.php',						// Appearance
		'plugins.php',						// Plugins
		'users.php',						// Users
		'tools.php',						// Tools
		'options-general.php',				// Settings
		'profile.php',						// User's profile.
	);
}
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'reorder_admin_menu' );
