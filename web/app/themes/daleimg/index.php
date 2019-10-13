<?php
/**
 * Main template file
 *
 * @package  sockman
 */

$context = Timber::get_context();
$wp_post = new TimberPost();
$context['post'] = $wp_post;

$context['posts'] = new Timber\PostQuery();

$templates = array( 'index.twig' );

if ( is_home() ) {
	array_unshift( $templates, 'home.twig' );
}

Timber::render( $templates, $context );
