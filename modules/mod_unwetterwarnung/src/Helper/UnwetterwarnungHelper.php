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

// Direktzugriff verhindern
\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;
use InvalidArgumentException;

/**
 * Helper class for Weather Warning module
 *
 * Provides methods to retrieve and process weather warnings from the OpenWeather API.
 * Handles caching, error states, and data transformation for the module display.
 * Implements proper error handling and follows Joomla 5.x+ patterns.
 *
 * @since  1.0.0
 */
class UnwetterwarnungHelper
{
    /**
     * OpenWeather API base URL
     *
     * @var    string
     * @since  1.0.0
     */
    private const API_BASE_URL = 'https://api.openweathermap.org/data/3.0/onecall';
    
    /**
     * Default cache time in seconds (30 minutes)
     *
     * @var    integer
     * @since  1.0.0
     */
    private const DEFAULT_CACHE_TIME = 1800;
    
    /**
     * The application object
     *
     * Used to access application-wide settings and services like input handling,
     * session management, and configuration values.
     *
     * @var    SiteApplication
     * @since  1.0.0
     */
    private SiteApplication $app;
    
    /**
     * Cache controller factory
     *
     * Used to create cache controllers for storing and retrieving
     * weather data to reduce API calls and improve performance.
     *
     * @var    CacheControllerFactoryInterface
     * @since  1.0.0
     */
    private CacheControllerFactoryInterface $cacheFactory;
    
    /**
     * Constructor
     *
     * Initializes the helper with required dependencies.
     * All dependencies are injected rather than retrieved from factories
     * following Joomla 5.x+ dependency injection patterns.
     *
     * @param   SiteApplication                    $app           The application object
     * @param   CacheControllerFactoryInterface    $cacheFactory  The cache factory
     *
     * @since   1.0.0
     */
    public function __construct(
        SiteApplication $app,
        CacheControllerFactoryInterface $cacheFactory
    ) {
        $this->app = $app;
        $this->cacheFactory = $cacheFactory;
    }
    
    /**
     * Retrieves weather warnings for a specific location
     *
     * Fetches current weather alerts from the OpenWeather API.
     * Results are cached to reduce API calls and improve performance.
     * Handles both city names and coordinate-based locations.
     *
     * @param   string  $location  The location to check (city name or coordinates)
     * @param   array   $params    Module parameters including API key and cache settings
     *
     * @return  array  Array of weather warnings with severity, title, and description
     *
     * @since   1.0.0
     * @throws  RuntimeException  When API key is missing or API request fails
     */
    public function getWarnings(string $location, array $params): array
    {
        // Validate required parameters
        $apiKey = ArrayHelper::getValue($params, 'api_key', '');
        if (empty($apiKey)) {
            throw new RuntimeException('API key is required');
        }
        
        // Validate and sanitize location
        $location = $this->validateLocation($location);
        
        // Get cache settings
        $cacheTime = (int) ArrayHelper::getValue($params, 'cache_time', self::DEFAULT_CACHE_TIME);
        $maxWarnings = (int) ArrayHelper::getValue($params, 'max_warnings', 5);
        
        // Try to get cached data first
        $cacheKey = $this->generateCacheKey($location, $apiKey);
        $cachedData = $this->getCachedAlerts($cacheKey, $cacheTime);
        
        if ($cachedData !== null) {
            return $this->limitWarnings($cachedData, $maxWarnings);
        }
        
        // Fetch fresh data from API
        $apiData = $this->fetchFromApi($location, $apiKey, $params);
        $formattedData = $this->formatAlertData($apiData);
        
        // Cache the results
        $this->setCachedAlerts($cacheKey, $formattedData, $cacheTime);
        
        return $this->limitWarnings($formattedData, $maxWarnings);
    }
    
    /**
     * Gets DWD Geoserver configuration for interactive map
     *
     * Prepares configuration settings for the DWD map template including
     * coordinates, zoom level, height, and layer visibility options.
     *
     * @param   Registry  $params  Module parameters
     *
     * @return  array  Configuration array for DWD map
     *
     * @since   1.0.0
     */
    public function getDwdGeoserverConfig(Registry $params): array
    {
        return [
            'center' => [
                (float) $params->get('map_center_lat', 50.264024),
                (float) $params->get('map_center_lon', 9.319105)
            ],
            'zoom' => (int) $params->get('map_zoom', 10),
            'height' => (int) $params->get('map_height', 400),
            'showGemeinden' => (bool) $params->get('show_gemeindegrenzen', 1),
            'wmsUrl' => 'https://maps.dwd.de/geoserver/dwd/wms/',
            'warnLayer' => 'Warnungen_Gemeinden_vereinigt',
            'gemeindeLayer' => 'Warngebiete_Gemeinden',
            'osmUrl' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'osmAttribution' => 'Map data: &copy; <a href="https://openstreetmap.org" target="_blank">OpenStreetMap</a> contributors',
            'dwdAttribution' => 'Warndaten: &copy; <a href="https://www.dwd.de" target="_blank">DWD</a>',
            'maxZoom' => 18
        ];
    }
    
    /**
     * Validates and sanitizes location input
     *
     * Ensures the location string is safe for API requests and properly formatted.
     * Supports city names, postal codes, and coordinates (lat,lon).
     *
     * @param   string  $location  Raw location input from user
     *
     * @return  string  Sanitized location string ready for API use
     *
     * @since   1.0.0
     * @throws  InvalidArgumentException  When location format is invalid
     */
    protected function validateLocation(string $location): string
    {
        $location = trim($location);
        
        if (empty($location)) {
            throw new InvalidArgumentException('Location cannot be empty');
        }
        
        // Check if it's coordinates (lat,lon format)
        if (preg_match('/^-?\d+\.?\d*,-?\d+\.?\d*$/', $location)) {
            return $location;
        }
        
        // Sanitize city name
        $location = filter_var($location, FILTER_SANITIZE_STRING);
        
        if (empty($location)) {
            throw new InvalidArgumentException('Invalid location format');
        }
        
        return $location;
    }
    
    /**
     * Fetches weather data from the OpenWeather API
     *
     * Makes HTTP request to the OpenWeather API and handles potential errors.
     * Converts location to coordinates if necessary and includes proper error handling.
     *
     * @param   string  $location  The location to fetch data for
     * @param   string  $apiKey    The OpenWeather API key
     * @param   array   $params    Additional parameters for the API request
     *
     * @return  array  Raw API response data
     *
     * @since   1.0.0
     * @throws  RuntimeException  When API request fails
     */
    protected function fetchFromApi(string $location, string $apiKey, array $params): array
    {
        // Get coordinates for the location if needed
        $coordinates = $this->getCoordinates($location, $apiKey);
        
        // Build API URL
        $url = $this->buildApiUrl($coordinates, $apiKey, $params);
        
        // Make HTTP request
        $http = HttpFactory::getHttp();
        
        try {
            $response = $http->get($url);
            
            if ($response->code !== 200) {
                throw new RuntimeException('API request failed with status: ' . $response->code);
            }
            
            $data = json_decode($response->body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException('Invalid JSON response from API');
            }
            
            return $data;
            
        } catch (\Exception $e) {
            Log::add(
                'OpenWeather API Error: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );
            
            throw new RuntimeException('Failed to fetch weather data: ' . $e->getMessage());
        }
    }
    
    /**
     * Formats raw API data into standardized warning structure
     *
     * Converts OpenWeather API response into a consistent format for display.
     * Handles missing fields and provides fallback values.
     *
     * @param   array  $rawData  Raw API response data
     *
     * @return  array  Formatted array of weather warnings
     *
     * @since   1.0.0
     */
    protected function formatAlertData(array $rawData): array
    {
        $warnings = [];
        
        // Check if alerts exist in the response
        if (!isset($rawData['alerts']) || !is_array($rawData['alerts'])) {
            return $warnings;
        }
        
        foreach ($rawData['alerts'] as $alert) {
            $warnings[] = [
                'id' => $alert['id'] ?? uniqid(),
                'title' => $alert['event'] ?? Text::_('MOD_UNWETTERWARNUNG_UNKNOWN_EVENT'),
                'description' => $alert['description'] ?? '',
                'severity' => $this->mapSeverity($alert['severity'] ?? 'unknown'),
                'start' => $alert['start'] ?? time(),
                'end' => $alert['end'] ?? null,
                'sender' => $alert['sender_name'] ?? Text::_('MOD_UNWETTERWARNUNG_UNKNOWN_SENDER'),
                'tags' => $alert['tags'] ?? [],
            ];
        }
        
        return $warnings;
    }
    
    /**
     * Maps API severity levels to standardized values
     *
     * Converts OpenWeather severity strings to consistent severity levels
     * for styling and display purposes.
     *
     * @param   string  $apiSeverity  Severity from API response
     *
     * @return  string  Standardized severity level
     *
     * @since   1.0.0
     */
    protected function mapSeverity(string $apiSeverity): string
    {
        $severityMap = [
            'minor' => 'low',
            'moderate' => 'medium',
            'severe' => 'high',
            'extreme' => 'critical',
        ];
        
        return $severityMap[$apiSeverity] ?? 'unknown';
    }
    
    /**
     * Generates cache key for location and API key combination
     *
     * Creates a unique cache key based on location and API key
     * to ensure proper cache isolation between different configurations.
     *
     * @param   string  $location  The location string
     * @param   string  $apiKey    The API key
     *
     * @return  string  Cache key
     *
     * @since   1.0.0
     */
    protected function generateCacheKey(string $location, string $apiKey): string
    {
        return 'mod_unwetterwarnung_' . md5($location . $apiKey);
    }
    
    /**
     * Retrieves cached weather alerts
     *
     * Attempts to retrieve cached weather data for the given cache key.
     * Returns null if cache is expired or doesn't exist.
     *
     * @param   string   $cacheKey   The cache key to retrieve
     * @param   integer  $cacheTime  Cache lifetime in seconds
     *
     * @return  array|null  Cached data or null if not found/expired
     *
     * @since   1.0.0
     */
    protected function getCachedAlerts(string $cacheKey, int $cacheTime): ?array
    {
        try {
            /** @var CallbackController $cache */
            $cache = $this->cacheFactory->createCacheController('callback', [
                'defaultgroup' => 'mod_unwetterwarnung',
                'cachebase' => $this->app->get('cache_path', JPATH_CACHE),
                'lifetime' => $cacheTime,
            ]);
            
            $data = $cache->get($cacheKey);
            
            return is_array($data) ? $data : null;
            
        } catch (\Exception $e) {
            Log::add(
                'Cache retrieval error: ' . $e->getMessage(),
                Log::WARNING,
                'mod_unwetterwarnung'
            );
            
            return null;
        }
    }
    
    /**
     * Stores weather alerts in cache
     *
     * Saves formatted weather data to cache for future requests.
     * Handles cache errors gracefully without affecting main functionality.
     *
     * @param   string   $cacheKey   The cache key to store under
     * @param   array    $data       The data to cache
     * @param   integer  $cacheTime  Cache lifetime in seconds
     *
     * @return  void
     *
     * @since   1.0.0
     */
    protected function setCachedAlerts(string $cacheKey, array $data, int $cacheTime): void
    {
        try {
            /** @var CallbackController $cache */
            $cache = $this->cacheFactory->createCacheController('callback', [
                'defaultgroup' => 'mod_unwetterwarnung',
                'cachebase' => $this->app->get('cache_path', JPATH_CACHE),
                'lifetime' => $cacheTime,
            ]);
            
            $cache->store($data, $cacheKey);
            
        } catch (\Exception $e) {
            Log::add(
                'Cache storage error: ' . $e->getMessage(),
                Log::WARNING,
                'mod_unwetterwarnung'
            );
        }
    }
    
    /**
     * Limits the number of warnings returned
     *
     * Restricts the warning array to the specified maximum number
     * while preserving the most severe warnings first.
     *
     * @param   array    $warnings     Array of weather warnings
     * @param   integer  $maxWarnings  Maximum number of warnings to return
     *
     * @return  array  Limited array of warnings
     *
     * @since   1.0.0
     */
    protected function limitWarnings(array $warnings, int $maxWarnings): array
    {
        if (count($warnings) <= $maxWarnings) {
            return $warnings;
        }
        
        // Sort by severity (most severe first)
        usort($warnings, function ($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1, 'unknown' => 0];
            return ($severityOrder[$b['severity']] ?? 0) - ($severityOrder[$a['severity']] ?? 0);
        });
        
        return array_slice($warnings, 0, $maxWarnings);
    }
    
    /**
     * Gets coordinates for a location
     *
     * Placeholder method for coordinate resolution.
     * In a full implementation, this would handle geocoding.
     *
     * @param   string  $location  Location string
     * @param   string  $apiKey    API key for geocoding
     *
     * @return  array  Coordinates array with lat and lon
     *
     * @since   1.0.0
     */
    protected function getCoordinates(string $location, string $apiKey): array
    {
        // TODO: Implement geocoding for city names
        // For now, assume coordinates are provided directly
        if (preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/', $location, $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lon' => (float) $matches[2],
            ];
        }
        
        // Fallback coordinates (Berlin)
        return [
            'lat' => 52.5200,
            'lon' => 13.4050,
        ];
    }
    
    /**
     * Builds the complete API URL
     *
     * Constructs the OpenWeather API URL with all necessary parameters.
     *
     * @param   array   $coordinates  Latitude and longitude
     * @param   string  $apiKey       API key
     * @param   array   $params       Additional parameters
     *
     * @return  string  Complete API URL
     *
     * @since   1.0.0
     */
    protected function buildApiUrl(array $coordinates, string $apiKey, array $params): string
    {
        $queryParams = [
            'lat' => $coordinates['lat'],
            'lon' => $coordinates['lon'],
            'appid' => $apiKey,
            'exclude' => 'minutely,hourly,daily',
            'lang' => ArrayHelper::getValue($params, 'language_override', 'en'),
            'units' => ArrayHelper::getValue($params, 'units', 'metric'),
        ];
        
        return self::API_BASE_URL . '?' . http_build_query($queryParams);
    }
} 
