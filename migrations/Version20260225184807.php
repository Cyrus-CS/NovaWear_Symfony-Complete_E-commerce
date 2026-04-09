<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225184807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1) On remet tous les rating_count à 0 pour éviter tout problème de conversion
        $this->addSql('UPDATE product SET rating_count = 0');
        $this->addSql('UPDATE product SET sales_count = 0');

        $this->addSql('ALTER TABLE product 
            CHANGE price price NUMERIC(10, 2) NOT NULL,
            CHANGE compare_at_price compare_at_price NUMERIC(10, 2) DEFAULT NULL,
            CHANGE stock stock INT DEFAULT 0 NOT NULL,
            CHANGE rating_count rating_count INT DEFAULT 0 NOT NULL,
            CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL,
            CHANGE sales_count sales_count INT DEFAULT 0 NOT NULL
        ');
    }
}
