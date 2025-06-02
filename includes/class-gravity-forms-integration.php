<?php

/**
 * Clase para la integración con Gravity Forms
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class TaxiClass_Gravity_Forms_Integration
{

    /**
     * ID del formulario de Gravity Forms
     */
    private static $form_id = 5;

    /**
     * IDs de los campos del formulario
     */
    private static $field_ids = array(
        'id_entrada' => 'id',
        'dia_recogida' => '23',
        'nombre_pasajero' => '19',
        'email' => '20'
    );

    /**
     * Verificar si Gravity Forms está activo
     */
    public static function is_gravity_forms_active()
    {
        return class_exists('GFAPI');
    }

    /**
     * Obtener entradas por fecha de recogida
     * 
     * @param string $fecha Fecha en formato Y-m-d
     * @return array Array de entradas o array vacío si hay error
     */
    public static function get_entries_by_pickup_date($fecha)
    {
        if (!self::is_gravity_forms_active()) {
            error_log('TaxiClass Review: Gravity Forms no está activo');
            return array();
        }

        // Configurar los parámetros de búsqueda
        $search_criteria = array(
            'status' => 'active',
            'field_filters' => array(
                array(
                    'key' => self::$field_ids['dia_recogida'],
                    'value' => $fecha,
                    'operator' => '='
                )
            )
        );

        // Configurar ordenamiento
        $sorting = array(
            'key' => 'id',
            'direction' => 'DESC'
        );

        // Configurar paginación (obtenemos todas las entradas)
        $paging = array(
            'offset' => 0,
            'page_size' => 999999 // Número alto para obtener todas
        );

        // Obtener las entradas
        $entries = GFAPI::get_entries(
            self::$form_id,
            $search_criteria,
            $sorting,
            $paging
        );

        // Verificar si hubo error
        if (is_wp_error($entries)) {
            error_log('TaxiClass Review: Error al obtener entradas - ' . $entries->get_error_message());
            return array();
        }

        return $entries;
    }

    /**
     * Procesar entradas y preparar datos para la base de datos
     * 
     * @param string $fecha Fecha a procesar
     * @return array Array con los datos procesados
     */
    public static function process_entries_for_date($fecha)
    {
        $entries = self::get_entries_by_pickup_date($fecha);
        $processed_data = array();

        if (empty($entries)) {
            error_log('TaxiClass Review: No se encontraron entradas para la fecha ' . $fecha);
            return $processed_data;
        }

        foreach ($entries as $entry) {
            // Verificar que tengamos todos los campos necesarios
            if (
                empty($entry[self::$field_ids['nombre_pasajero']]) ||
                empty($entry[self::$field_ids['email']])
            ) {
                continue;
            }

            // Preparar datos para insertar (sin sanitización ya que vienen validados de GF)
            $data = array(
                'dia' => $fecha,
                'id_cliente' => intval($entry['id']),
                'nombre_cliente' => $entry[self::$field_ids['nombre_pasajero']],
                'email_cliente' => $entry[self::$field_ids['email']]
            );

            $processed_data[] = $data;
        }

        error_log('TaxiClass Review: Procesadas ' . count($processed_data) . ' entradas para la fecha ' . $fecha);

        return $processed_data;
    }

    /**
     * Obtener el nombre del formulario
     */
    public static function get_form_name()
    {
        if (!self::is_gravity_forms_active()) {
            return 'Gravity Forms no activo';
        }

        $form = GFAPI::get_form(self::$form_id);

        if (!$form) {
            return 'Formulario no encontrado';
        }

        return $form['title'];
    }

    /**
     * Verificar si el formulario existe
     */
    public static function form_exists()
    {
        if (!self::is_gravity_forms_active()) {
            return false;
        }

        $form = GFAPI::get_form(self::$form_id);
        return !empty($form);
    }

    /**
     * Obtener información de diagnóstico
     */
    public static function get_diagnostic_info()
    {
        $info = array(
            'gravity_forms_active' => self::is_gravity_forms_active(),
            'form_exists' => false,
            'form_name' => '',
            'total_entries' => 0
        );

        if ($info['gravity_forms_active']) {
            $info['form_exists'] = self::form_exists();

            if ($info['form_exists']) {
                $info['form_name'] = self::get_form_name();

                // Contar total de entradas
                $entries = GFAPI::get_entries(self::$form_id);
                if (!is_wp_error($entries)) {
                    $info['total_entries'] = count($entries);
                }
            }
        }

        return $info;
    }
}
