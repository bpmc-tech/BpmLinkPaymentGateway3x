<?php

/*
 * This file is part of the BpmcPaymentGateway
 *
 * Copyright (C) 2017 K.Matsuki <info@bpmc.co.jp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BpmLinkPaymentGateway\ServiceProvider;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\BpmLinkPaymentGateway\Form\Type\BpmLinkPaymentGatewayConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class BpmLinkPaymentGatewayServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // プラグイン用設定画面
        $app->match('/'.$app['config']['admin_route'].'/plugin/BpmLinkPaymentGateway/config', 'Plugin\BpmLinkPaymentGateway\Controller\ConfigController::index')->bind('plugin_BpmLinkPaymentGateway_config');

        // 独自コントローラ
        $app->match('/bpm_link_payment/bridge', 'Plugin\BpmLinkPaymentGateway\Controller\BpmLinkPaymentGatewayController::bridge')->bind('plugin_BpmLinkPaymentGateway_bridge');
        $app->match('/bpm_link_payment/callback/{result_type}/{pre_order_id}', 'Plugin\BpmLinkPaymentGateway\Controller\BpmLinkPaymentGatewayController::callback')
                        ->assert('result_type', 'cancel|successfully')->bind('plugin_BpmLinkPaymentGateway_callback');

        $app->match('/bpm_link_payment/fook_result', 'Plugin\BpmLinkPaymentGateway\Controller\BpmLinkPaymentGatewayController::fook_result')
                        ->bind('plugin_BpmLinkPaymentGateway_fook_result');

        $app->match('/bpm_link_payment/assets/{assets_type}/{assets_name}', 'Plugin\BpmLinkPaymentGateway\Controller\BpmLinkPaymentGatewayController::assets')
                        ->assert('assets_type', 'css|js|img')->assert('assets_name', '[a-z0-9.\-_]+')->bind('plugin_BpmLinkPaymentGateway_assets');

        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new BpmLinkPaymentGatewayConfigType($app);

            return $types;
        }));

        // Repository
        $app['eccube.plugin.bpmc_pg.repository.bpmc_plugin'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin');
        });

        $app['eccube.plugin.bpmc_pg.repository.bpmc_payment_method'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod');
        });

        $app['eccube.plugin.bpmc_pg.repository.bpmc_order_payment'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\BpmLinkPaymentGateway\Entity\BpmcOrderPayment');
        });

        // ログファイル設定
        $app['monolog.logger.bpmlinkpaymentgateway'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('bpmlinkpaymentgateway');

            $filename = $app['config']['root_dir'].'/app/log/bpmlinkpaymentgateway.log';
            $RotateHandler = new RotatingFileHandler($filename, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'bpmlinkpaymentgateway_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::ERROR),
                    0,
                    true,
                    true,
                    Logger::INFO
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }

}
