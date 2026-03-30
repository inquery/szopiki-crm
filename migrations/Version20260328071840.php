<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328071840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE prospect DROP FOREIGN KEY `FK_C9CE8C7D5AA408DD`');
        $this->addSql('ALTER TABLE prospect DROP FOREIGN KEY `FK_C9CE8C7DADF66B1A`');
        $this->addSql('DROP TABLE prospect');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY `FK_C7440455EDFD56AA`');
        $this->addSql('DROP INDEX IDX_C7440455EDFD56AA ON client');
        $this->addSql('ALTER TABLE client ADD source VARCHAR(100) DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, DROP source_prospect_id');
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY `FK_B7D58B0D182060A`');
        $this->addSql('DROP INDEX IDX_B7D58B0D182060A ON email_message');
        $this->addSql('ALTER TABLE email_message DROP prospect_id');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY `FK_F515E139D182060A`');
        $this->addSql('DROP INDEX IDX_F515E139D182060A ON meeting');
        $this->addSql('ALTER TABLE meeting DROP prospect_id');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY `FK_CFBDFA14D182060A`');
        $this->addSql('DROP INDEX IDX_CFBDFA14D182060A ON note');
        $this->addSql('ALTER TABLE note DROP prospect_id');
        $this->addSql('ALTER TABLE user ADD phone VARCHAR(20) DEFAULT NULL, ADD two_factor_enabled TINYINT NOT NULL, ADD two_factor_code VARCHAR(10) DEFAULT NULL, ADD two_factor_code_expires_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE prospect (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, contact_person VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, phone VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, address LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, source VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, status VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, notes LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, converted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, assigned_user_id INT DEFAULT NULL, converted_client_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_C9CE8C7D5AA408DD (converted_client_id), INDEX IDX_C9CE8C7DADF66B1A (assigned_user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT `FK_C9CE8C7D5AA408DD` FOREIGN KEY (converted_client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT `FK_C9CE8C7DADF66B1A` FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE client ADD source_prospect_id INT DEFAULT NULL, DROP source, DROP notes');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT `FK_C7440455EDFD56AA` FOREIGN KEY (source_prospect_id) REFERENCES prospect (id)');
        $this->addSql('CREATE INDEX IDX_C7440455EDFD56AA ON client (source_prospect_id)');
        $this->addSql('ALTER TABLE email_message ADD prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE email_message ADD CONSTRAINT `FK_B7D58B0D182060A` FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('CREATE INDEX IDX_B7D58B0D182060A ON email_message (prospect_id)');
        $this->addSql('ALTER TABLE meeting ADD prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT `FK_F515E139D182060A` FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('CREATE INDEX IDX_F515E139D182060A ON meeting (prospect_id)');
        $this->addSql('ALTER TABLE note ADD prospect_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT `FK_CFBDFA14D182060A` FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('CREATE INDEX IDX_CFBDFA14D182060A ON note (prospect_id)');
        $this->addSql('ALTER TABLE `user` DROP phone, DROP two_factor_enabled, DROP two_factor_code, DROP two_factor_code_expires_at');
    }
}
