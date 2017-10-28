<?php

declare(strict_types=1);

namespace Bip70\Test\X509;

use Bip70\X509\TrustStoreLoader;
use Composer\CaBundle\CaBundle;
use PHPUnit\Framework\TestCase;
use Sop\CryptoEncoding\PEMBundle;
use X509\Certificate\CertificateBundle;

class TrustStoreLoaderTest extends TestCase
{
    public function testLoadFromCaStore()
    {
        $caFile = CaBundle::getSystemCaRootBundlePath();
        $this->assertFileExists($caFile);

        $cas = CertificateBundle::fromPEMBundle(PEMBundle::fromFile($caFile));
        $this->assertGreaterThan(0, count($cas));

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

        $cas = CertificateBundle::fromPEMBundle(PEMBundle::fromFile($path));
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

        $cas = CertificateBundle::fromPEMBundle(PEMBundle::fromFile($path));
        foreach ($cas->getIterator() as $ca) {
            $this->assertTrue($composerBundle->contains($ca));
        }
    }
}
