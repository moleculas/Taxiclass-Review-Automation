<?php
/**
 * Clase para el menú de administración
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class TaxiClass_Admin_Menu {
    
    /**
     * Inicializar el menú
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
    }
    
    /**
     * Añadir el menú al admin
     */
    public static function add_admin_menu() {
        add_menu_page(
            'TaxiClass Reviews',           // Título de la página
            'TaxiClass Reviews',           // Título del menú
            'manage_options',              // Capacidad requerida
            'taxiclass-reviews',           // Slug
            array(__CLASS__, 'render_admin_page'), // Callback
            'dashicons-star-filled',       // Icono
            30                             // Posición
        );
    }
    
    /**
     * Renderizar la página de administración
     */
    public static function render_admin_page() {
        // Procesar acciones si las hay
        if (isset($_POST['action'])) {
            self::handle_admin_actions();
        }
        
        // Incluir la plantilla
        require_once TAXICLASS_REVIEW_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Manejar las acciones del admin
     */
    private static function handle_admin_actions() {
        // Verificar nonce
        if (!isset($_POST['taxiclass_nonce']) || !wp_verify_nonce($_POST['taxiclass_nonce'], 'taxiclass_admin_action')) {
            wp_die('Acción no autorizada');
        }
        
        $action = sanitize_text_field($_POST['action']);
        
        switch ($action) {
            case 'process_date':
                if (isset($_POST['fecha'])) {
                    $fecha = sanitize_text_field($_POST['fecha']);
                    $result = TaxiClass_Review_Processor::process_reviews_for_date($fecha);
                    
                    if ($result['success']) {
                        add_settings_error('taxiclass_messages', 'taxiclass_message', $result['message'], 'success');
                    } else {
                        add_settings_error('taxiclass_messages', 'taxiclass_message', $result['message'], 'error');
                    }
                }
                break;
                
            case 'test_email':
                if (isset($_POST['test_email'])) {
                    $email = sanitize_email($_POST['test_email']);
                    $sent = TaxiClass_Email_Sender::send_test_email($email);
                    
                    if ($sent) {
                        add_settings_error('taxiclass_messages', 'taxiclass_message', 'Email de prueba enviado a ' . $email, 'success');
                    } else {
                        add_settings_error('taxiclass_messages', 'taxiclass_message', 'Error al enviar email de prueba', 'error');
                    }
                }
                break;
        }
    }
}