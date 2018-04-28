<?php

declare(strict_types=1);

namespace Bip70\X509;

use Bip70\Exception\X509Exception;
use Bip70\X509\Exception\InvalidCertificateChainException;
use Bip70\X509\Exception\InvalidX509Signature;
use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\Protobuf\Proto\X509Certificates;
use Sop\CryptoBridge\Crypto;
use Sop\CryptoTypes\AlgorithmIdentifier\Feature\AsymmetricCryptoAlgorithmIdentifier;
use Sop\CryptoTypes\Signature\Signature;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;
use X509\CertificationPath\CertificationPath;
use X509\CertificationPath\PathValidation\PathValidationConfig;

class RequestValidation
{
    /**
     * @var PathValidationConfig
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
        if (!$x509->hasCertificate()) {
            throw new InvalidCertificateChainException("No certificates in bundle");
        }

        /** @var Certificate[] $certificates */
        $certificates = [];
        foreach ($x509->getCertificateList() as $certDer) {
            $certificates[] = Certificate::fromDER($certDer);
        }

        $endEntity = $certificates[0];
        $intermediate = new CertificateBundle(...array_slice($certificates, 1));

        return $this->validateCertificates($endEntity, $intermediate);
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
     * @throws InvalidX509Signature
     * @throws X509Exception
     */
    public function validateX509Signature(Certificate $endEntity, PaymentRequest $paymentRequest)
    {
        $subjectKey = $endEntity->tbsCertificate()->subjectPublicKeyInfo();

        /** @var AsymmetricCryptoAlgorithmIdentifier $algOid */
        $algOid = $subjectKey->algorithmIdentifier();
        $signAlgorithm = SignatureAlgorithmFactory::getSignatureAlgorithm($paymentRequest->getPkiType(), $algOid);

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
    }

    /**
     * @param PaymentRequest $paymentRequest
     * @return \X509\CertificationPath\PathValidation\PathValidationResult
     */
    public function verifyX509Details(PaymentRequest $paymentRequest)
    {
        if (PKIType::NONE === $paymentRequest->getPkiType()) {
            throw new X509Exception("Cannot verify a request without a signature. You should check before calling verify.");
        }

        $x509 = new X509Certificates();
        $x509->parse($paymentRequest->getPkiData());

        $qualifiedCert = $this->validateCertificateChain($x509);
        $validationResult = $qualifiedCert->getValidationResult();
        $this->validateX509Signature($validationResult->certificate(), $paymentRequest);

        return $validationResult;
    }
}
