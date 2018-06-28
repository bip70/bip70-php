<?php

declare(strict_types=1);

namespace Bip70\Test\Client;

use Bip70\Client\MIMEType;
use Bip70\Client\NetworkConfig\BitcoinNetworkConfig;
use PHPUnit\Framework\TestCase;

class MIMETypeTest extends TestCase
{
    public function testTypes()
    {
        $networkConfig = new BitcoinNetworkConfig();
        $this->assertEquals("application/bitcoin-paymentrequest", $networkConfig->getPaymentRequestMimeType());
        $this->assertEquals("application/bitcoin-payment", $networkConfig->getPaymentMimeType());
        $this->assertEquals("application/bitcoin-paymentack", $networkConfig->getPaymentAckMimeType());
    }
}
