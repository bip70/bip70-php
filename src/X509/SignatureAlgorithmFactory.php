<?php

declare(strict_types=1);

namespace Bip70\X509;

use Bip70\Exception\X509Exception;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AsymmetricCryptoAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\SignatureAlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA1AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;

class SignatureAlgorithmFactory
{
    public static function getSignatureAlgorithm(
        string $pkiType,
        AsymmetricCryptoAlgorithmIdentifier $keyAlgorithm
    ): SignatureAlgorithmIdentifier {
        if ($pkiType === PKIType::X509_SHA1) {
            $hashAlgId = new SHA1AlgorithmIdentifier();
        } else if ($pkiType === PKIType::X509_SHA256) {
            $hashAlgId = new SHA256AlgorithmIdentifier();
        } else {
            throw new X509Exception("Unknown signature scheme");
        }

        return SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
            $keyAlgorithm,
            $hashAlgId
        );
    }
}
