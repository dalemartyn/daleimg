<?php
/**
 * Filters.
 *
 * @package Sockman
 */

/**
 * Change max srcset width
 */
add_filter( 'max_srcset_image_width', 'sockman_max_srcset_image_width' );

/**
 * Include wide images in the srcset
 */
function sockman_max_srcset_image_width() {
	return 2560;
}







add_filter( 'jpeg_quality', 'ade_image_quality', 10, 2 );
add_filter( 'wp_editor_set_quality', 'ade_image_quality', 10, 2 );

/**
 * Change the image quality
 *
 * @param int    $quality Quality level between 0 (low) and 100 (high) of the JPEG. Default 82.
 * @param string $context Context of the filter.
 * @return $quality
 */
function ade_image_quality( $quality, $context ) {
	return 82;
}





add_filter( 'wp_generate_attachment_metadata', 'ade_pngquant', 10, 2 );

/**
 * Optimize our pngs.
 *
 * @param array $metadata An array of attachment meta data.
 * @param int   $attachment_id Current attachment ID.
 * @return $metadata
 */
function ade_pngquant( $metadata, $attachment_id ) {
	$attachment = get_post( $attachment_id );
	$mime_type = get_post_mime_type( $attachment );

	if ( 'image/png' !== $mime_type ) {
		return $metadata;
	}

	$uploads_dir = wp_upload_dir();
	$uploads_base_dir = $uploads_dir['basedir'];
	$file_dir = dirname( $metadata['file'] );

	$directory = $uploads_base_dir . '/' . $file_dir;

	foreach ( $metadata['sizes'] as $size => $size_info ) {
		$size_path = $directory . '/' . $size_info['file'];
		compress_png( $size_path, 70 );
	}

	return $metadata;

}

/**
 * Optimizes PNG file with pngquant 1.8 or later (reduces file size of 24-bit/32-bit PNG images).
 *
 * You need to install pngquant 1.8 on the server (ancient version 1.0 won't work).
 * There's package for Debian/Ubuntu and RPM for other distributions on http://pngquant.org
 *
 * @param string $path_to_png_file The path to any PNG file, e.g. $_FILE['file']['tmp_name'].
 * @param int    $max_quality The conversion quality, useful values from 60 to 100 (smaller number = smaller file).
 * @return void
 * @throws Exception File does not exist.
 */
function compress_png( $path_to_png_file, $max_quality = 70 ) {
	if ( ! file_exists( $path_to_png_file ) ) {
		throw new Exception( "File does not exist: $path_to_png_file" );
	}

	// use 0 so we don't get error code 99.
	$min_quality = 0;

	// '<' makes it read from the given file path
	// escapeshellarg() makes this safe to use with any path
	exec( "pngquant --quality=$min_quality-$max_quality --speed=10 --ext=.png --force " . escapeshellarg( $path_to_png_file ), $output, $return );

	if ( 0 !== $return ) {
		throw new Exception( 'Conversion to compressed PNG failed. Is pngquant 1.8+ installed on the server?' );
	}
}
