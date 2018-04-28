<?php

declare(strict_types=1);

namespace Bip70\Test\X509;

use Bip70\Protobuf\Proto\PaymentDetails;
use Bip70\Protobuf\Proto\X509Certificates;
use Bip70\X509\PKIType;
use Bip70\X509\RequestSigner;
use Bip70\X509\RequestValidation;
use Bip70\X509\TrustStoreLoader;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoEncoding\PEMBundle;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;
use X509\CertificationPath\PathValidation\PathValidationConfig;

class RequestSignerTest extends TestCase
{
    public function testSignRequiresPkiType()
    {
        $details = new   PaymentDetails();
        $privateKey = PrivateKeyInfo::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.key.pem"));
        $cert = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));
        $certBundle = new CertificateBundle();
        $requestSigner = new RequestSigner();

        $this->expectExceptionMessage("Don't call sign with pki_type = none");
        $this->expectException(\RuntimeException::class);

        $requestSigner->sign($details, PKIType::NONE, $privateKey, $cert, $certBundle);
    }

    public function testSignRequiresValidPkiType()
    {
        $details = new PaymentDetails();
        $privateKey = PrivateKeyInfo::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.key.pem"));
        $cert = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));
        $certBundle = new CertificateBundle();
        $requestSigner = new RequestSigner();

        $this->expectExceptionMessage("Unknown signature scheme");
        $this->expectException(\RuntimeException::class);

        $requestSigner->sign($details, "purebs", $privateKey, $cert, $certBundle);
    }

    /**
     * @return array
     */
    public function getSignFixtures(): array
    {
        $keySelfSigned = PrivateKeyInfo::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.key.pem"));
        $certSelfSigned = Certificate::fromPEM(PEM::fromFile(__DIR__ . "/../../data/selfsigned.cert.pem"));
        $selfSignedStore = new CertificateBundle($certSelfSigned);
        $emptyBundle = new CertificateBundle();

        $keySigned = PrivateKeyInfo::fromPEM(PEM::fromFile(__DIR__ . "/../../data/testnet-only-cert-not-valid.key"));
        $certs = CertificateBundle::fromPEMBundle(PEMBundle::fromFile(__DIR__ . "/../../data/testnet-only-cert-not-valid.cabundle.pem"));
        $certSigned = $certs->all()[0];
        $certBundle = new CertificateBundle(...array_slice($certs->all(), 1));

        $systemStore = TrustStoreLoader::fromSystem();
        return [
            // test a self signed case
            [$selfSignedStore, $keySelfSigned, $certSelfSigned, $emptyBundle],
            [$systemStore, $keySigned, $certSigned, $certBundle],
        ];
    }

    /**
     * @param PrivateKeyInfo $privateKey
     * @param Certificate $cert
     * @param CertificateBundle $certBundle
     * @param CertificateBundle $trustStore
     * @dataProvider getSignFixtures
     */
    public function testSignOperation(CertificateBundle $trustStore, PrivateKeyInfo $privateKey, Certificate $cert, CertificateBundle $certBundle)
    {
        $now = time();

        $details = new PaymentDetails();
        $details->setTime($now);

        $requestSigner = new RequestSigner();
        // 10/12/2017 ish
        $now = new \DateTimeImmutable();
        $now = $now->setTimestamp(1509692666);

        $requestValidator = new RequestValidation(new PathValidationConfig($now, 10), $trustStore);

        foreach ([PKIType::X509_SHA256, PKIType::X509_SHA1] as $pkiType) {
            $request = $requestSigner->sign($details, $pkiType, $privateKey, $cert, $certBundle);
            $this->assertTrue($request->hasSignature());
            $this->assertTrue($request->hasPkiData());
            $this->assertTrue($request->hasPkiType());

            $this->assertEquals($pkiType, $request->getPkiType());

            try {
                $requestValidator->validateX509Signature($cert, $request);
            } catch (\Exception $e) {
                $this->fail("verification of own signature should always succeed");
                return;
            }

            $x509 = new X509Certificates();
            $x509->parse($request->getPkiData());

            $this->assertEquals(1 + count($certBundle), count($x509->getCertificateList()));

            /** @var Certificate[] $allCerts */
            $allCerts = array_merge([$cert], $certBundle->all());
            foreach ($allCerts as $i => $certificate) {
                $this->assertEquals($certificate->toDER(), $x509->getCertificate($i));
            }

            try {
                $qualifiedCert = $requestValidator->validateCertificateChain($x509);
            } catch (\Exception $e) {
                $this->fail("certificate chain validation shouldn't fail");
                return;
            }

            $this->assertTrue($cert->equals($qualifiedCert->getPath()->endEntityCertificate()));

            try {
                $result = $requestValidator->verifyX509Details($request);
                $threw = false;
            } catch (\Exception $e) {
                $threw = true;
            }

            $this->assertFalse($threw, "verifying cert details shouldn't fail");

            $this->assertTrue($result->certificate()->equals($cert));
        }
    }
}
