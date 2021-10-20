<?php
/**
 * Property Search Results Loop Start
 *
 * @author 		PropertyHive
 * @package 	PropertyHive/Templates
 * @version     1.0.0
 */
$view = !empty($_GET['view']) ? $_GET['view'] : 'grid'; ?>

<ul class="properties clear view-<?php echo $view; ?>">
