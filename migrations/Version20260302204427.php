<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302204427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE color ADD cover_image_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE color ADD CONSTRAINT FK_665648E9E5A0E336 FOREIGN KEY (cover_image_id) REFERENCES product_image (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_665648E95E237E06 ON color (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_665648E9E5A0E336 ON color (cover_image_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE color DROP FOREIGN KEY FK_665648E9E5A0E336');
        $this->addSql('DROP INDEX UNIQ_665648E95E237E06 ON color');
        $this->addSql('DROP INDEX UNIQ_665648E9E5A0E336 ON color');
        $this->addSql('ALTER TABLE color DROP cover_image_id');
    }
}
