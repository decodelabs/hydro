<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro;

use Closure;
use DecodeLabs\Atlas\File;
use DecodeLabs\Atlas\File\Local as LocalFile;
use DecodeLabs\Atlas\File\Memory as MemoryFile;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Deliverance\DataReceiver;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

interface Client extends ClientInterface
{
    /**
     * @param string|array<string,mixed> $url
     */
    public function request(
        string $method,
        string|array $url,
        ?Closure $onFailure = null,
    ): ResponseInterface;

    /**
     * @param string|array<string,mixed> $url
     */
    public function get(
        string|array $url,
        ?Closure $onFailure = null
    ): File;

    /**
     * @param string|array<string,mixed> $url
     */
    public function getString(
        string|array $url,
        ?Closure $onFailure = null
    ): string;

    /**
     * @param string|array<string,mixed> $url
     */
    public function getFile(
        string|array $url,
        string|LocalFile $path,
        ?Closure $onFailure = null
    ): LocalFile;

    /**
     * @param string|array<string,mixed> $url
     */
    public function getTempFile(
        string|array $url,
        ?Closure $onFailure = null
    ): MemoryFile;

    /**
     * @param string|array<string,mixed> $url
     * @return string|int|float|bool|array<string,int|float|bool|array<mixed>>|null
     */
    public function getJson(
        string|array $url,
        ?Closure $onFailure = null
    ): string|int|float|bool|array|null;

    /**
     * @param string|array<string,mixed> $url
     * @return Tree<string|int|float|bool>
     */
    public function getJsonTree(
        string|array $url,
        ?Closure $onFailure = null
    ): Tree;




    public function responseToFile(
        ResponseInterface $response,
        string|LocalFile $path
    ): LocalFile;

    public function responseToMemoryFile(
        ResponseInterface $response
    ): MemoryFile;

    public function transferStream(
        StreamInterface $stream,
        DataReceiver $receiver
    ): DataReceiver;

    /**
     * @param array<string,mixed> $options
     */
    public function sendRequest(
        RequestInterface $request,
        array $options = []
    ): ResponseInterface;
}
