<?php

declare(strict_types=1);

namespace Bip70\Test\Client\GuzzleClient;

use Bip70\Client\GuzzleHttpClient;
use Bip70\Client\MIMEType;
use Bip70\Client\PaymentRequestInfo;
use Bip70\Protobuf\Proto\PaymentDetails;
use Bip70\X509\PKIType;
use Bip70\X509\RequestSigner;
use Bip70\X509\RequestValidation;
use Bip70\X509\TrustStoreLoader;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoEncoding\PEMBundle;
use Sop\CryptoTypes\Asymmetric\PrivateKeyInfo;
use X509\Certificate\CertificateBundle;
use X509\CertificationPath\PathValidation\PathValidationConfig;
use X509\CertificationPath\PathValidation\PathValidationResult;

class GetsSignedRequestTest extends TestCase
{
    public function testPerformsX509IfPkiTypeNotNone()
    {
        $keySigned = PrivateKeyInfo::fromPEM(PEM::fromFile(__DIR__ . "/../../../data/testnet-only-cert-not-valid.key"));
        $certs = CertificateBundle::fromPEMBundle(PEMBundle::fromFile(__DIR__ . "/../../../data/testnet-only-cert-not-valid.cabundle.pem"));
        $certSigned = $certs->all()[0];
        $certBundle = new CertificateBundle(...array_slice($certs->all(), 1));

        $now = 1509692666;
        $details = new PaymentDetails();
        $details->setTime($now);

        $requestSigner = new RequestSigner();
        $request = $requestSigner->sign($details, PKIType::X509_SHA256, $keySigned, $certSigned, $certBundle);
        $serializedRequest = $request->serialize();

        $container = [];
        $history = Middleware::history($container);

        $handler = new MockHandler([
            // response to our GET PAYMENTREQUEST request
            new Response(200, [
                'Content-Type' => [
                    'application/bitcoin-paymentrequest',
                ]
            ], $serializedRequest)
        ]);

        $stack = HandlerStack::create($handler);
        $stack->push($history);

        $mockClient = new \GuzzleHttp\Client([
            'handler' => $stack,
        ]);

        $client = new GuzzleHttpClient($mockClient);

        $requestUrl = "https://development.bip70.local/invoice/1";

        $validator = $this->getMockBuilder(RequestValidation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validator->expects($this->once())
            ->method('verifyX509Details')
            ->withAnyParameters()
            ->willReturnCallback(function () {
                return $this->getMockBuilder(PathValidationResult::class)
                    ->disableOriginalConstructor()
                    ->getMock();
            });
        $data = $client->getRequest($requestUrl, $validator);

        $this->assertInstanceOf(PaymentRequestInfo::class, $data);
        $this->assertEquals($serializedRequest, $data->request()->serialize());
        $this->assertCount(1, $container);
        $this->assertInstanceOf(PathValidationResult::class, $data->pathValidation());
    }

    public function testGetsSignedRequest()
    {
        $keySigned = PrivateKeyInfo::fromPEM(PEM::fromFile(__DIR__ . "/../../../data/testnet-only-cert-not-valid.key"));
        $certs = CertificateBundle::fromPEMBundle(PEMBundle::fromFile(__DIR__ . "/../../../data/testnet-only-cert-not-valid.cabundle.pem"));
        $certSigned = $certs->all()[0];
        $certBundle = new CertificateBundle(...array_slice($certs->all(), 1));

        $now = 1509692666;
        $details = new PaymentDetails();
        $details->setTime($now);

        $requestSigner = new RequestSigner();
        $request = $requestSigner->sign($details, PKIType::X509_SHA256, $keySigned, $certSigned, $certBundle);
        $serializedRequest = $request->serialize();

        $container = [];
        $history = Middleware::history($container);

        $handler = new MockHandler([
            // response to our GET PAYMENTREQUEST request
            new Response(200, [
                'Content-Type' => [
                    'application/bitcoin-paymentrequest',
                ]
            ], $serializedRequest)
        ]);

        $stack = HandlerStack::create($handler);
        $stack->push($history);

        $mockClient = new \GuzzleHttp\Client([
            'handler' => $stack,
        ]);

        $client = new GuzzleHttpClient($mockClient);

        $requestUrl = "https://development.bip70.local/invoice/1";

        // 10/12/2017 ish
        $dtnow = new \DateTimeImmutable();
        $dtnow = $dtnow->setTimestamp($now);

        $validationConfig = new PathValidationConfig($dtnow, 10);
        $validator = new RequestValidation($validationConfig, TrustStoreLoader::fromSystem());
        $data = $client->getRequest($requestUrl, $validator);

        $this->assertInstanceOf(PaymentRequestInfo::class, $data);
        $this->assertEquals($serializedRequest, $data->request()->serialize());
        $this->assertCount(1, $container);
        $this->assertInstanceOf(PathValidationResult::class, $data->pathValidation());

        /** @var RequestInterface $req1 */
        $req1 = $container[0]['request'];
        $this->assertEquals($requestUrl, $req1->getUri());
        $this->assertTrue($req1->hasHeader('Accept'));
        $this->assertEquals(MIMEType::PAYMENT_REQUEST, $req1->getHeader('Accept')[0]);

        /** @var ResponseInterface $res1 */
        $res1 = $container[0]['response'];
        $res1->getBody()->rewind();
        $this->assertEquals($serializedRequest, $res1->getBody()->getContents());
    }
}
