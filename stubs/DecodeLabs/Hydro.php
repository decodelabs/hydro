<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Hydro\Client\Guzzle as Inst;
use Psr\Http\Message\RequestInterface as Ref0;
use Psr\Http\Message\ResponseInterface as Ref1;
use GuzzleHttp\Client as Ref2;
use Closure as Ref3;
use DecodeLabs\Atlas\File as Ref4;
use DecodeLabs\Collections\Tree as Ref5;
use Psr\Http\Message\StreamInterface as Ref6;
use DecodeLabs\Deliverance\DataReceiver as Ref7;

class Hydro implements Proxy
{
    use ProxyTrait;

    const Veneer = 'DecodeLabs\\Hydro';
    const VeneerTarget = Inst::class;

    public static Inst $instance;

    public static function newRequest(string $method, string $url): Ref0 {
        return static::$instance->newRequest(...func_get_args());
    }
    public static function sendRequest(Ref0 $request, array $options = []): Ref1 {
        return static::$instance->sendRequest(...func_get_args());
    }
    public static function newGuzzleClient(array $options = []): Ref2 {
        return static::$instance->newGuzzleClient(...func_get_args());
    }
    public static function request(string $method, array|string $url, ?Ref3 $onFailure = NULL): Ref1 {
        return static::$instance->request(...func_get_args());
    }
    public static function get(array|string $url, ?Ref3 $onFailure = NULL): Ref4 {
        return static::$instance->get(...func_get_args());
    }
    public static function getString(array|string $url, ?Ref3 $onFailure = NULL): string {
        return static::$instance->getString(...func_get_args());
    }
    public static function getFile(array|string $url, string $path, ?Ref3 $onFailure = NULL): Ref4 {
        return static::$instance->getFile(...func_get_args());
    }
    public static function getTempFile(array|string $url, ?Ref3 $onFailure = NULL): Ref4 {
        return static::$instance->getTempFile(...func_get_args());
    }
    public static function getJson(array|string $url, ?Ref3 $onFailure = NULL): mixed {
        return static::$instance->getJson(...func_get_args());
    }
    public static function getJsonTree(array|string $url, ?Ref3 $onFailure = NULL): Ref5 {
        return static::$instance->getJsonTree(...func_get_args());
    }
    public static function responseToFile(Ref1 $response, Ref4|string $file): Ref4 {
        return static::$instance->responseToFile(...func_get_args());
    }
    public static function responseToMemoryFile(Ref1 $response): Ref4 {
        return static::$instance->responseToMemoryFile(...func_get_args());
    }
    public static function responseToTempFile(Ref1 $response): Ref4 {
        return static::$instance->responseToTempFile(...func_get_args());
    }
    public static function transferStream(Ref6 $stream, Ref7 $receiver): Ref7 {
        return static::$instance->transferStream(...func_get_args());
    }
};
