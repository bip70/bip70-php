<?php

declare(strict_types=1);

namespace Bip70\Test\Client;

use Bip70\Client\MIMEType;
use PHPUnit\Framework\TestCase;

class MIMETypeTest extends TestCase
{
    public function testTypes()
    {
        $this->assertEquals("application/bitcoin-paymentrequest", MIMEType::PAYMENT_REQUEST);
        $this->assertEquals("application/bitcoin-payment", MIMEType::PAYMENT);
        $this->assertEquals("application/bitcoin-paymentack", MIMEType::PAYMENT_ACK);
    }
}
