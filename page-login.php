<?php /* Template Name: Login */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$context['register_page_url'] = get_the_permalink(get_option('propertyhive_applicant_registration_page_id'));

Timber::render(array('page-login.twig','page.twig'), $context);
