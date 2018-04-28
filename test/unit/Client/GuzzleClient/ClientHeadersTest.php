<?php

declare(strict_types=1);

namespace Bip70\Test\Client\GuzzleClient;

use Bip70\Client\Exception\ProtocolException;
use Bip70\Client\MIMEType;
use PHPUnit\Framework\TestCase;

use Bip70\Client\GuzzleHttpClient;
use Bip70\X509\RequestValidation;
use Bip70\X509\TrustStoreLoader;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use X509\CertificationPath\PathValidation\PathValidationConfig;

class ClientHeadersTest extends TestCase
{
    public function testMissingContentType()
    {
        $container = [];
        $history = Middleware::history($container);

        $handler = new MockHandler([
            // response to our GET PAYMENTREQUEST request
            new Response(200, [], '{"result":true}')
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

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("Missing Content-Type header");

        $client->getRequest($requestUrl, $validator);
    }

    public function testInvalidContentType()
    {
        $container = [];
        $history = Middleware::history($container);

        $handler = new MockHandler([
            // response to our GET PAYMENTREQUEST request
            new Response(200, [
                'Content-Type' => [
                    'application/json',
                ]
            ], '{"result":true}')
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

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("Content-Type was not " . MIMEType::PAYMENT_REQUEST);

        $client->getRequest($requestUrl, $validator);
    }
}
