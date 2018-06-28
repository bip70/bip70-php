<?php

declare(strict_types=1);

namespace Bip70\Client\NetworkConfig;

class BitcoinNetworkConfig implements NetworkConfigInterface
{
    public function getPaymentMimeType()
    {
        return "application/bitcoin-payment";
    }

    public function getPaymentRequestMimeType()
    {
        return "application/bitcoin-paymentrequest";
    }

    public function getPaymentAckMimeType()
    {
        return "application/bitcoin-paymentack";
    }
}
