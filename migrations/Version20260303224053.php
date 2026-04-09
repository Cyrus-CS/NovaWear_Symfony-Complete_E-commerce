<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303224053 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_color_variant (id INT AUTO_INCREMENT NOT NULL, price NUMERIC(10, 0) NOT NULL, stock INT DEFAULT NULL, compare_at_price NUMERIC(10, 0) DEFAULT NULL, color_id INT NOT NULL, product_id BINARY(16) NOT NULL, INDEX IDX_78382E717ADA1FB5 (color_id), INDEX IDX_78382E714584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_color_variant_size (product_color_variant_id INT NOT NULL, size_id INT NOT NULL, INDEX IDX_E32402D51077769D (product_color_variant_id), INDEX IDX_E32402D5498DA827 (size_id), PRIMARY KEY (product_color_variant_id, size_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product_color_variant ADD CONSTRAINT FK_78382E717ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)');
        $this->addSql('ALTER TABLE product_color_variant ADD CONSTRAINT FK_78382E714584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_color_variant_size ADD CONSTRAINT FK_E32402D51077769D FOREIGN KEY (product_color_variant_id) REFERENCES product_color_variant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_color_variant_size ADD CONSTRAINT FK_E32402D5498DA827 FOREIGN KEY (size_id) REFERENCES size (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_color_variant DROP FOREIGN KEY FK_78382E717ADA1FB5');
        $this->addSql('ALTER TABLE product_color_variant DROP FOREIGN KEY FK_78382E714584665A');
        $this->addSql('ALTER TABLE product_color_variant_size DROP FOREIGN KEY FK_E32402D51077769D');
        $this->addSql('ALTER TABLE product_color_variant_size DROP FOREIGN KEY FK_E32402D5498DA827');
        $this->addSql('DROP TABLE product_color_variant');
        $this->addSql('DROP TABLE product_color_variant_size');
    }
}
