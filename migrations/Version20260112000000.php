<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds rarity table.
 */
final class Version20260112000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds rarity table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rarity (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX rarity_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE card ADD rarity_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3F3747573 FOREIGN KEY (rarity_id) REFERENCES rarity (id)');
        $this->addSql('CREATE INDEX IDX_161498D3F3747573 ON card (rarity_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE card DROP FOREIGN KEY FK_161498D3F3747573');
        $this->addSql('DROP TABLE rarity');
        $this->addSql('DROP INDEX IDX_161498D3F3747573 ON card');
        $this->addSql('ALTER TABLE card DROP rarity_id');
    }
}
