<?php

/**
 * Plugin Name: TaxiClass Review Automation
 * Plugin URI: https://taxiclassrent.com/
 * Description: Automatiza el envío de solicitudes de reseñas a clientes y gestiona el registro en base de datos
 * Version: 1.0.0
 * Author: TaxiClass Development Team
 * Author URI: Artikaweb
 * License: GPL v2 or later
 * Text Domain: taxiclass-review
 * Domain Path: /languages
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes del plugin
define('TAXICLASS_REVIEW_VERSION', '1.0.0');
define('TAXICLASS_REVIEW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TAXICLASS_REVIEW_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TAXICLASS_REVIEW_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Activación del plugin
register_activation_hook(__FILE__, 'taxiclass_review_activate');
function taxiclass_review_activate()
{
    // Cargar la clase de base de datos
    require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-database.php';

    // Crear la tabla
    TaxiClass_Review_Database::create_table();
}

// Desactivación del plugin
register_deactivation_hook(__FILE__, 'taxiclass_review_deactivate');
function taxiclass_review_deactivate()
{
    // Por ahora no necesitamos hacer nada
}

// Cargar el plugin
add_action('plugins_loaded', 'taxiclass_review_load_plugin');
function taxiclass_review_load_plugin()
{
    // Cargar las clases necesarias
    require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-database.php';
    require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-gravity-forms-integration.php';
    require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-email-sender.php';
    require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-review-processor.php';

    // Cargar admin solo si estamos en el admin
    if (is_admin()) {
        require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-admin-menu.php';
        TaxiClass_Admin_Menu::init();
    }
}
