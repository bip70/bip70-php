<?php

namespace Bip70\Protobuf\Proto;

use DrSlump\Protobuf\Message;

class Payment extends Message
{

    /**  @var string */
    public $merchant_data;

    /**  @var string[] */
    public $transactions = array();

    /**  @var \Bip70\Protobuf\Proto\Output[] */
    public $refund_to = array();

    /**  @var string */
    public $memo;


    /** @var \Closure[] */
    protected static $__extensions = array();

    public static function descriptor()
    {
        $descriptor = new \DrSlump\Protobuf\Descriptor(__CLASS__, 'payments.Payment');

        // OPTIONAL BYTES merchant_data = 1
        $f = new \DrSlump\Protobuf\Field();
        $f->number = 1;
        $f->name = 'merchant_data';
        $f->type = \DrSlump\Protobuf::TYPE_BYTES;
        $f->rule = \DrSlump\Protobuf::RULE_OPTIONAL;
        $descriptor->addField($f);

        // REPEATED BYTES transactions = 2
        $f = new \DrSlump\Protobuf\Field();
        $f->number = 2;
        $f->name = 'transactions';
        $f->type = \DrSlump\Protobuf::TYPE_BYTES;
        $f->rule = \DrSlump\Protobuf::RULE_REPEATED;
        $descriptor->addField($f);

        // REPEATED MESSAGE refund_to = 3
        $f = new \DrSlump\Protobuf\Field();
        $f->number = 3;
        $f->name = 'refund_to';
        $f->type = \DrSlump\Protobuf::TYPE_MESSAGE;
        $f->rule = \DrSlump\Protobuf::RULE_REPEATED;
        $f->reference = Output::class;
        $descriptor->addField($f);

        // OPTIONAL STRING memo = 4
        $f = new \DrSlump\Protobuf\Field();
        $f->number = 4;
        $f->name = 'memo';
        $f->type = \DrSlump\Protobuf::TYPE_STRING;
        $f->rule = \DrSlump\Protobuf::RULE_OPTIONAL;
        $descriptor->addField($f);

        foreach (self::$__extensions as $cb) {
            $descriptor->addField($cb(), true);
        }

        return $descriptor;
    }

    /**
     * Check if <merchant_data> has a value
     *
     * @return boolean
     */
    public function hasMerchantData()
    {
        return $this->_has(1);
    }

    /**
     * Clear <merchant_data> value
     *
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function clearMerchantData()
    {
        return $this->_clear(1);
    }

    /**
     * Get <merchant_data> value
     *
     * @return string
     */
    public function getMerchantData()
    {
        return $this->_get(1);
    }

    /**
     * Set <merchant_data> value
     *
     * @param string $value
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function setMerchantData($value)
    {
        return $this->_set(1, $value);
    }

    /**
     * Check if <transactions> has a value
     *
     * @return boolean
     */
    public function hasTransactions()
    {
        return $this->_has(2);
    }

    /**
     * Clear <transactions> value
     *
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function clearTransactions()
    {
        return $this->_clear(2);
    }

    /**
     * Get <transactions> value
     *
     * @param int $idx
     * @return string
     */
    public function getTransactions($idx = null)
    {
        return $this->_get(2, $idx);
    }

    /**
     * Set <transactions> value
     *
     * @param string $value
     * @param int $idx
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function setTransactions($value, $idx = null)
    {
        return $this->_set(2, $value, $idx);
    }

    /**
     * Get all elements of <transactions>
     *
     * @return string[]
     */
    public function getTransactionsList()
    {
        return $this->_get(2);
    }

    /**
     * Add a new element to <transactions>
     *
     * @param string $value
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function addTransactions($value)
    {
        return $this->_add(2, $value);
    }

    /**
     * Check if <refund_to> has a value
     *
     * @return boolean
     */
    public function hasRefundTo()
    {
        return $this->_has(3);
    }

    /**
     * Clear <refund_to> value
     *
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function clearRefundTo()
    {
        return $this->_clear(3);
    }

    /**
     * Get <refund_to> value
     *
     * @param int $idx
     * @return \Bip70\Protobuf\Proto\Output
     */
    public function getRefundTo($idx = null)
    {
        return $this->_get(3, $idx);
    }

    /**
     * Set <refund_to> value
     *
     * @param \Bip70\Protobuf\Proto\Output $value
     * @param int $idx
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function setRefundTo(\Bip70\Protobuf\Proto\Output $value, $idx = null)
    {
        return $this->_set(3, $value, $idx);
    }

    /**
     * Get all elements of <refund_to>
     *
     * @return \Bip70\Protobuf\Proto\Output[]
     */
    public function getRefundToList()
    {
        return $this->_get(3);
    }

    /**
     * Add a new element to <refund_to>
     *
     * @param \Bip70\Protobuf\Proto\Output $value
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function addRefundTo(\Bip70\Protobuf\Proto\Output $value)
    {
        return $this->_add(3, $value);
    }

    /**
     * Check if <memo> has a value
     *
     * @return boolean
     */
    public function hasMemo()
    {
        return $this->_has(4);
    }

    /**
     * Clear <memo> value
     *
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function clearMemo()
    {
        return $this->_clear(4);
    }

    /**
     * Get <memo> value
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->_get(4);
    }

    /**
     * Set <memo> value
     *
     * @param string $value
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function setMemo($value)
    {
        return $this->_set(4, $value);
    }
}
