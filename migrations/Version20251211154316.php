<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251211154316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE taches ADD utilisateur_id INT NOT NULL, DROP utilisateur, CHANGE description description LONGTEXT NOT NULL, CHANGE statut statut VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE taches ADD CONSTRAINT FK_3BF2CD98FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('CREATE INDEX IDX_3BF2CD98FB88E14F ON taches (utilisateur_id)');
        $this->addSql('ALTER TABLE utilisateurs DROP prenom, DROP nom');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE taches DROP FOREIGN KEY FK_3BF2CD98FB88E14F');
        $this->addSql('DROP INDEX IDX_3BF2CD98FB88E14F ON taches');
        $this->addSql('ALTER TABLE taches ADD utilisateur VARCHAR(255) NOT NULL, DROP utilisateur_id, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE statut statut VARCHAR(20) DEFAULT \'A_FAIRE\' NOT NULL');
        $this->addSql('ALTER TABLE utilisateurs ADD prenom VARCHAR(100) NOT NULL, ADD nom VARCHAR(100) NOT NULL');
    }
}
