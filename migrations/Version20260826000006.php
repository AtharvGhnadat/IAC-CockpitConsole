<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create device_health table for Phase 9';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE device_health (id INT AUTO_INCREMENT NOT NULL, device_id INT NOT NULL, last_seen_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_valid_event_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_error_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_error_code VARCHAR(255) DEFAULT NULL, consecutive_failures INT DEFAULT 0 NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_DEVICE (device_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE device_health ADD CONSTRAINT FK_DEVICE_HEALTH FOREIGN KEY (device_id) REFERENCES devices (id)');
        
        // Initialize health records for all existing devices
        $this->addSql('INSERT INTO device_health (device_id, consecutive_failures) SELECT id, 0 FROM devices');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE device_health DROP FOREIGN KEY FK_DEVICE_HEALTH');
        $this->addSql('DROP TABLE device_health');
    }
}
