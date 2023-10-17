<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Hydro;

use DecodeLabs\Hydro;
use DecodeLabs\Hydro\Client\Guzzle;
use DecodeLabs\Veneer;

// Register the Veneer facade
Veneer::register(Factory::getClientClass(), Hydro::class);

class Factory
{
    /**
     * @return class-string<Client>
     */
    public static function getClientClass(): string
    {
        return Guzzle::class;
    }
}
