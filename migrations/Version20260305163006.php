<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305163006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shipment (id INT AUTO_INCREMENT NOT NULL, carrier VARCHAR(100) DEFAULT NULL, tracking_number VARCHAR(100) DEFAULT NULL, status VARCHAR(255) NOT NULL, shipped_at DATETIME NOT NULL, order_id_id INT DEFAULT NULL, INDEX IDX_2CB20DCFCDAEAAA (order_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE shipment ADD CONSTRAINT FK_2CB20DCFCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shipment DROP FOREIGN KEY FK_2CB20DCFCDAEAAA');
        $this->addSql('DROP TABLE shipment');
    }
}
