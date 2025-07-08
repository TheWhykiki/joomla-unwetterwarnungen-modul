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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filter\InputFilter;
use Joomla\Registry\Registry;
use InvalidArgumentException;
use Whykiki\Module\Unwetterwarnung\Site\Helper\OpenWeatherAPIHelper;

/**
 * Helper class for Weather Warning module
 *
 * Provides methods to retrieve and process weather warnings from the OpenWeather API.
 * Handles caching, error states, and data transformation for the module display.
 * Implements proper error handling and follows Joomla 5.x+ patterns.
 *
 * @see https://github.com/whykiki/mod_unwetterwarnung#unwetterwarnunghelper
 * @since  1.0.0
 */
class UnwetterwarnungHelper
{
    /**
     * Default cache time in seconds (30 minutes)
     *
     * @var    integer
     * @since  1.0.0
     */
    private const DEFAULT_CACHE_TIME = 1800;

    /**
     * The application instance
     *
     * @var    SiteApplication
     * @since  1.0.0
     */
    private SiteApplication $app;

    /**
     * Constructor
     *
     * @since   1.0.0
     */
    public function __construct()
    {
        // No dependencies needed - SiteApplication is passed via method parameters
    }

    /**
     * Retrieves weather warnings for a specific location
     *
     * [CUSTOM FUNCTION] Main function for retrieving weather warnings.
     * Manages caching, API calls and data processing with comprehensive
     * error handling and parameter validation.
     *
     * @param   Registry        $params  Module parameters
     * @param   SiteApplication $app     The application
     *
     * @return  array  Array of weather warnings
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#getwarnings
     * @since   1.0.0
     */
    public function getWarnings(Registry $params, SiteApplication $app): array
    {
        // Validate required parameters
        $apiKey = $params->get('api_key', '');
        if (empty($apiKey)) {
            return [];
        }

        $location = $params->get('location', '');
        if (empty($location)) {
            return [];
        }

        // Validate and sanitize location
        try {
            $location = $this->validateLocation($location);
        } catch (InvalidArgumentException $e) {
            Log::add(
                'Invalid location format: ' . $e->getMessage(),
                Log::WARNING,
                'mod_unwetterwarnung'
            );
            return [];
        }

        // Get cache settings
        $cacheTime = (int) $params->get('cache_time', self::DEFAULT_CACHE_TIME);
        $maxWarnings = (int) $params->get('max_warnings', 5);

        // Try to get cached data first
        $cacheKey = $this->generateCacheKey($location, $apiKey);
        $cachedData = $this->getCachedAlerts($cacheKey, $cacheTime, $app);

        if ($cachedData !== null) {
            return $this->limitWarnings($cachedData, $maxWarnings);
        }

        try {
            // Get OpenWeatherAPIHelper from container
            $apiClient = Factory::getContainer()->get(OpenWeatherAPIHelper::class);

            // Get coordinates for location if needed
            $coordinates = $this->getCoordinates($location, $params, $app, $apiClient);

            // Fetch weather alerts
            $lang = $app->getLanguage()->getTag();
            $lang = substr($lang, 0, 2); // Get language code (de, en, etc.)

            $apiData = $apiClient->getWeatherAlerts(
                $params,
                $app,
                $coordinates['lat'],
                $coordinates['lon'],
                $lang
            );

            $formattedData = $this->formatAlertData($apiData);

            // Cache the results
            $this->setCachedAlerts($cacheKey, $formattedData, $cacheTime, $app);

            return $this->limitWarnings($formattedData, $maxWarnings);

        } catch (\Exception $e) {
            Log::add(
                'Weather Warning Module Error: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );

            return [];
        }
    }

    /**
     * Gets DWD Geoserver configuration for interactive map
     *
     * [CUSTOM FUNCTION] Creates configuration for DWD weather maps.
     * Prepares configuration settings for the DWD map template including
     * coordinates, zoom level, height, and layer visibility options.
     *
     * @param   Registry  $params  Module parameters
     *
     * @return  array  Configuration array for DWD map
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#dwd-config
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
     * [CUSTOM FUNCTION] Ensures location string is safe for API requests.
     * Ensures the location string is safe for API requests and properly formatted.
     * Supports city names, postal codes, and coordinates (lat,lon).
     *
     * @param   string  $location  Raw location input from user
     *
     * @return  string  Sanitized location string ready for API use
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#validate-location
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
     * Formats API alert data into module display format
     *
     * [CUSTOM FUNCTION] Transforms raw API data into standardized format.
     * Converts raw API response into standardized alert format with severity mapping,
     * time formatting, and description processing for template rendering.
     *
     * @param   array  $rawData  Raw alert data from the API
     *
     * @return  array  Formatted alerts ready for display
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#format-alert-data
     * @since   1.0.0
     */
    protected function formatAlertData(array $rawData): array
    {
        if (empty($rawData)) {
            return [];
        }

        $formattedAlerts = [];

        foreach ($rawData as $alert) {
            $formattedAlerts[] = [
                'id' => md5($alert['event'] . $alert['start'] . $alert['description']),
                'event' => $alert['event'] ?? Text::_('MOD_UNWETTERWARNUNG_UNKNOWN_EVENT'),
                'description' => $alert['description'] ?? '',
                'severity' => $this->mapSeverity($alert['severity'] ?? 'minor'),
                'urgency' => $alert['urgency'] ?? 'unknown',
                'certainty' => $alert['certainty'] ?? 'unknown',
                'start' => $this->formatTimestamp($alert['start'] ?? time()),
                'end' => $this->formatTimestamp($alert['end'] ?? time()),
                'tags' => $alert['tags'] ?? [],
                'sender' => $alert['sender_name'] ?? Text::_('MOD_UNWETTERWARNUNG_UNKNOWN_SENDER')
            ];
        }

        return $formattedAlerts;
    }

    /**
     * Maps API severity levels to module severity levels
     *
     * [CUSTOM FUNCTION] Converts API severity to CSS-compatible classes.
     * Maps OpenWeatherMap severity levels to Bootstrap-compatible
     * CSS classes for consistent UI representation.
     *
     * @param   string  $apiSeverity  Severity from API response
     *
     * @return  string  Mapped severity level for CSS classes
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#map-severity
     * @since   1.0.0
     */
    protected function mapSeverity(string $apiSeverity): string
    {
        $severityMap = [
            'extreme' => 'danger',
            'severe' => 'warning',
            'moderate' => 'info',
            'minor' => 'success'
        ];

        return $severityMap[$apiSeverity] ?? 'info';
    }

    /**
     * Formats a Unix timestamp for display
     *
     * [CUSTOM FUNCTION] Converts Unix timestamp to readable German format.
     * Formats timestamps using German date/time format (d.m.Y H:i)
     * for consistent display across the module.
     *
     * @param   int  $timestamp  Unix timestamp
     *
     * @return  string  Formatted date/time string
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#format-timestamp
     * @since   1.0.0
     */
    protected function formatTimestamp(int $timestamp): string
    {
        return date('d.m.Y H:i', $timestamp);
    }

    /**
     * Generates a cache key for the given location and API key
     *
     * [CUSTOM FUNCTION] Creates secure cache key for request isolation.
     * Creates a unique, secure cache key based on location and API key
     * for proper cache isolation between different configurations.
     *
     * @param   string  $location  The location string
     * @param   string  $apiKey    The API key
     *
     * @return  string  Cache key for the request
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#generate-cache-key
     * @since   1.0.0
     */
    protected function generateCacheKey(string $location, string $apiKey): string
    {
        return 'mod_unwetterwarnung_' . md5($location . $apiKey);
    }

    /**
     * Retrieves cached alert data if available and not expired
     *
     * [CUSTOM FUNCTION] Fetches cached data using Joomla's cache system.
     * Checks the Joomla cache system for existing alert data and returns it
     * if found and still valid according to the cache time setting.
     *
     * @param   string           $cacheKey   The cache key to check
     * @param   int              $cacheTime  Cache validity time in seconds
     * @param   SiteApplication  $app        The application instance
     *
     * @return  array|null  Cached data or null if not found/expired
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#get-cached-alerts
     * @since   1.0.0
     */
    protected function getCachedAlerts(string $cacheKey, int $cacheTime, SiteApplication $app): ?array
    {
        if ($cacheTime <= 0) {
            return null;
        }

        try {
            $container = Factory::getContainer();
            $cacheControllerFactory = $container->get(CacheControllerFactoryInterface::class);
            $cacheController = $cacheControllerFactory->createCacheController('output', [
                'defaultgroup' => 'mod_unwetterwarnung',
                'lifetime' => $cacheTime,
                'cachebase' => JPATH_CACHE
            ]);

            $cachedData = $cacheController->get($cacheKey);

            if ($cachedData !== false && !empty($cachedData)) {
                $decodedData = json_decode($cachedData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decodedData;
                }
            }

        } catch (\Exception $e) {
            Log::add(
                'Cache retrieval failed: ' . $e->getMessage(),
                Log::WARNING,
                'mod_unwetterwarnung'
            );
        }

        return null;
    }

    /**
     * Stores alert data in the cache system
     *
     * [CUSTOM FUNCTION] Saves data to Joomla's cache system.
     * Saves the formatted alert data to the Joomla cache system for
     * the specified cache time to reduce API calls.
     *
     * @param   string           $cacheKey   The cache key to store under
     * @param   array            $data       The data to cache
     * @param   int              $cacheTime  Cache validity time in seconds
     * @param   SiteApplication  $app        The application instance
     *
     * @return  void
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#set-cached-alerts
     * @since   1.0.0
     */
    protected function setCachedAlerts(string $cacheKey, array $data, int $cacheTime, SiteApplication $app): void
    {
        if ($cacheTime <= 0) {
            return;
        }

        try {
            $container = Factory::getContainer();
            $cacheControllerFactory = $container->get(CacheControllerFactoryInterface::class);
            $cacheController = $cacheControllerFactory->createCacheController('output', [
                'defaultgroup' => 'mod_unwetterwarnung',
                'lifetime' => $cacheTime,
                'cachebase' => JPATH_CACHE
            ]);

            $encodedData = json_encode($data);
            if ($encodedData !== false) {
                $cacheController->store($cacheKey, $encodedData);
            }

        } catch (\Exception $e) {
            Log::add(
                'Cache storage failed: ' . $e->getMessage(),
                Log::WARNING,
                'mod_unwetterwarnung'
            );
        }
    }

    /**
     * Limits the number of warnings to display
     *
     * [CUSTOM FUNCTION] Truncates warning array to prevent UI overload.
     * Truncates the warnings array to the maximum number specified
     * in the module configuration to prevent overwhelming displays.
     *
     * @param   array  $warnings     Array of formatted warnings
     * @param   int    $maxWarnings  Maximum number of warnings to return
     *
     * @return  array  Limited warnings array
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#limit-warnings
     * @since   1.0.0
     */
    protected function limitWarnings(array $warnings, int $maxWarnings): array
    {
        if ($maxWarnings <= 0 || count($warnings) <= $maxWarnings) {
            return $warnings;
        }

        return array_slice($warnings, 0, $maxWarnings);
    }

    /**
     * Gets coordinates for a location using the OpenWeatherAPIHelper
     *
     * [CUSTOM FUNCTION] Converts location string to GPS coordinates.
     * Handles both coordinate strings and location names using geocoding.
     * Supports direct lat,lon input or uses OpenWeatherMap's geocoding API.
     *
     * @param   string                $location   Location string (city name or lat,lon)
     * @param   Registry              $params     Module parameters
     * @param   SiteApplication       $app        The application
     * @param   OpenWeatherAPIHelper  $apiClient  The API client helper
     *
     * @return  array  Array with 'lat' and 'lon' keys
     *
     * @see https://github.com/whykiki/mod_unwetterwarnung#get-coordinates
     * @since   1.0.0
     * @throws  \Exception  When coordinates cannot be determined
     */
    protected function getCoordinates(string $location, Registry $params, SiteApplication $app, OpenWeatherAPIHelper $apiClient): array
    {
        // Check if location is already in lat,lon format
        if (preg_match('/^(-?\d+\.?\d*),(-?\d+\.?\d*)$/', $location, $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lon' => (float) $matches[2]
            ];
        }

        // Use geocoding to get coordinates
        $geocodeResults = $apiClient->geocodeLocation($params, $app, $location, 1);

        if (empty($geocodeResults)) {
            throw new \Exception('Location not found: ' . $location);
        }

        return [
            'lat' => (float) $geocodeResults[0]['lat'],
            'lon' => (float) $geocodeResults[0]['lon']
        ];
    }
}
