# MCP Tool Usage

## MCP Tools in Project Overview:
- **Context7 MCP**: For Joomla 5.x+ documentation, best practices, and API references.
- **Playwright MCP**: For writing, running, and debugging tests in the module development process.
- **Sequential Thinking**: Always analyze and plan before coding, using the MCP tools for reference.
- **Puppeteer MCP**: For additional browser automation and testing

## Context7 MCP

### When to Use
- Joomla API questions
- Best practices clarification
- Documentation lookup
- Code examples from core

### Available URLs
```
https://context7.com/joomla/joomla-cms
https://context7.com/joomla/manual
https://context7.com/context7/openweathermap_org-current
https://context7.com/leaflet/leaflet
```

### Usage Examples
```
# Look up Joomla module structure
context7: "joomla module dispatcher"

# Find OpenWeatherMap API endpoints
context7: "openweathermap alerts api"

# Research Leaflet map integration
context7: "leaflet marker clustering"
```

### Common Queries
- "Joomla 5 module service provider"
- "Joomla dependency injection"
- "Joomla cache API"
- "OpenWeatherMap one call API"
- "Leaflet responsive maps"

---

## Playwright MCP

### When to Use
- Writing test files
- Running tests
- Debugging failures
- Setting up test environment

### Test Commands

#### Basic Execution
```bash
# Run all module tests
npx playwright test tests/playwright/mod_unwetterwarnung/

# Run specific test file
npx playwright test tests/playwright/mod_unwetterwarnung/basic.spec.js

# Run in headed mode (see browser)
npx playwright test --headed

# Run specific browser
npx playwright test --project=chromium
```

#### Development Mode
```bash
# UI mode (recommended for development)
npx playwright test --ui

# Debug mode (opens Playwright inspector)
npx playwright test --debug

# Watch mode
npx playwright test --watch
```

#### Test Options
```bash
# Stop on first failure
npx playwright test --bail

# Run tests in parallel
npx playwright test --workers=4

# Generate HTML report
npx playwright test --reporter=html
```

### Test Configuration
```javascript
// Custom timeout for slow operations
test.setTimeout(60000);

// Retry flaky tests
test.describe.configure({ retries: 2 });

// Run tests in sequence
test.describe.configure({ mode: 'serial' });
```

### Debugging Tips
1. Use `page.pause()` to stop execution
2. Take screenshots: `await page.screenshot({ path: 'debug.png' })`
3. Check console: `page.on('console', msg => console.log(msg.text()))`
4. Trace viewer: `npx playwright show-trace trace.zip`

## Puppeteer MCP

### When to Use
- For advanced browser automation
- When Playwright does not support a specific feature
- For legacy codebases that still use Puppeteer
- Debugging complex browser interactions
---


## Tool Integration Workflow

### 1. Research Phase
```
Context7 → Joomla documentation
Context7 → API documentation
Context7 → Best practices
```

### 2. Implementation Phase
```
Code → Test with Playwright
Fail → Debug with Playwright
Pass → Continue coding
```

### 3. Validation Phase
```
Playwright → Full test suite
Context7 → Verify standards
Playwright → Performance tests
```

### Best Practices
- Always research before implementing
- Write tests before complex features
- Use Context7 for clarification
- Run Playwright tests frequently
- Don't skip the research phase
