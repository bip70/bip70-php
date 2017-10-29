<?php

declare(strict_types=1);

namespace Bip70\Client;

use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\X509\RequestValidation;
use Bip70\X509\PKIType;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class GuzzleHttpClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var RequestValidation
     */
    private $requestValidation;

    /**
     * @var bool
     */
    private $checkContentType = true;

    /**
     * HttpClient constructor.
     * @param RequestValidation $requestValidation
     * @param Client|null $client
     */
    public function __construct(RequestValidation $requestValidation, Client $client = null)
    {
        $this->client = $client ?: new Client();
        $this->requestValidation = $requestValidation;
    }

    /**
     * @param bool $setting
     * @return void
     */
    public function checkContentType(bool $setting)
    {
        $this->checkContentType = $setting;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param string $url
     * @param string $acceptType
     * @return ResponseInterface
     */
    private function get($url, string $acceptType)
    {
        $options = [
            "headers" => ["Accept" => $acceptType,]
        ];

        $response = $this->client->get($url, $options);

        if ($this->checkContentType) {
            if (!$response->hasHeader("Content-Type")) {
                throw new \RuntimeException("Missing content-type header");
            }

            $contentType = $response->getHeader("Content-Type");
            if (!in_array($acceptType, $contentType)) {
                throw new \RuntimeException("Content-type was not " . $acceptType);
            }
        }

        return $response;
    }

    /**
     * @param string $requestUrl
     * @return PaymentRequestInfo
     */
    public function getRequest(string $requestUrl): PaymentRequestInfo
    {
        $response = $this->get($requestUrl, MIMEType::PAYMENT_REQUEST);
        $body = $response->getBody()->getContents();

        $paymentRequest = new PaymentRequest();

        try {
            $paymentRequest->parse($body, new NonDiscardingBinaryCodec());
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to decode payment request");
        }

        $validationResult = null;
        if ($paymentRequest->getPkiType() !== PKIType::NONE) {
            $validationResult = $this->requestValidation->verifyX509Details($paymentRequest);
        }

        return new PaymentRequestInfo($paymentRequest, $validationResult);
    }
}
