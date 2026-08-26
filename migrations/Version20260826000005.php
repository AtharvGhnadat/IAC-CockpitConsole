<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Phase 8 dispatch_events and update cockpit_state for available stock';
    }

    public function up(Schema $schema): void
    {
        // Create dispatch_events table
        $this->addSql('CREATE TABLE dispatch_events (id BIGINT AUTO_INCREMENT NOT NULL, device_event_id BIGINT NOT NULL, cockpit_id INT NOT NULL, dispatch_uuid VARCHAR(36) NOT NULL, scanner_model VARCHAR(255) NOT NULL, quantity INT NOT NULL, device_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', received_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_11100002DISPATCH (dispatch_uuid), UNIQUE INDEX UNIQ_11100002DEVICE (device_event_id), INDEX IDX_11100002COCKPIT (cockpit_id), INDEX idx_dispatch_time (device_timestamp, received_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dispatch_events ADD CONSTRAINT FK_11100002DEVICE FOREIGN KEY (device_event_id) REFERENCES device_events (id)');
        $this->addSql('ALTER TABLE dispatch_events ADD CONSTRAINT FK_11100002COCKPIT FOREIGN KEY (cockpit_id) REFERENCES cockpits (id)');
        
        // Add new columns to cockpit_state
        $this->addSql('ALTER TABLE cockpit_state ADD total_dispatched BIGINT DEFAULT 0 NOT NULL, ADD available_stock BIGINT DEFAULT 0 NOT NULL');
        
        // Initialize available_stock = total_produced - 0
        $this->addSql('UPDATE cockpit_state SET available_stock = total_produced');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dispatch_events DROP FOREIGN KEY FK_11100002DEVICE');
        $this->addSql('ALTER TABLE dispatch_events DROP FOREIGN KEY FK_11100002COCKPIT');
        $this->addSql('DROP TABLE dispatch_events');
        $this->addSql('ALTER TABLE cockpit_state DROP total_dispatched, DROP available_stock');
    }
}
