<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230526095115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact CHANGE objet objet VARCHAR(100) NOT NULL, CHANGE date date DATETIME NOT NULL, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE mail mail VARCHAR(100) NOT NULL, CHANGE nom nom VARCHAR(50) NOT NULL, CHANGE prenom prenom VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE user ADD activate TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contact CHANGE objet objet VARCHAR(100) DEFAULT NULL, CHANGE date date DATETIME DEFAULT NULL, CHANGE contenu contenu LONGTEXT DEFAULT NULL, CHANGE mail mail VARCHAR(100) DEFAULT NULL, CHANGE nom nom VARCHAR(50) DEFAULT NULL, CHANGE prenom prenom VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP activate');
    }
}
