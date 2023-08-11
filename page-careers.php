<?php /* Template Name: Careers */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

remove_action('after_header_breadcrumbs','sa_breadcrumbs',10);

Timber::render('page-careers.twig', $context);
