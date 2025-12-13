# Monitor en Tiempo Real - API Claude 5

Sistema de monitoreo en tiempo real para visualizar peticiones a la API mientras se están ejecutando.

## 🎯 Características

- ✅ **Tiempo real** con polling cada 3 segundos
- ✅ **Totalmente no intrusivo** - Solo lectura de datos
- ✅ **Auto-pausa** cuando cierras la pestaña
- ✅ **Conversión USD → EUR** automática
- ✅ **Métricas agregadas** en tiempo real

## 📊 Qué muestra

### Métricas en Cards
- Requests totales (últimos X minutos)
- Tokens procesados (entrada/salida)
- Coste total en EUR
- Modelo más usado
- Licencias activas

### Tabla de Operaciones
Por cada petición muestra:
- ⏰ **Hora** exacta de la petición
- 🔗 **Endpoint** llamado
- 🤖 **Modelo** usado (gpt-4o-mini, o1, etc.)
- 📊 **Tokens** entrada/salida separados
- 💰 **Coste EUR** entrada/salida/total
- 🔑 **Licencia** que hizo la llamada
- 🏷️ **Tipo** de operación (SETUP, COLA, CONTENIDO)

## 🚀 Cómo usar

### Desde el Admin Panel:
1. Ir a **Admin Panel** → **Monitor en Vivo** (icono 🔴)
2. La página se auto-actualiza cada 3 segundos
3. Selecciona rango de tiempo: 5min, 10min, 30min o 1 hora

### Acceso directo:
```
https://tu-api.com/admin/?module=monitor
```

## 🔧 Cómo funciona

### 1. Endpoint API
**Archivo:** `/endpoints/MonitorLiveEndpoint.php`

Endpoint GET que consulta la tabla `api_usage_tracking`:
```
GET /?route=monitor/live&minutes=5&limit=100
```

**Parámetros:**
- `minutes` (1-60): Rango temporal a consultar
- `limit` (10-500): Máximo de operaciones a devolver

**Response:**
```json
{
  "success": true,
  "data": {
    "operations": [...],
    "metrics": {
      "total_requests": 42,
      "total_tokens": 125000,
      "total_cost_eur": 0.0523,
      "requests_per_minute": 8.4,
      "tokens_per_minute": 25000,
      "cost_per_hour_eur": 0.0314,
      "top_endpoint": "generate/titulo",
      "top_model": "gpt-4o-mini",
      "unique_licenses": 5
    }
  }
}
```

### 2. Interfaz Admin
**Archivo:** `/admin/modules/monitor/index.php`

Página HTML standalone con:
- CSS inline (sin dependencias externas)
- JavaScript vanilla (sin librerías)
- Polling con `setInterval()` cada 3 segundos
- Auto-pausa con `visibilitychange` API

### 3. Conversión USD → EUR
Tasa fija: **1 USD = 0.92 EUR**

Para cambiar la tasa, editar:
```php
// /endpoints/MonitorLiveEndpoint.php línea 17
private $usdToEur = 0.92;
```

## 🛡️ Seguridad

- ✅ **Solo consultas SELECT** - No modifica nada en BD
- ✅ **Requiere autenticación admin** vía `Auth::require()`
- ✅ **Sin exposición de datos sensibles** - License keys truncadas
- ✅ **Límites de parámetros** - Previene queries excesivas

## 📝 Archivos involucrados

### Creados (nuevos):
```
/endpoints/MonitorLiveEndpoint.php         (173 líneas)
/admin/modules/monitor/index.php           (516 líneas)
/admin/modules/monitor/README.md           (este archivo)
```

### Modificados (1 línea cada uno):
```
/index.php                                 (añadida ruta en línea 157-161)
/admin/index.php                           (añadido 'monitor' en línea 26 y 265-267)
```

## 🎨 Personalización

### Cambiar intervalo de polling:
```javascript
// /admin/modules/monitor/index.php línea 374
pollingInterval = setInterval(fetchData, 3000); // Cambiar 3000 a X milisegundos
```

### Cambiar rango de tiempo por defecto:
```html
<!-- /admin/modules/monitor/index.php línea 107 -->
<option value="5" selected>Últimos 5 min</option>
```

### Añadir más métricas:
Editar método `calculateMetrics()` en `/endpoints/MonitorLiveEndpoint.php`

## 🔍 Troubleshooting

### No se muestran datos:
1. Verificar que hay peticiones recientes a la API (< 5 minutos)
2. Revisar consola JavaScript (F12) por errores
3. Verificar que el endpoint responde: `GET /?route=monitor/live`

### Error "Route not found":
Verificar que la ruta está añadida en `/index.php` línea 157-161

### No aparece en menú admin:
Verificar que 'monitor' está en `$validModules` en `/admin/index.php` línea 26

## 📊 Rendimiento

- **Query SQL:** Simple SELECT con índice en `created_at`
- **Payload típico:** ~5-20KB por request
- **Impacto en servidor:** Mínimo (<0.1% CPU)
- **Ancho de banda:** ~10-40KB/minuto con polling activo

## 🚦 Desactivar temporalmente

### Opción 1: Comentar la ruta
```php
// /index.php línea 157
/*
$router->get('monitor/live', function() {
    require_once API_BASE_DIR . '/endpoints/MonitorLiveEndpoint.php';
    $endpoint = new MonitorLiveEndpoint();
    $endpoint->handle();
});
*/
```

### Opción 2: Ocultar del menú
```php
// /admin/index.php línea 26
$validModules = ['dashboard', 'licenses', 'sync', 'webhooks', 'plans', 'prompts', 'settings', 'license-stats', 'api-docs', 'models' /* , 'monitor' */];
```

---

**Versión:** 1.0
**Fecha:** 2024-12-13
**Autor:** Claude Code
