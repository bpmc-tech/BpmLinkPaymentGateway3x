<?php

/*
 * This file is part of the BpmLinkPaymentGateway
 *
 * Copyright (C) 2017 K.Matsuki <info@bpmc.co.jp>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\BpmLinkPaymentGateway\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Plugin\BpmLinkPaymentGateway\Form\Type\BpmLinkPaymentGatewayConfigType;
use Plugin\BpmLinkPaymentGateway\Controller\Util\PluginUtil;

class ConfigController
{
    const LOG_NS = 'monolog.logger.bpmlinkpaymentgateway';
    private $app;

    /**
     * BpmLinkPaymentGateway用設定画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $this->app = $app;
        

        $configFrom = new BpmLinkPaymentGatewayConfigType($this->app);
        $form = $this->app['form.factory']->createBuilder($configFrom)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->app[self::LOG_NS]->info('ConfigController.index.Submitted.START');
            // 
            $data = $form->getData();

            // add code...
            $this->app['orm.em']->getConnection()->beginTransaction();

            $this->saveUserSetting( $data );
            $this->savePaymentData( $data );

            $this->app['orm.em']->getConnection()->commit();

            $this->app[self::LOG_NS]->info('ConfigController.index.Submitted.END');

            //message
            $app->addSuccess('admin.register.complete', 'admin');
        }

        return $this->app->render('BpmLinkPaymentGateway/Resource/template/admin/config.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * BpmcPluginテーブル登録処理
     *
     * @param FormData $formData
     */
    public function saveUserSetting($formData){

        $this->app[self::LOG_NS]->info('ConfigController.saveUserSetting.Submitted.START');

        $pluginUtil = & PluginUtil::getInstance($this->app);

        $pluginCode = $pluginUtil->getCode(true);
        $BpmcPlugin = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin')->findOneBy(array('code' => $pluginCode));

        if( is_null($BpmcPlugin) ){
            $BpmcPlugin = new \Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin();
            $BpmcPlugin->setDelFlg(0);
            $BpmcPlugin->setCreateDate(new \DateTime());
        } else {
            
            $BpmcPlugin->setUpdateDate(new \DateTime());
        }

        $BpmcPlugin->setCode($pluginUtil->getCode());
        $BpmcPlugin->setName($pluginUtil->getName());
        $BpmcPlugin->setApiToken($formData['api_token']);

        $this->app['orm.em']->persist($BpmcPlugin);
        $this->app['orm.em']->flush();

        $this->app[self::LOG_NS]->info('ConfigController.saveUserSetting.Submitted.END');
    }

    /**
     * 支払い方法登録処理
     *
     * @param FormData $formData
     */
    public function savePaymentData($formData){

        $this->app[self::LOG_NS]->info('ConfigController.savePayment.Submitted.START');
        
        $paymentTypeId = $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PAYID_CREDIT_LINK'];

        $paymentId = $this->savePayment($paymentTypeId);
        $this->saveBpmcPlayment($paymentId, $paymentTypeId);
        
        $this->app[self::LOG_NS]->info('ConfigController.savePayment.Submitted.END');
    }

    /**
     * Paymentテーブル登録処理
     *
     * @param 決済タイプID $paymentTypeId
     * @return int
     */
    public function savePayment($paymentTypeId){
        
        $pluginUtil = & PluginUtil::getInstance($this->app);
        $Payments = $pluginUtil->getPayments();

        $Payment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')
                                ->findOneBy(array('payment_type' => $paymentTypeId));

        if (is_null($Payment)) {
            $Payment = $this->app['orm.em']->getRepository('\Eccube\Entity\Payment')->findOrCreate(0);
        } else {
            $Payment = $this->app['orm.em']->getRepository('\Eccube\Entity\Payment')->findOneBy(array('id' => $Payment->getId() ));
        }

        $Payment->setMethod( $Payments[$paymentTypeId] );
        $Payment->setFixFlg(1);
        $Payment->setRuleMin( $pluginUtil->getRuleMin() );
        $Payment->setRuleMax( $pluginUtil->getRuleMax() );
        $Payment->setDelFlg(0);
        $Payment->setUpdateDate(new \DateTime());
        $Payment->setCreateDate(new \DateTime());
        $Payment->setCharge(0);

        $this->app['orm.em']->persist($Payment);
        $this->app['orm.em']->flush();
        return $Payment->getId();
        //getPayments
           
    }

    /**
     * Paymentテーブル登録処理
     *
     * @param 決済タイプID $paymentTypeId
     * @return int
     */
    public function saveBpmcPlayment($id, $paymentTypeId){

        $pluginUtil = & PluginUtil::getInstance($this->app);
        $Payments = $pluginUtil->getPayments();

        $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')
                                ->findOneBy(array('payment_type' => $paymentTypeId));

        if( is_null($BpmcPayment) ) {
            $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->findOrCreate(0);
            $BpmcPayment->setCreateDate(new \DateTime());
        }
        
        $BpmcPayment->setId( $id );
        $BpmcPayment->setMethod( $Payments[$paymentTypeId] );
        $BpmcPayment->setDelFlg(0);
        $BpmcPayment->setUpdateDate(new \DateTime());
        

        //getRetType
        
        $BpmcPayment->setJob( $pluginUtil->getJob() );
        $BpmcPayment->setRetType( $pluginUtil->getRetType() );

        $BpmcPayment->setRetUrl( '/bpm_link_payment/callback/successfully' );
        $BpmcPayment->setRetCancelUrl( '/bpm_link_payment/callback/cancel' );

        $BpmcPayment->setItemType( $pluginUtil->getItemType() );

        $BpmcPayment->setPaymentType( $paymentTypeId );
        $BpmcPayment->setCode( $pluginUtil->getCode() );

        $this->app['orm.em']->persist($BpmcPayment);
        $this->app['orm.em']->flush();

    }
}



















