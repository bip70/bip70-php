<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Proto;

use Bip70\Protobuf\Proto\Output;
use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    public function testOutput()
    {
        $amount = 1;
        $script = "ascii";

        $output = new Output();
        $this->assertTrue($output->hasAmount());
        $this->assertEquals(0, $output->getAmount());
        $output->setAmount($amount);
        $this->assertTrue($output->hasAmount());
        $this->assertEquals($amount, $output->getAmount());

        $this->assertFalse($output->hasScript());
        $output->setScript($script);
        $this->assertTrue($output->hasScript());
        $this->assertEquals($script, $output->getScript());

        $serialized = $output->serialize();
        $parsed = new Output();
        $parsed->parse($serialized);
        $this->assertEquals($parsed->getAmount(), $output->getAmount());
        $this->assertEquals($parsed->getScript(), $output->getScript());

        $output->clearScript();
        $this->assertFalse($output->hasScript());
        $this->assertNull($output->getScript());

        $output->clearAmount();
        $this->assertFalse($output->hasAmount());
        $this->assertNull($output->getAmount());
    }
}
