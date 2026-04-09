<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223200615 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE color (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, hex_code VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_type_variation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE size (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, product_type_variation_id INT DEFAULT NULL, INDEX IDX_F7C0246A6244378E (product_type_variation_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE size ADD CONSTRAINT FK_F7C0246A6244378E FOREIGN KEY (product_type_variation_id) REFERENCES product_type_variation (id)');
        $this->addSql('ALTER TABLE product ADD sales_count INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE size DROP FOREIGN KEY FK_F7C0246A6244378E');
        $this->addSql('DROP TABLE color');
        $this->addSql('DROP TABLE product_type_variation');
        $this->addSql('DROP TABLE size');
        $this->addSql('ALTER TABLE product DROP sales_count');
    }
}
