<?php
// Evitar acceso directo
if (!defined('ABSPATH')) {
    exit;
}

// Obtener estadísticas
$stats = TaxiClass_Review_Processor::get_statistics();
$gf_info = TaxiClass_Gravity_Forms_Integration::get_diagnostic_info();

// Obtener registros recientes
$recent_records = TaxiClass_Review_Database::get_all_records(1, 10);
?>

<div class="wrap">
    <h1>TaxiClass Review Automation</h1>
    
    <?php settings_errors('taxiclass_messages'); ?>
    
    <!-- Estado del Sistema -->
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Estado del Sistema</h2>
        <table class="widefat">
            <tr>
                <td><strong>Gravity Forms:</strong></td>
                <td><?php echo $gf_info['gravity_forms_active'] ? '✅ Activo' : '❌ No activo'; ?></td>
            </tr>
            <tr>
                <td><strong>Formulario ID 5:</strong></td>
                <td><?php echo $gf_info['form_exists'] ? '✅ ' . esc_html($gf_info['form_name']) : '❌ No encontrado'; ?></td>
            </tr>
            <tr>
                <td><strong>Total entradas en formulario:</strong></td>
                <td><?php echo number_format($gf_info['total_entries']); ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Estadísticas -->
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Estadísticas</h2>
        <table class="widefat">
            <tr>
                <td><strong>Total procesados:</strong></td>
                <td><?php echo number_format($stats['total']); ?></td>
            </tr>
            <tr>
                <td><strong>Emails enviados:</strong></td>
                <td><?php echo number_format($stats['enviados']); ?></td>
            </tr>
            <tr>
                <td><strong>Errores:</strong></td>
                <td><?php echo number_format($stats['errores']); ?></td>
            </tr>
            <tr>
                <td><strong>Últimos 7 días:</strong></td>
                <td><?php echo number_format($stats['ultimos_7_dias']); ?></td>
            </tr>
        </table>
    </div>
    
    <!-- Acciones -->
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Acciones</h2>
        
        <!-- Procesar fecha específica -->
        <form method="post" style="margin-bottom: 20px;">
            <?php wp_nonce_field('taxiclass_admin_action', 'taxiclass_nonce'); ?>
            <input type="hidden" name="action" value="process_date">
            <table class="form-table">
                <tr>
                    <th>Procesar fecha:</th>
                    <td>
                        <input type="date" name="fecha" required value="<?php echo date('Y-m-d', strtotime('-1 day')); ?>">
                        <button type="submit" class="button button-primary">Procesar</button>
                        <p class="description">Procesará las reservas de la fecha seleccionada</p>
                    </td>
                </tr>
            </table>
        </form>
        
        <!-- Enviar email de prueba -->
        <form method="post">
            <?php wp_nonce_field('taxiclass_admin_action', 'taxiclass_nonce'); ?>
            <input type="hidden" name="action" value="test_email">
            <table class="form-table">
                <tr>
                    <th>Email de prueba:</th>
                    <td>
                        <input type="email" name="test_email" required placeholder="correo@ejemplo.com">
                        <button type="submit" class="button">Enviar prueba</button>
                        <p class="description">Envía un email de prueba para verificar el funcionamiento</p>
                    </td>
                </tr>
            </table>
        </form>
    </div>
    
    <!-- Últimos registros -->
    <div class="card" style="max-width: 800px; margin-top: 20px;">
        <h2>Últimos 10 registros</h2>
        <?php if (!empty($recent_records['records'])): ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Enviado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_records['records'] as $record): ?>
                        <tr>
                            <td><?php echo esc_html($record['dia']); ?></td>
                            <td><?php echo esc_html($record['nombre_cliente']); ?></td>
                            <td><?php echo esc_html($record['email_cliente']); ?></td>
                            <td>
                                <?php if ($record['estado'] == 'enviado'): ?>
                                    <span style="color: green;">✅ Enviado</span>
                                <?php elseif ($record['estado'] == 'error'): ?>
                                    <span style="color: red;">❌ Error</span>
                                <?php else: ?>
                                    <span style="color: orange;">⏳ Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $record['fecha_envio'] ? esc_html($record['fecha_envio']) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay registros aún.</p>
        <?php endif; ?>
    </div>
</div>