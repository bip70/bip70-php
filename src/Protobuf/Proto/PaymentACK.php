<?php

declare(strict_types=1);

namespace Bip70\Protobuf\Proto;

class PaymentACK extends \DrSlump\Protobuf\Message
{

    /**  @var \Bip70\Protobuf\Proto\Payment */
    public $payment;

    /**  @var string */
    public $memo;


    /** @var \Closure[] */
    protected static $__extensions = array();

    public static function descriptor()
    {
        $descriptor = new \DrSlump\Protobuf\Descriptor(__CLASS__, 'payments.PaymentACK');

        // REQUIRED MESSAGE payment = 1
        $f = new \DrSlump\Protobuf\Field();
        $f->number = 1;
        $f->name = 'payment';
        $f->type = \DrSlump\Protobuf::TYPE_MESSAGE;
        $f->rule = \DrSlump\Protobuf::RULE_REQUIRED;
        $f->reference = Payment::class;
        $descriptor->addField($f);

        // OPTIONAL STRING memo = 2
        $f = new \DrSlump\Protobuf\Field();
        $f->number = 2;
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
     * Check if <payment> has a value
     *
     * @return boolean
     */
    public function hasPayment()
    {
        return $this->_has(1);
    }

    /**
     * Clear <payment> value
     *
     * @return \Bip70\Protobuf\Proto\PaymentACK
     */
    public function clearPayment()
    {
        return $this->_clear(1);
    }

    /**
     * Get <payment> value
     *
     * @return \Bip70\Protobuf\Proto\Payment
     */
    public function getPayment()
    {
        return $this->_get(1);
    }

    /**
     * Set <payment> value
     *
     * @param \Bip70\Protobuf\Proto\Payment $value
     * @return \Bip70\Protobuf\Proto\PaymentACK
     */
    public function setPayment(\Bip70\Protobuf\Proto\Payment $value)
    {
        return $this->_set(1, $value);
    }

    /**
     * Check if <memo> has a value
     *
     * @return boolean
     */
    public function hasMemo()
    {
        return $this->_has(2);
    }

    /**
     * Clear <memo> value
     *
     * @return \Bip70\Protobuf\Proto\PaymentACK
     */
    public function clearMemo()
    {
        return $this->_clear(2);
    }

    /**
     * Get <memo> value
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->_get(2);
    }

    /**
     * Set <memo> value
     *
     * @param string $value
     * @return \Bip70\Protobuf\Proto\PaymentACK
     */
    public function setMemo($value)
    {
        return $this->_set(2, $value);
    }
}
