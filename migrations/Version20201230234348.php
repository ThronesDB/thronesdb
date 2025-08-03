<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Keep track of when restricted lists were created and updated.
 */
final class Version20201230234348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Keep track of when restricted lists were created and updated.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE restriction ADD date_creation DATETIME ');
        $this->addSql('UPDATE restriction SET date_creation = CURRENT_TIMESTAMP()');
        $this->addSql('ALTER TABLE restriction MODIFY date_creation DATETIME NOT NULL');

        $this->addSql('ALTER TABLE restriction ADD date_update DATETIME ');
        $this->addSql('UPDATE restriction SET date_update = CURRENT_TIMESTAMP()');
        $this->addSql('ALTER TABLE restriction MODIFY date_update DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE restriction DROP date_update');
        $this->addSql('ALTER TABLE restriction DROP date_creation');
    }
}
