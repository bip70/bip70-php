<?php

declare(strict_types=1);

namespace Bip70\X509;

use Bip70\Exception\X509Exception;
use X501\ASN1\Name;
use X509\CertificationPath\CertificationPath;
use X509\CertificationPath\PathValidation\PathValidationResult;

class QualifiedCertificate
{
    /**
     * @var CertificationPath
     */
    private $path;

    /**
     * @var PathValidationResult
     */
    private $validationResult;

    /**
     * QualifiedCertificate constructor.
     * @param CertificationPath $path
     * @param PathValidationResult $result
     * @throws X509Exception
     */
    public function __construct(CertificationPath $path, PathValidationResult $result)
    {
        if (!$result->certificate()->equals($path->endEntityCertificate())) {
            throw new X509Exception("CertificationPath entity certificate must match PathValidationResult certificate");
        }

        $this->path = $path;
        $this->validationResult = $result;
    }

    /**
     * @return Name
     */
    public function subject(): Name
    {
        return $this->path->endEntityCertificate()->tbsCertificate()->subject();
    }

    /**
     * @return CertificationPath
     */
    public function getPath(): CertificationPath
    {
        return $this->path;
    }

    /**
     * @return PathValidationResult
     */
    public function getValidationResult(): PathValidationResult
    {
        return $this->validationResult;
    }
}
