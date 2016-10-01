<?php namespace Toin0u\GeocoderLaravel\Tests\Laravel5_3\Providers;

use Toin0u\GeocoderLaravel\Tests\Laravel5_3\TestCase;

class GeocoderServiceProviderTest extends TestCase
{
    public function testItResolvesAGivenAddress()
    {
        $result = app('geocoder')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20003', $result[0]->getPostalCode());
    }

    public function testItResolvesAGivenIPAddress()
    {
        $result = app('geocoder')
            ->geocode('8.8.8.8')
            ->all();
        $this->assertEquals('US', $result[0]->getCountry()->getCode());
    }

    public function testItResolvesAGivenAddressWithUmlauts()
    {
        $result = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->all();
        $this->assertEquals('22', $result[0]->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $result[0]->getStreetName());
        $this->assertEquals('Wien', $result[0]->getLocality());
        $this->assertEquals('1020', $result[0]->getPostalCode());
    }

    public function testItCanUseMaxMindBinaryWithoutProvider()
    {
        $result = app('geocoder')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20003', $result[0]->getPostalCode());
    }
}
