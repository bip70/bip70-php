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
    }
}
