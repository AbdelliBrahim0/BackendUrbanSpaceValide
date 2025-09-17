<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250917054100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE black_friday (id INT AUTO_INCREMENT NOT NULL, nouveau_prix DOUBLE PRECISION NOT NULL, date_creation DATETIME NOT NULL, produit_id INT NOT NULL, INDEX IDX_E7D5D078F347EFB (produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE black_friday ADD CONSTRAINT FK_E7D5D078F347EFB FOREIGN KEY (produit_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE black_friday DROP FOREIGN KEY FK_E7D5D078F347EFB');
        $this->addSql('DROP TABLE black_friday');
    }
}
