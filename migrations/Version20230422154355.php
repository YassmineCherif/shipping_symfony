<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230422154355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY fk_client');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY fk_client1');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY fk_livreur');
        $this->addSql('ALTER TABLE colis DROP FOREIGN KEY fk_livreur1');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE colis');
        $this->addSql('DROP TABLE moyen de transport');
        $this->addSql('DROP TABLE paiement');
        $this->addSql('ALTER TABLE livreur CHANGE nbre_reclamation nbre_reclamation INT NOT NULL, CHANGE nbre_colis_total nbre_colis_total INT NOT NULL, CHANGE nbre_colis_courant nbre_colis_courant INT NOT NULL');
        $this->addSql('ALTER TABLE partenaire CHANGE zone zone VARCHAR(255) NOT NULL, CHANGE prix_poids prix_poids DOUBLE PRECISION NOT NULL, CHANGE prix_zone prix_zone VARCHAR(100) NOT NULL, CHANGE inflammable inflammable INT NOT NULL, CHANGE fragile fragile INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP confirmation_token');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, text TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, personne_reclamé VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, type_reclamation VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, date DATE NOT NULL, id_client INT NOT NULL, ref VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, stars INT NOT NULL, INDEX i1 (id_client), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE colis (id INT AUTO_INCREMENT NOT NULL, id_client INT DEFAULT NULL, id_livreur INT DEFAULT NULL, ref VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, hauteur INT NOT NULL, largeur INT NOT NULL, poids INT NOT NULL, prix VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, fragile TINYINT(1) NOT NULL, inflammable TINYINT(1) NOT NULL, depart VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, destination VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, etat_colis VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, zone VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, urgent TINYINT(1) NOT NULL, nom_partenaire VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, INDEX fk_client (id_client), INDEX fk_livreur (id_livreur), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE moyen de transport (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, type INT DEFAULT NULL, Matricule VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, id_par INT NOT NULL, INDEX idp (id_par), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = MyISAM COMMENT = \'\' ');
        $this->addSql('CREATE TABLE paiement (id_colis INT NOT NULL, type VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`, date DATE NOT NULL, PRIMARY KEY(id_colis)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT fk_client FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT fk_client1 FOREIGN KEY (id_client) REFERENCES client (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT fk_livreur FOREIGN KEY (id_livreur) REFERENCES livreur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE colis ADD CONSTRAINT fk_livreur1 FOREIGN KEY (id_livreur) REFERENCES livreur (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('ALTER TABLE user ADD confirmation_token VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE partenaire CHANGE zone zone VARCHAR(255) DEFAULT NULL, CHANGE prix_poids prix_poids DOUBLE PRECISION DEFAULT NULL, CHANGE prix_zone prix_zone VARCHAR(100) DEFAULT NULL, CHANGE inflammable inflammable INT DEFAULT NULL, CHANGE fragile fragile INT DEFAULT NULL');
        $this->addSql('ALTER TABLE livreur CHANGE nbre_reclamation nbre_reclamation INT DEFAULT NULL, CHANGE nbre_colis_total nbre_colis_total INT DEFAULT NULL, CHANGE nbre_colis_courant nbre_colis_courant INT DEFAULT NULL');
    }
}
