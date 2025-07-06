<?php

/**
 * ___       __          __   __   __
 * |  |     |  |        |  | |  | |  |
 * |  |     |  |        |  | |  | |  |
 * |  |     |  |        |  | |  | |  |
 * |  |_____|  |________|  |_|  |_|  |
 * |_________|___________|____________|
 * 
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 * @copyright   (C) 2024 Whykiki. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @since       1.0.0
 */

namespace Whykiki\Module\Unwetterwarnung\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Weather API Client for OpenWeatherMap integration
 * 
 * Handles all API communication, rate limiting, and error handling
 * for weather data retrieval from OpenWeatherMap services.
 *
 * @since 1.0.0
 */
class WeatherApiClient
{
    /**
     * OpenWeatherMap API base URL
     */
    private const API_BASE_URL = 'https://api.openweathermap.org';
    
    /**
     * API version
     */
    private const API_VERSION = '2.5';
    
    /**
     * Maximum API requests per minute
     */
    private const RATE_LIMIT = 60;
    
    /**
     * Request timeout in seconds
     */
    private const REQUEST_TIMEOUT = 10;
    
    /**
     * API key for OpenWeatherMap
     */
    private string $apiKey;
    
    /**
     * HTTP client instance
     */
    private $httpClient;
    
    /**
     * Module parameters
     */
    private Registry $params;
    
    /**
     * Constructor
     * 
     * @param string $apiKey OpenWeatherMap API key
     * @param Registry $params Module parameters
     */
    public function __construct(string $apiKey, Registry $params)
    {
        $this->apiKey = $apiKey;
        $this->params = $params;
        $this->httpClient = HttpFactory::getHttp();
    }
    
    /**
     * Get weather alerts for specific coordinates
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param string $lang Language code (default: 'de')
     * 
     * @return array Weather alerts data
     * @throws \Exception When API request fails
     */
    public function getWeatherAlerts(float $lat, float $lon, string $lang = 'de'): array
    {
        $url = $this->buildApiUrl('onecall', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'lang' => $lang,
            'exclude' => 'minutely,hourly,daily'
        ]);
        
        try {
            $response = $this->makeRequest($url);
            
            if (!isset($response['alerts'])) {
                return [];
            }
            
            return $this->processAlerts($response['alerts']);
            
        } catch (\Exception $e) {
            Log::add(
                'Weather API request failed: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );
            
            throw new \Exception(Text::_('MOD_UNWETTERWARNUNG_ERROR_API_REQUEST'));
        }
    }
    
    /**
     * Get current weather data for coordinates
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param string $lang Language code
     * 
     * @return array Current weather data
     * @throws \Exception When API request fails
     */
    public function getCurrentWeather(float $lat, float $lon, string $lang = 'de'): array
    {
        $url = $this->buildApiUrl('weather', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $this->apiKey,
            'lang' => $lang,
            'units' => 'metric'
        ]);
        
        try {
            return $this->makeRequest($url);
            
        } catch (\Exception $e) {
            Log::add(
                'Current weather request failed: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );
            
            throw new \Exception(Text::_('MOD_UNWETTERWARNUNG_ERROR_WEATHER_REQUEST'));
        }
    }
    
    /**
     * Geocode location name to coordinates
     * 
     * @param string $location Location name (city, country)
     * @param int $limit Maximum number of results
     * 
     * @return array Geocoding results
     * @throws \Exception When geocoding fails
     */
    public function geocodeLocation(string $location, int $limit = 5): array
    {
        $url = $this->buildApiUrl('geo/1.0/direct', [
            'q' => $location,
            'limit' => $limit,
            'appid' => $this->apiKey
        ], 'http://api.openweathermap.org');
        
        try {
            return $this->makeRequest($url);
            
        } catch (\Exception $e) {
            Log::add(
                'Geocoding request failed: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );
            
            throw new \Exception(Text::_('MOD_UNWETTERWARNUNG_ERROR_GEOCODING'));
        }
    }
    
    /**
     * Reverse geocode coordinates to location name
     * 
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @param int $limit Maximum number of results
     * 
     * @return array Reverse geocoding results
     * @throws \Exception When reverse geocoding fails
     */
    public function reverseGeocode(float $lat, float $lon, int $limit = 5): array
    {
        $url = $this->buildApiUrl('geo/1.0/reverse', [
            'lat' => $lat,
            'lon' => $lon,
            'limit' => $limit,
            'appid' => $this->apiKey
        ], 'http://api.openweathermap.org');
        
        try {
            return $this->makeRequest($url);
            
        } catch (\Exception $e) {
            Log::add(
                'Reverse geocoding request failed: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );
            
            throw new \Exception(Text::_('MOD_UNWETTERWARNUNG_ERROR_REVERSE_GEOCODING'));
        }
    }
    
    /**
     * Build API URL with parameters
     * 
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param string $baseUrl Base URL (optional)
     * 
     * @return string Complete API URL
     */
    private function buildApiUrl(string $endpoint, array $params, string $baseUrl = null): string
    {
        $baseUrl = $baseUrl ?? self::API_BASE_URL;
        $url = $baseUrl . '/data/' . self::API_VERSION . '/' . $endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * Make HTTP request to API
     * 
     * @param string $url Request URL
     * 
     * @return array Decoded JSON response
     * @throws \Exception When request fails
     */
    private function makeRequest(string $url): array
    {
        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'User-Agent' => 'Joomla-ModUnwetterwarnung/1.0'
            ]
        ];
        
        $response = $this->httpClient->get($url, [], $options);
        
        if ($response->code !== 200) {
            throw new \Exception(
                sprintf('API request failed with status %d: %s', $response->code, $response->body)
            );
        }
        
        $data = json_decode($response->body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from API');
        }
        
        if (isset($data['cod']) && $data['cod'] !== 200) {
            throw new \Exception($data['message'] ?? 'API error');
        }
        
        return $data;
    }
    
    /**
     * Process and normalize alerts data
     * 
     * @param array $alerts Raw alerts from API
     * 
     * @return array Processed alerts
     */
    private function processAlerts(array $alerts): array
    {
        $processed = [];
        
        foreach ($alerts as $alert) {
            $processed[] = [
                'sender_name' => $alert['sender_name'] ?? 'Unknown',
                'event' => $alert['event'] ?? 'Weather Alert',
                'start' => $alert['start'] ?? time(),
                'end' => $alert['end'] ?? time(),
                'description' => $alert['description'] ?? '',
                'tags' => $alert['tags'] ?? [],
                'severity' => $this->determineSeverity($alert),
                'urgency' => $this->determineUrgency($alert),
                'certainty' => $this->determineCertainty($alert)
            ];
        }
        
        // Sort by severity (highest first)
        usort($processed, function($a, $b) {
            return $this->getSeverityWeight($b['severity']) <=> $this->getSeverityWeight($a['severity']);
        });
        
        return $processed;
    }
    
    /**
     * Determine alert severity based on event type and description
     * 
     * @param array $alert Alert data
     * 
     * @return string Severity level
     */
    private function determineSeverity(array $alert): string
    {
        $event = strtolower($alert['event'] ?? '');
        $description = strtolower($alert['description'] ?? '');
        
        // Extreme severity keywords
        if (strpos($event, 'extreme') !== false || 
            strpos($description, 'life-threatening') !== false ||
            strpos($description, 'catastrophic') !== false) {
            return 'extreme';
        }
        
        // Severe severity keywords
        if (strpos($event, 'severe') !== false || 
            strpos($event, 'warning') !== false ||
            strpos($description, 'dangerous') !== false) {
            return 'severe';
        }
        
        // Moderate severity keywords
        if (strpos($event, 'moderate') !== false || 
            strpos($event, 'watch') !== false) {
            return 'moderate';
        }
        
        // Default to minor
        return 'minor';
    }
    
    /**
     * Determine alert urgency
     * 
     * @param array $alert Alert data
     * 
     * @return string Urgency level
     */
    private function determineUrgency(array $alert): string
    {
        $now = time();
        $start = $alert['start'] ?? $now;
        
        $timeDiff = $start - $now;
        
        if ($timeDiff <= 3600) { // Within 1 hour
            return 'immediate';
        } elseif ($timeDiff <= 14400) { // Within 4 hours
            return 'expected';
        } else {
            return 'future';
        }
    }
    
    /**
     * Determine alert certainty
     * 
     * @param array $alert Alert data
     * 
     * @return string Certainty level
     */
    private function determineCertainty(array $alert): string
    {
        $description = strtolower($alert['description'] ?? '');
        
        if (strpos($description, 'observed') !== false || 
            strpos($description, 'confirmed') !== false) {
            return 'observed';
        } elseif (strpos($description, 'likely') !== false) {
            return 'likely';
        } elseif (strpos($description, 'possible') !== false) {
            return 'possible';
        } else {
            return 'unlikely';
        }
    }
    
    /**
     * Get numeric weight for severity sorting
     * 
     * @param string $severity Severity level
     * 
     * @return int Numeric weight
     */
    private function getSeverityWeight(string $severity): int
    {
        switch ($severity) {
            case 'extreme':
                return 4;
            case 'severe':
                return 3;
            case 'moderate':
                return 2;
            case 'minor':
                return 1;
            default:
                return 0;
        }
    }
    
    /**
     * Validate API key format
     * 
     * @param string $apiKey API key to validate
     * 
     * @return bool True if valid format
     */
    public static function validateApiKey(string $apiKey): bool
    {
        // OpenWeatherMap API keys are 32 character alphanumeric strings
        return preg_match('/^[a-zA-Z0-9]{32}$/', $apiKey) === 1;
    }
} 
