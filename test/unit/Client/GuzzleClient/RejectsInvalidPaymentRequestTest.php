<?php

declare(strict_types=1);

namespace Bip70\Test\Client;

use Bip70\Client\Exception\ProtocolException;
use Bip70\Client\GuzzleHttpClient;
use Bip70\Client\NetworkConfig\BitcoinNetworkConfig;
use Bip70\X509\RequestValidation;
use Bip70\X509\TrustStoreLoader;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use X509\CertificationPath\PathValidation\PathValidationConfig;

class RejectsInvalidPaymentRequestTest extends TestCase
{
    public function testThrowsIfRequestInvalid()
    {
        $container = [];
        $history = Middleware::history($container);

        $handler = new MockHandler([
            // response to our GET PAYMENTREQUEST request
            new Response(200, [
                'Content-Type' => [
                    'application/bitcoin-paymentrequest',
                ]
            ], "GET")
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

        $networkConfig = new BitcoinNetworkConfig();

        $validationConfig = new PathValidationConfig($now, 10);
        $validator = new RequestValidation($validationConfig, TrustStoreLoader::fromSystem());

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage("Failed to decode payment request");

        $client->getRequest($requestUrl, $validator, $networkConfig);
    }
}
