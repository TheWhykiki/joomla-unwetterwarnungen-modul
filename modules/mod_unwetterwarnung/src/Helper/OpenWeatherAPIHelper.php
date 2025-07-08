<?php

declare(strict_types=1);

/**
 *    __          __ _    _ __     __ _  __ _____  _  __ _____
 *     \ \        / /| |  | |\ \   / /| |/ /|_   _|| |/ /|_   _|
 *      \ \  /\  / / | |__| | \ \_/ / | ' /   | |  | ' /   | |
 *       \ \/  \/ /  |  __  |  \   /  |  <    | |  |  <    | |
 *        \  /\  /   | |  | |   | |   | . \  _| |_ | . \  _| |_
 *         \/  \/    |_|  |_|   |_|   |_|\_\|_____||_|\_\|_____|
 *
 * @package     Whykiki.Module
 * @subpackage  mod_unwetterwarnung
 * @copyright   Copyright (C) 2025 Whykiki
 * @author      Kiki Schuelling
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0.0
 */

namespace Whykiki\Module\Unwetterwarnung\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Weather API Client Helper for OpenWeatherMap integration
 *
 * Handles all API communication, rate limiting, and error handling
 * for weather data retrieval from OpenWeatherMap services.
 * Implements secure HTTP requests with proper timeout handling.
 *
 * @see https://github.com/whykiki/mod_unwetterwarnung#openweatherapihelper
 * @since 1.0.0
 */
class OpenWeatherAPIHelper
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
     * Request timeout in seconds
     */
    private const REQUEST_TIMEOUT = 10;

    /**
     * Get weather alerts for specific coordinates
     *
     * [CUSTOM FUNCTION] Retrieves weather warnings from OpenWeatherMap API.
     * Fetches current weather alerts for given coordinates with language
     * localization and comprehensive error handling.
     *
     * @param   Registry        $params Module parameters
     * @param   SiteApplication $app    The application
     * @param   float           $lat    Latitude
     * @param   float           $lon    Longitude
     * @param   string          $lang   Language code (default: 'de')
     *
     * @return  array Weather alerts data
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#weather-alerts
     * @since 1.0.0
     * @throws  \Exception When API request fails
     */
    public function getWeatherAlerts(Registry $params, SiteApplication $app, float $lat, float $lon, string $lang = 'de'): array
    {
        $apiKey = $params->get('api_key', '');
        if (empty($apiKey)) {
            return [];
        }

        $url = $this->buildApiUrl('onecall', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
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

            return [];
        }
    }

    /**
     * Get current weather data for coordinates
     *
     * [CUSTOM FUNCTION] Fetches current weather conditions.
     * Retrieves current weather data including temperature, humidity,
     * pressure and weather conditions for given coordinates.
     *
     * @param   Registry        $params Module parameters
     * @param   SiteApplication $app    The application
     * @param   float           $lat    Latitude
     * @param   float           $lon    Longitude
     * @param   string          $lang   Language code
     *
     * @return  array Current weather data
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#current-weather
     * @since 1.0.0
     */
    public function getCurrentWeather(Registry $params, SiteApplication $app, float $lat, float $lon, string $lang = 'de'): array
    {
        $apiKey = $params->get('api_key', '');
        if (empty($apiKey)) {
            return [];
        }

        $url = $this->buildApiUrl('weather', [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
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

            return [];
        }
    }

    /**
     * Geocode location name to coordinates
     *
     * [CUSTOM FUNCTION] Converts location names to GPS coordinates.
     * Uses OpenWeatherMap's geocoding API to convert city names,
     * postal codes, or addresses to latitude/longitude coordinates.
     *
     * @param   Registry        $params   Module parameters
     * @param   SiteApplication $app      The application
     * @param   string          $location Location name (city, country)
     * @param   int             $limit    Maximum number of results
     *
     * @return  array Geocoding results
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#geocoding
     * @since 1.0.0
     */
    public function geocodeLocation(Registry $params, SiteApplication $app, string $location, int $limit = 5): array
    {
        $apiKey = $params->get('api_key', '');
        if (empty($apiKey)) {
            return [];
        }

        $url = $this->buildApiUrl('geo/1.0/direct', [
            'q' => $location,
            'limit' => $limit,
            'appid' => $apiKey
        ], $params->get('api_base_url', 'https://api.openweathermap.org'), null, true);

        try {
            return $this->makeRequest($url);

        } catch (\Exception $e) {
            Log::add(
                'Geocoding request failed: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );

            return [];
        }
    }

    /**
     * Reverse geocode coordinates to location name
     *
     * [CUSTOM FUNCTION] Converts GPS coordinates to location names.
     * Uses OpenWeatherMap's reverse geocoding API to convert
     * latitude/longitude coordinates to human-readable location names.
     *
     * @param   Registry        $params Module parameters
     * @param   SiteApplication $app    The application
     * @param   float           $lat    Latitude
     * @param   float           $lon    Longitude
     * @param   int             $limit  Maximum number of results
     *
     * @return  array Reverse geocoding results
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#reverse-geocoding
     * @since 1.0.0
     */
    public function reverseGeocode(Registry $params, SiteApplication $app, float $lat, float $lon, int $limit = 5): array
    {
        $apiKey = $params->get('api_key', '');
        if (empty($apiKey)) {
            return [];
        }

        $url = $this->buildApiUrl('geo/1.0/reverse', [
            'lat' => $lat,
            'lon' => $lon,
            'limit' => $limit,
            'appid' => $apiKey
        ], $params->get('api_base_url', 'https://api.openweathermap.org'), null, true);

        try {
            return $this->makeRequest($url);

        } catch (\Exception $e) {
            Log::add(
                'Reverse geocoding request failed: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );

            return [];
        }
    }

    /**
     * Build API URL with parameters
     *
     * [CUSTOM FUNCTION] Constructs complete API URLs with query parameters.
     * Builds properly formatted URLs for OpenWeatherMap API endpoints
     * with all required parameters and proper encoding.
     *
     * @param   string $endpoint API endpoint
     * @param   array  $params   Query parameters
     * @param   string $baseUrl  Base URL (optional)
     * @param   string $apiVersion  API version (optional)
     * @param   bool $isGeoApi  Is Geo API (optional)
     *
     * @return  string Complete API URL
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#build-api-url
     * @since 1.0.0
     */
    private function buildApiUrl(string $endpoint, array $params, string $baseUrl = null, string $apiVersion = null, bool $isGeoApi = false): string
    {
        $baseUrl = $baseUrl ?? 'https://api.openweathermap.org';
        
        if ($isGeoApi) {
            $url = $baseUrl . '/' . $endpoint;
        } else {
            $apiVersion = $apiVersion ?? '2.5';
            $url = $baseUrl . '/data/' . $apiVersion . '/' . $endpoint;
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Make HTTP request to API
     *
     * [CUSTOM FUNCTION] Executes HTTP requests with error handling.
     * Performs secure HTTP requests to the OpenWeatherMap API with
     * proper timeout handling and response validation.
     *
     * @param   string $url Request URL
     *
     * @return  array Decoded JSON response
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#make-request
     * @since 1.0.0
     * @throws  \Exception When request fails
     */
    private function makeRequest(string $url): array
    {
        $httpClient = HttpFactory::getHttp();

        $options = [
            'timeout' => self::REQUEST_TIMEOUT,
            'headers' => [
                'User-Agent' => 'Joomla-ModUnwetterwarnung/1.0'
            ]
        ];

        $response = $httpClient->get($url, [], $options);

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
     * [CUSTOM FUNCTION] Transforms raw API alerts into standardized format.
     * Processes raw alert data from the API, normalizes severity levels,
     * and sorts alerts by priority for consistent display.
     *
     * @param   array $alerts Raw alerts from API
     *
     * @return  array Processed alerts
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#process-alerts
     * @since 1.0.0
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
     * [CUSTOM FUNCTION] Analyzes alert content to determine severity level.
     * Examines event type and description keywords to classify alerts
     * into appropriate severity levels (extreme, severe, moderate, minor).
     *
     * @param   array $alert Alert data
     *
     * @return  string Severity level
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#determine-severity
     * @since 1.0.0
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
     * Determine alert urgency based on timing
     *
     * [CUSTOM FUNCTION] Calculates urgency level based on alert timing.
     * Analyzes the time difference between current time and alert start
     * to determine urgency level (immediate, expected, future).
     *
     * @param   array $alert Alert data
     *
     * @return  string Urgency level
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#determine-urgency
     * @since 1.0.0
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
     * Determine alert certainty based on description keywords
     *
     * [CUSTOM FUNCTION] Analyzes description to determine certainty level.
     * Examines alert description for certainty keywords to classify
     * alerts into certainty levels (observed, likely, possible, unlikely).
     *
     * @param   array $alert Alert data
     *
     * @return  string Certainty level
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#determine-certainty
     * @since 1.0.0
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
     * [CUSTOM FUNCTION] Converts severity levels to numeric weights.
     * Provides numeric values for severity levels to enable proper
     * sorting of alerts by importance/severity.
     *
     * @param   string $severity Severity level
     *
     * @return  int Numeric weight
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#severity-weight
     * @since 1.0.0
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
     * [CUSTOM FUNCTION] Validates OpenWeatherMap API key format.
     * Checks if the provided API key matches the expected format
     * for OpenWeatherMap API keys (32 character alphanumeric string).
     *
     * @param   string $apiKey API key to validate
     *
     * @return  bool True if valid format
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#validate-api-key
     * @since 1.0.0
     */
    public static function validateApiKey(string $apiKey): bool
    {
        // OpenWeatherMap API keys are 32 character alphanumeric strings
        return preg_match('/^[a-zA-Z0-9]{32}$/', $apiKey) === 1;
    }
}
