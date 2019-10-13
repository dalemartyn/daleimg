<?php
/**
 * The template for attachment pages.
 *
 * @package  sockman
 */

$context = Timber::get_context();
$wp_post = new TimberPost();
$context['post'] = $wp_post;
Timber::render( 'attachment.twig', $context );
