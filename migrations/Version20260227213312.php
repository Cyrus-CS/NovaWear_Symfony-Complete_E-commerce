<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227213312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_size (product_id BINARY(16) NOT NULL, size_id INT NOT NULL, INDEX IDX_7A2806CB4584665A (product_id), INDEX IDX_7A2806CB498DA827 (size_id), PRIMARY KEY (product_id, size_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_color (product_id BINARY(16) NOT NULL, color_id INT NOT NULL, INDEX IDX_C70A33B54584665A (product_id), INDEX IDX_C70A33B57ADA1FB5 (color_id), PRIMARY KEY (product_id, color_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product_size ADD CONSTRAINT FK_7A2806CB4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_size ADD CONSTRAINT FK_7A2806CB498DA827 FOREIGN KEY (size_id) REFERENCES size (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT FK_C70A33B54584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_color ADD CONSTRAINT FK_C70A33B57ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product ADD type_variation_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADD6C105DD FOREIGN KEY (type_variation_id) REFERENCES product_type_variation (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADD6C105DD ON product (type_variation_id)');
        $this->addSql('ALTER TABLE product_image ADD color_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F037ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id)');
        $this->addSql('CREATE INDEX IDX_64617F037ADA1FB5 ON product_image (color_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_size DROP FOREIGN KEY FK_7A2806CB4584665A');
        $this->addSql('ALTER TABLE product_size DROP FOREIGN KEY FK_7A2806CB498DA827');
        $this->addSql('ALTER TABLE product_color DROP FOREIGN KEY FK_C70A33B54584665A');
        $this->addSql('ALTER TABLE product_color DROP FOREIGN KEY FK_C70A33B57ADA1FB5');
        $this->addSql('DROP TABLE product_size');
        $this->addSql('DROP TABLE product_color');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADD6C105DD');
        $this->addSql('DROP INDEX IDX_D34A04ADD6C105DD ON product');
        $this->addSql('ALTER TABLE product DROP type_variation_id');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F037ADA1FB5');
        $this->addSql('DROP INDEX IDX_64617F037ADA1FB5 ON product_image');
        $this->addSql('ALTER TABLE product_image DROP color_id');
    }
}
