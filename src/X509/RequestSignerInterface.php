<?php

declare(strict_types=1);

namespace Bip70\X509;

use Bip70\Protobuf\Proto\PaymentDetails;
use Bip70\Protobuf\Proto\PaymentRequest;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;

interface RequestSignerInterface
{
    /**
     * @param PaymentDetails $details
     * @param string $pkiType
     * @param PrivateKeyInfo $privateKey
     * @param Certificate $cert
     * @param CertificateBundle $intermediates
     * @return PaymentRequest
     */
    public function sign(PaymentDetails $details, string $pkiType, PrivateKeyInfo $privateKey, Certificate $cert, CertificateBundle $intermediates): PaymentRequest;
}
