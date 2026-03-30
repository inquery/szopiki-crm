<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260328065400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(255) NOT NULL, tax_id VARCHAR(20) DEFAULT NULL, contact_person VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address LONGTEXT DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, postal_code VARCHAR(20) DEFAULT NULL, country VARCHAR(100) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, assigned_user_id INT DEFAULT NULL, source_prospect_id INT DEFAULT NULL, INDEX IDX_C7440455ADF66B1A (assigned_user_id), INDEX IDX_C7440455EDFD56AA (source_prospect_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE deal (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, value NUMERIC(12, 2) DEFAULT NULL, currency VARCHAR(3) NOT NULL, stage VARCHAR(20) NOT NULL, probability INT DEFAULT NULL, expected_close_date DATE DEFAULT NULL, actual_close_date DATE DEFAULT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INT NOT NULL, assigned_user_id INT DEFAULT NULL, INDEX IDX_E3FEC11619EB6921 (client_id), INDEX IDX_E3FEC116ADF66B1A (assigned_user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE email_account (id INT AUTO_INCREMENT NOT NULL, email_address VARCHAR(255) NOT NULL, display_name VARCHAR(255) DEFAULT NULL, imap_host VARCHAR(255) NOT NULL, imap_port INT NOT NULL, imap_encryption VARCHAR(10) NOT NULL, smtp_host VARCHAR(255) NOT NULL, smtp_port INT NOT NULL, smtp_encryption VARCHAR(10) NOT NULL, username VARCHAR(255) NOT NULL, password_encrypted LONGTEXT NOT NULL, is_active TINYINT NOT NULL, last_sync_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE email_attachment (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, mime_type VARCHAR(100) DEFAULT NULL, size INT DEFAULT NULL, file_path VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL, email_message_id INT NOT NULL, INDEX IDX_D5EC2B64FFC9E1F6 (email_message_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE email_message (id INT AUTO_INCREMENT NOT NULL, message_id VARCHAR(255) DEFAULT NULL, in_reply_to VARCHAR(255) DEFAULT NULL, subject VARCHAR(500) DEFAULT NULL, from_address VARCHAR(255) NOT NULL, to_addresses JSON DEFAULT NULL, cc_addresses JSON DEFAULT NULL, body_text LONGTEXT DEFAULT NULL, body_html LONGTEXT DEFAULT NULL, direction VARCHAR(10) NOT NULL, folder VARCHAR(100) NOT NULL, is_read TINYINT NOT NULL, received_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, email_account_id INT NOT NULL, client_id INT DEFAULT NULL, prospect_id INT DEFAULT NULL, INDEX IDX_B7D58B037D8AD65 (email_account_id), INDEX IDX_B7D58B019EB6921 (client_id), INDEX IDX_B7D58B0D182060A (prospect_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE meeting (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, location VARCHAR(255) DEFAULT NULL, type VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INT DEFAULT NULL, prospect_id INT DEFAULT NULL, deal_id INT DEFAULT NULL, organizer_id INT NOT NULL, INDEX IDX_F515E13919EB6921 (client_id), INDEX IDX_F515E139D182060A (prospect_id), INDEX IDX_F515E139F60E2305 (deal_id), INDEX IDX_F515E139876C4DDA (organizer_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE meeting_participant (id INT AUTO_INCREMENT NOT NULL, external_name VARCHAR(255) DEFAULT NULL, external_email VARCHAR(255) DEFAULT NULL, meeting_id INT NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_FBFF656467433D9C (meeting_id), INDEX IDX_FBFF6564A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INT DEFAULT NULL, prospect_id INT DEFAULT NULL, deal_id INT DEFAULT NULL, author_id INT NOT NULL, INDEX IDX_CFBDFA1419EB6921 (client_id), INDEX IDX_CFBDFA14D182060A (prospect_id), INDEX IDX_CFBDFA14F60E2305 (deal_id), INDEX IDX_CFBDFA14F675F31B (author_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE oauth_token (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(500) NOT NULL, refresh_token VARCHAR(500) DEFAULT NULL, scopes JSON DEFAULT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL, revoked_at DATETIME DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_D8344B2A5F37A13B (token), INDEX IDX_D8344B2AA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE panel_config (id INT AUTO_INCREMENT NOT NULL, panel_type VARCHAR(50) NOT NULL, panel_url VARCHAR(500) DEFAULT NULL, panel_username_encrypted LONGTEXT DEFAULT NULL, panel_password_encrypted LONGTEXT DEFAULT NULL, database_host_encrypted LONGTEXT DEFAULT NULL, database_name_encrypted LONGTEXT DEFAULT NULL, database_username_encrypted LONGTEXT DEFAULT NULL, database_password_encrypted LONGTEXT DEFAULT NULL, is_installed TINYINT NOT NULL, installed_at DATETIME DEFAULT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, installed_by_id INT DEFAULT NULL, client_id INT DEFAULT NULL, INDEX IDX_208E5C3E129255A7 (installed_by_id), INDEX IDX_208E5C3E19EB6921 (client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE prospect (id INT AUTO_INCREMENT NOT NULL, company_name VARCHAR(255) NOT NULL, contact_person VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(50) DEFAULT NULL, address LONGTEXT DEFAULT NULL, source VARCHAR(100) DEFAULT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, converted_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, assigned_user_id INT DEFAULT NULL, converted_client_id INT DEFAULT NULL, INDEX IDX_C9CE8C7DADF66B1A (assigned_user_id), UNIQUE INDEX UNIQ_C9CE8C7D5AA408DD (converted_client_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_active TINYINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455EDFD56AA FOREIGN KEY (source_prospect_id) REFERENCES prospect (id)');
        $this->addSql('ALTER TABLE deal ADD CONSTRAINT FK_E3FEC11619EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE deal ADD CONSTRAINT FK_E3FEC116ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE email_attachment ADD CONSTRAINT FK_D5EC2B64FFC9E1F6 FOREIGN KEY (email_message_id) REFERENCES email_message (id)');
        $this->addSql('ALTER TABLE email_message ADD CONSTRAINT FK_B7D58B037D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_account (id)');
        $this->addSql('ALTER TABLE email_message ADD CONSTRAINT FK_B7D58B019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE email_message ADD CONSTRAINT FK_B7D58B0D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E13919EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139F60E2305 FOREIGN KEY (deal_id) REFERENCES deal (id)');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139876C4DDA FOREIGN KEY (organizer_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE meeting_participant ADD CONSTRAINT FK_FBFF656467433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id)');
        $this->addSql('ALTER TABLE meeting_participant ADD CONSTRAINT FK_FBFF6564A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1419EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14D182060A FOREIGN KEY (prospect_id) REFERENCES prospect (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14F60E2305 FOREIGN KEY (deal_id) REFERENCES deal (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14F675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE oauth_token ADD CONSTRAINT FK_D8344B2AA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE panel_config ADD CONSTRAINT FK_208E5C3E129255A7 FOREIGN KEY (installed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE panel_config ADD CONSTRAINT FK_208E5C3E19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT FK_C9CE8C7DADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE prospect ADD CONSTRAINT FK_C9CE8C7D5AA408DD FOREIGN KEY (converted_client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455ADF66B1A');
        $this->addSql('ALTER TABLE client DROP FOREIGN KEY FK_C7440455EDFD56AA');
        $this->addSql('ALTER TABLE deal DROP FOREIGN KEY FK_E3FEC11619EB6921');
        $this->addSql('ALTER TABLE deal DROP FOREIGN KEY FK_E3FEC116ADF66B1A');
        $this->addSql('ALTER TABLE email_attachment DROP FOREIGN KEY FK_D5EC2B64FFC9E1F6');
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY FK_B7D58B037D8AD65');
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY FK_B7D58B019EB6921');
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY FK_B7D58B0D182060A');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E13919EB6921');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139D182060A');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139F60E2305');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139876C4DDA');
        $this->addSql('ALTER TABLE meeting_participant DROP FOREIGN KEY FK_FBFF656467433D9C');
        $this->addSql('ALTER TABLE meeting_participant DROP FOREIGN KEY FK_FBFF6564A76ED395');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA1419EB6921');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14D182060A');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14F60E2305');
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_CFBDFA14F675F31B');
        $this->addSql('ALTER TABLE oauth_token DROP FOREIGN KEY FK_D8344B2AA76ED395');
        $this->addSql('ALTER TABLE panel_config DROP FOREIGN KEY FK_208E5C3E129255A7');
        $this->addSql('ALTER TABLE panel_config DROP FOREIGN KEY FK_208E5C3E19EB6921');
        $this->addSql('ALTER TABLE prospect DROP FOREIGN KEY FK_C9CE8C7DADF66B1A');
        $this->addSql('ALTER TABLE prospect DROP FOREIGN KEY FK_C9CE8C7D5AA408DD');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE deal');
        $this->addSql('DROP TABLE email_account');
        $this->addSql('DROP TABLE email_attachment');
        $this->addSql('DROP TABLE email_message');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('DROP TABLE meeting_participant');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE oauth_token');
        $this->addSql('DROP TABLE panel_config');
        $this->addSql('DROP TABLE prospect');
        $this->addSql('DROP TABLE `user`');
    }
}
