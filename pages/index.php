<?php

/**
 * Template Manager
 * 
 * @author KLXM
 */

$addon = rex_addon::get('template_manager');

echo rex_view::title($addon->i18n('template_manager_title'));

rex_be_controller::includeCurrentPageSubPath();
