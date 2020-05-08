<?php

/*
 * This file is part of the BpmLinkPaymentGateway
 *
 * Copyright (C) [year] [author]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Eccube\Application;
use Eccube\Common\Constant;

class Version20170409133104 extends AbstractMigration
{
    protected $entities = array(
        'Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin',
        'Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod',
        'Plugin\BpmLinkPaymentGateway\Entity\BpmcOrderPayment',
    );

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->createTableDtbBpmcPlugin($schema);
        $this->createTableDtbBpmcPaymentMethod($schema);
        $this->createTableDtbBpmcOrderPayment($schema);
        /*
        // this up() migration is auto-generated, please modify it to your needs
        if (version_compare(Constant::VERSION, '3.0.9', '>=')) {
            // 3.0.9 以降の場合、dcm.ymlの定義からテーブル生成を行う.
            $app = Application::getInstance();
            $meta = $this->getMetadata($app['orm.em']);
            $tool = new SchemaTool($app['orm.em']);
            $tool->createSchema($meta);
        } else {
            // 3.0.0 - 3.0.8
            $this->createTableDtbBpmcPlugin($schema);
            $this->createTableDtbBpmcPaymentMethod($schema);
            $this->createTableDtbBpmcOrderPayment($schema);  
        }
        */
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('dtb_bpmlpg_plugin');
        $schema->dropTable('dtb_bpmlpg_payment_method');
        $schema->dropTable('dtb_bpmlpg_order_payment');
        // this down() migration is auto-generated, please modify it to your needs
        /*
        if (version_compare(Constant::VERSION, '3.0.9', '>=')) {
            // 3.0.9 以降の場合、dcm.ymlの定義からテーブル/シーケンスの削除を行う
            $app = Application::getInstance();
            $meta = $this->getMetadata($app['orm.em']);

            $tool = new SchemaTool($app['orm.em']);
            $schemaFromMetadata = $tool->getSchemaFromMetadata($meta);

            // テーブル削除
            foreach ($schemaFromMetadata->getTables() as $table) {
                if ($schema->hasTable($table->getName())) {
                    $schema->dropTable($table->getName());
                }
            }

            // シーケンス削除
            foreach ($schemaFromMetadata->getSequences() as $sequence) {
                if ($schema->hasSequence($sequence->getName())) {
                    $schema->dropSequence($sequence->getName());
                }
            }
        } else {
            // 3.0.0 - 3.0.8
            $schema->dropTable('dtb_bpmc_plugin');
            $schema->dropTable('dtb_bpmc_payment_method');
            $schema->dropTable('dtb_bpmc_order_payment');
        }
        */
    }

    /**
     * @param EntityManager $em
     * @return array
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     */
    protected function getMetadata(EntityManager $em)
    {
        $meta = array();
        foreach ($this->entities as $entity) {
            $meta[] = $em->getMetadataFactory()->getMetadataFor($entity);
        }

        return $meta;
    }


    /**
     * @param Schema $schema
     */
    public function createTableDtbBpmcPlugin(Schema $schema)
    {
        $table = $schema->createTable('dtb_bpmlpg_plugin');
        $table->addColumn('plugin_id', 'integer', array(
            'notnull' => false,
        ));
        $table->addColumn('plugin_code', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('plugin_name', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('api_token', 'text', array(
            'notnull' => true,
        ));
        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => false,
        ));
        $table->addColumn('create_date', 'datetime', array(
            'notnull' => false,
        ));
        $table->addColumn('update_date', 'datetime', array(
            'notnull' => false,
        ));
    }

    /**
     * @param Schema $schema
     */
    public function createTableDtbBpmcPaymentMethod(Schema $schema)
    {
        $table = $schema->createTable('dtb_bpmlpg_payment_method');
        $table->addColumn('payment_id', 'integer', array(
            'notnull' => false,
        ));
        $table->addColumn('payment_method', 'text', array(
            'notnull' => true,
        ));
        $table->addColumn('del_flg', 'smallint', array(
            'notnull' => false,
        ));
        $table->addColumn('create_date', 'datetime', array(
            'notnull' => false,
        ));
        $table->addColumn('update_date', 'datetime', array(
            'notnull' => false,
        ));
        $table->addColumn('job', 'text', array(
            'notnull' => true,
        ));
        $table->addColumn('ret_type', 'smallint', array(
            'notnull' => false,
        ));
        $table->addColumn('ret_url', 'text', array(
            'notnull' => true,
        ));
        $table->addColumn('ret_cancel_url', 'text', array(
            'notnull' => true,
        ));
        $table->addColumn('item_type', 'smallint', array(
            'notnull' => false,
        ));
        $table->addColumn('payment_type', 'integer', array(
            'notnull' => false,
        ));
        $table->addColumn('plugin_code', 'text', array(
            'notnull' => true,
        ));
    }

    /**
     * @param Schema $schema
     */
    public function createTableDtbBpmcOrderPayment(Schema $schema)
    {
        $table = $schema->createTable('dtb_bpmlpg_order_payment');
        $table->addColumn('order_id', 'integer', array(
            'notnull' => false,
        ));
        $table->addColumn('shop_trakking', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('job', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('amount', 'integer', array(
            'notnull' => false,
        ));
        $table->addColumn('tran_id', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('result', 'smallint', array(
            'notnull' => false,
        ));
        $table->addColumn('error_code', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('error_msg', 'text', array(
            'notnull' => false,
        ));
        $table->addColumn('create_date', 'datetime', array(
            'notnull' => false,
        ));
        $table->addColumn('update_date', 'datetime', array(
            'notnull' => false,
        ));
    }

}
