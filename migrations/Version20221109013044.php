<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds parent deck id column to deck table.
 */
final class Version20221109013044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds parent deck id column to deck table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deck ADD parent_deck_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC363763513C9A FOREIGN KEY (parent_deck_id) REFERENCES deck (id)');
        $this->addSql('CREATE INDEX IDX_4FAC363763513C9A ON deck (parent_deck_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deck DROP FOREIGN KEY FK_4FAC363763513C9A');
        $this->addSql('DROP INDEX IDX_4FAC363763513C9A ON deck');
        $this->addSql('ALTER TABLE deck DROP parent_deck_id');
    }
}
