<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Proto;

use Bip70\Protobuf\Proto\Payment;
use Bip70\Protobuf\Proto\PaymentACK;
use PHPUnit\Framework\TestCase;

class PaymentACKTest extends TestCase
{
    public function testMemo()
    {
        $blob = "So long, and thanks for all the pizza!";

        $ack = new PaymentACK();
        $ack->setPayment(new Payment());
        $this->assertFalse($ack->hasMemo());
        $ack->setMemo($blob);
        $this->assertTrue($ack->hasMemo());

        $serialized = $ack->serialize();
        $p1 = new PaymentACK();
        $p1->parse($serialized);

        $this->assertEquals($blob, $p1->getMemo());

        $ack->clearMemo();
        $this->assertFalse($ack->hasMemo());
    }

    public function testPayment()
    {
        $tx1 = "abcd";
        $payment = new Payment();
        $payment->addTransactions($tx1);

        $ack = new PaymentACK();
        $this->assertFalse($ack->hasPayment());
        $ack->setPayment($payment);
        $this->assertTrue($ack->hasPayment());

        $serialized = $ack->serialize();
        $p1 = new PaymentACK();
        $p1->parse($serialized);

        $this->assertTrue($p1->hasPayment());
        $this->assertTrue($p1->getPayment()->hasTransactions());
        $this->assertEquals($tx1, $p1->getPayment()->getTransactions(0));

        $ack->clearPayment();
        $this->assertFalse($ack->hasPayment());
    }
}
