<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260111174528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE feeds ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL AFTER image');
        $this->addSql('ALTER TABLE feeds ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL AFTER created_at');

        // Evitamos que pete la migracion
        $this->addSql('UPDATE feeds SET body = "" WHERE body IS NULL');
        $this->addSql('UPDATE feeds SET published_at = CURRENT_TIMESTAMP WHERE published_at IS NULL');

        // Aplicamos los not null
        $this->addSql('ALTER TABLE feeds CHANGE published_at published_at DATETIME NOT NULL, CHANGE body body LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feeds DROP created_at, DROP updated_at, CHANGE body body LONGTEXT DEFAULT NULL, CHANGE published_at published_at DATETIME DEFAULT NULL');
    }
}
