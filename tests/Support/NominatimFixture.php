<?php

namespace Geocoder\Laravel\Tests\Support;

class NominatimFixture
{
    private const LICENCE = 'Data © OpenStreetMap contributors, ODbL 1.0. https://osm.org/copyright';

    public static function whiteHouse(): array
    {
        return [[
            'place_id' => 1,
            'licence' => self::LICENCE,
            'osm_type' => 'way',
            'osm_id' => 238241022,
            'lat' => '38.8976998',
            'lon' => '-77.0365534',
            'category' => 'office',
            'type' => 'government',
            'display_name' => '1600 Pennsylvania Avenue Northwest, Washington, DC, USA',
            'boundingbox' => ['38.8974', '38.8979', '-77.0367', '-77.0363'],
            'address' => [
                'house_number' => '1600',
                'road' => 'Pennsylvania Avenue Northwest',
                'city' => 'Washington',
                'state' => 'District of Columbia',
                'postcode' => '20500',
                'country' => 'United States',
                'country_code' => 'us',
            ],
        ]];
    }

    public static function losAngeles(): array
    {
        return [[
            'place_id' => 2,
            'licence' => self::LICENCE,
            'osm_type' => 'relation',
            'osm_id' => 207359,
            'lat' => '34.0536909',
            'lon' => '-118.2427666',
            'category' => 'boundary',
            'type' => 'administrative',
            'display_name' => 'Los Angeles, CA, USA',
            'boundingbox' => ['33.7036', '34.3373', '-118.6682', '-118.1553'],
            'address' => [
                'city' => 'Los Angeles',
                'state' => 'California',
                'country' => 'United States',
                'country_code' => 'us',
            ],
        ]];
    }

    public static function whiteHouseReverse(): array
    {
        return [
            'place_id' => 3,
            'licence' => self::LICENCE,
            'osm_type' => 'node',
            'osm_id' => 1,
            'lat' => '38.8791981',
            'lon' => '-76.9818437',
            'display_name' => 'Washington, DC, USA',
            'boundingbox' => ['38.879', '38.880', '-76.982', '-76.981'],
            'address' => [
                'city' => 'Washington',
                'state' => 'District of Columbia',
                'country' => 'United States',
                'country_code' => 'us',
            ],
        ];
    }
}
