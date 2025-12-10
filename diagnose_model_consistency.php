<?php
/**
 * Diagnóstico: Verificar consistencia de nombres de modelos
 */

define('API_ACCESS', true);
define('API_BASE_DIR', __DIR__ . '/api_claude_5');

require_once API_BASE_DIR . '/config.php';
require_once API_BASE_DIR . '/core/Database.php';

$db = Database::getInstance();

echo "=== DIAGNÓSTICO: CONSISTENCIA DE NOMBRES DE MODELOS ===\n\n";

// 1. Ver modelos en api_model_prices
echo "1. MODELOS EN api_model_prices:\n";
echo str_repeat("-", 80) . "\n";

$models_db = $db->query("SELECT model_name, price_input_per_1k, price_output_per_1k, source, is_active
    FROM " . DB_PREFIX . "model_prices
    ORDER BY is_active DESC, model_name");

foreach ($models_db as $m) {
    $status = $m['is_active'] ? '[ACTIVO]' : '[INACTIVO]';
    echo sprintf("%-8s %-40s  \$%.6f / \$%.6f  (%s)\n",
        $status,
        $m['model_name'],
        $m['price_input_per_1k'],
        $m['price_output_per_1k'],
        $m['source']
    );
}

// 2. Ver modelos únicos en api_usage_tracking
echo "\n2. MODELOS ÚNICOS EN api_usage_tracking:\n";
echo str_repeat("-", 80) . "\n";

$models_tracking = $db->query("SELECT DISTINCT model, COUNT(*) as count
    FROM " . DB_PREFIX . "usage_tracking
    WHERE model IS NOT NULL AND model != ''
    GROUP BY model
    ORDER BY count DESC");

foreach ($models_tracking as $m) {
    echo sprintf("%-40s  (%d registros)\n", $m['model'], $m['count']);
}

// 3. Ver configuraciones de endpoints
echo "\n3. CONFIGURACIÓN DE MODELOS POR ENDPOINT:\n";
echo str_repeat("-", 80) . "\n";

$configs = $db->query("SELECT setting_key, setting_value
    FROM " . DB_PREFIX . "settings
    WHERE setting_key LIKE '%_model' OR setting_key LIKE 'model_%'
    ORDER BY setting_key");

if (empty($configs)) {
    echo "No hay configuraciones de modelo en api_settings\n";
} else {
    foreach ($configs as $c) {
        echo sprintf("%-40s = %s\n", $c['setting_key'], $c['setting_value']);
    }
}

// 4. Verificar inconsistencias
echo "\n4. VERIFICACIÓN DE INCONSISTENCIAS:\n";
echo str_repeat("-", 80) . "\n";

// Modelos en tracking que NO existen en model_prices
$models_tracking_names = array_column($models_tracking, 'model');
$models_db_names = array_column($models_db, 'model_name');

$inconsistent = [];
foreach ($models_tracking_names as $track_model) {
    // Buscar coincidencia exacta
    if (!in_array($track_model, $models_db_names)) {
        $inconsistent[] = $track_model;
    }
}

if (empty($inconsistent)) {
    echo "✅ Todos los modelos en tracking existen en model_prices\n";
} else {
    echo "⚠️  MODELOS EN TRACKING QUE NO ESTÁN EN MODEL_PRICES:\n";
    foreach ($inconsistent as $model) {
        echo "   - $model\n";
    }
}

// 5. Verificar nombres de modelos que NO existen en OpenAI
echo "\n5. MODELOS INEXISTENTES EN OPENAI:\n";
echo str_repeat("-", 80) . "\n";

$invalid_models = [];
$valid_patterns = [
    '/^gpt-4o(-mini)?(-\d{4}-\d{2}-\d{2})?$/',
    '/^gpt-4(-turbo)?(-\d{4}-\d{2}-\d{2})?$/',
    '/^gpt-4(-0613|-0314)?$/',
    '/^gpt-3\.5-turbo(-\d{4})?$/',
    '/^o1(-mini|-preview)?$/',
    '/^chatgpt-4o-latest$/',
    '/^claude-3(-5)?-(opus|sonnet|haiku)(-\d{8})?$/',
];

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
    echo "✅ Todos los modelos en tracking son válidos\n";
} else {
    echo "❌ MODELOS INVÁLIDOS (NO EXISTEN EN OPENAI/ANTHROPIC):\n";
    foreach ($invalid_models as $model) {
        $count = 0;
        foreach ($models_tracking as $m) {
            if ($m['model'] === $model) {
                $count = $m['count'];
                break;
            }
        }
        echo "   - $model ($count registros)\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "DIAGNÓSTICO COMPLETADO\n";
