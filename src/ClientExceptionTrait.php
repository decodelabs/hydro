<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro;

use DecodeLabs\Exceptional;

use Psr\Http\Message\RequestInterface;

trait ClientExceptionTrait
{
    public function getRequest(): RequestInterface
    {
        if (!$this->data instanceof RequestInterface) {
            throw Exceptional::Runtime(
                'No request available'
            );
        }

        return $this->data;
    }
}
