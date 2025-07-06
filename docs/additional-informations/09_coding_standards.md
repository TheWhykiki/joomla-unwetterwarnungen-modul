# Coding Standards for Joomla 5.x+ Modules

# Joomla 5/6 Coding Standards

## Kritische Anforderungen

### Kompatibilität
- ✅ **MUSS** mit deaktiviertem Backward Compatibility Plugin funktionieren
- ✅ **MUSS** bei `error_reporting(E_ALL)` fehlerfrei laufen
- ✅ **KEINE** PHP Notices, Warnings oder Deprecated-Meldungen
- ✅ **Joomla 6 ready**: Keine veralteten APIs verwenden

### Entwicklungseinstellungen
```php
// Für Entwicklung in configuration.php
public $error_reporting = 'maximum';
public $debug = true;
public $debug_lang = true;
```


## DocBlock Standards

### Datei-Header (PFLICHT)
```php
<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Direktzugriff verhindern
\defined('_JEXEC') or die;
```

### Klassen-DocBlock
```php
/**
 * Helper class for Weather Warning module
 *
 * Provides methods to retrieve and process weather warnings from external APIs.
 * Handles caching, error states, and data transformation for the module display.
 *
 * @since  1.0.0
 */
class UnwetterwarnungHelper
{
    /**
     * Retrieves weather warnings for a specific location
     *
     * Fetches current weather alerts from the configured API provider (OpenWeatherMap).
     * Results are cached to reduce API calls and improve performance.
     *
     * @param   string  $location  The location to check (city name or coordinates)
     * @param   array   $params    Module parameters including API key and cache settings
     *
     * @return  array  Array of weather warnings with severity, title, and description
     *
     * @since   1.0.0
     * @throws  \RuntimeException  When API key is missing or API request fails
     */
    public function getWarnings(string $location, array $params): array
    {
        // Implementation
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
     * @throws  \InvalidArgumentException  When location format is invalid
     */
    protected function validateLocation(string $location): string
    {
        // Implementation
    }
}
```

### Property-DocBlock
```php
/**
 * The application object
 *
 * Used to access application-wide settings and services like input handling,
 * session management, and configuration values.
 *
 * @var    ApplicationInterface
 * @since  1.0.0
 */
private ApplicationInterface $app;

/**
 * Cache time in seconds
 *
 * Determines how long API responses are cached before making a new request.
 * Default is 600 seconds (10 minutes) to respect API rate limits.
 *
 * @var    integer
 * @since  1.0.0
 */
protected int $cacheTime = 600;

/**
 * Weather API client instance
 *
 * Handles all communication with the external weather service API.
 * Configured with API key and endpoints during construction.
 *
 * @var    WeatherApiInterface|null
 * @since  1.0.0
 */
private ?WeatherApiInterface $apiClient = null;
```

## Namespace & Use Statements

### Korrekte Reihenfolge
```php
<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Unwetterwarnung\Site\Helper;

// Direktzugriff verhindern
\defined('_JEXEC') or die;

// Core Joomla
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;

// Joomla Framework
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// PHP Standard
use RuntimeException;
use InvalidArgumentException;
```

## Joomla Core Funktionen nutzen

### ❌ FALSCH: Eigene Implementierung
```php
// Nicht machen!
private function sanitizeInput($input)
{
    return htmlspecialchars(strip_tags($input));
}

private function getParam($params, $key, $default = null)
{
    return isset($params[$key]) ? $params[$key] : $default;
}
```

### ✅ RICHTIG: Joomla Funktionen
```php
// Text Handling
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;

$filter = InputFilter::getInstance();
$clean = $filter->clean($input, 'string');

// Parameter Handling
use Joomla\Registry\Registry;

$params = new Registry($module->params);
$value = $params->get('key', 'default');

// Array Handling
use Joomla\Utilities\ArrayHelper;

$array = ArrayHelper::toObject($data);
$value = ArrayHelper::getValue($array, 'key', 'default');
```

## Fehlerbehandlung

### Type Declarations & Strict Types
```php
<?php

declare(strict_types=1);

/**
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 */

namespace Joomla\Module\Unwetterwarnung\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

/**
 * Dispatcher class for mod_unwetterwarnung
 *
 * Handles the module's execution flow and prepares data for rendering.
 * Implements helper factory pattern for clean dependency management.
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;
    
    /**
     * Returns the layout data
     *
     * Prepares all necessary data for the module template including
     * weather warnings, module parameters, and display settings.
     *
     * @return  array  Associative array containing all template variables
     *
     * @since   1.0.0
     * @throws  \RuntimeException  When weather data cannot be retrieved
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();
        
        // Keine undefined index warnings
        $data['warnings'] = $this->getHelperFactory()
            ->getHelper('UnwetterwarnungHelper')
            ->getWarnings(
                (string) $data['params']->get('location', ''),
                $data['params']->toArray()
            );
            
        return $data;
    }
    
    /**
     * Validates module configuration before execution
     *
     * Ensures all required parameters are present and valid.
     * Prevents module execution if critical configuration is missing.
     *
     * @return  bool  True if configuration is valid, false otherwise
     *
     * @since   1.0.0
     */
    protected function validateConfiguration(): bool
    {
        $params = $this->getParams();
        
        // Check for required API key
        if (empty($params->get('api_key'))) {
            $this->app->enqueueMessage(
                Text::_('MOD_UNWETTERWARNUNG_ERROR_NO_API_KEY'),
                'error'
            );
            return false;
        }
        
        return true;
    }
}
```

### Null Safety
```php
// ❌ FALSCH: Kann PHP Warnings erzeugen
$user = Factory::getUser();
$name = $user->name; // Warning wenn $user null

// ✅ RICHTIG: Null-safe
$user = Factory::getUser();
$name = $user?->name ?? 'Guest';

// Oder mit Type Check
if ($user instanceof User) {
    $name = $user->name;
}
```

## Service Provider (Joomla 5/6)

```php
<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The weather warning module service provider
 *
 * Registers all necessary services for the module in the dependency injection container.
 * This includes the dispatcher, helper factory, and module services.
 *
 * @since  1.0.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container
     *
     * Sets up the dependency injection for the weather warning module.
     * Registers dispatcher factory, helper factory, and module service provider.
     *
     * @param   Container  $container  The DI container to register services in
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\Joomla\\Module\\Unwetterwarnung'));
        $container->registerServiceProvider(new HelperFactory('\\Joomla\\Module\\Unwetterwarnung\\Site\\Helper'));
        $container->registerServiceProvider(new Module());
    }
};
```

## Deprecation Handling

### Vermeidung veralteter APIs
```php
// ❌ DEPRECATED in Joomla 5/6
$app = Factory::getApplication();
$db = Factory::getDbo();
$input = $app->input;

// ✅ KORREKT: Dependency Injection
use Joomla\CMS\Application\ApplicationInterface;
use Joomla\Database\DatabaseInterface;

class UnwetterwarnungHelper
{
    /**
     * The application object
     * 
     * Provides access to the Joomla application context including
     * configuration, session, and request handling.
     *
     * @var ApplicationInterface
     * @since 1.0.0
     */
    private ApplicationInterface $app;
    
    /**
     * The database connection
     * 
     * Used for storing and retrieving cached weather data
     * and module-specific settings from the database.
     *
     * @var DatabaseInterface
     * @since 1.0.0
     */
    private DatabaseInterface $db;
    
    /**
     * Constructor
     *
     * Initializes the helper with required dependencies.
     * All dependencies are injected rather than retrieved from factories.
     *
     * @param   ApplicationInterface  $app  The application object for context access
     * @param   DatabaseInterface     $db   The database object for data persistence
     *
     * @since   1.0.0
     */
    public function __construct(
        ApplicationInterface $app,
        DatabaseInterface $db
    ) {
        $this->app = $app;
        $this->db = $db;
    }
    
    /**
     * Fetches weather data from cache or API
     *
     * Attempts to retrieve cached data first, falling back to API request
     * if cache is expired or empty. Automatically updates cache with fresh data.
     *
     * @param   string  $location  Location identifier for weather data
     * @param   bool    $forceRefresh  Force API request ignoring cache
     *
     * @return  array  Weather data array with warnings and metadata
     *
     * @since   1.0.0
     * @throws  \RuntimeException  When neither cache nor API provide valid data
     */
    public function getWeatherData(string $location, bool $forceRefresh = false): array
    {
        // Implementation
    }
}
```

## Code Quality Checks

### Pre-Flight Checklist
```bash
# PHP CodeSniffer mit Joomla Standard
phpcs --standard=Joomla mod_unwetterwarnung/

# PHP Static Analysis
phpstan analyse mod_unwetterwarnung/

# Check for deprecations
grep -r "Factory::get" mod_unwetterwarnung/
grep -r "JFactory" mod_unwetterwarnung/
grep -r "jimport" mod_unwetterwarnung/
```

### Typische Fehlerquellen

| Problem | Lösung |
|---------|--------|
| Undefined array index | Nutze `??` operator oder `ArrayHelper::getValue()` |
| Type mismatch | Strict types und Type Declarations verwenden |
| Deprecated functions | Dependency Injection statt Factory |
| Missing DocBlocks | Alle public methods dokumentieren |
| Direct superglobals | Nutze `$app->getInput()` |

## Template Best Practices

```php
<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// Type safety für Template-Variablen
/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();

/** @var \Joomla\Registry\Registry $params */
/** @var array $warnings */

// Assets nur wenn benötigt laden
if (!empty($warnings)) {
    $wa->useScript('mod_unwetterwarnung.warnings');
    $wa->useStyle('mod_unwetterwarnung.style');
}

// HTML ausgeben mit proper escaping
?>
<div class="mod-unwetterwarnung">
    <?php if (empty($warnings)) : ?>
        <p><?php echo Text::_('MOD_UNWETTERWARNUNG_NO_WARNINGS'); ?></p>
    <?php else : ?>
        <ul class="warning-list">
            <?php foreach ($warnings as $warning) : ?>
                <li class="warning-item">
                    <?php echo HTMLHelper::_('string.truncate', 
                        $this->escape($warning->title), 
                        100
                    ); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
```

## Testing Standards

```php
// Entwicklung mit Maximum Error Reporting
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Test ohne B/C Layer
if (Factory::getApplication()->get('compat_plugin_enabled')) {
    Factory::getApplication()->set('compat_plugin_enabled', false);
}
```

## Zusammenfassung Checkliste

- [ ] Alle Dateien haben korrekte Header-DocBlocks
- [ ] Alle Klassen, Methoden und Properties sind dokumentiert
- [ ] `declare(strict_types=1)` in allen PHP-Dateien
- [ ] Keine direkten `Factory::get*()` Aufrufe
- [ ] Dependency Injection wird verwendet
- [ ] Keine undefined index/property Zugriffe
- [ ] Joomla Core Funktionen statt eigene Implementierungen
- [ ] Läuft ohne Backward Compatibility Plugin
- [ ] Keine Fehler bei `error_reporting(E_ALL)`
- [ ] Code mit PHPCS Joomla Standard geprüft
