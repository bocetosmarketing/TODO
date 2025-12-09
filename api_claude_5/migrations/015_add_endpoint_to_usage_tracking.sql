-- Migración 015: Agregar columna endpoint a usage_tracking
-- Permite identificar qué endpoint específico realizó cada operación

ALTER TABLE `api_usage_tracking`
ADD COLUMN `endpoint` VARCHAR(100) NULL COMMENT 'Endpoint usado (generate-content, generate-title, etc)' AFTER `operation_type`,
ADD INDEX `idx_endpoint` (`endpoint`);
