<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial migration it sets up the schema.
 */
final class Version20190329032758 extends AbstractMigration
{
    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Initial schema setup.';
    }

    /**
     * @inheritDoc
     */
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, pack_id INT DEFAULT NULL, type_id INT DEFAULT NULL, faction_id INT DEFAULT NULL, position SMALLINT NOT NULL, code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci, cost VARCHAR(2) DEFAULT NULL COLLATE utf8_unicode_ci, text LONGTEXT NOT NULL COLLATE utf8_unicode_ci, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, quantity SMALLINT NOT NULL, income SMALLINT DEFAULT NULL, initiative SMALLINT DEFAULT NULL, claim SMALLINT DEFAULT NULL, reserve SMALLINT DEFAULT NULL, deck_limit SMALLINT DEFAULT NULL, designer TINYTEXT DEFAULT NULL COLLATE utf8_unicode_ci, strength SMALLINT DEFAULT NULL, traits VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, flavor LONGTEXT NOT NULL COLLATE utf8_unicode_ci, illustrator VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, is_unique TINYINT(1) NOT NULL, is_loyal TINYINT(1) NOT NULL, is_military TINYINT(1) NOT NULL, is_intrigue TINYINT(1) NOT NULL, is_power TINYINT(1) NOT NULL, octgn_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, is_multiple TINYINT(1) NOT NULL, image_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX card_code_idx (code), INDEX IDX_161498D3C54C8C93 (type_id), INDEX card_name_idx (name), INDEX IDX_161498D31919B217 (pack_id), INDEX IDX_161498D34448F8DA (faction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, decklist_id INT DEFAULT NULL, text LONGTEXT NOT NULL COLLATE utf8_unicode_ci, date_creation DATETIME NOT NULL, is_hidden TINYINT(1) NOT NULL, INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526CF4E9531B (decklist_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cycle (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(1024) NOT NULL COLLATE utf8_unicode_ci, position SMALLINT NOT NULL, size SMALLINT NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, UNIQUE INDEX cycle_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE deck (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, faction_id INT DEFAULT NULL, last_pack_id INT DEFAULT NULL, parent_decklist_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, description_md LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, problem VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, tags VARCHAR(4000) DEFAULT NULL COLLATE utf8_unicode_ci, major_version INT NOT NULL, minor_version INT NOT NULL, INDEX IDX_4FAC3637A76ED395 (user_id), INDEX IDX_4FAC36377F958E5F (last_pack_id), INDEX IDX_4FAC36379FC5416B (parent_decklist_id), INDEX IDX_4FAC36374448F8DA (faction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE deckchange (id INT AUTO_INCREMENT NOT NULL, deck_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, variation VARCHAR(1024) NOT NULL COLLATE utf8_unicode_ci, is_saved TINYINT(1) NOT NULL, version VARCHAR(8) DEFAULT NULL COLLATE utf8_unicode_ci, INDEX IDX_B32E853111948DC (deck_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE decklist (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, faction_id INT DEFAULT NULL, last_pack_id INT DEFAULT NULL, parent_deck_id INT DEFAULT NULL, precedent_decklist_id INT DEFAULT NULL, tournament_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name_canonical VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, description_md LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, description_html LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, signature VARCHAR(32) NOT NULL COLLATE utf8_unicode_ci, nb_votes INT NOT NULL, nb_favorites INT NOT NULL, nb_comments INT NOT NULL, version VARCHAR(8) NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_ED030EC6C386FA95 (precedent_decklist_id), INDEX IDX_ED030EC6A76ED395 (user_id), INDEX IDX_ED030EC67F958E5F (last_pack_id), INDEX IDX_ED030EC663513C9A (parent_deck_id), INDEX IDX_ED030EC633D1A3E7 (tournament_id), INDEX IDX_ED030EC64448F8DA (faction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE decklistslot (id INT AUTO_INCREMENT NOT NULL, decklist_id INT DEFAULT NULL, card_id INT DEFAULT NULL, quantity SMALLINT NOT NULL, INDEX IDX_2071B1F4E9531B (decklist_id), INDEX IDX_2071B14ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE deckslot (id INT AUTO_INCREMENT NOT NULL, deck_id INT DEFAULT NULL, card_id INT DEFAULT NULL, quantity SMALLINT NOT NULL, INDEX IDX_5C5D6B9111948DC (deck_id), INDEX IDX_5C5D6B94ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE faction (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(1024) NOT NULL COLLATE utf8_unicode_ci, is_primary TINYINT(1) NOT NULL, octgn_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX faction_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE favorite (decklist_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_68C58ED9A76ED395 (user_id), INDEX IDX_68C58ED9F4E9531B (decklist_id), PRIMARY KEY(decklist_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE follow (following_id INT NOT NULL, follower_id INT NOT NULL, INDEX IDX_68344470AC24F853 (follower_id), INDEX IDX_683444701816E3A3 (following_id), PRIMARY KEY(following_id, follower_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oauth2_access_token (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_454D96735F37A13B (token), INDEX IDX_454D9673A76ED395 (user_id), INDEX IDX_454D967319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oauth2_auth_code (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, redirect_uri LONGTEXT NOT NULL COLLATE utf8_unicode_ci, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_1D2905B55F37A13B (token), INDEX IDX_1D2905B5A76ED395 (user_id), INDEX IDX_1D2905B519EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oauth2_client (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, redirect_uris LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, allowed_grant_types LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', name VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE oauth2_refresh_token (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX UNIQ_4DD907325F37A13B (token), INDEX IDX_4DD90732A76ED395 (user_id), INDEX IDX_4DD9073219EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE pack (id INT AUTO_INCREMENT NOT NULL, cycle_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(1024) NOT NULL COLLATE utf8_unicode_ci, position SMALLINT NOT NULL, size SMALLINT NOT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, date_release DATE DEFAULT NULL, cgdb_id INT DEFAULT NULL, UNIQUE INDEX pack_code_idx (code), INDEX IDX_97DE5E235EC1162 (cycle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, card_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, text_md LONGTEXT NOT NULL COLLATE utf8_unicode_ci, text_html LONGTEXT NOT NULL COLLATE utf8_unicode_ci, nb_votes SMALLINT NOT NULL, INDEX IDX_794381C64ACC9A20 (card_id), INDEX IDX_794381C6A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reviewcomment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, review_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, text LONGTEXT NOT NULL COLLATE utf8_unicode_ci, INDEX IDX_E731F22FA76ED395 (user_id), INDEX IDX_E731F22F3E2E969B (review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE reviewvote (review_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1B4C9057A76ED395 (user_id), INDEX IDX_1B4C90573E2E969B (review_id), PRIMARY KEY(review_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(60) NOT NULL COLLATE utf8_unicode_ci, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, name VARCHAR(1024) NOT NULL COLLATE utf8_unicode_ci, UNIQUE INDEX type_code_idx (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, username_canonical VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, email VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, email_canonical VARCHAR(180) NOT NULL COLLATE utf8_unicode_ci, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, password VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL COLLATE utf8_unicode_ci, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:array)\', date_creation DATETIME NOT NULL, date_update DATETIME NOT NULL, reputation INT NOT NULL, resume LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, color VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, donation INT NOT NULL, is_notif_author TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_commenter TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_mention TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_follow TINYINT(1) DEFAULT \'1\' NOT NULL, is_notif_successor TINYINT(1) DEFAULT \'1\' NOT NULL, is_share_decks TINYINT(1) DEFAULT \'0\' NOT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vote (decklist_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_5A108564A76ED395 (user_id), INDEX IDX_5A108564F4E9531B (decklist_id), PRIMARY KEY(decklist_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D34448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D3C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE card ADD CONSTRAINT FK_161498D31919B217 FOREIGN KEY (pack_id) REFERENCES pack (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36374448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC3637A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36379FC5416B FOREIGN KEY (parent_decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE deck ADD CONSTRAINT FK_4FAC36377F958E5F FOREIGN KEY (last_pack_id) REFERENCES pack (id)');
        $this->addSql('ALTER TABLE deckchange ADD CONSTRAINT FK_B32E853111948DC FOREIGN KEY (deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC67F958E5F FOREIGN KEY (last_pack_id) REFERENCES pack (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC663513C9A FOREIGN KEY (parent_deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC6C386FA95 FOREIGN KEY (precedent_decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC64448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id)');
        $this->addSql('ALTER TABLE decklist ADD CONSTRAINT FK_ED030EC633D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE decklistslot ADD CONSTRAINT FK_2071B1F4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE decklistslot ADD CONSTRAINT FK_2071B14ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE deckslot ADD CONSTRAINT FK_5C5D6B94ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE deckslot ADD CONSTRAINT FK_5C5D6B9111948DC FOREIGN KEY (deck_id) REFERENCES deck (id)');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9F4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
        $this->addSql('ALTER TABLE favorite ADD CONSTRAINT FK_68C58ED9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_68344470AC24F853 FOREIGN KEY (follower_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE follow ADD CONSTRAINT FK_683444701816E3A3 FOREIGN KEY (following_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D967319EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (id)');
        $this->addSql('ALTER TABLE oauth2_access_token ADD CONSTRAINT FK_454D9673A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth2_auth_code ADD CONSTRAINT FK_1D2905B519EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (id)');
        $this->addSql('ALTER TABLE oauth2_auth_code ADD CONSTRAINT FK_1D2905B5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD90732A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth2_refresh_token ADD CONSTRAINT FK_4DD9073219EB6921 FOREIGN KEY (client_id) REFERENCES oauth2_client (id)');
        $this->addSql('ALTER TABLE pack ADD CONSTRAINT FK_97DE5E235EC1162 FOREIGN KEY (cycle_id) REFERENCES cycle (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C64ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE reviewcomment ADD CONSTRAINT FK_E731F22F3E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE reviewcomment ADD CONSTRAINT FK_E731F22FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reviewvote ADD CONSTRAINT FK_1B4C90573E2E969B FOREIGN KEY (review_id) REFERENCES review (id)');
        $this->addSql('ALTER TABLE reviewvote ADD CONSTRAINT FK_1B4C9057A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote ADD CONSTRAINT FK_5A108564F4E9531B FOREIGN KEY (decklist_id) REFERENCES decklist (id)');
    }

    /**
     * @inheritDoc
     */
    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Initial migration, cannot be reverted.');
    }
}
