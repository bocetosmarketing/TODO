<?php
/**
 * Endpoint: Decide Estilo Visual
 * Genera descripciones contextualizadas de estilos visuales para un negocio
 */

defined('API_ACCESS') or die('Direct access not permitted');

require_once API_BASE_DIR . '/core/BaseEndpoint.php';

class DecideEstiloEndpoint extends BaseEndpoint {

    public function handle() {
        $this->validateLicense();

        $niche = $this->params['niche'] ?? '';
        $companyDesc = $this->params['company_description'] ?? null;

        if (!$companyDesc) {
            Response::error('company_description es requerido', 400);
        }

        // Cargar template
        $promptTemplate = $this->loadPrompt('analiza_estilo');
        if (!$promptTemplate) {
            Response::error('Error cargando template', 500);
        }

        // Reemplazar variables
        $prompt = $this->replaceVariables($promptTemplate, [
            'niche' => $niche,
            'company_description' => $companyDesc
        ]);

        // Generar análisis de estilos
        $result = $this->openai->generateContent([
            'prompt' => $prompt,
            'max_tokens' => 600,
            'temperature' => 0.7
        ]);

        if (!$result['success']) {
            Response::error($result['error'], 500);
        }

        // Extraer JSON de la respuesta
        $content = trim($result['content']);

        // Limpiar markdown code blocks si existen
        $content = preg_replace('/^```json\s*/m', '', $content);
        $content = preg_replace('/^```\s*/m', '', $content);
        $content = preg_replace('/\s*```$/m', '', $content);

        // Intentar extraer solo el JSON si hay texto adicional
        // Buscar desde la primera { hasta la última }
        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        $content = trim($content);

        // Parsear JSON
        $styles = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log del contenido para debugging
            error_log('[DecideEstilo] Content recibido: ' . substr($content, 0, 500));
            error_log('[DecideEstilo] JSON error: ' . json_last_error_msg());

            Response::error('Error parseando JSON de la respuesta: ' . json_last_error_msg() . '. Content: ' . substr($content, 0, 200), 500);
        }

        // Validar que contenga los 8 estilos esperados
        $expectedStyles = ['lifestyle', 'technical', 'luxury', 'natural', 'documentary', 'minimalist', 'editorial', 'corporate'];
        foreach ($expectedStyles as $style) {
            if (!isset($styles[$style])) {
                Response::error("Falta el estilo '$style' en la respuesta", 500);
            }
        }

        $this->trackUsage('decide_estilo', $result);

        Response::success(['styles' => $styles]);
    }
}
