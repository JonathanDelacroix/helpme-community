<?php

namespace App\Tests\Service;

use App\Service\GeocodingService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GeocodingServiceTest extends TestCase
{
    public function testGetCoordinatesForCountryReturnsCoordinates(): void
    {
        $mockJson = json_encode([
            [
                'lat' => '31.7917',
                'lon' => '-7.0926',
                'display_name' => 'Maroc',
            ]
        ]);

        $httpClient = new MockHttpClient(new MockResponse($mockJson, ['http_code' => 200]));
        $service = new GeocodingService($httpClient);

        $result = $service->getCoordinatesForCountry('Maroc');

        $this->assertIsArray($result);
        $this->assertEquals(31.7917, $result['lat']);
        $this->assertEquals(-7.0926, $result['lng']);
    }

    public function testGetCoordinatesForEmptyCountryReturnsNull(): void
    {
        $httpClient = new MockHttpClient();
        $service = new GeocodingService($httpClient);

        $this->assertNull($service->getCoordinatesForCountry(''));
    }

    public function testGetCoordinatesForCountryReturnsNullWhenNoResults(): void
    {
        $httpClient = new MockHttpClient(new MockResponse('[]', ['http_code' => 200]));
        $service = new GeocodingService($httpClient);

        $this->assertNull($service->getCoordinatesForCountry('PaysInexistantXYZ'));
    }

}
