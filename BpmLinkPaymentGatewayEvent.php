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
use Eccube\Event\EventArgs;
use Eccube\Common\Constant;
use Eccube\Util\EntityUtil;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class BpmLinkPaymentGatewayEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    /**
     * BpmLinkPaymentGatewayEvent constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * ご注文内容のご確認ページBefore
     * @param FilterResponseEvent $event
     */
    public function onRenderShoppingBefore(FilterResponseEvent $event)
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onRenderShoppingBefore.START');

        $pre_order_id = $this->app['eccube.service.cart']->getPreOrderId();
        $Order = $this->app['eccube.repository.order']->findOneBy( array('pre_order_id' => $pre_order_id) );
        $this->app->addInfo('You got a new message!');
        if (!is_null($Order)) {

            $Payment = $Order->getPayment();
            $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find($Payment->getId());

            if(!is_null($BpmcPayment)) {
                // Get request
                $request = $event->getRequest();
                // Get response
                $response = $event->getResponse();
                $html = $this->getHtmlShoppingConfirm($request, $response, $Payment);
                // Set content for response
                $response->setContent($html);
                $event->setResponse($response);
            }
        }

        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onRenderShoppingBefore.END');
    }

    /**
     * ご注文内容のご確認ページHTML
     */
    public function getHtmlShoppingConfirm(Request $request, Response $response, $Payment){
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('getHtmlShoppingConfirm.START');
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        $newMethod = $Payment->getMethod() . 'へ';
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');

        try {

            if (in_array(Constant::VERSION, $listOldVersion)) {
                $oldMethod = $crawler->filter('.btn.btn-primary.btn-block')->html();
            }else{
                $oldMethod = $crawler->filter('#order-button')->html();
            }
            $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find($Payment->getId());
            $html = str_replace($oldMethod, $newMethod, $html);

        } catch (\InvalidArgumentException $e) {
            $this->app['monolog.logger.bpmlinkpaymentgateway']->info($e->getTraceAsString());            
        }

        return $html;
    }

    /**
     * ご注文内容のご確認をした後、クレジット決済ページへリダイレクト
     */
    public function onControllerShoppingConfirmBefore($event = null)
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onControllerShoppingConfirmBefore.START');

        $Order = $this->app['eccube.repository.order']->findOneBy(array('pre_order_id' => $this->app['eccube.service.cart']->getPreOrderId()));
        $listOldVersion = array('3.0.1', '3.0.2', '3.0.3', '3.0.4');
        if (in_array(Constant::VERSION, $listOldVersion)) {
            $form = $this->app['form.factory']->createBuilder('shopping')->getForm();
        } else {
            $form = $this->app['eccube.service.shopping']->getShippingForm($Order);
        }

        if ('POST' === $this->app['request']->getMethod()) {
            $form->handleRequest($this->app['request']);
            if ($form->isValid()) {
                $formData = $form->getData();

                $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find( $formData['payment']->getId() );
                if (in_array(Constant::VERSION, $listOldVersion)) {
                    $this->app['session']->set('gmo_payment_formData', $formData);
                }
                if (!is_null($BpmcPayment)) {

                    // [v1.0.5 / 20180712] BUG fix: お問い合わせ内容保存処理の追加
                    $Order->setMessage($formData['message']);
                    $Order->setOrderStatus($this->app['eccube.repository.order_status']->find($this->app['config']['order_processing']));
                    $this->app['orm.em']->persist($Order);
                    $this->app['orm.em']->flush();

                    $url = $this->app->url('plugin_BpmLinkPaymentGateway_bridge');
                    if ($event instanceof \Symfony\Component\HttpKernel\Event\KernelEvent) {
                        $response = $this->app->redirect($url);
                        $event->setResponse($response);
                        return;
                    } else {
                        header("Location: " . $url);
                        exit;
                    }
                }

            }
        }

    }

    /**
     * 注文完了
     * @param FilterResponseEvent $event
     */
    public function onRenderShoppingCompleteBefore(FilterResponseEvent $event)
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onRenderShoppingCompleteBefore.START');

        //$this->app['session']->set('eccube.plugin.bpmc_payment.order.id', $orderId);
        $orderId = $this->app['session']->get('eccube.plugin.bpmc_payment.order.id');

        if ($orderId == null) {
            return;
        }
        $orderRep = $this->app['orm.em']->getRepository('\Eccube\Entity\Order');
        $Order = $orderRep->findOneBy(array('id' => $orderId));

        if ($Order != null) {

            $paymentId = $Order->getPayment()->getId();
            $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find($paymentId);
            if(is_null($BpmcPayment)){
                return;
            }
        }

        // Get request
        $request = $event->getRequest();
        // Get response
        $response = $event->getResponse();
        // Find dom and add extension template
        $html = $this->getHTMLShoppingComplete($request, $response, $orderId);
        if(is_null($html)) {
            return;
        }

        // Set content for response
        $response->setContent($html);
        $event->setResponse($response);


        $this->app['session']->set('eccube.plugin.bpmc_payment.order.id', null);
    }

    /**
     * Find and add extension template to response.
     * @param FilterResponseEvent $event
     * @return type
     */
    public function getHTMLShoppingComplete(Request $request, Response $response, $orderId){
        $crawler = new Crawler($response->getContent());
        $html = $this->getHtml($crawler);
        
        return $html;   //

        $bpmcOrderPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcOrderPayment')->find($orderId);

        if(is_null($bpmcOrderPayment)){
            return null;
        }

        $insert = $this->app->render('BpmLinkPaymentGateway/Resource/template/bpmc_payment_result.twig', array(
            'bpmcOrderPayment' => $bpmcOrderPayment,
        ));

        try {
            $oldHtml = $crawler->filter('#deliveradd_input > div > div')->html();
            $newHtml = $oldHtml . $insert;

            $html = str_replace($oldHtml, $newHtml, $html);
        } catch (\InvalidArgumentException $e) {
            $this->app['monolog.logger.bpmlinkpaymentgateway']->info($e->getTraceAsString());
        }
        return $html;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminSettingShopPaymentEditBefore(FilterResponseEvent $event)
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onRenderAdminSettingShopPaymentEditBefore.START');
    }

    /**
     *
     */
    public function onControllerAdminSettingShopPaymentEditAfter()
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onControllerAdminSettingShopPaymentEditAfter.START');
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {
            $paymentId = $this->app['request']->get('id');
            
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminOrderEditBefore(FilterResponseEvent $event)
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onRenderAdminOrderEditBefore.START');
        // Check admin login
        if ($this->app['security']->isGranted('ROLE_ADMIN')) {

            $orderId = $this->app['request']->get('id');
            $BpmcOrderPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcOrderPayment')->find($orderId);
            $Payment = $this->app['eccube.repository.order']->findOneBy(array('id' => $orderId))->getPayment();

            if(is_null($BpmcOrderPayment) || EntityUtil::isEmpty($Payment)) {
                return;
            }
            $BpmcPayment = $this->app['orm.em']->getRepository('\Plugin\BpmLinkPaymentGateway\Entity\BpmcPaymentMethod')->find($Payment->getId());
            if(is_null($BpmcPayment)){
                return;
            }

            $json_data = array(
                'tran_id' => $BpmcOrderPayment->getTranId(),
                'amount' => $BpmcOrderPayment->getAmount(),
                'result' => $BpmcOrderPayment->getResult(),
                'error_code' => $BpmcOrderPayment->getErrorCode(),
                'error_msg' => $BpmcOrderPayment->getErrorMsg(),
            );

            $source = $event->getResponse()->getContent();
            $jqueryCode = '
<script type="text/javascript" id="bpmc-payment-script">
    (function(window, $){
        var _bpmc_order_payment = '.json_encode($json_data).';
        $(function(){
            var box = $("#payment_info_box");
            var boxBody = $(".box-body", box);
            var dl = $("dl.dl-horizontal", boxBody);
            var buf = "";

            buf += (_bpmc_order_payment.result === 1) ? "成功" : "<span style=\"color:red;\">失敗";
            if(_bpmc_order_payment.error_code) {
                buf += "<br/>";
                buf += "[" + _bpmc_order_payment.error_code + "] " + _bpmc_order_payment.error_msg;
            }
            buf += "</span>";
            dl.append(\'<dt>決済結果</dt>\');
            dl.append(\'<dd class="form-group form-inline">\' + buf + \'</dd>\');

            dl.append(\'<dt>承認番号</dt>\');
            dl.append(\'<dd class="form-group form-inline">\' + _bpmc_order_payment.tran_id + \'</dd>\');

            if(!_bpmc_order_payment.amount) _bpmc_order_payment.amount = 0;
            dl.append(\'<dt>決済金額</dt>\');
            dl.append(\'<dd class="form-group form-inline">￥ \' + _bpmc_order_payment.amount.toLocaleString() + \'</dd>\');

            console.log(_bpmc_order_payment);
        });
    })(window, jQuery);
</script>
            ';

            $source .= $jqueryCode;

            $request = $event->getRequest();
            $response = $event->getResponse();

            $crawler = new Crawler($source);
            $html = $this->getHtml($crawler);
            $response->setContent($html);
            $event->setResponse($response);
        }
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminOrderNewBefore(FilterResponseEvent $event)
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onRenderAdminOrderNewBefore.START');
    }

    /**
     *
     */
    public function onControllerAdminOrderEditAfter()
    {
        $this->app['monolog.logger.bpmlinkpaymentgateway']->info('onControllerAdminOrderEditAfter.START');
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderAdminCustomerEditBefore(FilterResponseEvent $event)
    {
        // Check admin login
        if (!$this->app['security']->isGranted('ROLE_ADMIN')) {
            return;
        }
        // Get customer id from request
        $customerId = $this->app['request']->get('id');
        if (!isset($customerId)) {
            return;
        }
    }

    /**
     *
     */
    public function onControllerAdminSettingShopPaymentDeleteBefore()
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageChangeBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageChangeCompleteBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageDeliveryBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageDeliveryNewBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageDeliveryEditBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageFavoriteBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageHistoryBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageOrderBefore(FilterResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onRenderMypageWithdrawBefore(FilterResponseEvent $event)
    {
    }

    /**
     * 解析用HTMLを取得
     *
     * @param Crawler $crawler
     * @return string
     */
    public static function getHtml(Crawler $crawler){
        $html = '';
        foreach ($crawler as $domElement) {
            $domElement->ownerDocument->formatOutput = true;
            $html .= $domElement->ownerDocument->saveHTML();
        }
        return BpmLinkPaymentGatewayEvent::my_html_entity_decode($html);
    }

    /**
     * HTMLエンティティに変換されたUTF-8文字列と円記号に関してのみデコードする
     *
     * @param string $html
     * @return string
     */
    public static function my_html_entity_decode($html) {
        $result = preg_replace_callback
            ("/(&#[0-9]+;|&yen;)/",
             function ($m) {
                return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
             },
             $html);
        return $result;
    }

}
