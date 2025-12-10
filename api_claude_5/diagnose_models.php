<?php
/**
 * Diagnóstico WEB: Verificar consistencia de nombres de modelos
 */

define('API_ACCESS', true);
define('API_BASE_DIR', __DIR__);

require_once API_BASE_DIR . '/config.php';
require_once API_BASE_DIR . '/core/Database.php';

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Modelos</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 { color: #4ec9b0; }
        h2 { color: #569cd6; margin-top: 30px; border-bottom: 2px solid #569cd6; padding-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; background: #252526; }
        th { background: #2d2d30; color: #4ec9b0; padding: 10px; text-align: left; border-bottom: 2px solid #3e3e42; }
        td { padding: 8px; border-bottom: 1px solid #3e3e42; }
        .active { color: #4ec9b0; font-weight: bold; }
        .inactive { color: #6c757d; }
        .warning { background: #3e2723; border-left: 4px solid #ff5722; padding: 15px; margin: 20px 0; }
        .success { background: #1b5e20; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; }
        .invalid { color: #f44336; font-weight: bold; }
        .valid { color: #4caf50; }
        pre { background: #252526; padding: 15px; overflow-x: auto; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Diagnóstico: Consistencia de Nombres de Modelos</h1>

<?php

try {
    $db = Database::getInstance();

    // 1. MODELOS EN api_model_prices
    echo '<h2>1. Modelos en api_model_prices</h2>';

    $models_db = $db->query("SELECT model_name, price_input_per_1k, price_output_per_1k, source, is_active
        FROM " . DB_PREFIX . "model_prices
        ORDER BY is_active DESC, model_name");

    echo '<table>';
    echo '<tr><th>Estado</th><th>Nombre del Modelo</th><th>Precio Input ($/1K)</th><th>Precio Output ($/1K)</th><th>Fuente</th></tr>';

    foreach ($models_db as $m) {
        $class = $m['is_active'] ? 'active' : 'inactive';
        $status = $m['is_active'] ? 'ACTIVO' : 'INACTIVO';
        echo '<tr>';
        echo '<td class="' . $class . '">' . $status . '</td>';
        echo '<td>' . htmlspecialchars($m['model_name']) . '</td>';
        echo '<td>$' . number_format($m['price_input_per_1k'], 6) . '</td>';
        echo '<td>$' . number_format($m['price_output_per_1k'], 6) . '</td>';
        echo '<td>' . htmlspecialchars($m['source']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // 2. MODELOS EN api_usage_tracking
    echo '<h2>2. Modelos únicos en api_usage_tracking</h2>';

    $models_tracking = $db->query("SELECT DISTINCT model, COUNT(*) as count
        FROM " . DB_PREFIX . "usage_tracking
        WHERE model IS NOT NULL AND model != ''
        GROUP BY model
        ORDER BY count DESC");

    echo '<table>';
    echo '<tr><th>Nombre del Modelo</th><th>Cantidad de Registros</th><th>¿Existe en model_prices?</th></tr>';

    $models_db_names = array_column($models_db, 'model_name');

    foreach ($models_tracking as $m) {
        $exists = in_array($m['model'], $models_db_names);
        $class = $exists ? 'valid' : 'invalid';
        $text = $exists ? '✓ SÍ' : '✗ NO';

        echo '<tr>';
        echo '<td>' . htmlspecialchars($m['model']) . '</td>';
        echo '<td>' . number_format($m['count']) . '</td>';
        echo '<td class="' . $class . '">' . $text . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // 3. CONFIGURACIÓN DE ENDPOINTS
    echo '<h2>3. Configuración de modelos por endpoint</h2>';

    $configs = $db->query("SELECT setting_key, setting_value
        FROM " . DB_PREFIX . "settings
        WHERE setting_key LIKE '%_model' OR setting_key LIKE 'model_%'
        ORDER BY setting_key");

    if (empty($configs)) {
        echo '<div class="warning">⚠️ No hay configuraciones de modelo en api_settings</div>';
    } else {
        echo '<table>';
        echo '<tr><th>Clave de Configuración</th><th>Modelo Configurado</th><th>¿Existe en model_prices?</th></tr>';

        foreach ($configs as $c) {
            $exists = in_array($c['setting_value'], $models_db_names);
            $class = $exists ? 'valid' : 'invalid';
            $text = $exists ? '✓ SÍ' : '✗ NO';

            echo '<tr>';
            echo '<td>' . htmlspecialchars($c['setting_key']) . '</td>';
            echo '<td>' . htmlspecialchars($c['setting_value']) . '</td>';
            echo '<td class="' . $class . '">' . $text . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    // 4. VERIFICAR MODELOS INVÁLIDOS
    echo '<h2>4. Modelos inexistentes en OpenAI/Anthropic</h2>';

    $valid_patterns = [
        '/^gpt-4o(-mini)?(-\d{4}-\d{2}-\d{2})?$/',
        '/^gpt-4(-turbo)?(-\d{4}-\d{2}-\d{2})?$/',
        '/^gpt-4(-0613|-0314)?$/',
        '/^gpt-3\.5-turbo(-\d{4})?$/',
        '/^o1(-mini|-preview)?$/',
        '/^chatgpt-4o-latest$/',
        '/^claude-3(-5)?-(opus|sonnet|haiku)(-\d{8})?$/',
    ];

    $invalid_models = [];
    $models_tracking_names = array_column($models_tracking, 'model');

    foreach ($models_tracking_names as $model) {
        $is_valid = false;
        foreach ($valid_patterns as $pattern) {
            if (preg_match($pattern, $model)) {
                $is_valid = true;
                break;
            }
        }
        if (!$is_valid) {
            $invalid_models[] = $model;
        }
    }

    if (empty($invalid_models)) {
        echo '<div class="success">✅ Todos los modelos en tracking son válidos</div>';
    } else {
        echo '<div class="warning">';
        echo '<strong>❌ MODELOS INVÁLIDOS (NO EXISTEN EN OPENAI/ANTHROPIC):</strong><br><br>';
        echo '<table>';
        echo '<tr><th>Modelo Inválido</th><th>Registros</th><th>Modelo Correcto Probable</th></tr>';

        foreach ($invalid_models as $model) {
            $count = 0;
            foreach ($models_tracking as $m) {
                if ($m['model'] === $model) {
                    $count = $m['count'];
                    break;
                }
            }

            // Sugerir modelo correcto
            $suggestion = 'Desconocido';
            if (strpos($model, 'gpt-4.1-mini') !== false) {
                $suggestion = 'gpt-4o-mini-2024-07-18';
            } elseif (strpos($model, 'gpt-4.1') !== false) {
                $suggestion = 'gpt-4o-2024-11-20 o gpt-4-turbo-2024-04-09';
            }

            echo '<tr>';
            echo '<td class="invalid">' . htmlspecialchars($model) . '</td>';
            echo '<td>' . number_format($count) . '</td>';
            echo '<td>' . htmlspecialchars($suggestion) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
    }

    // 5. INCONSISTENCIAS
    echo '<h2>5. Resumen de Inconsistencias</h2>';

    $issues = [];

    // Modelos en tracking que no están en model_prices
    $missing_in_prices = [];
    foreach ($models_tracking_names as $track_model) {
        if (!in_array($track_model, $models_db_names)) {
            $missing_in_prices[] = $track_model;
        }
    }

    if (!empty($missing_in_prices)) {
        $issues[] = count($missing_in_prices) . ' modelos en tracking NO están en model_prices';
    }

    if (!empty($invalid_models)) {
        $issues[] = count($invalid_models) . ' modelos NO existen en OpenAI/Anthropic';
    }

    if (empty($issues)) {
        echo '<div class="success">';
        echo '<strong>✅ NO SE ENCONTRARON INCONSISTENCIAS</strong><br>';
        echo 'Todos los nombres de modelos son consistentes entre las tablas.';
        echo '</div>';
    } else {
        echo '<div class="warning">';
        echo '<strong>⚠️ INCONSISTENCIAS ENCONTRADAS:</strong><br><ul>';
        foreach ($issues as $issue) {
            echo '<li>' . $issue . '</li>';
        }
        echo '</ul></div>';
    }

    // 6. RECOMENDACIONES
    echo '<h2>6. Recomendaciones</h2>';
    echo '<div class="warning">';
    echo '<strong>Acciones recomendadas:</strong><br><br>';

    if (!empty($invalid_models)) {
        echo '<strong>1. Corregir nombres de modelos inválidos:</strong><br>';
        echo 'Los modelos como "gpt-4.1" o "gpt-4.1-mini" NO EXISTEN en OpenAI.<br>';
        echo 'Necesitas encontrar dónde se están configurando estos nombres incorrectos.<br><br>';

        echo '<strong>2. Verificar módulo de configuración:</strong><br>';
        echo 'Revisar que los selectores de modelo solo muestren modelos de la tabla model_prices.<br><br>';

        echo '<strong>3. Actualizar tabla model_prices:</strong><br>';
        echo 'Ejecutar sincronización desde OpenAI en el admin: /api_claude_5/admin/?module=models<br><br>';
    }

    if (!empty($missing_in_prices)) {
        echo '<strong>4. Agregar modelos faltantes:</strong><br>';
        echo 'Algunos modelos en tracking no están en model_prices. Agregarlos manualmente o por sync.<br><br>';
    }

    echo '</div>';

} catch (Exception $e) {
    echo '<div class="warning">';
    echo '<strong>❌ ERROR:</strong><br>';
    echo htmlspecialchars($e->getMessage());
    echo '</div>';
}

?>

</div>
</body>
</html>
