<?php

namespace Plugin\BpmLinkPaymentGateway\Controller\Util;

/**
 * 決済モジュール基本クラス
 */
class PluginUtil
{

    private $app;

    public function __construct(\Eccube\Application $app)
    {
        $this->app = $app;
    }

    /** サブデータを保持する変数 */
    var $subData = null;

    /** モジュール情報 */
    var $pluginInfo = array(
        'paymentName' => 'BPMペイメントサービス',
        'pluginName' => 'BPMペイメントサービス決済モジュール',
        'pluginCode' => 'BpmLinkPaymentGateway',
        'pluginVersion' => '1.0.0',
    );
    private $pluginCode;

    static function &getInstance($app)
    {
        static $paymentUtil;
        if (empty($paymentUtil)) {
            $paymentUtil = new PluginUtil($app);
        }
        $paymentUtil->init();
        return $paymentUtil;
    }

    function init()
    {
        foreach ($this->pluginInfo as $k => $v) {
            $this->$k = $v;
        }

    }

    /**
     * 終了処理.
     */
    function destroy()
    {
    }

    /**
     * モジュール表示用名称を取得する
     *
     * @return string
     */
    function getName()
    {
        return $this->pluginName;
    }

    /**
     * 支払い方法名(決済モジュールの場合のみ)
     *
     * @return string
     */
    function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * モジュールコードを取得する
     *
     * @param boolean $toLower trueの場合は小文字へ変換する.デフォルトはfalse.
     * @return string
     */
    function getCode($toLower = false)
    {
        $pluginCode = $this->pluginCode;
        return $pluginCode;
    }

    /**
     * モジュールバージョンを取得する
     *
     * @return string
     */
    function getVersion()
    {
        return $this->pluginVersion;
    }


    /**
     * 支払い方法一覧を取得する
     *
     * @return array
     */
    function getPayments(){
      return array(
        $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PAYID_CREDIT_LINK'] => $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PAYNAME_CREDIT_LINK'],
      );
    }


    /**
     * クレジット決済の最小可能決済金額を取得
     *
     * @return array
     */
    function getRuleMin(){
      return $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PAYNAME_CREDIT_ROLE_MIN'];
    }

    /**
     * クレジット決済の最小可能決済金額を取得
     *
     * @return array
     */
    function getRuleMax(){
      return $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PAYNAME_CREDIT_ROLE_MAX'];
    }

    function getJob(){
      return $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PARAM_JOB'];
    }

    function getRetType(){
      return $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PARAM_RET_TYPE'];
      
    }

    function getItemType(){
      return $this->app['config']['BpmLinkPaymentGateway']['const']['PG_PARAM_ITEM_TYPE'];
    }

    function getLinkApiUrl(){
        return $this->app['config']['BpmLinkPaymentGateway']['const']['PG_LINK_API_URL'];   
    }

    function getLinkApiUrlByApiToken($api_token) {
        $url_fmt = $this->app['config']['BpmLinkPaymentGateway']['const']['PG_LINK_API_URL'];
        return sprintf($url_fmt, $api_token);
        
    }

    function getAbsUrlRoot(){
        return (empty($_SERVER["HTTPS"]) ? "http://" : "https://") . $_SERVER["HTTP_HOST"];
    }



}
