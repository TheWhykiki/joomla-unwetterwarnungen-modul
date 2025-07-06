# Required File Structure

```
mod_unwetterwarnung/
├── mod_unwetterwarnung.xml              # Module manifest
├── services/
│   └── provider.php                     # DI container setup
├── src/
│   ├── Dispatcher/
│   │   └── Dispatcher.php               # Main module logic
│   ├── Helper/
│   │   ├── UnwetterwarnungHelper.php   # Data processing
│   │   └── WeatherApiClient.php        # API abstraction
│   └── Field/
│       └── LocationField.php            # Custom form field
├── tmpl/
│   ├── default.php                      # Default layout
│   ├── carousel.php                     # Carousel layout
│   └── map.php                          # Map layout
├── language/
│   ├── en-GB/
│   │   ├── mod_unwetterwarnung.ini     # Frontend strings
│   │   └── mod_unwetterwarnung.sys.ini # Backend strings
│   └── de-DE/
│       ├── mod_unwetterwarnung.ini
│       └── mod_unwetterwarnung.sys.ini
├── media/
│   ├── joomla.asset.json                # Asset registration
│   ├── css/
│   │   └── mod_unwetterwarnung.css
│   ├── js/
│   │   ├── mod_unwetterwarnung.js
│   │   └── map-handler.js
│   └── images/
│       └── weather-icons/
├── tests/
│   └── playwright/
│       └── mod_unwetterwarnung/
│           ├── basic.spec.js
│           ├── api-errors.spec.js
│           └── layouts.spec.js
├── docs/
│   └── [timestamp]_plan.md
├── tasks/
│   └── [timestamp]_task.md
├── TASKS.md                             # Current progress
└── README.md                            # Module documentation
```
## IMPORTANT:
Stick to the specified file structure under “Recommended File Structure” and the Joomla 5 standards. Sketch any missing files and then create a new module structure with the new files if necessary. However, new files should only be created in addition to the current structure if they are really needed. Pay particular attention to the "Key rules and different sections to follow" - if there are any conflicts due to defined rules, report them immediately so that the rule can be fixed

## File Descriptions

### Root Files
- **mod_unwetterwarnung.xml**: Module manifest with configuration
- **mod_unwetterwarnung.php**: Module entry point (minimal code)

### Services
- **provider.php**: Dependency injection container setup

### Source Code (src/)
- **Dispatcher.php**: Main module logic and flow control
- **UnwetterwarnungHelper.php**: Data processing and business logic
- **WeatherApiClient.php**: API abstraction layer
- **LocationField.php**: Custom Joomla form field

### Templates (tmpl/)
- **default.php**: Standard warning display
- **carousel.php**: Rotating warning display
- **map.php**: Interactive map with warnings

### Language Files
- **mod_unwetterwarnung.ini**: Frontend translations
- **mod_unwetterwarnung.sys.ini**: Backend/admin translations

### Media Assets
- **joomla.asset.json**: Asset dependencies and loading
- **CSS/JS**: Styling and interactivity
- **weather-icons/**: Visual weather indicators

### Tests
- **basic.spec.js**: Core functionality tests
- **api-errors.spec.js**: Error handling tests
- **layouts.spec.js**: Layout rendering tests

### Documentation
- **docs/**: Planning documents with timestamps
- **tasks/**: Task tracking with timestamps
- **TASKS.md**: Current progress overview
- **README.md**: Module documentation
