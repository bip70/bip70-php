<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Proto;

use Bip70\Protobuf\Proto\Output;
use Bip70\Protobuf\Proto\PaymentDetails;
use PHPUnit\Framework\TestCase;

class PaymentDetailsTest extends TestCase
{
    public function testEmptyDetails()
    {
        $now = time();
        $details = new PaymentDetails();
        $details->setTime($now);

        $binary = $details->serialize();

        $parsed = new PaymentDetails();
        $parsed->parse($binary);

        $this->assertEquals($details->hasTime(), $parsed->hasTime());
        $this->assertEquals($details->getTime(), $parsed->getTime());

    }

    public function testTime()
    {
        $now = time();
        $details = new PaymentDetails();

        $this->assertFalse($details->hasTime());
        $details->setTime($now);
        $this->assertTrue($details->hasTime());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($now, $details->getTime());
        $this->assertEquals($details->hasTime(), $parsed->hasTime());
        $this->assertEquals($details->getTime(), $parsed->getTime());

        $details->clearTime();
        $this->assertFalse($details->hasTime());
    }

    public function testExpires()
    {
        $now = time();
        $expire = time() + 900;
        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertFalse($details->hasExpires());
        $details->setExpires($expire);
        $this->assertTrue($details->hasExpires());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($expire, $details->getExpires());
        $this->assertEquals($details->hasExpires(), $parsed->hasExpires());
        $this->assertEquals($details->getExpires(), $parsed->getExpires());

        $details->clearExpires();
        $this->assertFalse($details->hasExpires());
    }

    public function testMerchantData()
    {
        $now = time();
        $merchantData = "abcd1234";
        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertFalse($details->hasMerchantData());
        $details->setMerchantData($merchantData);
        $this->assertTrue($details->hasMerchantData());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($merchantData, $details->getMerchantData());
        $this->assertEquals($details->hasMerchantData(), $parsed->hasMerchantData());
        $this->assertEquals($details->getMerchantData(), $parsed->getMerchantData());

        $details->clearMerchantData();
        $this->assertFalse($details->hasMerchantData());
    }

    public function testPaymentUrl()
    {
        $now = time();
        $paymentUrl = "https://website.com/payment?id=123";
        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertFalse($details->hasPaymentUrl());
        $details->setPaymentUrl($paymentUrl);
        $this->assertTrue($details->hasPaymentUrl());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($paymentUrl, $details->getPaymentUrl());
        $this->assertEquals($details->hasPaymentUrl(), $parsed->hasPaymentUrl());
        $this->assertEquals($details->getPaymentUrl(), $parsed->getPaymentUrl());

        $details->clearPaymentUrl();
        $this->assertFalse($details->hasPaymentUrl());
    }

    public function testMemo()
    {
        $now = time();
        $memo = "abcd1234";
        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertFalse($details->hasMemo());
        $details->setMemo($memo);
        $this->assertTrue($details->hasMemo());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($memo, $details->getMemo());
        $this->assertEquals($details->hasMemo(), $parsed->hasMemo());
        $this->assertEquals($details->getMemo(), $parsed->getMemo());

        $details->clearMemo();
        $this->assertFalse($details->hasMemo());
    }

    public function testNetwork()
    {
        $now = time();
        $network = "test";
        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertTrue($details->hasNetwork());
        $this->assertEquals("main", $details->getNetwork());

        $details->setNetwork($network);
        $this->assertTrue($details->hasNetwork());
        $this->assertEquals($network, $details->getNetwork());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($details->hasNetwork(), $parsed->hasNetwork());
        $this->assertEquals($details->getNetwork(), $parsed->getNetwork());

        $details->clearNetwork();
        $this->assertFalse($details->hasNetwork());
    }

    public function testOutput()
    {
        $now = time();
        $output = new Output();
        $output->setAmount(123123123);
        $output->setScript("p2pkh....");

        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertFalse($details->hasOutputs());
        $details->setOutputs($output, 0);
        $this->assertTrue($details->hasOutputs());

        $this->assertCount(1, $details->getOutputsList());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($output->getAmount(), $details->getOutputs(0)->getAmount());
        $this->assertEquals($output->getScript(), $details->getOutputs(0)->getScript());

        $this->assertTrue($parsed->hasOutputs());
        $this->assertEquals($output->getAmount(), $parsed->getOutputs(0)->getAmount());
        $this->assertEquals($output->getScript(), $parsed->getOutputs(0)->getScript());

        $details->clearOutputs();
        $this->assertFalse($details->hasOutputs());
    }


    public function testOutputs()
    {
        $now = time();
        $output1 = new Output();
        $output1->setAmount(123123123);
        $output1->setScript("p2pkh....");

        $output2 = new Output();
        $output2->setAmount(424242);
        $output2->setScript("a.different.p2pkh....");

        $details = new PaymentDetails();
        $details->setTime($now);

        $this->assertFalse($details->hasOutputs());
        $details->setOutputs($output1, 0);
        $details->setOutputs($output2, 1);
        $this->assertTrue($details->hasOutputs());

        $this->assertCount(2, $details->getOutputsList());

        $parsed = new PaymentDetails();
        $parsed->parse($details->serialize());

        $this->assertEquals($output1->getAmount(), $details->getOutputs(0)->getAmount());
        $this->assertEquals($output1->getScript(), $details->getOutputs(0)->getScript());
        $this->assertEquals($output2->getAmount(), $details->getOutputs(1)->getAmount());
        $this->assertEquals($output2->getScript(), $details->getOutputs(1)->getScript());

        $this->assertTrue($parsed->hasOutputs());
        $this->assertEquals($output1->getAmount(), $parsed->getOutputs(0)->getAmount());
        $this->assertEquals($output1->getScript(), $parsed->getOutputs(0)->getScript());
        $this->assertEquals($output2->getAmount(), $parsed->getOutputs(1)->getAmount());
        $this->assertEquals($output2->getScript(), $parsed->getOutputs(1)->getScript());

        $details->clearOutputs();
        $this->assertFalse($details->hasOutputs());

        $details->addOutputs($output1);
        $details->addOutputs($output2);

        $this->assertCount(2, $details->getOutputsList());
        $this->assertEquals($output1->getAmount(), $details->getOutputs(0)->getAmount());
        $this->assertEquals($output1->getScript(), $details->getOutputs(0)->getScript());
        $this->assertEquals($output2->getAmount(), $details->getOutputs(1)->getAmount());
        $this->assertEquals($output2->getScript(), $details->getOutputs(1)->getScript());
    }

}
