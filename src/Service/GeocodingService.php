<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeocodingService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Geocodes a country or place name using an external API (OpenStreetMap Nominatim API)
     *
     * @param string $countryName
     * @return array{lat: float, lng: float}|null
     */
    public function getCoordinatesForCountry(string $countryName): ?array
    {
        $countryName = trim($countryName);
        if ($countryName === '') {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $countryName,
                    'format' => 'json',
                    'limit' => 1,
                    'accept-language' => 'fr,en',
                ],
                'headers' => [
                    'User-Agent' => 'HelpMeCommunityApp/1.0 (contact@helpmecommunity.org)',
                ],
                'timeout' => 5.0,
            ]);

            if ($response->getStatusCode() === 200) {
                $data = $response->toArray();
                if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                    return [
                        'lat' => (float) $data[0]['lat'],
                        'lng' => (float) $data[0]['lon'],
                    ];
                }
            }
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->error('Erreur lors du geocodage de ' . $countryName . ' : ' . $e->getMessage());
            }
        }

        return null;
    }
}
