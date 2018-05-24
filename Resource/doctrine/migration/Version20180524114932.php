<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180524114932 extends AbstractMigration
{
    const NAME = 'dtb_customer';
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $t = $schema->getTable(self::NAME);
        if($t->hasColumn('name02')){
             $t->changeColumn('name02', array('NotNull'=>false));
        }
    }
    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $t = $schema->getTable(self::NAME);
        if($t->hasColumn('name02')){
             $t->changeColumn('name02', array('NotNull'=>true));
        }
    }
}
