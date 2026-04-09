<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227183251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE newsletter_subscriber (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, is_active TINYINT DEFAULT NULL, subscribed_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, total_ttc NUMERIC(10, 0) NOT NULL, total_ht NUMERIC(10, 0) DEFAULT NULL, currency VARCHAR(4) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_item (id INT AUTO_INCREMENT NOT NULL, product_name VARCHAR(255) NOT NULL, quantity INT NOT NULL, unit_price NUMERIC(10, 0) DEFAULT NULL, total_price NUMERIC(10, 0) NOT NULL, order_id_id INT DEFAULT NULL, product_id_id BINARY(16) DEFAULT NULL, INDEX IDX_52EA1F09FCDAEAAA (order_id_id), INDEX IDX_52EA1F09DE18E50B (product_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09FCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09DE18E50B FOREIGN KEY (product_id_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09FCDAEAAA');
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09DE18E50B');
        $this->addSql('DROP TABLE newsletter_subscriber');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_item');
    }
}
