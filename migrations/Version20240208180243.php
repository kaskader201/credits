<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240208180243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "user" (id BYTEA NOT NULL, external_id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX EXTERNAL_ID ON "user" (external_id)');
        $this->addSql('CREATE TABLE credit (id BYTEA NOT NULL, amount NUMERIC(36, 2) NOT NULL, priority INT NOT NULL, type VARCHAR(255) NOT NULL, note VARCHAR(255) DEFAULT NULL, expired_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, usable BOOLEAN DEFAULT NULL, fully_used_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, expired_amount NUMERIC(36, 2) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id BYTEA NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1CC16EFEA76ED395 ON credit (user_id)');
        $this->addSql('CREATE INDEX USABLE_PRIORITY_EXPIRATION ON credit (usable, priority, expired_at)');
        $this->addSql('CREATE INDEX USABLE_USER_PRIORITY_EXPIRATION ON credit (usable, user_id, priority, expired_at)');
        $this->addSql('CREATE TABLE request (id BYTEA NOT NULL, request_id VARCHAR(255) NOT NULL, amount NUMERIC(36, 2) NOT NULL, operation VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, data JSON NOT NULL, user_id BYTEA NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3B978F9FA76ED395 ON request (user_id)');
        $this->addSql('CREATE TABLE transaction (id BYTEA NOT NULL, action VARCHAR(255) NOT NULL, amount NUMERIC(36, 2) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, user_id BYTEA NOT NULL, credit_id BYTEA NOT NULL, request_id BYTEA NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_723705D1A76ED395 ON transaction (user_id)');
        $this->addSql('CREATE INDEX IDX_723705D1CE062FF9 ON transaction (credit_id)');
        $this->addSql('CREATE INDEX IDX_723705D1427EB8A5 ON transaction (request_id)');
        $this->addSql('CREATE INDEX USER_CREDIT ON transaction (user_id, credit_id)');
        $this->addSql('CREATE INDEX USER_CREATED_AT ON transaction (user_id, created_at)');
        $this->addSql('ALTER TABLE credit ADD CONSTRAINT FK_1CC16EFEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE request ADD CONSTRAINT FK_3B978F9FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1CE062FF9 FOREIGN KEY (credit_id) REFERENCES credit (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1427EB8A5 FOREIGN KEY (request_id) REFERENCES request (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE credit DROP CONSTRAINT FK_1CC16EFEA76ED395');
        $this->addSql('ALTER TABLE request DROP CONSTRAINT FK_3B978F9FA76ED395');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1CE062FF9');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D1427EB8A5');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE credit');
        $this->addSql('DROP TABLE request');
        $this->addSql('DROP TABLE transaction');
    }
}
