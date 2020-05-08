<?php

namespace Plugin\BpmLinkPaymentGateway\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BpmcPaymentMethod
 */
class BpmcPaymentMethod extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $method;

    /**
     * @var integer
     */
    private $del_flg;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var string
     */
    private $job;

    /**
     * @var integer
     */
    private $ret_type;

    /**
     * @var string
     */
    private $ret_url;

    /**
     * @var string
     */
    private $ret_cancel_url;

    /**
     * @var integer
     */
    private $item_type;

    /**
     * @var integer
     */
    private $payment_type;

    /**
     * @var string
     */
    private $code;


    /**
     * Set id
     *
     * @param integer $id
     * @return BpmcPaymentMethod
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set method
     *
     * @param string $method
     * @return BpmcPaymentMethod
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get method
     *
     * @return string 
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return BpmcPaymentMethod
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    /**
     * Get del_flg
     *
     * @return integer 
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return BpmcPaymentMethod
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set update_date
     *
     * @param \DateTime $updateDate
     * @return BpmcPaymentMethod
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set job
     *
     * @param string $job
     * @return BpmcPaymentMethod
     */
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * Get job
     *
     * @return string 
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set ret_type
     *
     * @param integer $retType
     * @return BpmcPaymentMethod
     */
    public function setRetType($retType)
    {
        $this->ret_type = $retType;

        return $this;
    }

    /**
     * Get ret_type
     *
     * @return integer 
     */
    public function getRetType()
    {
        return $this->ret_type;
    }

    /**
     * Set ret_url
     *
     * @param string $retUrl
     * @return BpmcPaymentMethod
     */
    public function setRetUrl($retUrl)
    {
        $this->ret_url = $retUrl;

        return $this;
    }

    /**
     * Get ret_url
     *
     * @return string 
     */
    public function getRetUrl()
    {
        return $this->ret_url;
    }

    /**
     * Set ret_cancel_url
     *
     * @param string $retCancelUrl
     * @return BpmcPaymentMethod
     */
    public function setRetCancelUrl($retCancelUrl)
    {
        $this->ret_cancel_url = $retCancelUrl;

        return $this;
    }

    /**
     * Get ret_cancel_url
     *
     * @return string 
     */
    public function getRetCancelUrl()
    {
        return $this->ret_cancel_url;
    }

    /**
     * Set item_type
     *
     * @param integer $itemType
     * @return BpmcPaymentMethod
     */
    public function setItemType($itemType)
    {
        $this->item_type = $itemType;

        return $this;
    }

    /**
     * Get item_type
     *
     * @return integer 
     */
    public function getItemType()
    {
        return $this->item_type;
    }

    /**
     * Set payment_type
     *
     * @param integer $paymentType
     * @return BpmcPaymentMethod
     */
    public function setPaymentType($paymentType)
    {
        $this->payment_type = $paymentType;

        return $this;
    }

    /**
     * Get payment_type
     *
     * @return integer 
     */
    public function getPaymentType()
    {
        return $this->payment_type;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return BpmcPaymentMethod
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }
}
