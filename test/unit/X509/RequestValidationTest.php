<?php

declare(strict_types=1);

namespace Bip70\Test\X509;

use Bip70\Exception\X509Exception;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\Protobuf\Proto\X509Certificates;
use Bip70\X509\Exception\InvalidCertificateChainException;
use Bip70\X509\Exception\InvalidX509Signature;
use Bip70\X509\PKIType;
use Bip70\X509\QualifiedCertificate;
use Bip70\X509\RequestValidation;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEM;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;

class RequestValidationTest extends TestCase
{
    public function testValidateX509DetailsRequiresSignatureType()
    {
        $request = new PaymentRequest();
        $request->setPkiType(PKIType::NONE);

        $validator = new RequestValidation();

        $this->expectException(X509Exception::class);
        $this->expectExceptionMessage("Cannot verify a request without a signature. You should check before calling verify.");

        $validator->verifyX509Details($request);
    }

    public function testValidateX509SignatureRequiresSignatureType()
    {
        $request = new PaymentRequest();
        $request->setPkiType(PKIType::NONE);

        $validator = new RequestValidation();
        $certificate = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));

        $this->expectException(X509Exception::class);
        $this->expectExceptionMessage("Unknown signature scheme");

        $validator->validateX509Signature($certificate, $request);
    }

    public function testValidateCertificateChainRequiresCertificates()
    {
        $x509 = new X509Certificates();
        $validator = new RequestValidation();

        $this->expectException(InvalidCertificateChainException::class);
        $this->expectExceptionMessage("No certificates in bundle");

        $validator->validateCertificateChain($x509);
    }

    public function testSelfSignedChainValidationFails()
    {
        $cert = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));
        $x509Certs = new X509Certificates();
        $x509Certs->addCertificate($cert->toDER());

        $validator = new RequestValidation();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("No certification paths");

        $validator->validateCertificateChain($x509Certs);
    }

    public function testSelfSignedChainValidationSucceedsIfInStore()
    {
        $cert = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));
        $trustStore = new CertificateBundle($cert);
        $x509Certs = new X509Certificates();
        $x509Certs->addCertificate($cert->toDER());

        $validator = new RequestValidation(null, $trustStore);

        $result = $validator->validateCertificateChain($x509Certs);
        $this->assertInstanceOf(QualifiedCertificate::class, $result);
    }

    public function testDetectsInvalidSignature()
    {
        $cert = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));
        $x509 = new X509Certificates();
        $x509->addCertificate($cert);

        $request = new PaymentRequest();
        $request->setPkiType(PKIType::X509_SHA1);
        $request->setPkiData($x509->serialize());
        $request->setSignature("invalid signature");
        $request->setSerializedPaymentDetails("serialized details aren't checked");

        $validator = new RequestValidation();

        $this->expectException(InvalidX509Signature::class);

        $validator->validateX509Signature($cert, $request);
    }
}
