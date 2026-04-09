<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260305150522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, provider VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, amount NUMERIC(10, 0) NOT NULL, currency VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, order_id_id INT DEFAULT NULL, INDEX IDX_6D28840DFCDAEAAA (order_id_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DFCDAEAAA FOREIGN KEY (order_id_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DFCDAEAAA');
        $this->addSql('DROP TABLE payment');
    }
}
