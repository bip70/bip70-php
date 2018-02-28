<?php

declare(strict_types=1);

namespace Bip70\X509;

use Bip70\X509\Exception\InvalidCertificateChainException;
use Bip70\X509\Exception\InvalidX509Signature;
use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\Protobuf\Proto\X509Certificates;
use Sop\CryptoBridge\Crypto;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA1AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Hash\SHA256AlgorithmIdentifier;
use Sop\CryptoTypes\AlgorithmIdentifier\Signature\SignatureAlgorithmIdentifierFactory;
use Sop\CryptoTypes\Signature\Signature;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;
use X509\CertificationPath\CertificationPath;
use X509\CertificationPath\PathValidation\PathValidationConfig;

class RequestValidation
{
    /**
     * @var null|PathValidationConfig
     */
    private $validationConfig;

    /**
     * @var CertificateBundle
     */
    private $trustStore;

    /**
     * RequestValidation constructor.
     * @param PathValidationConfig|null $validationConfig
     * @param CertificateBundle|null $trustStore
     */
    public function __construct(PathValidationConfig $validationConfig = null, CertificateBundle $trustStore = null)
    {
        if (null === $validationConfig) {
            $validationConfig = PathValidationConfig::defaultConfig();
        }

        if (null === $trustStore) {
            $trustStore = new CertificateBundle();
        }

        $this->validationConfig = $validationConfig;
        $this->trustStore = $trustStore;
    }

    /**
     * @param X509Certificates $x509
     * @return \Bip70\X509\QualifiedCertificate
     * @throws InvalidCertificateChainException
     */
    public function validateCertificateChain(X509Certificates $x509)
    {
        $numCerts = count($x509->getCertificateList());
        if ($numCerts < 1) {
            throw new \RuntimeException("No certificates in bundle");
        }

        $endEntity = Certificate::fromDER($x509->getCertificate(0));
        $intermediates = [];
        for ($i = 1; $i < $numCerts; $i++) {
            $intermediates = Certificate::fromDER($x509->getCertificate($i));
        }

        return $this->validateCertificates(
            $endEntity,
            new CertificateBundle(...$intermediates)
        );
    }

    /**
     * @param Certificate $certificate
     * @param CertificateBundle $intermediate
     * @return QualifiedCertificate
     */
    public function validateCertificates(Certificate $certificate, CertificateBundle $intermediate): QualifiedCertificate
    {
        $path = CertificationPath::toTarget($certificate, $this->trustStore, $intermediate);
        return new QualifiedCertificate($path, $path->validate($this->validationConfig));
    }

    /**
     * @param Certificate $endEntity
     * @param PaymentRequest $paymentRequest
     * @return void
     * @throws InvalidX509Signature
     */
    public function validateX509Signature(Certificate $endEntity, PaymentRequest $paymentRequest)
    {
        if ($paymentRequest->getPkiType() === PKIType::X509_SHA1) {
            $hashAlgId = new SHA1AlgorithmIdentifier();
        } else if ($paymentRequest->getPkiType() === PKIType::X509_SHA256) {
            $hashAlgId = new SHA256AlgorithmIdentifier();
        } else {
            throw new \RuntimeException("Unknown signature scheme");
        }

        $subjectKey = $endEntity->tbsCertificate()->subjectPublicKeyInfo();
        $signAlgorithm = SignatureAlgorithmIdentifierFactory::algoForAsymmetricCrypto(
            $subjectKey->algorithmIdentifier(),
            $hashAlgId
        );

        $clone = new PaymentRequest();
        if ($paymentRequest->hasPaymentDetailsVersion()) {
            $clone->setPaymentDetailsVersion($paymentRequest->getPaymentDetailsVersion());
        }
        $clone->setPkiType($paymentRequest->getPkiType());
        $clone->setPkiData($paymentRequest->getPkiData());
        $clone->setSerializedPaymentDetails($paymentRequest->getSerializedPaymentDetails());
        $clone->setSignature('');

        $signData = $clone->serialize(new NonDiscardingBinaryCodec());
        $signature = Signature::fromSignatureData($paymentRequest->getSignature(), $signAlgorithm);
        if (!Crypto::getDefault()->verify($signData, $signature, $subjectKey, $signAlgorithm)) {
            throw new InvalidX509Signature("Invalid signature on request");
        }

        return;
    }

    /**
     * @param PaymentRequest $paymentRequest
     * @return \X509\CertificationPath\PathValidation\PathValidationResult
     */
    public function verifyX509Details(PaymentRequest $paymentRequest)
    {
        if (PKIType::NONE === $paymentRequest->getPkiType()) {
            throw new \RuntimeException("Cannot verify a request without a signature. You should check before calling verify.");
        }

        $x509 = new X509Certificates();
        $x509->parse($paymentRequest->getPkiData());

        $qualifiedCert = $this->validateCertificateChain($x509);
        $validationResult = $qualifiedCert->getValidationResult();
        $this->validateX509Signature($validationResult->certificate(), $paymentRequest);

        return $validationResult;
    }
}
