<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225185834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        //$this->addSql('ALTER TABLE product DROP product_id, CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE compare_at_price compare_at_price NUMERIC(10, 2) DEFAULT NULL, CHANGE stock stock INT DEFAULT 0 NOT NULL, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL, CHANGE sales_count sales_count INT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD product_id BINARY(16) DEFAULT NULL, CHANGE price price NUMERIC(10, 0) NOT NULL, CHANGE compare_at_price compare_at_price NUMERIC(10, 0) DEFAULT NULL, CHANGE stock stock INT NOT NULL, CHANGE is_active is_active TINYINT DEFAULT 1, CHANGE sales_count sales_count INT DEFAULT 0');
    }
}
