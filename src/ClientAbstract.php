<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro;

use Closure;

use DecodeLabs\Atlas;
use DecodeLabs\Atlas\File;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Collections\Tree\NativeMutable as NativeTree;
use DecodeLabs\Deliverance\DataReceiver;
use DecodeLabs\Exceptional;
use DecodeLabs\Hydro\Psr\ClientException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class ClientAbstract implements Client
{
    public function request(
        string $method,
        string|array $url,
        ?Closure $onFailure = null,
    ): ResponseInterface {
        $request = $this->newRequest(
            'GET',
            $this->prepareUrl($url)
        );

        return $this->manageRequest(
            $request,
            $onFailure,
            $this->prepareOptions($url, [
                'throw' => false
            ])
        );
    }

    public function get(
        string|array $url,
        ?Closure $onFailure = null
    ): File {
        $request = $this->newRequest(
            'GET',
            $this->prepareUrl($url)
        );

        return $this->responseToMemoryFile(
            $this->manageRequest(
                $request,
                $onFailure,
                $this->prepareOptions($url)
            )
        );
    }


    public function getString(
        string|array $url,
        ?Closure $onFailure = null
    ): string {
        $request = $this->newRequest(
            'GET',
            $this->prepareUrl($url)
        );

        return (string)$this->manageRequest(
            $request,
            $onFailure,
            $this->prepareOptions($url)
        )
            ->getBody();
    }


    public function getFile(
        string|array $url,
        string $path,
        ?Closure $onFailure = null
    ): File {
        $request = $this->newRequest(
            'GET',
            $this->prepareUrl($url)
        );

        return $this->responseToFile(
            $this->manageRequest(
                $request,
                $onFailure,
                $this->prepareOptions($url)
            ),
            $path
        );
    }


    public function getTempFile(
        string|array $url,
        ?Closure $onFailure = null
    ): File {
        $request = $this->newRequest(
            'GET',
            $this->prepareUrl($url)
        );

        return $this->responseToTempFile(
            $this->manageRequest(
                $request,
                $onFailure,
                $this->prepareOptions($url)
            )
        );
    }


    public function getJson(
        string|array $url,
        ?Closure $onFailure = null
    ): mixed {
        $output = $this->getString($url, $onFailure);
        return json_decode($output, true);
    }


    /**
     * Fetch json file over HTTP
     *
     * @param array<string, mixed> $url
     * @return Tree<mixed>
     */
    public function getJsonTree(
        string|array $url,
        ?Closure $onFailure = null
    ): Tree {
        if (!class_exists(NativeTree::class)) {
            throw Exceptional::ComponentUnavailable(
                'Cannot expand JSON response without decodelabs/collections'
            );
        }

        $json = $this->getJson($url, $onFailure);

        if (is_iterable($json)) {
            /** @var iterable<int|string, mixed> $json */
            $output = new NativeTree($json);
        } else {
            $output = new NativeTree(null, $json);
        }

        /** @var Tree<mixed> $output */
        return $output;
    }



    /**
     * @param array<string, mixed> $options
     */
    protected function manageRequest(
        RequestInterface $request,
        ?Closure $onFailure,
        array $options = [],
    ): ResponseInterface {
        try {
            return $this->sendRequest($request, $options);
        } catch (ClientException $e) {
            if (!$onFailure) {
                throw $e;
            }

            $out = $onFailure($e);

            if (!$out instanceof ResponseInterface) {
                throw Exceptional::Runtime(
                    'Failure callback must return a PSR7 ResponseInterface'
                );
            }

            return $out;
        }
    }

    /**
     * Send prepared request - PSR18
     *
     * @param array<string, mixed> $options
     */
    abstract public function sendRequest(
        RequestInterface $request,
        array $options = []
    ): ResponseInterface;


    /**
     * Create new PSR7 request
     */
    abstract protected function newRequest(
        string $method,
        string $url
    ): RequestInterface;

    /**
     * Prepare URL from input string or array
     *
     * @param string|array<string, mixed> $url
     */
    protected function prepareUrl(
        string|array $url
    ): string {
        if (is_array($url)) {
            $url = Coercion::toStringOrNull($url['url']) ?? '';
        }

        if (empty($url)) {
            throw Exceptional::InvalidArgument(
                'No request URL specified'
            );
        }

        return $url;
    }

    /**
     * Prepare options from input URL or array
     *
     * @param string|array<string, mixed> $options
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    protected function prepareOptions(
        string|array $options,
        array $defaults = []
    ): array {
        if (is_string($options)) {
            return [];
        }

        unset($options['url']);

        if (!isset($defaults['throw'])) {
            $defaults['throw'] = true;
        }

        return array_merge(
            $defaults,
            $options
        );
    }



    /**
     * Save PSR7 response to disk
     */
    public function responseToFile(
        ResponseInterface $response,
        string|File $file
    ): File {
        if (is_string($file)) {
            $file = Atlas::file($file, 'wb');
        } elseif (!$file->isOpen()) {
            $file->open('wb');
        }

        $this->transferStream($response->getBody(), $file);

        $file->close();
        return $file;
    }

    /**
     * Convert PSR7 response to DataProvider
     */
    public function responseToMemoryFile(
        ResponseInterface $response
    ): File {
        $file = Atlas::newMemoryFile();
        $this->transferStream($response->getBody(), $file);

        $file->setPosition(0);
        return $file;
    }

    /**
     * Save PSR7 response to disk as temp file
     */
    public function responseToTempFile(
        ResponseInterface $response
    ): File {
        $file = Atlas::newTempFile();
        $this->transferStream($response->getBody(), $file);

        $file->close();
        return $file;
    }

    /**
     * Transfer PSR7 stream to DataReceiver
     */
    public function transferStream(
        StreamInterface $stream,
        DataReceiver $receiver
    ): DataReceiver {
        while (!$stream->eof()) {
            $receiver->write($stream->read(8192));
        }

        return $receiver;
    }
}
