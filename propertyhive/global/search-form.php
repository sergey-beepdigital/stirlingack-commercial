<?php
/**
 * Property search form
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php $context = Timber::get_context();

$context['form_id'] = $id;
$context['property_archive_link'] = get_post_type_archive_link('property');
$context['form_controls'] = apply_filters('sa_property_search_controls', $form_controls, $id);

Timber::render( array( 'propertyhive/global/search-form-' . $id . '.twig', 'propertyhive/global/search-form-default.twig' ), $context );

