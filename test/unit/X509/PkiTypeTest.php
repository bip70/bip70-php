<?php

declare(strict_types=1);

namespace Bip70\Test\X509;

use Bip70\X509\PkiType;
use PHPUnit\Framework\TestCase;

class PkiTypeTest extends TestCase
{
    public function testTypes()
    {
        $this->assertEquals("none", PkiType::NONE);
        $this->assertEquals("x509+sha1", PkiType::X509_SHA1);
        $this->assertEquals("x509+sha256", PkiType::X509_SHA256);
    }
}
