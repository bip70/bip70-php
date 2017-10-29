<?php

declare(strict_types=1);

namespace Bip70\X509;

use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentDetails;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\Protobuf\Proto\X509Certificates;
use Sop\CryptoBridge\Crypto;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA1AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;

class RequestSigner implements RequestSignerInterface
{
    /**
     * @var Crypto
     */
    private $crypto;

    /**
     * RequestSigner constructor.
     * @param Crypto|null $crypto
     */
    public function __construct(Crypto $crypto = null)
    {
        $this->crypto = $crypto ?: Crypto::getDefault();
    }

    /**
     * @inheritdoc
     */
    public function sign(
        PaymentDetails $details,
        string $pkiType,
        PrivateKeyInfo $privateKey,
        Certificate $cert,
        CertificateBundle $intermediates
    ): PaymentRequest {
        if ($pkiType === PKIType::NONE) {
            throw new \RuntimeException("Don't call sign with pki_type = none");
        }

        if ($pkiType === PKIType::X509_SHA1) {
            $hashAlgId = new SHA1AlgorithmIdentifier();
        } else if ($pkiType === PKIType::X509_SHA256) {
            $hashAlgId = new SHA256AlgorithmIdentifier();
        } else {
            throw new \RuntimeException("Unknown signature scheme");
        }

        $signAlgorithm = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
            $privateKey->algorithmIdentifier(),
            $hashAlgId
        );

        $x509Certs = new X509Certificates();
        $x509Certs->setCertificate($cert->toDER(), 0);
        foreach ($intermediates as $i => $intermediate) {
            $x509Certs->setCertificate($intermediate->toDER(), $i + 1);
        }

        $request = new PaymentRequest();
        $request->setPkiType($pkiType);
        $request->setPkiData($x509Certs->serialize());
        $request->setSerializedPaymentDetails($details->serialize());
        $request->setSignature('');

        $signature = $this->crypto->sign($request->serialize(new NonDiscardingBinaryCodec()), $privateKey, $signAlgorithm);

        $request->setSignature($signature->bitString()->string());

        return $request;
    }
}
