<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260306225148 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE coupon (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, discount_type VARCHAR(20) DEFAULT NULL, discount_value NUMERIC(10, 0) DEFAULT NULL, starts_at DATETIME DEFAULT NULL, expires_at DATETIME DEFAULT NULL, is_active TINYINT DEFAULT NULL, min_cart_total NUMERIC(10, 0) NOT NULL, max_uses INT DEFAULT NULL, used_count INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE coupon_product (coupon_id INT NOT NULL, product_id BINARY(16) NOT NULL, INDEX IDX_3C22473B66C5951B (coupon_id), INDEX IDX_3C22473B4584665A (product_id), PRIMARY KEY (coupon_id, product_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE coupon_product ADD CONSTRAINT FK_3C22473B66C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE coupon_product ADD CONSTRAINT FK_3C22473B4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cart ADD discount_amount NUMERIC(10, 0) DEFAULT NULL, ADD coupon_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B766C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id)');
        $this->addSql('CREATE INDEX IDX_BA388B766C5951B ON cart (coupon_id)');
        $this->addSql('ALTER TABLE cart_item ADD variant_id INT DEFAULT NULL, ADD size_id INT DEFAULT NULL, CHANGE cart_id cart_id INT NOT NULL, CHANGE product_id product_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25273B69A9AF FOREIGN KEY (variant_id) REFERENCES product_color_variant (id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE2527498DA827 FOREIGN KEY (size_id) REFERENCES size (id)');
        $this->addSql('CREATE INDEX IDX_F0FE25273B69A9AF ON cart_item (variant_id)');
        $this->addSql('CREATE INDEX IDX_F0FE2527498DA827 ON cart_item (size_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE coupon_product DROP FOREIGN KEY FK_3C22473B66C5951B');
        $this->addSql('ALTER TABLE coupon_product DROP FOREIGN KEY FK_3C22473B4584665A');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE coupon_product');
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B766C5951B');
        $this->addSql('DROP INDEX IDX_BA388B766C5951B ON cart');
        $this->addSql('ALTER TABLE cart DROP discount_amount, DROP coupon_id');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25273B69A9AF');
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE2527498DA827');
        $this->addSql('DROP INDEX IDX_F0FE25273B69A9AF ON cart_item');
        $this->addSql('DROP INDEX IDX_F0FE2527498DA827 ON cart_item');
        $this->addSql('ALTER TABLE cart_item DROP variant_id, DROP size_id, CHANGE cart_id cart_id INT DEFAULT NULL, CHANGE product_id product_id BINARY(16) DEFAULT NULL');
    }
}
