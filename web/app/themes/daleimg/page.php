<?php
/**
 * Pages
 *
 * @package  sockman
 */

$context = Timber::get_context();
$wp_post = new TimberPost();
$context['post'] = $wp_post;
Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );
