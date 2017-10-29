<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Proto;

use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentDetails;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\X509\PkiType;
use PHPUnit\Framework\TestCase;

class PaymentRequestTest extends TestCase
{
    public function testSerializedPaymentDetails()
    {
        $now = time();
        $details = new PaymentDetails();
        $details->setTime($now);

        $request = new PaymentRequest();
        $this->assertFalse($request->hasSerializedPaymentDetails());
        $request->setSerializedPaymentDetails($details->serialize());
        $this->assertTrue($request->hasSerializedPaymentDetails());

        $codec = new NonDiscardingBinaryCodec();
        $parsed = new PaymentRequest();
        $parsed->parse($request->serialize($codec), $codec);

        $this->assertEquals($request->hasSerializedPaymentDetails(), $parsed->hasSerializedPaymentDetails());
        $this->assertEquals($request->getSerializedPaymentDetails(), $parsed->getSerializedPaymentDetails());

        $request->clearSerializedPaymentDetails();
        $this->assertFalse($request->hasSerializedPaymentDetails());
    }

    public function testDetailsVersion()
    {
        $now = time();
        $details = new PaymentDetails();
        $details->setTime($now);

        $request = new PaymentRequest();
        $request->setSerializedPaymentDetails($details->serialize());

        $this->assertTrue($request->hasPaymentDetailsVersion());
        $this->assertEquals(1, $request->getPaymentDetailsVersion());

        $request->clearPaymentDetailsVersion();
        $this->assertFalse($request->hasPaymentDetailsVersion());

        $request->setPaymentDetailsVersion(2);
        $this->assertTrue($request->hasPaymentDetailsVersion());
        $this->assertEquals(2, $request->getPaymentDetailsVersion());

        $codec = new NonDiscardingBinaryCodec();
        $parsed = new PaymentRequest();
        $parsed->parse($request->serialize($codec), $codec);

        $this->assertEquals($request->hasPaymentDetailsVersion(), $parsed->hasPaymentDetailsVersion());
        $this->assertEquals($request->getPaymentDetailsVersion(), $parsed->getPaymentDetailsVersion());
    }

    public function testPkiType()
    {
        $now = time();
        $details = new PaymentDetails();
        $details->setTime($now);

        $request = new PaymentRequest();
        $request->setSerializedPaymentDetails($details->serialize());

        $this->assertTrue($request->hasPkiType());
        $this->assertEquals(PkiType::NONE, $request->getPkiType());

        $request->clearPkiType();
        $this->assertFalse($request->hasPkiType());

        $request->setPkiType(PkiType::X509_SHA1);
        $this->assertTrue($request->hasPkiType());
        $this->assertEquals(PkiType::X509_SHA1, $request->getPkiType());

        $codec = new NonDiscardingBinaryCodec();
        $parsed = new PaymentRequest();
        $parsed->parse($request->serialize($codec), $codec);

        $this->assertEquals($request->hasPkiType(), $parsed->hasPkiType());
        $this->assertEquals($request->getPkiType(), $parsed->getPkiType());
    }

    public function testPkiData()
    {
        $now = time();
        $details = new PaymentDetails();
        $details->setTime($now);

        $x509data = 'serialized.x509..';

        $request = new PaymentRequest();
        $request->setSerializedPaymentDetails($details->serialize());

        $this->assertFalse($request->hasPkiData());
        $request->setPkiData($x509data);
        $this->assertTrue($request->hasPkiData());
        $this->assertEquals($x509data, $request->getPkiData());

        $codec = new NonDiscardingBinaryCodec();
        $parsed = new PaymentRequest();
        $parsed->parse($request->serialize($codec), $codec);

        $this->assertEquals($request->hasPkiData(), $parsed->hasPkiData());
        $this->assertEquals($request->getPkiData(), $parsed->getPkiData());

        $request->clearPkiData();
        $this->assertFalse($request->hasPkiData());
    }

    public function testSignature()
    {
        $now = time();
        $details = new PaymentDetails();
        $details->setTime($now);

        $signature = 'serialized.sig..';

        $request = new PaymentRequest();
        $request->setSerializedPaymentDetails($details->serialize());

        $this->assertFalse($request->hasSignature());
        $request->setSignature($signature);
        $this->assertTrue($request->hasSignature());
        $this->assertEquals($signature, $request->getSignature());

        $codec = new NonDiscardingBinaryCodec();
        $parsed = new PaymentRequest();
        $parsed->parse($request->serialize($codec), $codec);

        $this->assertEquals($request->hasSignature(), $parsed->hasSignature());
        $this->assertEquals($request->getSignature(), $parsed->getSignature());

        $request->clearSignature();
        $this->assertFalse($request->hasSignature());
    }
}
