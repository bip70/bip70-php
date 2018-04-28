<?php

declare(strict_types=1);

namespace Bip70\Test\Client;

use Bip70\Client\GuzzleHttpClient;
use Bip70\Client\MIMEType;
use Bip70\Client\PaymentRequestInfo;
use Bip70\X509\RequestValidation;
use Bip70\X509\TrustStoreLoader;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use X509\CertificationPath\PathValidation\PathValidationConfig;

class GetsUnsignedRequestTest extends TestCase
{
    public function testGetsUnsignedRequest()
    {
        $container = [];
        $history = Middleware::history($container);

        $handler = new MockHandler([
            // response to our GET PAYMENTREQUEST request
            new Response(200, [
                'Content-Type' => [
                    'application/bitcoin-paymentrequest',
                ]
            ], '')
        ]);

        $stack = HandlerStack::create($handler);
        $stack->push($history);

        $mockClient = new \GuzzleHttp\Client([
            'handler' => $stack,
        ]);

        $client = new GuzzleHttpClient($mockClient);

        $requestUrl = "https://development.bip70.local/invoice/1";

        // 10/12/2017 ish
        $now = new \DateTimeImmutable();
        $now = $now->setTimestamp(1509692666);

        $validationConfig = new PathValidationConfig($now, 10);
        $validator = new RequestValidation($validationConfig, TrustStoreLoader::fromSystem());
        $data = $client->getRequest($requestUrl, $validator);

        $this->assertInstanceOf(PaymentRequestInfo::class, $data);
        $this->assertCount(1, $container);

        /** @var RequestInterface $req1 */
        $req1 = $container[0]['request'];
        $this->assertEquals($requestUrl, $req1->getUri());
        $this->assertTrue($req1->hasHeader('Accept'));
        $this->assertEquals(MIMEType::PAYMENT_REQUEST, $req1->getHeader('Accept')[0]);
        $this->assertNull($data->pathValidation());
    }
}
