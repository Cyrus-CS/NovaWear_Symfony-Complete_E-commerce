<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218161242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, logo VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, descritpion VARCHAR(255) DEFAULT NULL, position INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id BINARY(16) NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) DEFAULT NULL, descritpion VARCHAR(255) NOT NULL, short_description VARCHAR(255) DEFAULT NULL, price NUMERIC(10, 0) NOT NULL, compare_at_price NUMERIC(10, 0) DEFAULT NULL, stock INT NOT NULL, rating_average NUMERIC(2, 0) DEFAULT NULL, rating_count INT DEFAULT NULL, is_active TINYINT NOT NULL, is_new TINYINT NOT NULL, is_top_selling TINYINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, brand_id INT DEFAULT NULL, review_id INT DEFAULT NULL, INDEX IDX_D34A04AD44F5D008 (brand_id), INDEX IDX_D34A04AD3E2E969B (review_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_category (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_category_product (product_category_id INT NOT NULL, product_id BINARY(16) NOT NULL, INDEX IDX_9A1E202FBE6903FD (product_category_id), INDEX IDX_9A1E202F4584665A (product_id), PRIMARY KEY (product_category_id, product_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_category_category (product_category_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_5B3AFE9BE6903FD (product_category_id), INDEX IDX_5B3AFE912469DE2 (category_id), PRIMARY KEY (product_category_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_image (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(255) NOT NULL, position INT NOT NULL, is_main TINYINT NOT NULL, product_id BINARY(16) NOT NULL, INDEX IDX_64617F034584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(255) NOT NULL, rating INT NOT NULL, content LONGTEXT NOT NULL, is_verified TINYINT NOT NULL, is_published TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_794381C6A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD3E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE product_category_product ADD CONSTRAINT FK_9A1E202FBE6903FD FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category_product ADD CONSTRAINT FK_9A1E202F4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category_category ADD CONSTRAINT FK_5B3AFE9BE6903FD FOREIGN KEY (product_category_id) REFERENCES product_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category_category ADD CONSTRAINT FK_5B3AFE912469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD44F5D008');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD3E2E969B');
        $this->addSql('ALTER TABLE product_category_product DROP FOREIGN KEY FK_9A1E202FBE6903FD');
        $this->addSql('ALTER TABLE product_category_product DROP FOREIGN KEY FK_9A1E202F4584665A');
        $this->addSql('ALTER TABLE product_category_category DROP FOREIGN KEY FK_5B3AFE9BE6903FD');
        $this->addSql('ALTER TABLE product_category_category DROP FOREIGN KEY FK_5B3AFE912469DE2');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE product_category_product');
        $this->addSql('DROP TABLE product_category_category');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE user DROP created_at, DROP updated_at');
    }
}
