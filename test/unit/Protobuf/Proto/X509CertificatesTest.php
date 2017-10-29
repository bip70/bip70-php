<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Proto;

use Bip70\Protobuf\Proto\X509Certificates;
use PHPUnit\Framework\TestCase;

class X509CertificatesTest extends TestCase
{
    public function testCertificate()
    {
        $cert = 'der....';
        $x509 = new X509Certificates();

        $this->assertFalse($x509->hasCertificate());
        $x509->setCertificate($cert, 0);
        $this->assertTrue($x509->hasCertificate());

        $parsed = new X509Certificates();
        $parsed->parse($x509->serialize());

        $this->assertCount(1, $parsed->getCertificateList());
        $this->assertEquals($cert, $parsed->getCertificate(0));

        $x509->clearCertificate();
        $this->assertFalse($x509->hasCertificate());

        $x509->addCertificate($cert);
        $this->assertTrue($x509->hasCertificate());
    }
}
