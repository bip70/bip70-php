<?php

declare(strict_types=1);

namespace Bip70\X509;

use Composer\CaBundle\CaBundle;
use Sop\CryptoEncoding\PEM;
use Sop\CryptoEncoding\PEMBundle;
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
            throw new \RuntimeException("Fallback to composer ca-bundle is disabled - you should install the ca-certificates package");
        }

        if (is_dir($rootBundlePath)) {
            return self::fromDirectory($rootBundlePath);
        } else {
            return self::fromFile($rootBundlePath);
        }
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

    /**
     * Load a trust store from a pem bundle file (can contain
     * multiple certificates)
     *
     * @param string $dir
     * @return CertificateBundle
     */
    public static function fromDirectory(string $dir): CertificateBundle
    {
        if (!is_dir($dir)) {
            throw new \RuntimeException("Invalid path passed to fromDirectory, is not a directory");
        }

        $pems = [];
        foreach (glob("$dir/*.pem") as $pemFile) {
            $pems[] = PEM::fromFile($pemFile);
        }

        if (count($pems) < 1) {
            throw new \RuntimeException("No PEM files in directory");
        }

        return CertificateBundle::fromPEMs(...$pems);
    }
}
