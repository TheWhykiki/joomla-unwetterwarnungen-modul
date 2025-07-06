# Implementation Guidelines

## Core Rules

1. **MVP First**: Start with minimal working version
2. **Joomla Standards**: Follow Joomla 5.x coding standards strictly
3. **Use Core**: Leverage Joomla's built-in helpers (ArrayHelper, Text, etc.)
4. **Test Early**: Write Playwright tests before complex features
5. **Document Progress**: Update TASKS.md after each milestone

## Code Quality Standards

### Good Practice Example
```php
// ✅ Good: Single responsibility
public function getWeatherWarnings(string $location): array
{
    $apiData = $this->fetchFromApi($location);
    return $this->parseWarnings($apiData);
}
```

### Bad Practice Example
```php
// ❌ Bad: Multiple concerns
public function getAndDisplayWarnings($location)
{
    // Fetching, parsing, and rendering in one method
    $data = file_get_contents($api_url);
    $parsed = json_decode($data);
    echo '<div>' . $parsed->warning . '</div>';
}
```

## File Size Limits

- **PHP Classes**: < 300 lines
- **Helper Methods**: < 50 lines each
- **Template Files**: < 150 lines
- **JavaScript**: < 200 lines per file

## Function Size Rule

- Functions: < 50 lines
- Single responsibility only
- Clear inputs/outputs
- Avoid blending concerns

## Abstraction & Centralization Rules

### DRY Principle
- Refactor only after 2+ repetitions
- Don't over-engineer early

### Centralize These Elements
- **Config**: Module params in `mod_unwetterwarnung.xml`
- **Constants**: In `UnwetterwarnungHelper.php`
- **Logging**: Use Joomla logger
- **Errors**: Try-Catch for API calls
- **Data access**: Helper class

### Design Patterns
- Use Dependency Injection via `services/provider.php`
- Interface-driven design
- Favor composition over inheritance
- Abstract APIs via adapters
- Keep decisions reversible

## Communication Style

- **Focus**: Core module functionality
- **Use**: Concise, structured notes
- **Avoid**: Redundancy, vague descriptions
- **Style**: Analyst briefing, clear and sharp
- **Follow**: SOLID, DRY, SRP principles
