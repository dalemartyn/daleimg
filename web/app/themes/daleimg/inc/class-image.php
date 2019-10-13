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
		if ( 'full' === $size ) {
			Helper::warn( 'Using an image without specifying size' );
			return;
		}
		return parent::src( $size );
	}

	/**
	 * Get the image srcset
	 *
	 * @param mixed $size The size string/array.
	 * @return String the srcset.
	 */
	public function srcset( $size = 'full' ) {
		$thumbnail_id = $this->id;
		// will return false if only one image matches the aspect ratio.
		$srcset = wp_get_attachment_image_srcset( $thumbnail_id, $size );

		if ( ! $srcset ) {
			$img  = wp_get_attachment_image_src( $thumbnail_id, $size );
			if ( $img ) {
				$srcset = $img[0];
			}
		}

		return $srcset;
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

}
