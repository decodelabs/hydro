<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro\Client;

use DecodeLabs\Exceptional;
use DecodeLabs\Hydro\ClientAbstract;
use DecodeLabs\Hydro\ClientExceptionTrait;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ConnectException as GuzzleConnectException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Guzzle extends ClientAbstract
{
    public function newRequest(
        string $method,
        string $url
    ): RequestInterface {
        return new Request($method, $url);
    }

    /**
     * Send prepared request - PSR18
     */
    public function sendRequest(
        RequestInterface $request,
        array $options = []
    ): ResponseInterface {
        $throw = $options['throw'] ?? false;
        unset($options['throw']);

        try {
            return $this->newGuzzleClient()->send($request, $options);
        } catch (GuzzleClientException | GuzzleServerException $responseException) {
            // 4xx and 5xx
            if (
                !$throw &&
                $responseException->hasResponse()
            ) {
                return $responseException->getResponse();
            }

            throw Exceptional::Client(
                message: $responseException->getMessage(),
                code: $responseException->getCode(),
                previous: $responseException,
                data: $request,
                traits: [
                    ClientExceptionTrait::class
                ],
                interfaces: [
                    ClientExceptionInterface::class
                ]
            );
        } catch (GuzzleConnectException $connectionException) {
            // Network error
            throw Exceptional::Network(
                message: $connectionException->getMessage(),
                code: $connectionException->getCode(),
                previous: $connectionException,
                data: $request,
                traits: [
                    ClientExceptionTrait::class
                ],
                interfaces: [
                    NetworkExceptionInterface::class
                ]
            );
        } catch (GuzzleRequestException $requestException) {
            // Invalid request
            throw Exceptional::Request(
                message: $requestException->getMessage(),
                code: $requestException->getCode(),
                previous: $requestException,
                data: $request,
                traits: [
                    ClientExceptionTrait::class
                ],
                interfaces: [
                    RequestExceptionInterface::class
                ]
            );
        } catch (Throwable $e) {
            throw Exceptional::Request(
                message: $e->getMessage(),
                code: $e->getCode(),
                previous: $e,
                data: $request,
                traits: [
                    ClientExceptionTrait::class
                ],
                interfaces: [
                    ClientExceptionInterface::class
                ]
            );
        }
    }


    /**
     * Create new HTTP client
     *
     * @param array<string, mixed> $options
     */
    public function newGuzzleClient(
        array $options = []
    ): GuzzleClient {
        if (!class_exists(GuzzleClient::class)) {
            throw Exceptional::ComponentUnavailable(
                message: 'Cannot create HTTP Client, GuzzleHttp is not installed'
            );
        }

        return new GuzzleClient($options);
    }
}
