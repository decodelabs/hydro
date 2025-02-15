<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro;

use DecodeLabs\Exceptional;
use DecodeLabs\Exceptional\Exception;
use Psr\Http\Message\RequestInterface;

/**
 * @phpstan-require-implements Exception
 */
trait ClientExceptionTrait
{
    public function getRequest(): RequestInterface
    {
        if (!$this->data instanceof RequestInterface) {
            throw Exceptional::Runtime(
                message: 'No request available'
            );
        }

        return $this->data;
    }
}
