<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro\Tests;

use DecodeLabs\Hydro\ClientExceptionTrait;
use DecodeLabs\Exceptional\Exception;
use DecodeLabs\Exceptional\ExceptionTrait;
use Exception as RootException;

class AnalyzeClientExceptionTrait extends RootException implements Exception
{
    use ClientExceptionTrait;
    use ExceptionTrait;
}
