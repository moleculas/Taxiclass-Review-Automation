# TaxiClass Review Automation Plugin

## 📋 Descripción General

Plugin de WordPress desarrollado para **TaxiClass Rent a Car** que automatiza el proceso de solicitud de reseñas en Google a los clientes después de utilizar el servicio de alquiler de vehículos.

## 🎯 Objetivo

Aumentar la cantidad y frecuencia de reseñas en Google Business mediante el envío automatizado de emails personalizados a los clientes, mejorando así la reputación online y el posicionamiento del negocio.

## ✨ Características Principales

- ✅ **Automatización completa**: Envío automático de emails sin intervención manual
- 📧 **Emails personalizados**: Mensajes con el nombre del cliente y detalles del servicio
- 🔄 **Procesamiento diario**: Cron job configurable para ejecución automática
- 📊 **Panel de administración**: Interfaz completa para gestión y monitoreo
- 📈 **Informes quincenales**: Reportes automáticos por email a administradores
- 🔐 **Control de duplicados**: Sistema que evita envíos múltiples al mismo cliente

## 🏗️ Arquitectura del Plugin

### Estructura de Archivos

taxiclass-review-automation/
├── taxiclass-review-automation.php    # Archivo principal del plugin
├── review-report-endpoint.php         # Endpoint para informes quincenales
├── includes/
│   └── class-review-automation.php    # Clase principal con toda la lógica
├── assets/
│   └── css/
│       └── admin-style.css           # Estilos del panel de administración
└── README.md                         # Este archivo

### Base de Datos

El plugin crea una tabla personalizada `wp_taxiclass_review_requests` con la siguiente estructura:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único autoincremental |
| dia | DATE | Fecha de recogida del vehículo |
| nombre_cliente | VARCHAR(255) | Nombre completo del cliente |
| email_cliente | VARCHAR(255) | Email del cliente |
| fecha_envio | DATETIME | Fecha y hora del envío del email |
| estado | VARCHAR(50) | Estado del envío (enviado/error) |
| created_at | TIMESTAMP | Fecha de creación del registro |

## 🔧 Funcionamiento Técnico

### 1. Integración con Gravity Forms

El plugin se integra con **Gravity Forms** para obtener las reservas de vehículos. Utiliza el campo personalizado con ID 23 ("Día recogida") del formulario ID 2 para identificar las reservas del día anterior.

### 2. Proceso de Envío Automatizado

// Ejecutado diariamente por el cron job a las 10:00 AM
1. Obtiene la fecha del día anterior
2. Busca todas las entradas de Gravity Forms (formulario ID 2) donde:
  - Campo ID 23 (Día recogida) = día anterior
  - Estado = activo
3. Para cada entrada:
  - Extrae nombre (campo ID 1) y email (campo ID 2) del cliente
  - Verifica que no se haya enviado previamente
  - Genera email personalizado
  - Envía el email
  - Registra en la base de datos

### 3. Template del Email

El email enviado incluye:
- Saludo personalizado con el nombre del cliente
- Agradecimiento por utilizar el servicio
- Botón destacado con enlace directo a Google Reviews
- Diseño responsive con colores corporativos (negro/amarillo)
- Footer con información de contacto

## 📊 Panel de Administración

### Funcionalidades del Panel

1. **Vista Principal**
  - Tabla con todos los envíos realizados
  - Búsqueda por nombre o email
  - Paginación (20 registros por página)
  - Ordenamiento por fecha descendente

2. **Estadísticas**
  - Total de emails enviados
  - Emails enviados en el último mes
  - Último procesamiento realizado
  - Estado del sistema

3. **Configuración**
  - URL de Google Reviews editable
  - Formulario de test para envío manual
  - Botón para procesar manualmente

### Acceso al Panel

WordPress Admin > TaxiClass Reviews

## 🔄 Configuración del Cron Job

### Cron Job Principal (Envío de Reseñas)

- **Identificador**: `taxiclass-review-automation`
- **Horario**: Diariamente a las 10:00 AM
- **Función**: Procesa entradas de Gravity Forms del día anterior y envía emails

### Cron Job de Informes

- **Identificador**: `taxiclass-review-report-automation`
- **Horario**: Días 1 y 16 de cada mes a las 10:00 AM
- **URL**: Dominio `/wp-content/plugins/taxiclass-review-automation/review-report-endpoint.php`
- **Función**: Genera y envía informe quincenal a administradores

## 📧 Endpoint de Informes Quincenales

### Descripción

Endpoint independiente que genera informes de las solicitudes de reseñas enviadas en los últimos 15 días.

### URL del Endpoint

Dominio /wp-content/plugins/taxiclass-review-automation/review-report-endpoint.php

### Funcionamiento

1. Calcula el período de 15 días anteriores
2. Extrae todos los registros del período de la tabla wp_taxiclass_review_requests
3. Genera tabla HTML formateada con estilo corporativo
4. Envía por email a los administradores configurados
5. Retorna JSON con el resultado de la operación

### Formato del Informe

- Tabla con columnas: ID, Día Recogida, Nombre Cliente, Email, Fecha Envío
- Resumen estadístico del período
- Diseño profesional con colores corporativos
- Optimizado para visualización en clientes de email

## 🛠️ Instalación

1. Subir la carpeta `taxiclass-review-automation` a `/wp-content/plugins/`
2. Activar el plugin desde el panel de WordPress
3. La tabla de base de datos se creará automáticamente
4. Configurar la URL de Google Reviews desde el panel
5. Configurar los cron jobs en el servidor de hosting

## 🔐 Seguridad

- Validación de permisos de administrador para acceso al panel
- Sanitización de todas las entradas de usuario
- Nonces de WordPress para protección CSRF en formularios
- Prepared statements para todas las consultas SQL
- Escape de salidas HTML para prevenir XSS

## 📊 Logs y Monitoreo

El plugin registra automáticamente:
- Cada envío de email exitoso
- Errores en el envío de emails
- Ejecuciones del cron job principal
- Generación de informes quincenales
- Acciones manuales desde el panel

Los logs se pueden revisar en:
- Panel de administración del plugin (sección de logs)
- Archivo error_log del servidor (vía PHP error_log)

## 🚀 Requisitos del Sistema

- WordPress 5.0 o superior
- Gravity Forms activo y configurado
- PHP 7.2 o superior
- MySQL 5.6 o superior
- Servidor con soporte para cron jobs
- Función mail() de PHP habilitada o SMTP configurado

## 🔍 IDs de Campos de Gravity Forms

Para referencia, los campos utilizados del formulario ID 2:
- **Campo 1**: Nombre del cliente
- **Campo 2**: Email del cliente
- **Campo 23**: Día de recogida del vehículo

## 👥 Soporte

Para soporte o consultas sobre el plugin:
- **Desarrollo**: ArtikaWeb
- **Cliente**: TaxiClass Rent a Car
- **Email de soporte**: info@artikaweb.com

## 📄 Licencia

Este plugin es software propietario desarrollado exclusivamente para TaxiClass Rent a Car. Todos los derechos reservados.

---

**Versión**: 1.0.0  
**Última actualización**: Junio 2025  
**Desarrollado por**: ArtikaWeb para TaxiClass Rent a Car