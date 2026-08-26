<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Phase 4 tables: users, fingerprint_user_mappings, terminals, terminal_sessions';
    }

    public function up(Schema $schema): void
    {
        // Users table
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Terminals table
        $this->addSql('CREATE TABLE terminals (id INT AUTO_INCREMENT NOT NULL, terminal_code VARCHAR(255) NOT NULL, terminal_name VARCHAR(255) NOT NULL, fingerprint_device_ip VARCHAR(45) DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_39C6D3B6CC9CDA0 (terminal_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Fingerprint_user_mappings table
        $this->addSql('CREATE TABLE fingerprint_user_mappings (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, essl_username VARCHAR(255) NOT NULL, machine_ip VARCHAR(45) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_FB233513A76ED395 (user_id), INDEX idx_essl_machine (essl_username, machine_ip), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fingerprint_user_mappings ADD CONSTRAINT FK_FB233513A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');

        // Terminal_sessions table
        $this->addSql('CREATE TABLE terminal_sessions (id INT AUTO_INCREMENT NOT NULL, terminal_id INT NOT NULL, user_id INT NOT NULL, fingerprint_event_id INT NOT NULL, session_uuid VARCHAR(36) NOT NULL, role VARCHAR(50) NOT NULL, started_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ended_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(50) NOT NULL, end_reason VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_8B7F5F5979C795C6 (session_uuid), INDEX IDX_8B7F5F59E1606B53 (terminal_id), INDEX IDX_8B7F5F59A76ED395 (user_id), INDEX IDX_8B7F5F59F870A0F0 (fingerprint_event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE terminal_sessions ADD CONSTRAINT FK_8B7F5F59E1606B53 FOREIGN KEY (terminal_id) REFERENCES terminals (id)');
        $this->addSql('ALTER TABLE terminal_sessions ADD CONSTRAINT FK_8B7F5F59A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE terminal_sessions ADD CONSTRAINT FK_8B7F5F59F870A0F0 FOREIGN KEY (fingerprint_event_id) REFERENCES device_events (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fingerprint_user_mappings DROP FOREIGN KEY FK_FB233513A76ED395');
        $this->addSql('ALTER TABLE terminal_sessions DROP FOREIGN KEY FK_8B7F5F59E1606B53');
        $this->addSql('ALTER TABLE terminal_sessions DROP FOREIGN KEY FK_8B7F5F59A76ED395');
        $this->addSql('ALTER TABLE terminal_sessions DROP FOREIGN KEY FK_8B7F5F59F870A0F0');
        $this->addSql('DROP TABLE fingerprint_user_mappings');
        $this->addSql('DROP TABLE terminal_sessions');
        $this->addSql('DROP TABLE terminals');
        $this->addSql('DROP TABLE users');
    }
}
