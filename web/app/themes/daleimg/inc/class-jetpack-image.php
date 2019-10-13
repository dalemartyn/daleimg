<?php
/**
 * Sockman Images.
 *
 * @package Sockman
 */

namespace Sockman;

use Timber\Helper;

use Timber\Image as TimberImage;

/**
 * Sockman Image class.
 */
class Image extends TimberImage {

	/**
	 * Creates a new SockmanImage object.
	 *
	 * @param int|string $iid The image id.
	 * @param object     $layout The sockman layout object.
	 */
	function __construct( $iid, $layout ) {
		parent::__construct( $iid );

		$this->layout = $layout;
	}

	/**
	 * Get the image src.
	 *
	 * @param string $size a size known to WordPress (like "medium").
	 * @return bool|string
	 * @throws Exception no size given.
	 */
	public function src( $size = 'full' ) {
		$all_image_sizes = wp_get_additional_image_sizes();
		if ( ! $this->jetpack_enabled() ) {
			return parent::src( $size );
		}

		if ( isset( $this->abs_url ) ) {
			return $this->_maybe_secure_url( $this->abs_url );
		}

		if ( ! $this->is_image() ) {
			return wp_get_attachment_url( $this->ID );
		}

		$src = wp_get_attachment_image_src( $this->ID, 'full' );
		$src = $src[0];

		/**
		 * If we have soil relative urls enabled, there's no host.
		 * could also have used `get_theme_support( 'soil-relative-urls' );`
		 */
		if ( ! array_key_exists( 'host', wp_parse_url( $src ) ) ) {
			$src = network_home_url() . $src;
		}

		/**
		 * Add query params to our URL so that it matches the requested size $size.
		 */
		$all_image_sizes = $this->get_all_image_sizes();
		if ( 'full' !== $size && array_key_exists( $size, $all_image_sizes ) ) {
			$image_size = $all_image_sizes[ $size ];
			$src = $this->get_src_with_query_string( $image_size, $src );
		}

		$src = apply_filters( 'timber/image/src', $src, $this->ID );
		$src = apply_filters( 'timber_image_src', $src, $this->ID );

		return $src;
	}

	/**
	 * Get the image srcset
	 *
	 * @param mixed $size The size string/array.
	 * @return String the srcset.
	 */
	public function srcset( $size = 'full' ) {
		/**
		 *  - get full size image url
		 *  - If photon active:
		 *  - look up our image sizes
		 *  - build an array of image sizes that matches our chosen size.
		 *  - filter each url using filters above.
		 *  - concatenate the urls with their size (width) and a comma
		 *  - return the srcset.
		 *  - else return full image size.
		 */

		$img_src   = wp_get_attachment_image_src( $this->ID, 'full' );
		$src       = $img_src[0];
		$width     = $img_src[1];
		$height    = $img_src[2];
		$crop      = false;
		$max_width = $width;

		if ( ! $this->jetpack_enabled() ) {
			return $src . ' ' . $width . 'w';
		}

		/**
		 * If we have soil relative urls enabled, there's no host.
		 * could also have used `get_theme_support( 'soil-relative-urls' );`
		 */
		if ( ! array_key_exists( 'host', wp_parse_url( $src ) ) ) {
			$src = network_home_url() . $src;
		}

		$all_image_sizes         = $this->get_all_image_sizes();
		$all_image_sizes['full'] = array(
			'width'  => $width,
			'height' => 0,
			'crop'   => false,
		);

		$sources = array();

		/**
		 * Get desired size.
		 */
		if ( 'full' !== $size && array_key_exists( $size, $all_image_sizes ) ) {
			$image_size = $all_image_sizes[ $size ];
			$width      = $image_size['width'];
			$height     = $image_size['height'];
			$crop       = $image_size['crop'];
		}

		/*
		 * Loop through available images. Only use images that are the same aspect ratio.
		 */
		foreach ( $all_image_sizes as $size_name => $image_size ) {
			$is_src = false;

			// Check if image meta isn't corrupted.
			if ( ! is_array( $image_size ) ) {
				continue;
			}

			/*
			 * Filters out images that are wider than '$max_width'.
			 */
			if ( $image_size['width'] > $max_width ) {
				continue;
			}

			/**
			 * Don't use soft cropped images if we want a hard crop. (e.g. default large size).
			 */
			if ( false === $image_size['crop'] && true === $crop ) {
				continue;
			}

			// If the image dimensions are within 1px of the expected size, use it.
			if ( wp_image_matches_ratio( $width, $height, $image_size['width'], $image_size['height'] ) ) {
				// Add the URL, descriptor, and value to the sources array to be returned.

				if ( 'full' === $size_name ) {
					$img_src = $src;
				} else {
					$img_src = $this->get_src_with_query_string( $image_size, $src );
				}

				$img_src = apply_filters( 'timber/image/src', $img_src, $this->ID );
				$img_src = apply_filters( 'timber_image_src', $img_src, $this->ID );

				$source = array(
					'url'        => $img_src,
					'descriptor' => 'w',
					'value'      => $image_size['width'],
				);

				$sources[ $image_size['width'] ] = $source;
			}
		}

		$srcset = '';

		foreach ( $sources as $source ) {
			$srcset .= str_replace( ' ', '%20', $source['url'] ) . ' ' . $source['value'] . $source['descriptor'] . ', ';
		}

		return rtrim( $srcset, ', ' );
	}

	/**
	 * Check if jetpack is enabled.
	 *
	 * @return $jetpack_enabled
	 */
	private function jetpack_enabled() {
		if (
			! class_exists( 'Jetpack' ) ||
			! method_exists( 'Jetpack', 'get_active_modules' ) ||
			! in_array( 'photon', \Jetpack::get_active_modules(), true )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Get the sizes.
	 *
	 * @param array $sizes The array of sizes. Each size is an array of span and cols (in that order).
	 * @return $sizes
	 */
	public function sizes( $sizes ) {
		$span_at_max = $sizes[0][0];
		$max_size = $this->layout->max_size( $span_at_max );

		$flexible_sizes = implode(', ', array_map( function ( $size ) {
			return $this->layout->flexible_width( $size );
		}, $sizes ) );

		return $max_size . ', ' . $flexible_sizes;
	}

	/**
	 * Get all image sizes for the image.
	 *
	 * @return $image_sizes
	 */
	public function get_all_image_sizes() {
		$all_image_sizes = wp_get_additional_image_sizes();
		$all_image_sizes['thumbnail'] = array(
			'width'  => 150,
			'height' => 150,
			'crop'   => true,
		);
		$all_image_sizes['medium'] = array(
			'width'  => 300,
			'height' => 300,
			'crop'   => false,
		);
		$all_image_sizes['medium_large'] = array(
			'width'  => 768,
			'height' => 0,
			'crop'   => false,
		);
		$all_image_sizes['large'] = array(
			'width'  => 1024,
			'height' => 1024,
			'crop'   => false,
		);

		return $all_image_sizes;
	}


	/**
	 * Get the args / query string for a photon image.
	 *
	 * @param array  $size The image size array.
	 * @param string $src The image src without query args.
	 * @return $src
	 */
	public function get_src_with_query_string( $size, $src ) {
		if ( true === $size['crop'] && 0 !== $size['height'] && 0 !== $size['width'] ) {
			$args = array(
				'resize' => $size['width'] . ',' . $size['height']
			);
			$img_src = $src . '?' . http_build_query( $args );
			return $img_src;
		} elseif ( 0 !== $size['width'] ) {
			$args = array();
			$args['w'] = $size['width'];

			if ( 0 !== $size['height'] ) {
				$args['h'] = $size['height'];
			}

			$img_src = $src . '?' . http_build_query( $args );
			return $img_src;
		}

		return $src;

	}

}
