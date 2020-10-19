<?php

namespace Rmk\AdrTest;

use PHPUnit\Framework\TestCase;
use Rmk\Adr\Payload;

class PayloadTest extends TestCase
{

    public function testGettersSetters()
    {
        $result = ['a' => 1, 'b' => 2];
        $payload = new Payload(Payload::OK, $result);
        $this->assertTrue($payload->isOk());
        $this->assertFalse($payload->isNotFoundError());
        $this->assertFalse($payload->isInternalError());
        $this->assertFalse($payload->isError());
        $this->assertEquals($result, $payload->getData());
        $this->assertEquals(Payload::OK, $payload->getStatus());
    }
}
