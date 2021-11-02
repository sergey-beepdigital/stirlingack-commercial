<?php
    $search_parameters = $this->process_search_url_to_array($saved_search['search_string']);
    echo '<div>';
    $search_name = isset( $saved_search['relationship_name'] ) ? $saved_search['relationship_name'] : __( 'Unnamed Search', 'propertyhive' ) ;
    echo '<h4>' . $search_name . '</h4>';
    echo '<span class="saved-search-attributes" style="color: #869099;">';
    foreach ($search_parameters as $search_field => $search_value)
    {
        if ( isset($form_controls[$search_field]) )
        {
            $label = isset( $form_controls[$search_field]['label'] ) ? $form_controls[$search_field]['label'] : ucwords(str_replace("_", " ", $search_field));

            $value = is_array($search_value) ? implode(', ', $search_value) : $search_value;
            if ( isset( $form_controls[$search_field]['options'] ) )
            {
                if ( !is_array($search_value) )
                {
                    $search_value = array($search_value);
                }
                $values_array = array();
                foreach ($search_value as $single_search_value)
                {
                    if ( isset($form_controls[$search_field]['options'][$single_search_value]) )
                    {
                        $values_array[] = $form_controls[$search_field]['options'][$single_search_value];
                    }
                }
                $value = implode(', ', $values_array);
            }
            else
            {
                if ( $form_controls[$search_field]['type'] == 'text' )
                {
                    $value = $search_value;
                }
                else
                {
                    if ( !is_array($search_value) )
                    {
                        $search_value = array($search_value);
                    }
                    $values_array = array();
                    foreach ($search_value as $single_search_value)
                    {
                        if ( taxonomy_exists($search_field) )
                        {
                            $term = get_term( $single_search_value, $search_field );
                            if ( !empty( $term ) && !is_wp_error( $term ) )
                            {
                                $values_array[] = $term->name; 
                            }
                        }
                    }
                    $value = implode(', ', $values_array);
                }
                
            }
            echo $label . ($label != '' ? ': ' : '') . $value . '<br>';
        }
        else
        {
            // TODO: Decide what to do with these
            if ( is_array($search_value) )
            {
                $search_value = implode(', ', $search_value);
            }
            echo ucwords(str_replace("_", " ", $search_field)) . ': ' . $search_value . '<br>';
        }
    }

    echo '</span><br>';

    $field = array(
        'class' => 'update_search_send_emails',
        'type' => 'checkbox',
        'label' => __( 'Receive Email Alerts For Properties Matching This Search', 'propertyhive' ),
        'show_label' => true,
        'value' => $key,
        'checked' => ( isset( $saved_search['send_matching_properties'] ) && $saved_search['send_matching_properties'] == 'yes'),
    );
    ph_form_field( 'update_search_send_emails', $field );
    
    echo '<a href="' . get_post_type_archive_link( 'property' ) . '?' . $saved_search['search_string'] . '" class="button btn btn-lg btn-primary text-uppercase" rel="nofollow"><i class="fa-regular fa-magnifying-glass"></i> ' . __( 'View Properties', 'propertyhive' ) . '</a>';
    echo '<br><a class="button-delete-search btn btn-lg btn-secondary text-uppercase" id="remove_saved_search" profile_to_remove="' . $key . '" rel="nofollow"><i class="fa-regular fa-trash"></i> ' . $remove_link_text . '</a>';
    echo '</div>';
