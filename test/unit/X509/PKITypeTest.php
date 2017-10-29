<?php

declare(strict_types=1);

namespace Bip70\Test\X509;

use Bip70\X509\PKIType;
use PHPUnit\Framework\TestCase;

class PKITypeTest extends TestCase
{
    public function testTypes()
    {
        $this->assertEquals("none", PKIType::NONE);
        $this->assertEquals("x509+sha1", PKIType::X509_SHA1);
        $this->assertEquals("x509+sha256", PKIType::X509_SHA256);
    }
}
