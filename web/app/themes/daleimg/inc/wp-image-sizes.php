<?php
/**
 * Image Sizes and srcset helpers.
 *
 * Hooked to 'init' action. Only need image size declarations.
 *
 * @package Sockman
 */

$sockman       = $this;
$layout        = $sockman->layout;
$content_width = $layout->content_width();


/**
 * Only generate the default image sizes so that the dashboard /
 * media library can be fast.
 */
add_filter(
	'intermediate_image_sizes_advanced',
	function( $sizes ) {
		$s = $sizes;
		return array(
			'thumbnail'    => $sizes['thumbnail'],
			'medium'       => $sizes['medium'],
			'medium_large' => $sizes['medium_large'],
			'large'        => $sizes['large'],
		);
	}
);



/*
 * default mobile sensible image sizes. No cropping.
 */
add_image_size( '360w', 360 );
add_image_size( '600w', 600 );
add_image_size( '960w', 960 );

/*
 * full-width image with no cropping. Will also use mobile sizes above.
 */
add_image_size( 'banner', $content_width );
add_image_size( 'banner_2x', $content_width * 2 );

/*
 * square thumbnails
 */
add_image_size( '1x1_360w', 360, 360, true );
add_image_size( '1x1_600w', 600, 600, true );
add_image_size( '1x1_960w', 960, 960, true );

/*
 * 3x2 thumbnails
 */
add_image_size( '3x2_360w', 360, 240, true );
add_image_size( '3x2_600w', 600, 400, true );
add_image_size( '3x2_960w', 960, 640, true );
