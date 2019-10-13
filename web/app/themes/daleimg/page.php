<?php
/**
 * Pages
 *
 * @package  sockman
 */

$context         = Timber::get_context();
$wp_post         = new TimberPost();
$context['post'] = $wp_post;

$context['images'] = Timber::get_posts(
	array(
		'post_type'      => 'attachment',
		'posts_per_page' => -1,
		'post_mime_type' => 'image',
		'post_status'    => 'any',
	),
	'Sockman\Image'
);

Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );
