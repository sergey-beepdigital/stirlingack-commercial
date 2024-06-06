<?php /* Template Name: Properties Listing */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render(array('page-properties-listing.twig','page.twig'), $context);
