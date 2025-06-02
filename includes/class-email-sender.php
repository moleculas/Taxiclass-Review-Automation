<?php
/**
 * Clase para el envío de emails
 */

// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

class TaxiClass_Email_Sender {
    
    /**
     * URL de Google Business para dejar reseñas
     * IMPORTANTE: Debes reemplazar esto con tu URL real de Google Business
     */
    private static $google_business_url = 'https://g.page/r/Cb8HekAm1rBiEBM/review';
    
    /**
     * Enviar email de solicitud de reseña
     * 
     * @param array $customer_data Array con datos del cliente (nombre_cliente, email_cliente)
     * @return bool True si se envió correctamente, false en caso contrario
     */
    public static function send_review_request($customer_data) {
        // Preparar datos
        $to = $customer_data['email_cliente'];
        $nombre = $customer_data['nombre_cliente'];
        $subject = 'Gracias por confiar en TaxiClass - Tu opinión nos importa';
        
        // Obtener el contenido HTML del email
        $message = self::get_email_template($nombre);
        
        // Configurar headers para HTML
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: TaxiClass <noreply@taxiclassrent.com>'
        );
        
        // Enviar email
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log del resultado
        if ($sent) {
            error_log('TaxiClass Review: Email enviado correctamente a ' . $to);
        } else {
            error_log('TaxiClass Review: Error al enviar email a ' . $to);
        }
        
        return $sent;
    }
    
    /**
     * Obtener la plantilla HTML del email
     * 
     * @param string $nombre Nombre del cliente
     * @return string HTML del email
     */
    private static function get_email_template($nombre) {
        $google_url = self::get_google_business_url();
        
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gracias por confiar en TaxiClass</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 0;
        }
        .header {
            background-color: #000000;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: normal;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
            line-height: 1.6;
        }
        .content h2 {
            color: #000000;
            font-size: 22px;
            margin-bottom: 20px;
        }
        .content p {
            margin-bottom: 15px;
            font-size: 16px;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .review-button {
            display: inline-block;
            background-color: #FFC107;
            color: #000000;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .review-button:hover {
            background-color: #FFB300;
        }
        .footer {
            background-color: #f8f8f8;
            padding: 20px 30px;
            text-align: center;
            font-size: 14px;
            color: #666666;
            border-top: 1px solid #e0e0e0;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 30px 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .content h2 {
                font-size: 20px;
            }
            .review-button {
                padding: 12px 30px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>TaxiClass</h1>
        </div>
        
        <div class="content">
            <h2>Hola ' . esc_html($nombre) . ',</h2>
            
            <p>¡Gracias por haber confiado en nuestro servicio! Esperamos que tu experiencia haya sido agradable y que hayamos cumplido tus expectativas.</p>
            
            <p>Tu opinión es muy importante para nosotros, ya que nos ayuda a mejorar y a seguir ofreciendo un servicio de calidad. ¿Podrías dedicarnos un minuto para valorar tu experiencia?</p>
            
            <div class="button-container">
                <a href="' . esc_url($google_url) . '" class="review-button" target="_blank">Dejar una reseña</a>
            </div>
            
            <p>Tu feedback nos ayuda a crecer y a ofrecer un mejor servicio a todos nuestros clientes.</p>
            
            <p>¡Muchas gracias por tu tiempo!</p>
            
            <p><strong>El equipo de TaxiClass</strong></p>
        </div>
        
        <div class="footer">
            <p>Este es un email automático. Por favor, no respondas a este mensaje.</p>
            <p>TaxiClass Rent | <a href="https://taxiclassrent.com" style="color: #666666;">taxiclassrent.com</a></p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    /**
     * Obtener la URL de Google Business
     * Permite sobrescribir la URL mediante filtro
     */
    public static function get_google_business_url() {
        $url = apply_filters('taxiclass_review_google_business_url', self::$google_business_url);
        return $url;
    }
    
    /**
     * Configurar la URL de Google Business
     * 
     * @param string $url Nueva URL
     */
    public static function set_google_business_url($url) {
        self::$google_business_url = $url;
    }
    
    /**
     * Enviar email de prueba
     * 
     * @param string $email Email de destino
     * @return bool
     */
    public static function send_test_email($email) {
        $test_data = array(
            'nombre_cliente' => 'Cliente de Prueba',
            'email_cliente' => $email
        );
        
        return self::send_review_request($test_data);
    }
}