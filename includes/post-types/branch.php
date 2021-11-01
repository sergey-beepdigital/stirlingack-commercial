<?php

$args = array(
    'labels'             => array(
        'name'          => __('Branches'),
        'singular_name' => __('Branch')
    ),
	'supports'              => array( 'title' ),
	'hierarchical'          => false,
	'public'                => true,
	'show_ui'               => true,
	'show_in_menu'          => true,
	//'menu_position'         => 5,
	'menu_icon'             => 'dashicons-building',
	'show_in_admin_bar'     => true,
	'show_in_nav_menus'     => false,
	'can_export'            => true,
	'has_archive'           => false,
	'exclude_from_search'   => true,
	'publicly_queryable'    => true,
	'rewrite'               => false,
	'capability_type'       => 'post',
	'show_in_rest'          => true,
);
register_post_type( 'sa_branch', $args );
