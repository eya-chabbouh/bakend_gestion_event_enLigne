<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128112029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date DATE NOT NULL, heure TIME NOT NULL, lieu VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, capacity INT NOT NULL, image VARCHAR(255) DEFAULT NULL, archive TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, expediteur_id INT NOT NULL, destinataire_id INT NOT NULL, evenement_id INT NOT NULL, message VARCHAR(255) NOT NULL, date_envoi DATETIME NOT NULL, INDEX IDX_BF5476CA10335F61 (expediteur_id), INDEX IDX_BF5476CAA4F84F6E (destinataire_id), INDEX IDX_BF5476CAFD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registre (id INT AUTO_INCREMENT NOT NULL, evenement_id INT NOT NULL, utilisateur_id INT NOT NULL, date_inscription DATETIME NOT NULL, INDEX IDX_D9A94145FD02F13 (evenement_id), INDEX IDX_D9A94145FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, mail VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, el INT NOT NULL, motdepasse VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA10335F61 FOREIGN KEY (expediteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA4F84F6E FOREIGN KEY (destinataire_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE registre ADD CONSTRAINT FK_D9A94145FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE registre ADD CONSTRAINT FK_D9A94145FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA10335F61');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA4F84F6E');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFD02F13');
        $this->addSql('ALTER TABLE registre DROP FOREIGN KEY FK_D9A94145FD02F13');
        $this->addSql('ALTER TABLE registre DROP FOREIGN KEY FK_D9A94145FB88E14F');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE registre');
        $this->addSql('DROP TABLE utilisateur');
    }
}
