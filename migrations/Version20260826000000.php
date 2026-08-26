<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Phase 2: CockpitConsole Database Foundation
 */
final class Version20260826000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initialize durable event journal, device master, cockpit master, and audit schemas.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE devices (id INT AUTO_INCREMENT NOT NULL, device_code VARCHAR(255) NOT NULL, device_name VARCHAR(255) DEFAULT NULL, device_type VARCHAR(100) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, is_active TINYINT(1) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_DEVICES_CODE (device_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cockpits (id INT AUTO_INCREMENT NOT NULL, cockpit_code VARCHAR(255) NOT NULL, cockpit_name VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_COCKPITS_CODE (cockpit_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cockpit_model_mappings (id INT AUTO_INCREMENT NOT NULL, cockpit_id INT NOT NULL, scanner_model VARCHAR(255) NOT NULL, mapping_type VARCHAR(50) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CMM_COCKPIT (cockpit_id), INDEX idx_scanner_model (scanner_model), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE device_events (id BIGINT AUTO_INCREMENT NOT NULL, device_id INT DEFAULT NULL, event_uuid VARCHAR(36) NOT NULL, source_type VARCHAR(50) NOT NULL, source_ip VARCHAR(45) DEFAULT NULL, device_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', received_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', raw_payload LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', payload_hash VARCHAR(64) NOT NULL, processing_status VARCHAR(50) NOT NULL, processing_attempts INT NOT NULL, processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_error LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_DEVICE_EVENTS_UUID (event_uuid), INDEX IDX_DEVICE_EVENTS_DEVICE (device_id), INDEX idx_received_at (received_at), INDEX idx_processing_status (processing_status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE processing_failures (id INT AUTO_INCREMENT NOT NULL, device_event_id BIGINT NOT NULL, failure_type VARCHAR(100) NOT NULL, message LONGTEXT NOT NULL, attempt_number INT NOT NULL, exception_class VARCHAR(255) DEFAULT NULL, context LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', resolved_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', resolution_note LONGTEXT DEFAULT NULL, INDEX IDX_PF_DEVICE_EVENT (device_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audit_events (id INT AUTO_INCREMENT NOT NULL, event_type VARCHAR(100) NOT NULL, actor_type VARCHAR(100) NOT NULL, actor_identifier VARCHAR(255) DEFAULT NULL, entity_type VARCHAR(100) DEFAULT NULL, entity_identifier VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, context LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_event_type (event_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        $this->addSql('ALTER TABLE cockpit_model_mappings ADD CONSTRAINT FK_CMM_COCKPIT FOREIGN KEY (cockpit_id) REFERENCES cockpits (id)');
        $this->addSql('ALTER TABLE device_events ADD CONSTRAINT FK_DEVICE_EVENTS_DEVICE FOREIGN KEY (device_id) REFERENCES devices (id)');
        $this->addSql('ALTER TABLE processing_failures ADD CONSTRAINT FK_PF_DEVICE_EVENT FOREIGN KEY (device_event_id) REFERENCES device_events (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cockpit_model_mappings DROP FOREIGN KEY FK_CMM_COCKPIT');
        $this->addSql('ALTER TABLE device_events DROP FOREIGN KEY FK_DEVICE_EVENTS_DEVICE');
        $this->addSql('ALTER TABLE processing_failures DROP FOREIGN KEY FK_PF_DEVICE_EVENT');
        
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP TABLE cockpits');
        $this->addSql('DROP TABLE cockpit_model_mappings');
        $this->addSql('DROP TABLE device_events');
        $this->addSql('DROP TABLE processing_failures');
        $this->addSql('DROP TABLE audit_events');
    }
}
