-- ====================================
-- ESQUEMA DE BASE DE DATOS
-- ====================================

SET FOREIGN_KEY_CHECKS=0;

-- -----------------------------------------------------------------------------------------
-- SECCIÓN 1: GESTIÓN DE USUARIOS Y EQUIPOS
-- -----------------------------------------------------------------------------------------

DROP TABLE IF EXISTS `users`, `teams`, `team_members`, `api_keys`;

-- Tabla para todos los usuarios del sistema.
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `full_name` VARCHAR(100),
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  `last_login_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para agrupar usuarios en equipos u organizaciones.
CREATE TABLE `teams` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `owner_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla pivote para la relación muchos-a-muchos entre usuarios y equipos.
CREATE TABLE `team_members` (
  `team_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `role` ENUM('admin', 'member') NOT NULL DEFAULT 'member',
  PRIMARY KEY (`team_id`, `user_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para gestionar claves de API para acceso programático.
CREATE TABLE `api_keys` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `key_hash` VARCHAR(255) NOT NULL UNIQUE,
  `label` VARCHAR(100),
  `permissions` JSON,
  `expires_at` TIMESTAMP NULL,
  `last_used_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------------------
-- SECCIÓN 2: GESTIÓN DE PROYECTOS Y FORMULARIOS (CON VERSIONADO)
-- -----------------------------------------------------------------------------------------

DROP TABLE IF EXISTS `projects`, `forms`, `form_versions`, `form_assets`, `project_permissions`;

-- Tabla para los proyectos, que son contenedores de formularios.
CREATE TABLE `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `owner_id` INT NOT NULL,
  `team_id` INT NULL,
  `sector` VARCHAR(100),
  `country` VARCHAR(100) NULL,
  `tags` JSON,
  `status` ENUM('draft', 'active', 'archived') NOT NULL DEFAULT 'draft',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`team_id`) REFERENCES `teams`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla que representa la identidad de un formulario, persistente a través de versiones.
CREATE TABLE `forms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `project_id` INT NOT NULL,
  `uuid` VARCHAR(36) NOT NULL UNIQUE,
  `title` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla CRÍTICA para el versionado de formularios.
CREATE TABLE `form_versions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `version_number` INT NOT NULL DEFAULT 1,
  `form_definition` JSON NOT NULL,
  `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
  `published_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `form_version_unique` (`form_id`, `version_number`),
  FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Archivos multimedia utilizados DENTRO del formulario.
CREATE TABLE `form_assets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_version_id` INT NOT NULL,
  `filename` VARCHAR(255) NOT NULL,
  `stored_path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`form_version_id`) REFERENCES `form_versions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Permisos de acceso a proyectos para otros usuarios.
CREATE TABLE `project_permissions` (
  `project_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `permission_level` ENUM('viewer', 'editor', 'manager') NOT NULL,
  PRIMARY KEY (`project_id`, `user_id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------------------
-- SECCIÓN 3: GESTIÓN DE DATOS Y ENVÍOS
-- -----------------------------------------------------------------------------------------

DROP TABLE IF EXISTS `submissions`, `media_attachments`, `webhooks`;

-- Tabla para cada envío de datos.
CREATE TABLE `submissions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(36) NOT NULL UNIQUE,
  `form_version_id` INT NOT NULL,
  `submitter_id` INT DEFAULT NULL,
  `submission_data` JSON NOT NULL,
  `validation_status` ENUM('approved', 'pending_review', 'rejected') NOT NULL DEFAULT 'pending_review',
  `notes` TEXT,
  `latitude` DECIMAL(10, 7) DEFAULT NULL,
  `longitude` DECIMAL(10, 7) DEFAULT NULL,
  `start_time` DATETIME DEFAULT NULL,
  `end_time` DATETIME DEFAULT NULL,
  `device_id` VARCHAR(100) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`form_version_id`) REFERENCES `form_versions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`submitter_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Archivos adjuntos de los envíos.
CREATE TABLE `media_attachments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `submission_id` INT NOT NULL,
  `question_name` VARCHAR(100) NOT NULL,
  `original_filename` VARCHAR(255) NOT NULL,
  `stored_path` VARCHAR(255) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size` BIGINT NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`submission_id`) REFERENCES `submissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para notificar a sistemas externos.
CREATE TABLE `webhooks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `target_url` VARCHAR(512) NOT NULL,
  `event_type` VARCHAR(50) NOT NULL DEFAULT 'submission.created',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `last_triggered_at` TIMESTAMP NULL,
  FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `audit_log`;

-- Tabla para registrar todas las acciones importantes.
CREATE TABLE `audit_log` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `action` VARCHAR(100) NOT NULL,
  `target_type` VARCHAR(50),
  `target_id` INT,
  `details` JSON,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `users` (`username`, `password_hash`, `email`, `full_name`, `role`) VALUES
('admin', '$2y$10$2ggKAYyNm0GsmybkJYUaq.OYhaoLjWbTovk6hAvzW7g6tuXQ.5Qie', 'admin@bsf.com', 'Administrador Principal', 'admin');