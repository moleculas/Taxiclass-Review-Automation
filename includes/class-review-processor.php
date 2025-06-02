<?php
/**
 * Clase principal para procesar las solicitudes de reseñas
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class TaxiClass_Review_Processor {
    
    /**
     * Procesar solicitudes de reseñas para una fecha específica
     * 
     * @param string $fecha Fecha en formato Y-m-d
     * @return array Resultado del procesamiento
     */
    public static function process_reviews_for_date($fecha) {
        $result = array(
            'success' => true,
            'message' => '',
            'processed' => 0,
            'sent' => 0,
            'errors' => 0,
            'details' => array()
        );
        
        // Verificar que Gravity Forms esté activo
        if (!TaxiClass_Gravity_Forms_Integration::is_gravity_forms_active()) {
            $result['success'] = false;
            $result['message'] = 'Gravity Forms no está activo';
            return $result;
        }
        
        // Obtener y procesar entradas de Gravity Forms
        $entries = TaxiClass_Gravity_Forms_Integration::process_entries_for_date($fecha);
        
        if (empty($entries)) {
            $result['success'] = false;
            $result['message'] = 'No se encontraron reservas para la fecha ' . $fecha;
            return $result;
        }
        
        $result['message'] = 'Encontradas ' . count($entries) . ' reservas para procesar';
        
        // Procesar cada entrada
        foreach ($entries as $entry) {
            $process_result = self::process_single_entry($entry);
            
            $result['processed']++;
            
            if ($process_result['success']) {
                if ($process_result['action'] === 'sent') {
                    $result['sent']++;
                }
                $result['details'][] = array(
                    'cliente' => $entry['nombre_cliente'],
                    'email' => $entry['email_cliente'],
                    'status' => 'success',
                    'message' => $process_result['message']
                );
            } else {
                $result['errors']++;
                $result['details'][] = array(
                    'cliente' => $entry['nombre_cliente'],
                    'email' => $entry['email_cliente'],
                    'status' => 'error',
                    'message' => $process_result['message']
                );
            }
        }
        
        // Actualizar mensaje final
        $result['message'] = sprintf(
            'Procesamiento completado: %d reservas procesadas, %d emails enviados, %d errores',
            $result['processed'],
            $result['sent'],
            $result['errors']
        );
        
        return $result;
    }
    
    /**
     * Procesar una entrada individual
     * 
     * @param array $entry Datos de la entrada
     * @return array Resultado del procesamiento
     */
    private static function process_single_entry($entry) {
        $result = array(
            'success' => false,
            'message' => '',
            'action' => ''
        );
        
        // Verificar si ya existe el registro
        if (TaxiClass_Review_Database::record_exists($entry['id_cliente'], $entry['dia'])) {
            $result['success'] = true;
            $result['message'] = 'Registro ya existente, omitido';
            $result['action'] = 'skipped';
            return $result;
        }
        
        // Insertar en la base de datos
        $insert_id = TaxiClass_Review_Database::insert_record($entry);
        
        if (is_wp_error($insert_id)) {
            $result['message'] = 'Error al guardar en base de datos: ' . $insert_id->get_error_message();
            return $result;
        }
        
        // Enviar el email
        $email_sent = TaxiClass_Email_Sender::send_review_request($entry);
        
        if ($email_sent) {
            // Actualizar estado a enviado
            TaxiClass_Review_Database::update_record_status($insert_id, 'enviado');
            
            $result['success'] = true;
            $result['message'] = 'Email enviado correctamente';
            $result['action'] = 'sent';
        } else {
            // Actualizar estado a error
            TaxiClass_Review_Database::update_record_status($insert_id, 'error');
            
            $result['message'] = 'Error al enviar el email';
            $result['action'] = 'error';
        }
        
        return $result;
    }
    
    /**
     * Procesar automáticamente para el día anterior
     * Esta función será llamada por el cron
     */
    public static function process_yesterday_reviews() {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        error_log('TaxiClass Review: Iniciando procesamiento automático para ' . $yesterday);
        
        $result = self::process_reviews_for_date($yesterday);
        
        // Registrar resultado en el log
        error_log('TaxiClass Review: ' . $result['message']);
        
        // Opcional: Enviar notificación al admin
        if ($result['errors'] > 0) {
            self::notify_admin_errors($result);
        }
        
        return $result;
    }
    
    /**
     * Notificar al administrador sobre errores
     * 
     * @param array $result Resultado del procesamiento
     */
    private static function notify_admin_errors($result) {
        $admin_email = get_option('admin_email');
        $subject = 'TaxiClass Review: Errores en el procesamiento';
        
        $message = "Se han producido errores durante el procesamiento automático de reseñas.\n\n";
        $message .= "Resumen:\n";
        $message .= "- Reservas procesadas: " . $result['processed'] . "\n";
        $message .= "- Emails enviados: " . $result['sent'] . "\n";
        $message .= "- Errores: " . $result['errors'] . "\n\n";
        $message .= "Detalles de errores:\n";
        
        foreach ($result['details'] as $detail) {
            if ($detail['status'] === 'error') {
                $message .= "- Cliente: " . $detail['cliente'] . " (" . $detail['email'] . ")\n";
                $message .= "  Error: " . $detail['message'] . "\n\n";
            }
        }
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Obtener estadísticas generales
     */
    public static function get_statistics() {
        global $wpdb;
        
        $table_name = TaxiClass_Review_Database::get_table_name();
        
        $stats = array(
            'total' => 0,
            'pendientes' => 0,
            'enviados' => 0,
            'errores' => 0,
            'ultimos_7_dias' => 0,
            'ultimos_30_dias' => 0
        );
        
        // Total de registros
        $stats['total'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // Por estado
        $stats['pendientes'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE estado = 'pendiente'");
        $stats['enviados'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE estado = 'enviado'");
        $stats['errores'] = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE estado = 'error'");
        
        // Últimos 7 días
        $stats['ultimos_7_dias'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        
        // Últimos 30 días
        $stats['ultimos_30_dias'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        
        return $stats;
    }
}