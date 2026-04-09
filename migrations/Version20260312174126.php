<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312174126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, iso_code VARCHAR(4) DEFAULT NULL, phone_code VARCHAR(12) DEFAULT NULL, is_active TINYINT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE address ADD country_id INT NOT NULL, DROP country, CHANGE company company VARCHAR(55) DEFAULT NULL, CHANGE street street VARCHAR(105) NOT NULL, CHANGE postal_code postal_code VARCHAR(20) NOT NULL, CHANGE phone phone VARCHAR(205) DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81F92F3E70 ON address (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE country');
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81F92F3E70');
        $this->addSql('DROP INDEX IDX_D4E6F81F92F3E70 ON address');
        $this->addSql('ALTER TABLE address ADD country VARCHAR(255) NOT NULL, DROP country_id, CHANGE company company VARCHAR(255) DEFAULT NULL, CHANGE street street VARCHAR(255) NOT NULL, CHANGE postal_code postal_code VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL');
    }
}
