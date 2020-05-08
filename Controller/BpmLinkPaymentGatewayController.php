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
use Symfony\Component\HttpFoundation\Response;
use Plugin\BpmLinkPaymentGateway\Controller\Util\PluginUtil;
use Eccube\Common\Constant;

class BpmLinkPaymentGatewayController
{
    const LOG_NS = 'monolog.logger.bpmlinkpaymentgateway';
    public $app;

    /**
     * BpmLinkPaymentGateway画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        return $app->render('BpmLinkPaymentGateway/Resource/template/index.twig', array(
            // add parameter...
        ));
    }

    /**
     * 決済ページへ遷移する画面
     */
    public function bridge(Application $app, Request $request)
    {
        $this->app = $app;

        /* LOG */ $app[self::LOG_NS]->info('BpmLinkPaymentGatewayController.index.START');

        $Order = $app['eccube.repository.order']->findOneBy(array('pre_order_id' => $app['eccube.service.cart']->getPreOrderId()));

        if (is_null($Order)) {
            $error_title = 'エラー';
            $error_message = "注文情報の取得が出来ませんでした。この手続きは無効となりました。";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }

        if (!$this->checkStockProduct($app, $Order)) {
            $app->addError('front.shopping.stock.error');
            return $app->redirect($app->url('shopping_error'));
        }

        $OrderDetails = $Order->getOrderDetails();

        $orderItemName = $OrderDetails[0]->getProductName();
        if($OrderDetails->count() > 1) {
            $orderItemName .= 'とその他'.($OrderDetails->count()-1).'点';
        }

        $Payment = $Order->getPayment();
        $BpmcPayment = $app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find($Payment->getId());

        // [20180413] ADD
        $Customer = $app['session']->get('eccube.front.shopping.nonmember');
        $shopData1 = "0";
        if ($app->isGranted('ROLE_USER')) {
            $Customer = $app->user();
            $shopData1 = $Customer->getId();
        }

        $email = $Order->getEmail();
        $telnumber = $Order->getTel01() . $Order->getTel02() . $Order->getTel03();

        $pluginUtil = & PluginUtil::getInstance($app);
        $pluginCode = $pluginUtil->getCode(true);
        $BpmcPlugin = $app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPlugin')->findOneBy(array('code' => $pluginCode));

        $api_token = $BpmcPlugin->getApiToken();
        $api_url = $pluginUtil->getLinkApiUrlByApiToken($api_token);


        //[20180404]    $isDebug = true;
        $isDebug = false;

        return $app->render('BpmLinkPaymentGateway/Resource/template/bridge.twig', array(
            'api_url' => $api_url,
            'pluginUtil' => $pluginUtil,
            'RetUrl' => ($pluginUtil->getAbsUrlRoot() . ($isDebug ? '/index_dev.php' : '') . $BpmcPayment->getRetUrl() . '/' . urlencode($Order->getPreOrderId()) ),
            'CancelUrl' => ($pluginUtil->getAbsUrlRoot() . ($isDebug ? '/index_dev.php' : '') . $BpmcPayment->getRetCancelUrl() . '/' . urlencode($Order->getPreOrderId()) ),
            'orderEmail' => $Order->getEmail(),
            'orderTelnumber' => $Order->getTel01() . $Order->getTel02() . $Order->getTel03(),
            'orderTotal' => $Order->getPaymentTotal(),
            'orderPreId' => $Order->getPreOrderId(),
            'orderItemName' => $orderItemName,
            'shopData1' => $shopData1,
        ));   
    }

    /**
     * POST
     * 決済ページから戻ってくる時の画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callback(Application $app, Request $request) {
        $this->app = $app;

        $result_type = $request->get('result_type');
        $preOrderId = $request->get('pre_order_id');
        $httpMethod = $request->server->get('REQUEST_METHOD');
        if($httpMethod == 'POST') {
            $requestData = $request->request->all();
        } else {
            $requestData = $request->query->all();    
        }
        
        if($result_type == 'cancel') {
            // Cancel時の処理
            $Order = $app['eccube.repository.order']->findOneBy(array('pre_order_id' => $preOrderId));
            if (is_null($Order)) {
                $error_title = 'エラー';
                $error_message = "注文情報の取得が出来ませんでした。この手続きは無効となりました。";
                return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
            }

            $Order->setOrderStatus($this->app['eccube.repository.order_status']->find( $this->app['config']['order_cancel'] ));
            $this->app['orm.em']->persist($Order);
            $this->app['orm.em']->flush();

            if(!isset($requestData['Result'])) {
                $app->addError('決済がキャンセルされました');
                return $app->redirect($app->url('shopping_error'));
            }

            // 決済失敗時もcancel urlに飛ぶ
            $apiResult = $requestData['Result'];
            $this->prepareOrderData($Order, $app, $requestData);
            $app->addError('決済に失敗しました。'."\n"."[CODE:".$requestData['ErrorCode']."]");
            return $app->redirect($app->url('shopping_error'));

        }

        $preOrderId = $requestData['shop_tracking'];
        $Order = $app['eccube.repository.order']->findOneBy(array('pre_order_id' => $preOrderId));

        if (is_null($Order)) {
            $error_title = 'エラー';
            $error_message = "注文情報の取得が出来ませんでした。この手続きは無効となりました。";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }

        $this->prepareOrderData($Order, $app, $requestData);

        $orderId = $Order->getId();
        $apiResult = $requestData['result_code'];

        if($apiResult != '0000') {
            // 決済失敗時の処理
            $app->addError('決済に失敗しました。');
            return $app->redirect($app->url('shopping_error'));
        }


        // 決済成功
        //$Order->setPaymentDate(new \DateTime());
        //$Order->setOrderStatus($this->app['eccube.repository.order_status']->find( $this->app['config']['order_pre_end'] ));
        //$this->app['orm.em']->persist($Order);
        //$this->app['orm.em']->flush();

        // カートをクリア
        $app['eccube.service.cart']->clear()->save();

        // Complete order
        $this->changeOrderData($Order);
        $this->app['session']->set('eccube.front.shopping.order.id', $orderId);// 本体の完了画面に受注IDを引き継ぐ
        $this->app['session']->set('eccube.plugin.bpmc_payment.order.id', $orderId);
        return $app->redirect($app['url_generator']->generate('shopping_complete'));
    }

    /**
     * GET
     * BPMC決済サーバーから決済結果のFookAPI Point
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fook_result(Application $app, Request $request)
    {
        $app['monolog.logger.bpmlinkpaymentgateway']->info('BpmLinkPaymentGatewayController.fook_result.START');

        $this->app = $app;
        //$requestData = $request->request->all();
        $httpMethod = $request->server->get('REQUEST_METHOD');
        //
        if($httpMethod == 'POST') {
            $requestData = $request->request->all();
        } else {
            $requestData = $request->query->all();    
        }
        
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/plain');
        //$response->setContent(json_encode($requestData));
        //return $response;

        if(!isset($requestData['shop_tracking'])) {
            $response->setContent('shop_tracking is requaired');
            return $response;
        }

        $preOrderId = $requestData['shop_tracking'];
        $Order = $app['eccube.repository.order']->findOneBy(array('pre_order_id' => $preOrderId));

        if (is_null($Order)) {
            $response->setContent('shop_tracking not found');
            return $response;
        }

        $this->prepareOrderData($Order, $app, $requestData, true);

        $orderId = $Order->getId();

        //決済エラー時は送信しない
        //$apiResult = $requestData['Result'];
        //if($apiResult != '1') {
        //    // 決済失敗
        //    $app['monolog.logger.bpmlinkpaymentgateway']->info('BpmLinkPaymentGatewayController.fook_result.決済失敗');
        //    $Order->setOrderStatus($this->app['eccube.repository.order_status']->find( $this->app['config']['order_cancel'] ));
        //    $this->app['orm.em']->persist($Order);
        //    $this->app['orm.em']->flush();
        //    $response->setContent('Failed payment');
        //    return $response;
        //}

        // 決済成功
        $Order->setPaymentDate(new \DateTime());
        $Order->setOrderStatus($this->app['eccube.repository.order_status']->find( $this->app['config']['order_pre_end'] ));
        $this->app['orm.em']->persist($Order);
        $this->app['orm.em']->flush();


        $shopData1 = $requestData['shop_data1'];
        $Customer = null;

        if(!empty($shopData1) && $shopData1 != "0") {
            $Customer = $app['eccube.repository.customer']->findOneBy(array('id' => $shopData1));
        }

        $this->changeOrderDataFromFook($app, $Order, $Customer);
        
        // 通知
        $this->sendOrderMail($Order);
        $response->setContent('OK');
        return $response;
    }

    /**
     * 支払い結果情報登録
     *
     * @param Order $Order
     * @param Application $app アプリケーション
     * @param Array $requestData リクエストデータ
     * @param Bool $callee_fook fook api経由の場合true
     */
    public function prepareOrderData($Order, $app, $requestData, $callee_fook = false)
    {
        $app[self::LOG_NS]->info('prepareOrderData.START');

        $orderId = $Order->getId();
        $paymentId = $Order->getPayment()->getId();
        $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find($paymentId);

        if( is_null($BpmcPayment) ){
            return;
        }


        $bpmcOrderPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcOrderPayment')->find($orderId);
        if( is_null($bpmcOrderPayment) ){
            $bpmcOrderPayment = new \Plugin\BpmLinkPaymentGateway\Entity\BpmcOrderPayment();
            $bpmcOrderPayment->setId($orderId);
        }

        if( $callee_fook ) {
            $bpmcOrderPayment->setJob( 'CAPTURE' );

            if(isset($requestData['amount']) && $requestData['amount'] != '0'){
                $paymentTotal = (int)trim($requestData['amount']);
                $bpmcOrderPayment->setAmount( $paymentTotal );
            }

            if( isset( $requestData['tran_code'] ) ){
                $bpmcOrderPayment->setTranId( $requestData['tran_code'] );    
            }
            $bpmcOrderPayment->setResult( 1 );
        }


        if( !$callee_fook ) {
            $bpmcOrderPayment->setShopTrakking( $requestData['shop_tracking'] );
            //if( isset( $requestData['ErrorCode'] ) ){
            //    $app['monolog.logger.bpmlinkpaymentgateway']->info('isset ErrorCode true => '.$requestData['ErrorCode'] );
            //    $bpmcOrderPayment->setErrorCode( $requestData['ErrorCode'] );
            //} else {
            //    $app['monolog.logger.bpmlinkpaymentgateway']->info('isset ErrorCode false');
            //}
            //if( isset( $requestData['ErrorMsg'] ) ){
            //    $bpmcOrderPayment->setErrorMsg( $requestData['ErrorMsg'] );
            //}
        }

        $app['orm.em']->persist($bpmcOrderPayment);
        $app['orm.em']->flush();

    }

    /**
     * /Resource/assets/以下の静的ファイルを出力
     */
    public function assets(Application $app, Request $request)
    {
        $this->app = $app;

        $assets_type = $request->get('assets_type');
        $assets_name = $request->get('assets_name');
        //$app['monolog.logger.bpmlinkpaymentgateway']->info($assets_type);
        

        $asset_path = $app['config']['plugin_realdir'] . '/BpmLinkPaymentGateway/Resource/assets/' . $assets_type . '/' . $assets_name;
        //$app['monolog.logger.bpmlinkpaymentgateway']->info($asset_path);

        if( !file_exists($asset_path) ) {
            $error_title = '404 File not found';
            $error_message = "URLをご確認ください";
            return $app['view']->render('error.twig', array('error_message' => $error_message, 'error_title'=> $error_title));
        }

        if($assets_type == 'img') {
            $response = new Response();
            $finfo = new \finfo(FILEINFO_MIME_TYPE);    
            $response->headers->set('Content-type', $finfo->file($asset_path));
            $response->sendHeaders();
            $response->setContent(readfile($asset_path));
        } else if($assets_type == 'js') {
            return new Response( 
                file_get_contents($asset_path),
                Response::HTTP_OK,
                array('content-type' => 'text/javascript') 
            );
        } else if($assets_type == 'css') {
            return new Response( 
                file_get_contents($asset_path),
                Response::HTTP_OK,
                array('content-type' => 'text/css') 
            );
        }

        return $response;
    }


    /**
     * BPMからの決済通知
     */
    public function changeOrderDataFromFook($app, $Order, $Customer = null){

        $this->app['session']->remove('formData');
        
        $em = $this->app['orm.em'];
        $em->getConnection()->beginTransaction();
        $Order->setOrderDate(new \DateTime());
        $orderService = $this->app['eccube.service.order'];
        $orderService->setStockUpdate($em, $Order);

        
        if (!is_null($Customer)) {
            // 会員の場合、購入金額を更新
            $orderService->setCustomerUpdate($em, $Order, $Customer);
        }

        $em->flush();
        $em->getConnection()->commit();


        // 配送業者・お届け時間の更新
        // $this->app['eccube.service.order']->setOrderUpdate関数
        $Shippings = $Order->getShippings();
        foreach ($Shippings as $Shipping) {
            if (!is_null($Shipping->getDeliveryTime())) {
                $Shipping->setShippingDeliveryTime($Shipping->getDeliveryTime()->getDeliveryTime());
            } else {
                $Shipping->setShippingDeliveryTime(null);
            }
            $app['orm.em']->persist($Shipping);
            $app['orm.em']->flush();
        }
        
    }

    /**
     * 受注状態
     */
    public function changeOrderData($Order){
        $this->app['session']->remove('formData');
        
        $em = $this->app['orm.em'];
        $em->getConnection()->beginTransaction();

        // [20180412] Comment
        /*
        $Order->setOrderDate(new \DateTime());
        $orderService = $this->app['eccube.service.order'];
        $orderService->setStockUpdate($em, $Order);
        if ($this->isGranted($this->app)) {
            // 会員の場合、購入金額を更新
            $orderService->setCustomerUpdate($em, $Order, $this->app->user());
        }
        */

        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            // 受注完了を他プラグインへ通知する.
            $this->app['eccube.service.shopping']->notifyComplete($Order);
        }

        $em->flush();
        $em->getConnection()->commit();
    }

    protected function isGranted($app) {
        if ($this->app['security']->isGranted('ROLE_USER')) {
            return true;
        }
        return false;
    }

    /**
     * Check product stock across eccube version.
     * @param type $app
     * @param type $Order
     */
    private function checkStockProduct($app, $Order){
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');
        $orderService = in_array(Constant::VERSION, $listOldVersion) ? $app['eccube.service.order'] : $app['eccube.service.shopping'];
        return $orderService->isOrderProduct($app['orm.em'], $Order);
    }

    private function sendOrderMail($Order)
    {
        if (version_compare(Constant::VERSION, '3.0.10', '>=')) {
            $this->app['eccube.service.shopping']->sendOrderMail($Order);
        } else {
            $this->app['eccube.service.mail']->sendOrderMail($Order);
        }
    }
}
