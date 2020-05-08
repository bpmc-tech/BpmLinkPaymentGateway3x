<?php

/*
 * This file is part of the BpmLinkPaymentGateway
 *
 * Copyright (C) 2017 K.Matsuki <info@bpmc.co.jp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BpmLinkPaymentGateway;

use Eccube\Application;
use Eccube\Plugin\AbstractPluginManager;

class PluginManager extends AbstractPluginManager
{

    /**
     * プラグインインストール時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function install($config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
    }

    /**
     * プラグイン削除時の処理
     *
     * @param $config
     * @param Application $app
     */
    public function uninstall($config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code'], 0);
    }

    /**
     * プラグイン有効時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function enable($config, Application $app)
    {
        if(!$this->updateStatusBpmcPayment(0, $app)){
            return;
        }
    }

    /**
     * プラグイン無効時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function disable($config, Application $app)
    {
        if(!$this->updateStatusBpmcPayment(1, $app)){
            return;
        }
    }

    public function updateStatusBpmcPayment($status, $app){
        $exceptVersions = array('3.0.4');
        if (in_array(\Eccube\Common\Constant::VERSION, $exceptVersions)){
            if (!empty ($app['config'])){
                if (empty($app['config']['BpmLinkPaymentGateway'])) {
                    $app = new \Eccube\Application();
                    $app->initDoctrine();
                    $app->register(new \Silex\Provider\FormServiceProvider());
                    $app->register(new \Eccube\ServiceProvider\EccubeServiceProvider());
                    $app->boot();
                }
            }
        }

        $Repo = $app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod');

        return true;
    }

    /**
     * プラグイン更新時の処理
     *
     * @param $config
     * @param Application $app
     * @throws \Exception
     */
    public function update($config, Application $app)
    {
        $this->migrationSchema($app, __DIR__ . '/Resource/doctrine/migration', $config['code']);
    }

}
