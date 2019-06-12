<?php

if( function_exists('acf_add_local_field_group') ) {
    acf_add_local_field_group(array (
        'key' => 'group_595e2b377bb9f',
        'title' => 'ACF Edit Screen',
        'fields' => array (
            array (
                'key' => 'field_595e2b4027c09',
                'label' => 'Enable ACF Edit Screen in the backend',
                'name' => 'enable_acf_edit',
                'type' => 'true_false',
                'instructions' => 'IMPORTANT. ACF Fields should be edited via a local version of the theme and the acf-json file sync, editing the fields on the site here will clash with changes in the theme and break the syncing. Enable this at your own risk.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => 'READ ABOVE BEFORE TICKING',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'acf-options-debug-options',
                ),
            )
        ),
        'menu_order' => -1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));
};
