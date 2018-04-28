<?php

declare(strict_types=1);

namespace Bip70\Client;

use Bip70\Protobuf\Proto\PaymentRequest;
use X509\CertificationPath\PathValidation\PathValidationResult;

class PaymentRequestInfo
{
    /**
     * @var PaymentRequest
     */
    private $request;

    /**
     * @var PathValidationResult
     */
    private $validationResult;

    /**
     * PaymentRequestInfo constructor.
     * @param PaymentRequest $request
     * @param PathValidationResult|null $pathValidation
     */
    public function __construct(PaymentRequest $request, PathValidationResult $pathValidation = null)
    {
        $this->request = $request;
        $this->validationResult = $pathValidation;
    }

    /**
     * @return PaymentRequest
     */
    public function request(): PaymentRequest
    {
        return $this->request;
    }

    /**
     * @return null|PathValidationResult
     */
    public function pathValidation()
    {
        return $this->validationResult;
    }
}
