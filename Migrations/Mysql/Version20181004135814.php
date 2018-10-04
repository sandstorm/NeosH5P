<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs! This block will be used as the migration description if getDescription() is not used.
 */
class Version20181004135814 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join DROP FOREIGN KEY FK_ABAE0A662C4D8F51');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join DROP FOREIGN KEY FK_ABAE0A662E33B826');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join ADD CONSTRAINT FK_ABAE0A662C4D8F51 FOREIGN KEY (neosh5p_cachedasset) REFERENCES sandstorm_neosh5p_domain_model_cachedasset (persistence_object_identifier) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join ADD CONSTRAINT FK_ABAE0A662E33B826 FOREIGN KEY (neosh5p_library) REFERENCES sandstorm_neosh5p_domain_model_library (persistence_object_identifier) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library DROP FOREIGN KEY FK_48621E7A7618A0BE');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library ADD CONSTRAINT FK_48621E7A7618A0BE FOREIGN KEY (zippedlibraryfile) REFERENCES neos_flow_resourcemanagement_persistentresource (persistence_object_identifier) ON DELETE SET NULL');

        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_content DROP FOREIGN KEY FK_1727B66FA9F268DA');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_content ADD CONSTRAINT FK_1727B66FA9F268DA FOREIGN KEY (exportfile) REFERENCES neos_flow_resourcemanagement_persistentresource (persistence_object_identifier) ON DELETE SET NULL');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join DROP FOREIGN KEY FK_ABAE0A662E33B826');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join DROP FOREIGN KEY FK_ABAE0A662C4D8F51');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join ADD CONSTRAINT FK_ABAE0A662E33B826 FOREIGN KEY (neosh5p_library) REFERENCES sandstorm_neosh5p_domain_model_library (persistence_object_identifier)');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library_cachedassets_join ADD CONSTRAINT FK_ABAE0A662C4D8F51 FOREIGN KEY (neosh5p_cachedasset) REFERENCES sandstorm_neosh5p_domain_model_cachedasset (persistence_object_identifier)');

        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_library DROP FOREIGN KEY FK_48621E7A7618A0BE');
        $this->addSql('CREATE UNIQUE INDEX libraryid ON sandstorm_neosh5p_domain_model_library (libraryid)');

        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_content DROP FOREIGN KEY FK_1727B66FA9F268DA');
        $this->addSql('ALTER TABLE sandstorm_neosh5p_domain_model_content ADD CONSTRAINT FK_1727B66FA9F268DA FOREIGN KEY (exportfile) REFERENCES neos_flow_resourcemanagement_persistentresource (persistence_object_identifier)');
    }
}
