<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Phase 5 tables: request_events and cockpit_state';
    }

    public function up(Schema $schema): void
    {
        // cockpit_state
        $this->addSql('CREATE TABLE cockpit_state (id INT AUTO_INCREMENT NOT NULL, cockpit_id INT NOT NULL, total_requested BIGINT NOT NULL, current_balance BIGINT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', version INT NOT NULL, UNIQUE INDEX UNIQ_D98B2FE5358055E8 (cockpit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cockpit_state ADD CONSTRAINT FK_D98B2FE5358055E8 FOREIGN KEY (cockpit_id) REFERENCES cockpits (id)');

        // request_events
        $this->addSql('CREATE TABLE request_events (id BIGINT AUTO_INCREMENT NOT NULL, device_event_id BIGINT NOT NULL, cockpit_id INT NOT NULL, request_uuid VARCHAR(36) NOT NULL, quantity INT NOT NULL, device_timestamp DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', received_at DATETIME(6) DEFAULT CURRENT_TIMESTAMP(6) NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', processed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_A80BD6598B7F5F59 (request_uuid), UNIQUE INDEX UNIQ_A80BD659F870A0F0 (device_event_id), INDEX IDX_A80BD659358055E8 (cockpit_id), INDEX idx_device_timestamp (device_timestamp), INDEX idx_received_at (received_at), INDEX idx_created_at (created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE request_events ADD CONSTRAINT FK_A80BD659F870A0F0 FOREIGN KEY (device_event_id) REFERENCES device_events (id)');
        $this->addSql('ALTER TABLE request_events ADD CONSTRAINT FK_A80BD659358055E8 FOREIGN KEY (cockpit_id) REFERENCES cockpits (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cockpit_state DROP FOREIGN KEY FK_D98B2FE5358055E8');
        $this->addSql('ALTER TABLE request_events DROP FOREIGN KEY FK_A80BD659F870A0F0');
        $this->addSql('ALTER TABLE request_events DROP FOREIGN KEY FK_A80BD659358055E8');
        $this->addSql('DROP TABLE cockpit_state');
        $this->addSql('DROP TABLE request_events');
    }
}
