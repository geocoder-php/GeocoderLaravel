<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Geocoder\Tests;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderFacadeTest extends TestCase
{
    public function testGeocoderFacade()
    {
        $this->assertTrue(is_array($providers = \Geocoder::getProviders()));
        $this->assertArrayHasKey('chain', $providers);
        $this->assertInstanceOf('Geocoder\\Provider\\ChainProvider', $providers['chain']);
    }
}
