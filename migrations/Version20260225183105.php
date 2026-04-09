<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260225183105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY `FK_D34A04AD44F5D008`');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY `FK_D34A04AD3E2E969B`');
        $this->addSql('DROP INDEX IDX_D34A04AD3E2E969B ON product');
        $this->addSql('ALTER TABLE product ADD product_id BINARY(16) DEFAULT NULL, DROP review_id, CHANGE rating_average rating_average NUMERIC(3, 1) DEFAULT NULL, CHANGE rating_count rating_count INT DEFAULT 0, CHANGE is_active is_active TINYINT DEFAULT 1, CHANGE sales_count sales_count INT DEFAULT 0');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD4584665A ON product (product_id)');
        $this->addSql('ALTER TABLE review ADD product_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_794381C64584665A ON review (product_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD44F5D008');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD4584665A');
        $this->addSql('DROP INDEX IDX_D34A04AD4584665A ON product');
        $this->addSql('ALTER TABLE product ADD review_id INT DEFAULT NULL, DROP product_id, CHANGE rating_average rating_average NUMERIC(2, 0) DEFAULT NULL, CHANGE rating_count rating_count INT DEFAULT NULL, CHANGE is_active is_active TINYINT DEFAULT NULL, CHANGE sales_count sales_count INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT `FK_D34A04AD44F5D008` FOREIGN KEY (brand_id) REFERENCES brand (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT `FK_D34A04AD3E2E969B` FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('CREATE INDEX IDX_D34A04AD3E2E969B ON product (review_id)');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C64584665A');
        $this->addSql('DROP INDEX IDX_794381C64584665A ON review');
        $this->addSql('ALTER TABLE review DROP product_id');
    }
}
