<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917061847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE black_hour (id INT AUTO_INCREMENT NOT NULL, discounted_price DOUBLE PRECISION NOT NULL, start_time DATETIME NOT NULL, end_time DATETIME NOT NULL, created_at DATETIME NOT NULL, product_id INT NOT NULL, INDEX IDX_A3FA51074584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE black_hour ADD CONSTRAINT FK_A3FA51074584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE black_hour DROP FOREIGN KEY FK_A3FA51074584665A');
        $this->addSql('DROP TABLE black_hour');
    }
}
