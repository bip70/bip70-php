<?php

declare(strict_types=1);

namespace Bip70\Client;

use Bip70\Client\Exception\ProtocolException;
use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\Payment;
use Bip70\Protobuf\Proto\PaymentACK;
use Bip70\Protobuf\Proto\PaymentDetails;
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
     * @var bool
     */
    private $checkContentType = true;

    /**
     * HttpClient constructor.
     * @param Client|null $client
     */
    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * @param bool $setting
     * @return void
     */
    public function setCheckContentType(bool $setting)
    {
        $this->checkContentType = $setting;
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
            $this->checkContentType($acceptType, $response);
        }

        return $response;
    }

    /**
     * @param string $url
     * @param string $acceptType
     * @param string $dataMIMEType
     * @param string $data
     * @return ResponseInterface
     */
    private function post(string $url, string $acceptType, string $dataMIMEType, string $data): ResponseInterface
    {
        $options = [
            "headers" => [
                "Accept" => $acceptType,
                "Content-Type" => $dataMIMEType,
            ],
            'body' => $data,
        ];

        $response = $this->client->post($url, $options);

        if ($this->checkContentType) {
            $this->checkContentType($acceptType, $response);
        }

        return $response;
    }

    /**
     * @param string $expectType
     * @param ResponseInterface $response
     */
    public function checkContentType(string $expectType, ResponseInterface $response)
    {
        if (!$response->hasHeader("Content-Type")) {
            throw new ProtocolException("Missing Content-Type header");
        }

        $contentType = $response->getHeader("Content-Type");
        if (!in_array($expectType, $contentType)) {
            throw new ProtocolException("Content-Type was not " . $expectType);
        }
    }

    /**
     * @param string $requestUrl
     * @param RequestValidation $requestValidation
     * @return PaymentRequestInfo
     */
    public function getRequest(string $requestUrl, RequestValidation $requestValidation): PaymentRequestInfo
    {
        $response = $this->get($requestUrl, MIMEType::PAYMENT_REQUEST);
        $paymentRequest = new PaymentRequest();

        try {
            $contents = $response->getBody()->getContents();
            $paymentRequest->parse($contents, new NonDiscardingBinaryCodec());
        } catch (\Exception $e) {
            throw new ProtocolException("Failed to decode payment request", 0, $e);
        }

        $validationResult = null;
        if ($paymentRequest->getPkiType() !== PKIType::NONE) {
            $validationResult = $requestValidation->verifyX509Details($paymentRequest);
        }

        return new PaymentRequestInfo($paymentRequest, $validationResult);
    }

    /**
     * @param PaymentDetails $details
     * @param string|null $memo
     * @param string ...$transactions
     * @return PaymentACK
     */
    public function sendPayment(PaymentDetails $details, string $memo = null, string ...$transactions): PaymentACK
    {
        if (!$details->hasPaymentUrl()) {
            throw new \RuntimeException("No payment url set on details");
        }

        $payment = new Payment();
        foreach ($transactions as $transaction) {
            $payment->addTransactions($transaction);
        }

        if ($details->hasMerchantData()) {
            $payment->setMerchantData($details->getMerchantData());
        }

        if ($memo) {
            $payment->setMemo($memo);
        }

        $paymentData = $payment->serialize();
        $result = $this->post($details->getPaymentUrl(), MIMEType::PAYMENT_ACK, MIMEType::PAYMENT, $paymentData);

        $ack = new PaymentACK();

        try {
            $ack->parse($result->getBody()->getContents());
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to decode Payment ACK message");
        }

        return $ack;
    }
}
