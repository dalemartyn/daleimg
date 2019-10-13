<?php
/**
 * Single posts
 *
 * @package  sockman
 */

$context = Timber::get_context();
$wp_post = Timber::query_post();
$context['post'] = $wp_post;

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
