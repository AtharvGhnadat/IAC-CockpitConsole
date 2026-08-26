<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Phase 6 production_events and extend cockpit_state';
    }

    public function up(Schema $schema): void
    {
        // cockpit_state
        $this->addSql('ALTER TABLE cockpit_state ADD total_produced BIGINT DEFAULT 0 NOT NULL AFTER total_requested');

        // production_events
        $this->addSql('CREATE TABLE production_events (id BIGINT AUTO_INCREMENT NOT NULL, device_event_id BIGINT NOT NULL, cockpit_id INT NOT NULL, production_uuid VARCHAR(36) NOT NULL, scanner_model VARCHAR(255) DEFAULT NULL, quantity INT NOT NULL, device_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', received_at DATETIME(6) DEFAULT CURRENT_TIMESTAMP(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_11100000PRODUCTION (production_uuid), UNIQUE INDEX UNIQ_11100000DEVICEEVENT (device_event_id), INDEX IDX_11100000COCKPIT (cockpit_id), INDEX idx_device_timestamp (device_timestamp), INDEX idx_received_at (received_at), INDEX idx_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE production_events ADD CONSTRAINT FK_11100000DEVICEEVENT FOREIGN KEY (device_event_id) REFERENCES device_events (id)');
        $this->addSql('ALTER TABLE production_events ADD CONSTRAINT FK_11100000COCKPIT FOREIGN KEY (cockpit_id) REFERENCES cockpits (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cockpit_state DROP total_produced');
        $this->addSql('ALTER TABLE production_events DROP FOREIGN KEY FK_11100000DEVICEEVENT');
        $this->addSql('ALTER TABLE production_events DROP FOREIGN KEY FK_11100000COCKPIT');
        $this->addSql('DROP TABLE production_events');
    }
}
