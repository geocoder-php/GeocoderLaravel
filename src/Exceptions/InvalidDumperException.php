<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Mike Bronner <hello@genealabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Geocoder\Laravel\Exceptions;

use Geocoder\Exception\Exception;
use Exception as BaseException;

/**
 * Exception to indicate an invalidly specified dumper identifier when calling
 * the `dump()` method on the ProviderAndDumperAggregator class.
 *
 * @author Mike Bronner <hello@genealabs.com>
 */
class InvalidDumperException extends BaseException implements Exception
{

}
