<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216021547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE adresse_user (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, rue VARCHAR(255) NOT NULL, ville VARCHAR(100) NOT NULL, code_postal VARCHAR(20) NOT NULL, pays VARCHAR(100) NOT NULL, INDEX IDX_7D95019FFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE checkout (id INT AUTO_INCREMENT NOT NULL, id_user INT DEFAULT NULL, id_produit INT DEFAULT NULL, first_name VARCHAR(255) NOT NULL, second_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, INDEX IDX_AF382D4E6B3CA4B (id_user), INDEX IDX_AF382D4EF7384557 (id_produit), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE products (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, title VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, categorie VARCHAR(255) NOT NULL, location VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, quantity VARCHAR(255) NOT NULL, INDEX IDX_B3BA5A5AFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, role VARCHAR(50) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adresse_user ADD CONSTRAINT FK_7D95019FFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE checkout ADD CONSTRAINT FK_AF382D4E6B3CA4B FOREIGN KEY (id_user) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE checkout ADD CONSTRAINT FK_AF382D4EF7384557 FOREIGN KEY (id_produit) REFERENCES products (id)');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE adresse_user DROP FOREIGN KEY FK_7D95019FFB88E14F');
        $this->addSql('ALTER TABLE checkout DROP FOREIGN KEY FK_AF382D4E6B3CA4B');
        $this->addSql('ALTER TABLE checkout DROP FOREIGN KEY FK_AF382D4EF7384557');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AFB88E14F');
        $this->addSql('DROP TABLE adresse_user');
        $this->addSql('DROP TABLE checkout');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE utilisateur');
    }
}
