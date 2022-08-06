<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds name column to oauth client table.
 */
final class Version20220806163946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds name column to oauth client table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client ADD name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE client DROP name');
    }
}
