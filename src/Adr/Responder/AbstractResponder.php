<?php

namespace Rmk\Adr\Responder;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Rmk\Adr\Plugin\PluginAwareInterface;
use Rmk\Adr\Plugin\PluginAwareTrait;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Class AbstractResponder
 *
 * @package Rmk\Adr
 */
abstract class AbstractResponder implements ResponderInterface, PluginAwareInterface
{
    use PluginAwareTrait;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * @param $result
     * @param string $contentType
     * @param int $statusCode
     *
     * @return ResponseInterface
     *
     * @throws JsonException
     */
    public function jsonResponse($result, string $contentType = 'application/json', int $statusCode = 200): ResponseInterface
    {
        $json = json_encode($result, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_BIGINT_AS_STRING);
        $this->response = $this->response->withStatus($statusCode)
            ->withHeader('Content-Type', $contentType)
            ->withBody(stream_for($json));

        return $this->response;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
