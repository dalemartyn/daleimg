<?php
/**
 * Functions.
 *
 * @package Sockman
 */

/**
 * ACF Gutenberg blocks render callback.
 *
 * @param   array  $block      The block settings and attributes.
 * @param   string $content    The block content (emtpy string).
 * @param   bool   $is_preview True during AJAX preview.
 */
function sockman_blocks_render_callback( $block, $content = '', $is_preview = false ) {
	$context = Timber::get_context();

	// Convert name ("acf/testimonial") into path friendly slug ("testimonial").
	$slug = str_replace( 'acf/', '', $block['name'] );

	// Store block values.
	$context['block'] = $block;

	// Store field values.
	$context['fields'] = get_fields();

	// Store $is_preview value.
	$context['is_preview'] = $is_preview;

	// Render the block.
	Timber::render( 'blocks/blocks.' . $slug . '.twig', $context );
}
