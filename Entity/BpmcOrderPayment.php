<?php

namespace Plugin\BpmLinkPaymentGateway\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BpmcOrderPayment
 */
class BpmcOrderPayment extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $shop_trakking;

    /**
     * @var string
     */
    private $job;

    /**
     * @var integer
     */
    private $amount;

    /**
     * @var string
     */
    private $tran_id;

    /**
     * @var integer
     */
    private $result;

    /**
     * @var string
     */
    private $error_code;

    /**
     * @var string
     */
    private $error_msg;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;


    /**
     * Set id
     *
     * @param integer $id
     * @return BpmcOrderPayment
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
     * Set shop_trakking
     *
     * @param string $shop_trakking
     * @return BpmcOrderPayment
     */
    public function setShopTrakking($shop_trakking)
    {
        $this->shop_trakking = $shop_trakking;

        return $this;
    }

    /**
     * Get shop_trakking
     *
     * @return string 
     */
    public function getShopTrakking()
    {
        return $this->shop_trakking;
    }

    /**
     * Set job
     *
     * @param string $job
     * @return BpmcOrderPayment
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
     * Set amount
     *
     * @param integer $amount
     * @return BpmcOrderPayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set tran_id
     *
     * @param string $tranId
     * @return BpmcOrderPayment
     */
    public function setTranId($tranId)
    {
        $this->tran_id = $tranId;

        return $this;
    }

    /**
     * Get tran_id
     *
     * @return string 
     */
    public function getTranId()
    {
        return $this->tran_id;
    }

    /**
     * Set result
     *
     * @param integer $result
     * @return BpmcOrderPayment
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return integer 
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set error_code
     *
     * @param string $errorCode
     * @return BpmcOrderPayment
     */
    public function setErrorCode($errorCode)
    {
        $this->error_code = $errorCode;

        return $this;
    }

    /**
     * Get error_code
     *
     * @return string 
     */
    public function getErrorCode()
    {
        return $this->error_code;
    }

    /**
     * Set error_msg
     *
     * @param string $errorMsg
     * @return BpmcOrderPayment
     */
    public function setErrorMsg($errorMsg)
    {
        $this->error_msg = $errorMsg;

        return $this;
    }

    /**
     * Get error_msg
     *
     * @return string 
     */
    public function getErrorMsg()
    {
        return $this->error_msg;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return BpmcOrderPayment
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
     * @return BpmcOrderPayment
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
}
