<?php
/**
 * Custom admin.
 *
 * @package Sockman
 */

/**
 *  Move Yoast to bottom
 *
 * @return $priority
 */
function sockman_move_yoast_to_bottom() {
	return 'low';
}

add_filter( 'wpseo_metabox_prio', 'sockman_move_yoast_to_bottom' );


/**
 * Show ACF admin on dev only.
 *
 * @return boolean
 */
function show_acf_admin() {
	if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG && current_user_can( 'administrator' ) ) {
		return true;
	}

	return false;
}
add_filter( 'acf/settings/show_admin', 'show_acf_admin' );

/**
 * Remove "Screen Options"
 *
 * @param bool $show_screen Whether to show the screen options.
 * @return bool $show_screen
 */
function sockman_remove_screen_options( $show_screen ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return false;
	}

	return $show_screen;
}

add_filter( 'screen_options_show_screen', 'sockman_remove_screen_options' );


/**
 * Remove "Help" tab
 */
function sockman_remove_help_tabs() {
	if ( ! current_user_can( 'administrator' ) ) {
		$screen = get_current_screen();
		$screen->remove_help_tabs();
	}
}

add_action( 'admin_head', 'sockman_remove_help_tabs' );


/**
 * Hide update nag
 *
 * @return void
 */
function sockman_hide_update_nag() {
	if ( ! current_user_can( 'administrator' ) ) {
		remove_action( 'admin_notices', 'update_nag', 3 );
	}
}
add_action( 'admin_head', 'sockman_hide_update_nag', 1 );


/**
 * Add logo to dashboard
 */
function sockman_add_logo_to_dashboard() {
	$icon = get_stylesheet_directory_uri() . '/assets/favicons/apple-touch-icon-114x114.png';

	echo '<script>
		jQuery(function($) {
			$(".index-php #wpbody-content .wrap h1:eq(0)")
				.before("<span class=\"c-dashboard-logo\"><img src=\"' . $icon . '\" alt=\"' . get_bloginfo( 'name' ) . '\" /></span>");


			$(".index-php #wpbody-content .wrap h1:eq(0)").after("<h2 class=\"c-dashboard-sitename\">' . get_bloginfo( 'name' ) . '</h2>");
		});
	</script>';

	echo '<style>.c-dashboard-logo {
			float: left;
			width: 57px;
			height: 57px;
			margin-top: 8px;
			margin-right: 12px;

			> img {
				width: 100%;
				height: 100%;
			}
		}

		.c-dashboard-sitename {
			margin: 0 0 12px;
		}</style>';
}

// add_action( 'admin_head', 'sockman_add_logo_to_dashboard' );


/**
 * Add site logo to login.
 *
 * @return void
 */
function sockman_custom_login_logo() {
	wp_print_scripts( array( 'jquery' ) );

	$icon = get_stylesheet_directory_uri() . '/assets/favicons/android-chrome-192x192.png';

	echo '<style type="text/css">
		.login h1 a { background-image: url(' . $icon . '); }
		</style> ';

	echo '<script type="text/javascript">
			jQuery(document).ready(function()
			{
				jQuery(\'#login h1 a\').attr(\'title\',\'' . get_bloginfo( 'name' ) . '\');
				jQuery(\'#login h1 a\').attr(\'href\',\'' . get_bloginfo( 'url' ) . '\');
			});
		</script>';
}

add_action( 'login_head', 'sockman_custom_login_logo' );




/**
 * Remove WordPress Version on Dashboard
 */
function sockman_hide_wp_version_on_dashboard() {
	if ( ! current_user_can( 'administrator' ) ) {
		echo '<style type="text/css">#wp-version-message { display: none; }</style>';
	}
}

/**
 * Remove WordPress Version on Dashboard
 *
 * @param string $content The footer content.
 */
function sockman_remove_footer_version( $content ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return '';
	}

	return $content;
}


add_filter( 'update_footer', 'sockman_remove_footer_version', 9999 );
add_action( 'admin_head', 'sockman_hide_wp_version_on_dashboard' );






/**
 * Custom admin footer text
 *
 * @param string $text The footer text.
 */
function sockman_admin_footer_text( $text ) {
	if ( ! current_user_can( 'administrator' ) ) {
		return '<span id="footer-thankyou">Design by <a target="_blank" href="https://www.adozeneggs.co.uk">a dozen eggs</a></span>';
	}

	return $text;
}


add_filter( 'admin_footer_text', 'sockman_admin_footer_text' );





/**
 * Customize dashboard widgets
 */
function sockman_remove_dashboard_widgets() {
	if ( ! current_user_can( 'administrator' ) ) {
		// remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );			// Right Now
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );		// Recent Comments
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );		// Incoming Links
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );				// Plugins
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );			// Quick Press
		// remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );			// Recent Drafts
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );				// WordPress blog
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );				// Other WordPress News
		remove_meta_box( 'wpseo-dashboard-overview', 'dashboard', 'normal' );		// Yoast
	}
}

add_action( 'wp_dashboard_setup', 'sockman_remove_dashboard_widgets' );
