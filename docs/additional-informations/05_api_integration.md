# API Integration Guidelines

## OpenWeatherMap Integration

### Interface Design
```php
// Define clear interface
interface WeatherApiInterface
{
    public function getCurrentWeather(string $location): array;
    public function getAlerts(string $location): array;
}

// Implement with OpenWeatherMap
class OpenWeatherMapClient implements WeatherApiInterface
{
    private string $apiKey;
    private int $cacheTime = 600; // 10 minutes
    
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    public function getCurrentWeather(string $location): array
    {
        // Implementation with error handling
        $url = $this->buildUrl('weather', $location);
        return $this->fetchData($url);
    }
    
    public function getAlerts(string $location): array
    {
        $url = $this->buildUrl('onecall', $location);
        $data = $this->fetchData($url);
        return $data['alerts'] ?? [];
    }
}
```

## Error Handling Pattern

```php
try {
    $warnings = $this->apiClient->getAlerts($location);
} catch (ApiException $e) {
    Log::error('Weather API Error: ' . $e->getMessage());
    return $this->getFallbackData();
} catch (\Exception $e) {
    Log::error('Unexpected error: ' . $e->getMessage());
    return [];
}
```

## Caching Strategy

```php
class CachedWeatherClient implements WeatherApiInterface
{
    private WeatherApiInterface $client;
    private CacheInterface $cache;
    private int $ttl;
    
    public function getAlerts(string $location): array
    {
        $cacheKey = 'weather_alerts_' . md5($location);
        
        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== false) {
            return $cached;
        }
        
        // Fetch fresh data
        $data = $this->client->getAlerts($location);
        
        // Cache the result
        $this->cache->store($cacheKey, $data, $this->ttl);
        
        return $data;
    }
}
```

## API Configuration

```xml
<!-- In mod_unwetterwarnung.xml -->
<fieldset name="api_settings">
    <field name="api_key" 
           type="text" 
           label="MOD_UNWETTERWARNUNG_API_KEY_LABEL"
           description="MOD_UNWETTERWARNUNG_API_KEY_DESC"
           required="true" />
           
    <field name="cache_time"
           type="number"
           label="MOD_UNWETTERWARNUNG_CACHE_TIME_LABEL"
           description="MOD_UNWETTERWARNUNG_CACHE_TIME_DESC"
           default="600"
           min="60"
           max="3600" />
           
    <field name="units"
           type="list"
           label="MOD_UNWETTERWARNUNG_UNITS_LABEL"
           default="metric">
        <option value="metric">Metric</option>
        <option value="imperial">Imperial</option>
    </field>
</fieldset>
```

## Rate Limiting

```php
class RateLimitedClient implements WeatherApiInterface
{
    private WeatherApiInterface $client;
    private RateLimiter $limiter;
    
    public function getAlerts(string $location): array
    {
        // Check rate limit
        if (!$this->limiter->allowRequest()) {
            throw new RateLimitException('API rate limit exceeded');
        }
        
        // Make request
        return $this->client->getAlerts($location);
    }
}
```

## Response Validation

```php
private function validateApiResponse(array $response): bool
{
    // Check required fields
    if (!isset($response['cod']) || $response['cod'] !== 200) {
        return false;
    }
    
    // Validate data structure
    if (!isset($response['weather']) || !is_array($response['weather'])) {
        return false;
    }
    
    return true;
}
```

## Fallback Strategy

```php
private function getFallbackData(): array
{
    return [
        'status' => 'error',
        'message' => Text::_('MOD_UNWETTERWARNUNG_API_UNAVAILABLE'),
        'cached' => $this->getLastCachedData(),
        'timestamp' => time()
    ];
}
```
