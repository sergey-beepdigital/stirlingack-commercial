<?php /* Template Name: Sections */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

Timber::render(array('page-sections.twig'), $context);
