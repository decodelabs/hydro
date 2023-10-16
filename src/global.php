<?php

/**
 * @package Hydro
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

/**
 * global helpers
 */

namespace DecodeLabs\Hydro
{
    use DecodeLabs\Hydro;
    use DecodeLabs\Veneer;

    // Register the Veneer facade
    Veneer::register(Factory::getClientClass(), Hydro::class);
}
