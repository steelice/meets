<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210205235422 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE meeting (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, location LONGTEXT NOT NULL, begins_at DATETIME NOT NULL, description LONGTEXT NOT NULL, users_going INT NOT NULL, total_comments INT NOT NULL, main_photo VARCHAR(255) NOT NULL, gallery_files LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', UNIQUE INDEX UNIQ_F515E139F47645AE (url), INDEX IDX_F515E139A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_comment (id INT AUTO_INCREMENT NOT NULL, meeting_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, is_approved TINYINT(1) NOT NULL, text LONGTEXT NOT NULL, approve_hash VARCHAR(32) NOT NULL, INDEX IDX_CA61AE167433D9C (meeting_id), INDEX IDX_CA61AE1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE meeting_visitor (id INT AUTO_INCREMENT NOT NULL, meeting_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_5237A91267433D9C (meeting_id), INDEX IDX_5237A912A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meeting_comment ADD CONSTRAINT FK_CA61AE167433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id)');
        $this->addSql('ALTER TABLE meeting_comment ADD CONSTRAINT FK_CA61AE1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE meeting_visitor ADD CONSTRAINT FK_5237A91267433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id)');
        $this->addSql('ALTER TABLE meeting_visitor ADD CONSTRAINT FK_5237A912A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE meeting_comment DROP FOREIGN KEY FK_CA61AE167433D9C');
        $this->addSql('ALTER TABLE meeting_visitor DROP FOREIGN KEY FK_5237A91267433D9C');
        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E139A76ED395');
        $this->addSql('ALTER TABLE meeting_comment DROP FOREIGN KEY FK_CA61AE1A76ED395');
        $this->addSql('ALTER TABLE meeting_visitor DROP FOREIGN KEY FK_5237A912A76ED395');
        $this->addSql('DROP TABLE meeting');
        $this->addSql('DROP TABLE meeting_comment');
        $this->addSql('DROP TABLE meeting_visitor');
        $this->addSql('DROP TABLE user');
    }
}
