<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Proto;

use Bip70\Protobuf\Proto\Output;
use Bip70\Protobuf\Proto\Payment;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testRefundTo()
    {
        $output = new Output();
        $output->setAmount(123123);
        $output->setScript(hex2bin("76a914536ffa992491508dca0354e52f32a3a7a679a53a88ac"));

        $payment = new Payment();
        $payment->setRefundTo($output, 0);

        $serialized = $payment->serialize();
        $p1 = new Payment();
        $p1->parse($serialized);

        $this->assertEquals(1, count($p1->getRefundToList()));
        $this->assertEquals($output->getAmount(), $p1->getRefundTo(0)->getAmount());
        $this->assertEquals($output->getScript(), $p1->getRefundTo(0)->getScript());

        $payment->clearRefundTo();

        $this->assertEquals(0, count($payment->getRefundToList()));
    }

    public function testTransactions()
    {
        $tx1 = hex2bin("02000000019c2e2676241c5291f627efb7730039d60c77211e170590490282329db50a0901010000006b483045022100a839e79124f24795a7d73c655e104ad086dfa5939c39e8e5dc4a2a199eb8587d02204f5349dde69d51a858db163c8dc5ec45ddc5f8ff1e61cd1b96c153c08bc15c3201210350f333bee5c1effa176f5af10ab543ff24c61154f4a187dc65b7fc448fefa4ebffffffff021af25400000000001976a914b64124373f1406cf641330b7f7fd193f73a212c088acc5243003000000001976a9148cb1c9b6ae120fab9785cb17cff15d6c79651a0588ac00000000");
        $tx2 = hex2bin("02000000000101afe1370451607754c99ed336dd1590f92ab901598036f490c47029ebb3214fdb0000000017160014f893cb68bf16d5dbd9cf7d4c9d5b020030b32697feffffff02117fa3000000000017a914245f980e8c56ba2104090b0fb4aece1ebe7b0f378776dd1900000000001976a9144511abad64398e34b0f8abf003112d69d45c40d088ac02473044022039ec1b761f926a9d850928c651a248f79189ae4d706057e90678ec5dc5b62455022063e64eb6be0236c217ca59df22dd89503af7fcc14a180630550aa7c3e8d6b9f0012103b4197036263319d0d61f7e208345277e94392078560084aeb92de90baf86c34e00000000");
        $payment = new Payment();
        $this->assertFalse($payment->hasTransactions());
        $payment->setTransactions($tx1, 0);
        $this->assertTrue($payment->hasTransactions());

        $serialized = $payment->serialize();
        $p1 = new Payment();
        $p1->parse($serialized);

        $this->assertEquals(1, count($p1->getTransactionsList()));
        $this->assertEquals($tx1, $p1->getTransactions(0));

        $payment->setTransactions($tx2, 1);

        $serialized = $payment->serialize();
        $p1 = new Payment();
        $p1->parse($serialized);

        $this->assertEquals(2, count($p1->getTransactionsList()));
        $this->assertEquals($tx1, $p1->getTransactions(0));
        $this->assertEquals($tx2, $p1->getTransactions(1));
    }

    public function testMerchantData()
    {
        $blob = json_encode([
            "some_merchant" => "abcd1234",
            "invoice" => "1234abcd"
        ]);

        $payment = new Payment();

        $this->assertFalse($payment->hasMerchantData());
        $payment->setMerchantData($blob);
        $this->assertTrue($payment->hasMerchantData());

        $serialized = $payment->serialize();
        $p1 = new Payment();
        $p1->parse($serialized);

        $this->assertEquals($blob, $p1->getMerchantData());
    }

    public function testMemo()
    {
        $blob = "So long, and thanks for all the pizza!";

        $payment = new Payment();

        $this->assertFalse($payment->hasMemo());
        $payment->setMemo($blob);
        $this->assertTrue($payment->hasMemo());

        $serialized = $payment->serialize();
        $p1 = new Payment();
        $p1->parse($serialized);

        $this->assertEquals($blob, $p1->getMemo());
    }
}
