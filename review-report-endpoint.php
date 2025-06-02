<?php
/**
 * Endpoint para generar y enviar informe quincenal de reseñas
 * Para ser ejecutado por cron los días 15 y 30 de cada mes
 * 
 * URL: https://www.taxiclassrent.com/wp-content/plugins/taxiclass-review-automation/review-report-endpoint.php
 */

// Cargar WordPress
require_once(dirname(__FILE__) . '/../../../wp-load.php');

// Verificar que WordPress esté cargado
if (!defined('ABSPATH')) {
    die('WordPress no está cargado');
}

/**
 * Función principal para generar y enviar el informe
 */
function generate_and_send_review_report() {
    global $wpdb;
    
    // Configuración de destinatarios
    $recipients = array(
        'isaias@artikaweb.com',
        'm.grau@adgoritmo.com',
        'nico.mateo@taxiclassrent.com',
        'jordi.lopez@taxiclassrent.com'        
    );
    
    // Calcular fechas (15 días atrás desde hoy)
    $fecha_hasta = date('Y-m-d');
    $fecha_desde = date('Y-m-d', strtotime('-15 days'));
    
    // Consultar la base de datos
    $table_name = $wpdb->prefix . 'taxiclass_review_requests';
    
    $query = $wpdb->prepare(
        "SELECT id, dia, nombre_cliente, email_cliente, fecha_envio 
         FROM $table_name 
         WHERE created_at >= %s AND created_at <= %s 
         ORDER BY created_at DESC",
        $fecha_desde . ' 00:00:00',
        $fecha_hasta . ' 23:59:59'
    );
    
    $results = $wpdb->get_results($query);
    
    // Generar el HTML del email
    $html_content = generate_email_html($results, $fecha_desde, $fecha_hasta);
    
    // Configurar el email
    $subject = 'TaxiClass - Informe Quincenal de Reseñas (' . date('d/m/Y') . ')';
    
    // Configurar headers para HTML
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: TaxiClass Review System <no-reply@taxiclassrent.com>'
    );
    
    // Enviar el email
    $email_sent = wp_mail($recipients, $subject, $html_content, $headers);
    
    // Log del resultado
    if ($email_sent) {
        error_log('TaxiClass Review Report: Informe enviado correctamente a ' . implode(', ', $recipients));
        echo json_encode(array(
            'success' => true,
            'message' => 'Informe enviado correctamente',
            'recipients' => $recipients,
            'records' => count($results),
            'date_range' => array(
                'from' => $fecha_desde,
                'to' => $fecha_hasta
            )
        ));
    } else {
        error_log('TaxiClass Review Report: Error al enviar el informe');
        echo json_encode(array(
            'success' => false,
            'message' => 'Error al enviar el informe',
            'error' => 'wp_mail failed'
        ));
    }
}

/**
 * Generar el HTML del email
 */
function generate_email_html($results, $fecha_desde, $fecha_hasta) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 900px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background-color: #000;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .content {
                padding: 20px;
                background-color: #f9f9f9;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                background-color: white;
                margin: 20px 0;
            }
            th {
                background-color: #FFB800;
                color: black;
                padding: 12px;
                text-align: left;
                font-weight: bold;
            }
            td {
                border: 1px solid #ddd;
                padding: 10px;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .footer {
                margin-top: 30px;
                padding: 20px;
                text-align: center;
                color: #666;
                font-size: 12px;
            }
            .stats {
                background-color: #FFB800;
                color: black;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>TaxiClass</h1>
                <h2>Informe Quincenal de Solicitudes de Reseñas</h2>
            </div>
            
            <div class="content">
                <p><strong>Período del informe:</strong> 
                   Desde <?php echo date('d/m/Y', strtotime($fecha_desde)); ?> 
                   hasta <?php echo date('d/m/Y', strtotime($fecha_hasta)); ?></p>
                
                <div class="stats">
                    <h3>Resumen del período:</h3>
                    <p>Total de solicitudes enviadas: <strong><?php echo count($results); ?></strong></p>
                </div>
                
                <?php if ($results) : ?>
                    <h3>Detalle de solicitudes enviadas:</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Día Recogida</th>
                                <th>Nombre Cliente</th>
                                <th>Email Cliente</th>
                                <th>Fecha Envío</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row) : ?>
                                <tr>
                                    <td><?php echo esc_html($row->id); ?></td>
                                    <td><?php echo esc_html($row->dia); ?></td>
                                    <td><?php echo esc_html($row->nombre_cliente); ?></td>
                                    <td><?php echo esc_html($row->email_cliente); ?></td>
                                    <td><?php echo esc_html($row->fecha_envio); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p>No se encontraron solicitudes de reseñas en el período especificado.</p>
                <?php endif; ?>
                
                <div class="footer">
                    <p>Este es un informe automático generado por el sistema de automatización de reseñas de TaxiClass.</p>
                    <p>Fecha de generación: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// Ejecutar la función principal
generate_and_send_review_report();
?>