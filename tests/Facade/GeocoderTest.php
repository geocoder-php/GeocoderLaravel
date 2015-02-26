<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toin0u\Tests\Geocoder\Facade;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderTest extends \Toin0u\Tests\Geocoder\TestCase
{
    public function testGeocoderFacade()
    {
        $this->assertTrue(is_array($providers = \Geocoder::getProviders()));
        $this->assertArrayHasKey('chain', $providers);
        $this->assertInstanceOf('Geocoder\\Provider\\ChainProvider', $providers['chain']);
    }
}
