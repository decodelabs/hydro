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
use DecodeLabs\Atlas\File\Memory as MemoryFile;
use DecodeLabs\Coercion;
use DecodeLabs\Collections\Tree;
use DecodeLabs\Deliverance\DataReceiver;
use DecodeLabs\Exceptional;
use DecodeLabs\Hydro\Psr\ClientException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ClientTrait
{
    public function request(
        string $method,
        string|array $url,
        ?Closure $onFailure = null,
    ): ResponseInterface {
        $request = $this->newRequest(
            strtoupper($method),
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
    ): MemoryFile {
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


    public function getJson(
        string|array $url,
        ?Closure $onFailure = null
    ): string|int|float|bool|array|null {
        $output = $this->getString($url, $onFailure);
        /** @var string|int|float|bool|array<string,int|float|bool|array<mixed>>|null $output */
        $output = json_decode($output, true);
        return $output;
    }


    /**
     * @param array<string, mixed> $url
     * @return Tree<string|int|float|bool>
     */
    public function getJsonTree(
        string|array $url,
        ?Closure $onFailure = null
    ): Tree {
        if (!class_exists(Tree::class)) {
            throw Exceptional::ComponentUnavailable(
                message: 'Cannot expand JSON response without decodelabs/collections'
            );
        }

        $json = $this->getJson($url, $onFailure);

        if (is_iterable($json)) {
            // @phpstan-ignore-next-line
            $output = new Tree($json);
        } else {
            $output = new Tree(null, $json);
        }

        /** @var Tree<string|int|float|bool> $output */
        return $output;
    }



    /**
     * @param array<string,mixed> $options
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
                    message: 'Failure callback must return a PSR7 ResponseInterface'
                );
            }

            return $out;
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    abstract public function sendRequest(
        RequestInterface $request,
        array $options = []
    ): ResponseInterface;


    abstract protected function newRequest(
        string $method,
        string $url
    ): RequestInterface;

    /**
     * @param string|array<string, mixed> $url
     */
    protected function prepareUrl(
        string|array $url
    ): string {
        if (is_array($url)) {
            $url = Coercion::tryString($url['url']) ?? '';
        }

        if (empty($url)) {
            throw Exceptional::InvalidArgument(
                message: 'No request URL specified'
            );
        }

        return $url;
    }

    /**
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



    public function responseToFile(
        ResponseInterface $response,
        string|File $file
    ): File {
        if (is_string($file)) {
            $file = Atlas::getFile($file, 'wb');
        } elseif (!$file->isOpen()) {
            $file->open('wb');
        }

        $this->transferStream($response->getBody(), $file);

        $file->close();
        return $file;
    }


    public function responseToMemoryFile(
        ResponseInterface $response
    ): MemoryFile {
        $file = Atlas::newMemoryFile();
        $this->transferStream($response->getBody(), $file);

        $file->setPosition(0);
        return $file;
    }


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
