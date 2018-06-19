<?php

declare(strict_types=1);

namespace Bip70\Client\NetworkConfig;

interface NetworkConfigInterface
{
    public function getPaymentMimeType();

    public function getPaymentRequestMimeType();

    public function getPaymentAckMimeType();
}
