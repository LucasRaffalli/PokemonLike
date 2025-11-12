<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112144219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE battle (id INT AUTO_INCREMENT NOT NULL, challenger_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', opponent_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', winner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', completed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', battle_log JSON DEFAULT NULL, INDEX IDX_139917342D521FDF (challenger_id), INDEX IDX_139917347F656CDC (opponent_id), INDEX IDX_139917345DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE battle ADD CONSTRAINT FK_139917342D521FDF FOREIGN KEY (challenger_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE battle ADD CONSTRAINT FK_139917347F656CDC FOREIGN KEY (opponent_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE battle ADD CONSTRAINT FK_139917345DFCD4B8 FOREIGN KEY (winner_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE battle DROP FOREIGN KEY FK_139917342D521FDF');
        $this->addSql('ALTER TABLE battle DROP FOREIGN KEY FK_139917347F656CDC');
        $this->addSql('ALTER TABLE battle DROP FOREIGN KEY FK_139917345DFCD4B8');
        $this->addSql('DROP TABLE battle');
    }
}
