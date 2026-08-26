<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Phase 7 production_queue table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE production_queue (id BIGINT AUTO_INCREMENT NOT NULL, cockpit_id INT NOT NULL, trigger_request_event_id BIGINT NOT NULL, queue_uuid VARCHAR(36) NOT NULL, pending_device_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', pending_received_at DATETIME(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', pending_event_id BIGINT NOT NULL, status VARCHAR(30) NOT NULL, entered_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', selected_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', started_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_11100001QUEUE (queue_uuid), INDEX IDX_11100001COCKPIT (cockpit_id), INDEX IDX_11100001REQUEST (trigger_request_event_id), INDEX idx_fifo_ordering (status, pending_device_timestamp, pending_received_at, pending_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE production_queue ADD CONSTRAINT FK_11100001COCKPIT FOREIGN KEY (cockpit_id) REFERENCES cockpits (id)');
        $this->addSql('ALTER TABLE production_queue ADD CONSTRAINT FK_11100001REQUEST FOREIGN KEY (trigger_request_event_id) REFERENCES request_events (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE production_queue DROP FOREIGN KEY FK_11100001COCKPIT');
        $this->addSql('ALTER TABLE production_queue DROP FOREIGN KEY FK_11100001REQUEST');
        $this->addSql('DROP TABLE production_queue');
    }
}
