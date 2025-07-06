# Testing Strategy

## Test Philosophy

**CRITICAL**: Tests must catch real bugs, not just pass

1. **Integration Tests First**: Test full module flow at `https://openweather.joomla.local`
2. **Mock Only Externals**: Weather APIs, not Joomla internals
3. **Real Environment**: Test directly on the local Joomla instance
4. **Test Errors**: Simulate failed API calls, invalid configs
5. **Quality > Coverage**: Tests must catch actual issues

## Playwright Configuration

```javascript
// tests/playwright/playwright.config.js
module.exports = {
  testDir: './mod_unwetterwarnung',
  timeout: 30000,
  use: {
    baseURL: 'https://openweather.joomla.local',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],
};
```

## Test Scenarios

### 1. Module Rendering
- Verify module loads without errors
- Check default layout displays
- Validate CSS/JS loading

### 2. API Integration
- Test successful API calls
- Verify error handling
- Check cache functionality

### 3. User Interactions
- Test carousel navigation
- Verify map interactions
- Check responsive behavior

### 4. Edge Cases
- No API key configured
- Invalid location
- Network timeouts
- Empty responses

## Test Execution Commands

```bash
# Run all tests
npx playwright test tests/playwright/mod_unwetterwarnung/

# Debug mode
npx playwright test --debug

# UI mode (recommended for development)
npx playwright test --ui

# Run with specific browser
npx playwright test --project=chromium --headed

# Stop on first failure
npx playwright test --bail
```

## Test File Structure

```
tests/playwright/mod_unwetterwarnung/
├── basic.spec.js          # Core functionality
├── api-errors.spec.js     # Error scenarios
├── layouts.spec.js        # Layout variations
├── multilingual.spec.js  # Language switching
└── performance.spec.js    # Load time checks
```

## Test Best Practices

### Red-Green-Refactor
1. Write failing test first
2. Implement code to pass
3. Refactor while keeping green

### Test Patterns
```javascript
// Good test structure
test('should display weather warning when API returns data', async ({ page }) => {
  // Arrange
  await page.goto('/');
  
  // Act
  await page.waitForSelector('.weather-warning');
  
  // Assert
  const warning = await page.textContent('.weather-warning');
  expect(warning).toContain('Storm Warning');
});
```

### Common Test Helpers
```javascript
// Wait for module to load
await page.waitForSelector('.mod-unwetterwarnung');

// Check for error states
await expect(page.locator('.error-message')).toBeVisible();

// Verify API calls
await page.waitForResponse(response => 
  response.url().includes('api.openweathermap.org')
);
```

## Test Quality Metrics

- Tests must catch last 3 bugs fixed
- Rewrite failing or flaky tests
- Each test should have clear purpose
- Avoid testing implementation details
- Focus on user-visible behavior
