<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250816050029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sub_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1638D5A55E237E06 (name), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sub_categories_categories (sub_category_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_4F97BE71F7BFE87C (sub_category_id), INDEX IDX_4F97BE7112469DE2 (category_id), PRIMARY KEY (sub_category_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sub_categories_categories ADD CONSTRAINT FK_4F97BE71F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES sub_categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sub_categories_categories ADD CONSTRAINT FK_4F97BE7112469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE categories DROP subcategories, DROP products');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sub_categories_categories DROP FOREIGN KEY FK_4F97BE71F7BFE87C');
        $this->addSql('ALTER TABLE sub_categories_categories DROP FOREIGN KEY FK_4F97BE7112469DE2');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE sub_categories');
        $this->addSql('DROP TABLE sub_categories_categories');
        $this->addSql('ALTER TABLE categories ADD subcategories JSON NOT NULL, ADD products JSON NOT NULL');
    }
}
