<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Exception;
use Ramsey\Uuid\Uuid;

/**
 * Adds UUID column to deck table.
 */
final class Version20190922213719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds UUID column to deck table.';
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deck ADD uuid CHAR(36) COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4FAC3637D17F50A6 ON deck (uuid)');

        $sql = 'SELECT id FROM deck ORDER BY id';
        $rows = $this->connection->executeQuery($sql)->fetchAll();

        foreach ($rows as $row) {
            $uuid = Uuid::uuid4();
            $this->addSql("UPDATE deck SET uuid = :uuid WHERE id = :id", ["uuid" => $uuid, "id" => $row["id"]]);
        }
    }

    /**
     * @inheritdoc
     */
    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_4FAC3637D17F50A6 ON deck');
        $this->addSql('ALTER TABLE deck DROP uuid');
    }
}
