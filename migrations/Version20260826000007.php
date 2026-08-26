<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260826000007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create dashboard layout configuration tables with seed data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dashboard_rows (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, display_order INT NOT NULL, is_visible TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dashboard_columns (id INT AUTO_INCREMENT NOT NULL, dashboard_row_id INT NOT NULL, cockpit_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, metric_key VARCHAR(100) NOT NULL, display_order INT NOT NULL, is_visible TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_DASHBOARD_ROW (dashboard_row_id), INDEX IDX_DASHBOARD_COCKPIT (cockpit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dashboard_columns ADD CONSTRAINT FK_DASHBOARD_COLUMN_ROW FOREIGN KEY (dashboard_row_id) REFERENCES dashboard_rows (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dashboard_columns ADD CONSTRAINT FK_DASHBOARD_COLUMN_COCKPIT FOREIGN KEY (cockpit_id) REFERENCES cockpits (id) ON DELETE SET NULL');

        // Seed data for Default 4-row layout
        $now = date('Y-m-d H:i:s');

        // ROW 1: Production Overview
        $this->addSql("INSERT INTO dashboard_rows (name, display_order, is_visible, created_at) VALUES ('Production Overview', 1, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Total Requested', 'OVERALL_REQUESTED', 1, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Total Produced', 'OVERALL_PRODUCED', 2, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Total Dispatched', 'OVERALL_DISPATCHED', 3, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Available Stock', 'OVERALL_AVAILABLE', 4, 1, '{$now}')");

        // ROW 2: Cockpit Status
        $this->addSql("INSERT INTO dashboard_rows (name, display_order, is_visible, created_at) VALUES ('Cockpit Status', 2, 1, '{$now}')");

        // ROW 3: Production Queue
        $this->addSql("INSERT INTO dashboard_rows (name, display_order, is_visible, created_at) VALUES ('Production Queue', 3, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Current Cockpit', 'FIFO_CURRENT', 1, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Next Cockpit', 'FIFO_NEXT', 2, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Queue Size', 'FIFO_QUEUE_SIZE', 3, 1, '{$now}')");

        // ROW 4: System Status
        $this->addSql("INSERT INTO dashboard_rows (name, display_order, is_visible, created_at) VALUES ('System Status', 4, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Overall Health', 'HEALTH_OVERALL', 1, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'PLC Status', 'HEALTH_PLC', 2, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Scanner 1', 'HEALTH_SCANNER1', 3, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'Scanner 2', 'HEALTH_SCANNER2', 4, 1, '{$now}')");
        $this->addSql("INSERT INTO dashboard_columns (dashboard_row_id, name, metric_key, display_order, is_visible, created_at) VALUES (LAST_INSERT_ID(), 'eSSL Fingerprint', 'HEALTH_ESSL', 5, 1, '{$now}')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dashboard_columns DROP FOREIGN KEY FK_DASHBOARD_COLUMN_ROW');
        $this->addSql('ALTER TABLE dashboard_columns DROP FOREIGN KEY FK_DASHBOARD_COLUMN_COCKPIT');
        $this->addSql('DROP TABLE dashboard_columns');
        $this->addSql('DROP TABLE dashboard_rows');
    }
}
