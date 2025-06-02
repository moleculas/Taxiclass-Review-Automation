<?php
/**
 * Clase para gestionar la base de datos del plugin
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class TaxiClass_Review_Database {
    
    /**
     * Nombre de la tabla (sin prefijo)
     */
    private static $table_name = 'taxiclass_review_requests';
    
    /**
     * Obtener el nombre completo de la tabla con prefijo
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::$table_name;
    }
    
    /**
     * Crear la tabla en la base de datos
     */
    public static function create_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            dia DATE NOT NULL,
            id_cliente INT(11) NOT NULL,
            nombre_cliente VARCHAR(255) NOT NULL,
            email_cliente VARCHAR(255) NOT NULL,
            fecha_envio DATETIME DEFAULT NULL,
            estado ENUM('pendiente', 'enviado', 'error') DEFAULT 'pendiente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            INDEX idx_dia (dia),
            INDEX idx_estado (estado),
            INDEX idx_email (email_cliente)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Guardar la versión de la base de datos
        update_option('taxiclass_review_db_version', '1.0');
    }
    
    /**
     * Insertar un registro en la tabla
     */
    public static function insert_record($data) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'dia' => $data['dia'],
                'id_cliente' => $data['id_cliente'],
                'nombre_cliente' => $data['nombre_cliente'],
                'email_cliente' => $data['email_cliente'],
                'fecha_envio' => null,
                'estado' => 'pendiente'
            ),
            array('%s', '%d', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_insert_error', 'Error al insertar el registro: ' . $wpdb->last_error);
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Obtener registros por día
     */
    public static function get_records_by_day($dia) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE dia = %s ORDER BY created_at DESC",
            $dia
        );
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Actualizar el estado de un registro
     */
    public static function update_record_status($id, $estado) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $result = $wpdb->update(
            $table_name,
            array(
                'estado' => $estado,
                'fecha_envio' => ($estado === 'enviado') ? current_time('mysql') : null
            ),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Verificar si ya existe un registro para un cliente en un día específico
     */
    public static function record_exists($id_cliente, $dia) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE id_cliente = %d AND dia = %s",
            $id_cliente,
            $dia
        ));
        
        return $count > 0;
    }
    
    /**
     * Obtener todos los registros con paginación
     */
    public static function get_all_records($page = 1, $per_page = 20, $estado = null) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $offset = ($page - 1) * $per_page;
        
        $where = '';
        if ($estado) {
            $where = $wpdb->prepare(" WHERE estado = %s", $estado);
        }
        
        $query = "SELECT * FROM $table_name" . $where . " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $query = $wpdb->prepare($query, $per_page, $offset);
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Obtener el total de registros
        $count_query = "SELECT COUNT(*) FROM $table_name" . $where;
        $total = $wpdb->get_var($count_query);
        
        return array(
            'records' => $results,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page
        );
    }
    
    /**
     * Eliminar la tabla (para desinstalación completa)
     */
    public static function drop_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        delete_option('taxiclass_review_db_version');
    }
}