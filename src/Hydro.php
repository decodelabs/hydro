<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use Closure;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\File\Memory as MemoryFile;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Deliverance\DataReceiver;
use DecodeLabs\Hydro\Client;
use DecodeLabs\Hydro\Client\Guzzle;
use DecodeLabs\Kingdom\ContainerAdapter;
use DecodeLabs\Kingdom\Service;
use DecodeLabs\Kingdom\ServiceTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Hydro implements Client, Service
{
    use ServiceTrait;

    public static function provideService(
        ContainerAdapter $container
    ): static {
        if (!$container->has(Client::class)) {
            $container->setType(Client::class, Guzzle::class);
        }

        return $container->getOrCreate(static::class);
    }

    public function __construct(
        protected Client $client
    ) {
    }

    public function request(
        string $method,
        string|array $url,
        ?Closure $onFailure = null,
    ): ResponseInterface {
        return $this->client->request($method, $url, $onFailure);
    }

    /**
     * @param array<string,mixed> $options
     */
    public function sendRequest(
        RequestInterface $request,
        array $options = []
    ): ResponseInterface {
        return $this->client->sendRequest($request, $options);
    }

    public function get(
        string|array $url,
        ?Closure $onFailure = null
    ): File {
        return $this->client->get($url, $onFailure);
    }

    public function getString(
        string|array $url,
        ?Closure $onFailure = null
    ): string {
        return $this->client->getString($url, $onFailure);
    }

    public function getFile(
        string|array $url,
        string|LocalFile $path,
        ?Closure $onFailure = null
    ): LocalFile {
        return $this->client->getFile($url, $path, $onFailure);
    }

    public function getTempFile(
        string|array $url,
        ?Closure $onFailure = null
    ): MemoryFile {
        return $this->client->getTempFile($url, $onFailure);
    }

    public function getJson(
        string|array $url,
        ?Closure $onFailure = null
    ): string|int|float|bool|array|null {
        return $this->client->getJson($url, $onFailure);
    }

    public function getJsonTree(
        string|array $url,
        ?Closure $onFailure = null
    ): Tree {
        return $this->client->getJsonTree($url, $onFailure);
    }


    public function responseToFile(
        ResponseInterface $response,
        string|LocalFile $path
    ): LocalFile {
        return $this->client->responseToFile($response, $path);
    }

    public function responseToMemoryFile(
        ResponseInterface $response
    ): MemoryFile {
        return $this->client->responseToMemoryFile($response);
    }

    public function transferStream(
        StreamInterface $stream,
        DataReceiver $receiver
    ): DataReceiver {
        return $this->client->transferStream($stream, $receiver);
    }
}
