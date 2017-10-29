<?php

declare(strict_types=1);

namespace Bip70\Test\Protobuf\Codec;

use Bip70\Protobuf\Codec\NonDiscardingBinaryCodec;
use Bip70\Protobuf\Proto\PaymentDetails;
use PHPUnit\Framework\TestCase;

class NonDiscardingBinaryCodecTest extends TestCase
{
    public function testChecksThatRequiredValuesArePassed()
    {
        $details = new PaymentDetails();

        $this->expectExceptionMessage("Message " . PaymentDetails::class . "'s field tag 3(time) is required but has no value");

        $details->serialize(new NonDiscardingBinaryCodec());
    }
}
