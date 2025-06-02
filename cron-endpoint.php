<?php
/**
 * Endpoint para ejecutar el procesamiento de reseñas
 * Este archivo será llamado directamente por el cron del servidor
 */

// Cargar WordPress
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

// Verificar que el plugin esté activo
if (!defined('TAXICLASS_REVIEW_PLUGIN_DIR')) {
    die('Plugin TaxiClass Review no está activo');
}

// Cargar las clases necesarias
require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-database.php';
require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-gravity-forms-integration.php';
require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-email-sender.php';
require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'includes/class-review-processor.php';

// Ejecutar el procesamiento
$result = TaxiClass_Review_Processor::process_yesterday_reviews();

// Log del resultado
error_log('TaxiClass Review Cron: ' . $result['message']);

// Mostrar resultado (opcional, para debugging)
echo 'TaxiClass Review Cron ejecutado: ' . $result['message'];