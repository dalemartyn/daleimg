<?php
/**
 * Sockman Class.
 *
 * @package Sockman
 */

namespace Sockman;

require_once( 'class-timberphoton.php' );
require_once( 'class-jetpack-image.php' );
require_once( 'class-layout.php' );

use Timber;
use Timber\Site as TimberSite;
use Timber\Menu as TimberMenu;

/**
 * The main Sockman class.
 */
class Site extends TimberSite {

	/**
	 * Setup class.
	 */
	function __construct() {
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ) );
		add_action( 'after_setup_theme', array( $this, 'block_editor_support' ) );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		add_filter( 'post_gallery', array( $this, 'gallery' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ),  20 );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_menus' ) );
		add_action( 'init', array( $this, 'register_image_sizes' ) );
		add_action( 'acf/init', array( $this, 'register_acf_blocks' ) );
		add_action( 'init', array( $this, 'admin_add_wysiwyg_styles' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'block_editor_scripts' ) );

		require( 'wp-filters.php' );
		require( 'wp-functions.php' );
		require( 'wp-hooks.php' );

		require( 'admin/wp-custom-admin.php' );
		require( 'admin/wp-custom-admin-menu.php' );
		require( 'admin/wp-custom-menu-bar.php' );

		$this->layout = new Layout();

		add_action( 'render_design_grid', array( $this, 'design_grid' ) );
		parent::__construct();
	}

	/**
	 * Add theme support
	 */
	function add_theme_support() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_theme_support( 'html5',
			array(
				'comment-list',
				'comment-form',
				'search-form',
				'gallery',
				'caption',
			)
		);

		/*
		 * Soil features: https://roots.io/plugins/soil/
		 */
		add_theme_support( 'soil-clean-up' );
		add_theme_support( 'soil-disable-asset-versioning' );
		add_theme_support( 'soil-disable-trackbacks' );
		// add_theme_support( 'soil-google-analytics', 'UA-XXXXX-Y' );
		// add_theme_support( 'soil-jquery-cdn' );
		add_theme_support( 'soil-js-to-footer' );
		add_theme_support( 'soil-nav-walker' );
		add_theme_support( 'soil-nice-search' );
		add_theme_support( 'soil-relative-urls' );

		// remove default admin bar margin-tops.
		add_theme_support( 'admin-bar', array(
			'callback' => '__return_false',
		) );
	}

	/**
	 * Block editor support
	 */
	function block_editor_support() {

		add_theme_support( 'editor-color-palette', array(
			array(
				'name' => 'Grey',
				'slug' => 'grey',
				'color' => '#999999',
			),
		) );

		add_theme_support( 'align-wide' );

		add_theme_support( 'editor-font-sizes', array(
			array(
				'name' => __( 'Normal' ),
				'shortName' => __( 'N' ),
				'size' => 18,
				'slug' => 'normal',
			),
			array(
				'name' => __( 'Large' ),
				'shortName' => __( 'L' ),
				'size' => 32,
				'slug' => 'large',
			),
		) );

		add_theme_support( 'disable-custom-font-sizes' );
	}

	/**
	 * Custom post types.
	 */
	function register_post_types() {
		require( 'wp-post-types.php' );
	}

	/**
	 * Taxonomies.
	 */
	function register_taxonomies() {
		require( 'wp-taxonomies.php' );
	}

	/**
	 * Menu locations.
	 */
	function register_menus() {
		require( 'wp-menus.php' );
	}

	/**
	 * Image sizes.
	 */
	function register_image_sizes() {
		require( 'wp-image-sizes.php' );
	}

	/**
	 * ACF gutenberg blocks.
	 */
	function register_acf_blocks() {
		if ( function_exists( 'acf_register_block' ) ) {
			require( 'wp-blocks.php' );
		}
	}


	/**
	 * Add global Timber variables.
	 *
	 * @param array $context Timber context.
	 * @return $context
	 */
	function add_to_context( $context ) {
		$context['site'] = $this;
		return $context;
	}

	/**
	 * Add to twig
	 *
	 * @see https://timber.github.io/docs/guides/extending-timber/
	 *
	 * @param Object $twig The twig object.
	 * @return $twig
	 */
	function add_to_twig( $twig ) {
		/*
		 * this is where to add your own extensions or filters to twig.
		 */

		/**
		 * Return a Sockman Image.
		 */
		$twig->addFunction( new Timber\Twig_Function( 'SockmanImage',  function( $pid ) {
			return new Image( $pid, $this->layout );
		} ) );

		return $twig;
	}

	/**
	 * Enqueue styles and scripts
	 */
	function scripts() {
		// wp-embed is not needed.
		wp_deregister_script( 'wp-embed' );

		// remove jetpack devicepx.
		wp_dequeue_script( 'devicepx' );

		// remove photon js
		wp_dequeue_script( 'jetpack-photon' );

		// remove block library css
		wp_dequeue_style( 'wp-block-library' );

		if ( defined( 'WP_ENV' ) && 'production' === WP_ENV || 'staging' === WP_ENV ) {
			wp_enqueue_style( 'sockman-style', get_template_directory_uri() . '/assets/dist/css/' . $this->get_css_filename( 'style.css' ), '', null );
		} else {
			wp_enqueue_style( 'sockman-style', get_template_directory_uri() . '/assets/dist/css/' . $this->get_css_filename( 'style-dev.css' ), '', null );
		}

		wp_enqueue_script( 'sockman-script', get_template_directory_uri() . '/assets/dist/js/' . $this->get_js_filename( 'site.js' ), '', null, true );

		if ( defined( 'WP_ENV' ) && 'development' === WP_ENV ) {
			// wp_enqueue_script( 'livereload-server', get_home_url() . '/livereload.js?snipver=1&port=80', '', null, true );
			wp_enqueue_script( 'livereload-server', 'http://127.0.0.1:35729/livereload.js', '', null, true );
		}
	}

	/**
	 * Add custom editor styles for wysiwyg.
	 *
	 * @return void
	 */
	function admin_add_wysiwyg_styles() {
		add_editor_style( '/assets/dist/css/' . $this->get_css_filename( 'wysiwyg.css' ) );
	}

	/**
	 * Add custom editor styles for gutenberg.
	 *
	 * @return void
	 */
	function block_editor_scripts() {
		wp_enqueue_style( 'sockman-editor-style', get_template_directory_uri() . '/assets/dist/css/' . $this->get_css_filename( 'editor.css' ), '', null );

		// gutenberg customisation scripts.
		wp_enqueue_script( 'block-styles-edit', get_template_directory_uri() . '/assets/dist/js/' . $this->get_js_filename( 'block-editor-customisations.js' ), array( 'wp-blocks', 'wp-dom' ), null, true );
	}

	/**
	 * Get the stylesheet path
	 *
	 * @param  string $filename Name of stylesheet.
	 * @return string
	 */
	function get_css_filename( $filename ) {
		$manifest_path = get_template_directory() . '/assets/dist/css/css-manifest.json';

		if ( file_exists( $manifest_path ) ) {
			$manifest = json_decode( file_get_contents( $manifest_path ), true );
		} else {
			$manifest = [];
		}

		if ( array_key_exists( $filename, $manifest ) ) {
			return $manifest[ $filename ];
		}

		return $filename;
	}

	/**
	 * Get the script path
	 *
	 * @param  string $filename Name of script.
	 * @return string
	 */
	function get_js_filename( $filename ) {
		$manifest_path = get_template_directory() . '/assets/dist/js/js-manifest.json';

		if ( file_exists( $manifest_path ) ) {
			$manifest = json_decode( file_get_contents( $manifest_path ), true );
		} else {
			$manifest = [];
		}

		if ( array_key_exists( $filename, $manifest ) ) {
			return $manifest[ $filename ];
		}

		return $filename;
	}

	/**
	 * Show the grid in development
	 */
	function design_grid() {
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) :
		?>
			<input type="checkbox" id="grid-checkbox">
			<div class="c-design-grid">
				<div class="o-wrapper">
					<div class="o-layout">
		<?php
		for ( $i = 0; $i < 16; $i++ ) :
		?>
						<div class="o-layout__cell o-layout__cell--span-1 c-design-grid__cell"></div>
		<?php
		endfor;
		?>
					</div>
				</div>
			</div>
		<?php
		endif;
	}

	/**
	 * Gallery that uses our components.gallery.twig file instead of the default WordPress gallery
	 * defined in gallery_shortcode in /web/wp/wp-includes/media.php
	 *
	 * @param string $output The gallery output. Default empty.
	 * @param array  $attr Attributes of the gallery shortcode.
	 * @param int    $instance Unique numeric ID of this gallery shortcode instance.
	 * @return $output
	 */
	function gallery( $output, $attr, $instance ) {

		$post = get_post();

		$atts = shortcode_atts( array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
			'itemtag'    => 'figure',
			'icontag'    => 'div',
			'captiontag' => 'figcaption',
			'columns'    => 3,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => '',
			'link'       => '',
		), $attr, 'gallery' );

		$id = intval( $atts['id'] );

		if ( ! empty( $atts['include'] ) ) {
			$_attachments = get_posts( array( 'include' => $atts['include'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );

			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[ $val->ID ] = $_attachments[ $key ];
			}
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $atts['exclude'], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		} else {
			$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $atts['order'], 'orderby' => $atts['orderby'] ) );
		}

		if ( empty( $attachments ) ) {
			return '';
		}

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
			}
			return $output;
		}

		return Timber::compile( 'components/components.gallery.twig', array(
			'attachments' => $attachments,
			'atts' => $atts,
		) );
	}
}

return new Site();
