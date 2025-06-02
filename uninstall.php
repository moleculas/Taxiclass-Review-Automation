<?php
/**
 * Archivo de desinstalación del plugin
 * Se ejecuta cuando el plugin es eliminado desde WordPress
 */

// Si no es WordPress quien llama este archivo, salir
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Cargar la clase de base de datos
require_once plugin_dir_path(__FILE__) . 'includes/class-database.php';

// Eliminar la tabla
TaxiClass_Review_Database::drop_table();

// Eliminar opciones del plugin si las hay
delete_option('taxiclass_review_settings');