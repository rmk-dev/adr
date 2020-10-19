<?php

namespace Rmk\AdrTest;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Rmk\Adr\Responder\AbstractResponder;

/**
 * Class AbstractResponderTest
 * @package Rmk\AdrTest
 */
class AbstractResponderTest extends TestCase
{

    protected $responder;
    private int $statusCode;
    private array $headers;
    private $body;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ResponseInterface
     */
    private $response;

    protected function setUp(): void
    {
        $this->responder = $this->getMockForAbstractClass(AbstractResponder::class);
        $this->statusCode = 200;
        $this->headers = [];
        $this->body = null;
        $this->response = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->response->method('withStatus')->willReturnCallback(function($newStatus) {
            $this->statusCode = $newStatus;
            return $this->response;
        });
        $this->response->method('withHeader')->willReturnCallback(function($name, $value)  {
            $this->headers[$name] = $value;
            return $this->response;
        });
        $this->response->method('withBody')->willReturnCallback(function(StreamInterface $newBody) {
            $this->body = $newBody;
            return $this->response;
        });
        $this->responder->setResponse($this->response);
    }

    public function testGettersSetters()
    {
        $this->assertSame($this->response, $this->responder->getResponse());
    }

    public function testJsonResponse()
    {
        $result = ['success' => true, 'payload' => ['name' => 'John Doe', 'role' => 'visitor']];
        $newResponse = $this->responder->jsonResponse($result);
        $this->assertSame($this->response, $newResponse);
        $this->assertEquals(200, $this->statusCode);
        $this->assertArrayHasKey('Content-Type', $this->headers);
        $this->assertEquals('application/json', $this->headers['Content-Type']);
        $this->assertJsonStringEqualsJsonString($this->body.'', json_encode($result));
    }
}