<?php

declare(strict_types=1);

namespace Bip70\Client;

class MIMEType
{
    const PAYMENT_REQUEST = "application/bitcoin-paymentrequest";
    const PAYMENT = "application/bitcoin-payment";
    const PAYMENT_ACK = "application/bitcoin-paymentack";
}
