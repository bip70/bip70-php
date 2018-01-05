<?php

declare(strict_types=1);

namespace Bip70\Test\X509;

use Bip70\Exception\Bip70Exception;
use Bip70\X509\TrustStoreLoader;
use Composer\CaBundle\CaBundle;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEMBundle;
use X509\Certificate\Certificate;
use X509\Certificate\CertificateBundle;

class TrustStoreLoaderTest extends TestCase
{
    public function testLoadFromCaStore()
    {
        $caFile = CaBundle::getSystemCaRootBundlePath();

        $this->assertFileExists($caFile);

        return $caFile === CaBundle::getBundledCaBundlePath();
    }

    /**
     * @return array
     */
    public function getFromFileFixtures(): array
    {
        return [
            [CaBundle::getSystemCaRootBundlePath(), [TrustStoreLoader::class, 'fromSystem']],
            [CaBundle::getBundledCaBundlePath(), [TrustStoreLoader::class, 'fromComposerBundle']],
        ];
    }

    /**
     * @dataProvider getFromFileFixtures
     * @param string $path
     */
    public function testLoadFromFile(string $path)
    {
        $systemStore = TrustStoreLoader::fromFile($path);
        $this->assertGreaterThan(0, count($systemStore));

        $bundle = PEMBundle::fromFile($path);
        $certs = [];
        foreach ($bundle->getIterator() as $it) {
            try {
                $cert = Certificate::fromPEM($it);
                $certs[] = $cert;
            } catch (\Exception $e) {
            }
        }

        $cas = new CertificateBundle(...$certs);
        foreach ($cas->getIterator() as $ca) {
            $this->assertTrue($systemStore->contains($ca));
        }
    }

    /**
     * @dataProvider getFromFileFixtures
     * @param string $path
     * @param callable $fxn
     */
    public function testLoadFromComposerBundle($path, callable $fxn)
    {
        // this should always work..
        $composerBundle = call_user_func($fxn);
        $this->assertGreaterThan(0, count($composerBundle));

        $pems = PEMBundle::fromFile($path);
        $certs = [];
        foreach ($pems as $pem) {
            try {
                $cert = Certificate::fromPEM($pem);
                $certs[] = $cert;
            } catch (\Exception $e) {
            }
        }

        $cas = new CertificateBundle(...$certs);
        foreach ($cas->getIterator() as $ca) {
            $this->assertTrue($composerBundle->contains($ca));
        }
    }

    public function testDisablingFallbackToComposer()
    {
        $envVar = "SSL_CERT_FILE";

        // unset caPath variable first
        $refObject   = new \ReflectionObject(new CaBundle());
        $refProperty = $refObject->getProperty('caPath');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null, null);

        putenv("{$envVar}=" . CaBundle::getBundledCaBundlePath());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Fallback to composer ca-bundle is disabled - you should install the ca-certificates package");

        try {
            TrustStoreLoader::fromSystem(false);
        } catch (\Exception $e) {
            $refObject   = new \ReflectionObject(new CaBundle());
            $refProperty = $refObject->getProperty('caPath');
            $refProperty->setAccessible(true);
            $refProperty->setValue(null, null);

            putenv($envVar."=");

            throw $e;
        }
    }

    public function testHandlingDirectoryInFromSystem()
    {
        $envVar = "SSL_CERT_DIR";

        $systemPath = CaBundle::getSystemCaRootBundlePath();

        // unset caPath variable first
        $refObject   = new \ReflectionObject(new CaBundle());
        $refProperty = $refObject->getProperty('caPath');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null, null);

        $path = implode("/", array_slice(explode("/", $systemPath), 0, -1));

        putenv("{$envVar}=" . $path);

        $trustStore = TrustStoreLoader::fromSystem();
        $this->assertGreaterThan(0, $trustStore->count());

        putenv("{$envVar}=");
    }

    public function testFromDirectoryRequiresADirectory()
    {
        $this->expectExceptionMessage("Invalid path passed to fromDirectory, is not a directory");
        $this->expectException(\RuntimeException::class);

        TrustStoreLoader::fromDirectory("/some/invalid/path");
    }

    public function testDirectoryMustHavePems()
    {
        $tmpDir = __DIR__ . "/../../../" . bin2hex(random_bytes(4));
        mkdir($tmpDir);

        $this->expectExceptionMessage("No PEM files in directory");
        $this->expectException(Bip70Exception::class);

        try {
            TrustStoreLoader::fromDirectory($tmpDir);
        } catch (\Exception $e) {
            rmdir($tmpDir);
            throw $e;
        }
    }

    public function testLoadFromDirectory()
    {
        // unset caPath variable first
        $refObject   = new \ReflectionObject(new CaBundle());
        $refProperty = $refObject->getProperty('caPath');
        $refProperty->setAccessible(true);
        $refProperty->setValue(null, null);

        $systemPath = CaBundle::getSystemCaRootBundlePath();

        // We expect ca-certificates to be installed
        $this->assertFalse(CaBundle::getBundledCaBundlePath() === $systemPath);

        $path = implode("/", array_slice(explode("/", $systemPath), 0, -1));
        $store = TrustStoreLoader::fromDirectory($path);

        $this->assertGreaterThan(0, $store->count());
    }
}
