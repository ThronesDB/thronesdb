<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds banner alerts table.
 */
final class Version20250803234943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds banner alerts table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE banneralert (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, text LONGTEXT NOT NULL, level SMALLINT NOT NULL, active TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE banneralert');
    }
}
