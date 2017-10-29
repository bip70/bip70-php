<?php

namespace Bip70\Client;

use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentRequest;
use Bip70\X509\RequestValidation;
use Bip70\X509\PkiType;
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

    const TYPE_PAYMENT_REQUEST = "application/bitcoin-paymentrequest";
    const TYPE_PAYMENT = "application/bitcoin-payment";
    const TYPE_PAYMENT_ACK = "application/bitcoin-paymentack";

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

        return $this->client->get($url, $options);
    }

    /**
     * @param string $requestUrl
     * @return PaymentRequestInfo
     */
    public function getRequest(string $requestUrl): PaymentRequestInfo
    {
        $response = $this->get($requestUrl, self::TYPE_PAYMENT_REQUEST);

        if ($this->checkContentType) {
            if (!$response->hasHeader("Content-Type")) {
                throw new \RuntimeException("Missing content-type header");
            }

            $contentType = $response->getHeader("Content-Type");
            if (!in_array(self::TYPE_PAYMENT_REQUEST, $contentType)) {
                throw new \RuntimeException("Content-type was not application/bitcoin-paymentrequest");
            }
        }

        $body = $response->getBody()->getContents();
        $codec = new NonDiscardingBinaryCodec();
        $paymentRequest = new PaymentRequest();

        try {
            $paymentRequest->parse($body, $codec);
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to decode payment request");
        }

        $validationResult = null;
        if ($paymentRequest->getPkiType() !== PkiType::NONE) {
            $validationResult = $this->requestValidation->verifyX509Details($paymentRequest);
        }

        return new PaymentRequestInfo($paymentRequest, $validationResult);
    }
}
