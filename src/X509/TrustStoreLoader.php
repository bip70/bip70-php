<?php

namespace Bip70\X509;

use Composer\CaBundle\CaBundle;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoEncoding\PEMBundle;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;

class TrustStoreLoader
{
    /**
     * Loads the currently installed ca-certificates file.
     * The approach taken by composer/ca-bundle is to attempt
     * to load certificates if openssl-like envvars and
     * config values are set.
     *
     * If $allowFallback is false, and the bundled composer
     * ca file is returned, an exception will be thrown.
     *
     * @see CaBundle::getSystemCaRootBundlePath()
     * @param bool $allowFallback
     * @return CertificateBundle
     */
    public static function fromSystem(bool $allowFallback = true): CertificateBundle
    {
        $rootBundlePath = CaBundle::getSystemCaRootBundlePath();

        if (!$allowFallback && CaBundle::getBundledCaBundlePath() === $rootBundlePath) {
            throw new \RuntimeException("No certificate store found on system - perhaps you need the ca-certificates package?");
        }

        return self::fromFile($rootBundlePath);
    }

    /**
     * Loads a trust store completely controlled by what is
     * included in the `composer/ca-bundle` package.
     * @return CertificateBundle
     */
    public static function fromComposerBundle(): CertificateBundle
    {
        return self::fromFile(CaBundle::getBundledCaBundlePath());
    }

    /**
     * Load a trust store from a set of files.
     *
     * @param string[] ...$files
     * @return CertificateBundle
     */
    public static function fromFiles(string ...$files): CertificateBundle
    {
        /** @var Certificate[] $certs */
        $roots = [];
        foreach ($files as $file) {
            try {
                $certificate = Certificate::fromPEM(PEM::fromFile($file));
            } catch (\Exception $e) {
                throw new \RuntimeException("Invalid PEM file found", 0, $e);
            }

            if ($certificate->isSelfIssued()) {
                $roots[] = $certificate;
            }
        }

        return new CertificateBundle(...$roots);
    }

    /**
     * Load a trust store from a pem bundle file (can contain
     * multiple certificates)
     *
     * @param string $file
     * @return CertificateBundle
     */
    public static function fromFile(string $file): CertificateBundle
    {
        return CertificateBundle::fromPEMBundle(PEMBundle::fromFile($file));
    }
}
