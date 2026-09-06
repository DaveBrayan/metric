-- ==============================================================================
-- METRIC V2 — Script SQL para Creación y Carga de Equipos en Producción (MySQL)
-- ==============================================================================

-- 1. Crear tabla `equipment` si no existe
CREATE TABLE IF NOT EXISTS `equipment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `model` VARCHAR(255) NULL DEFAULT NULL,
  `serial_number` VARCHAR(255) NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `image` VARCHAR(255) NULL DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Operativo',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar los 18 equipos del inventario inicial extraídos de las capturas
INSERT INTO `equipment` (`name`, `model`, `serial_number`, `description`, `image`, `status`, `created_at`, `updated_at`) VALUES
('Anemómetro', 'AirflowTes-Master', 'mbjb021078', 'AirflowTes-Master', NULL, 'Operativo', NOW(), NOW()),
('Anemómetro', 'AN100', '211111005', NULL, NULL, 'Operativo', NOW(), NOW()),
('Contador de Partículas Suspendidas', 'LKC-1000 2ND', '23.08.10 qq', NULL, NULL, 'Operativo', NOW(), NOW()),
('Distanciómetro', '60MT', '11', NULL, NULL, 'Operativo', NOW(), NOW()),
('Distanciómetro', 'LS - P', '12', NULL, NULL, 'Operativo', NOW(), NOW()),
('Dosímetro', 'GA113', '5', NULL, NULL, 'Operativo', NOW(), NOW()),
('Luxómetro', 'Lux Test Master', '2', NULL, NULL, 'Operativo', NOW(), NOW()),
('Luxómetro', 'PCE-174', '150206371', NULL, NULL, 'Operativo', NOW(), NOW()),
('Medidor de calidad de aire', 'VSON', 'wp6930s', NULL, NULL, 'Operativo', NOW(), NOW()),
('Medidor de calidad de aire', 'TEMTOP', '1234', NULL, NULL, 'Operativo', NOW(), NOW()),
('Medidor de CO2', 'CO2+CO METER', '10', NULL, NULL, 'Operativo', NOW(), NOW()),
('Medidor Multigases', 'BH - 45', '9', NULL, NULL, 'Operativo', NOW(), NOW()),
('Proyectora', NULL, '13', 'Equipo sin numero de serie', NULL, 'Operativo', NOW(), NOW()),
('Sonómetro', 'SL400', '4', NULL, NULL, 'Operativo', NOW(), NOW()),
('Sonómetro', 'SoundTestMaster', '3', NULL, NULL, 'Operativo', NOW(), NOW()),
('Termohigrómetro', 'TC100', '6', NULL, NULL, 'Operativo', NOW(), NOW()),
('Trípode', 'INSTRUMENT TRIPOD', '14', NULL, NULL, 'Operativo', NOW(), NOW()),
('Vibrometro', 'Inlite', '123', NULL, NULL, 'Operativo', NOW(), NOW());
