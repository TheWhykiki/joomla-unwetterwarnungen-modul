# Documentation Templates

## Plan File Template

```markdown
# Plan for mod_unwetterwarnung
**Date**: YYYYMMDD_HHMMSS
**Task File**: tasks/YYYYMMDD_HHMMSS_task.md

## Module Purpose
Display real-time weather warnings for specified locations with interactive visualizations

## Core Features
- Weather API integration (OpenWeatherMap)
- Multiple display layouts (default, carousel, map)
- Interactive Leaflet map
- Multilingual support (EN, DE)
- Caching for performance
- Error handling with fallbacks

## Technical Approach
- Service-oriented architecture
- Dependency injection pattern
- Cached API responses (configurable TTL)
- Progressive enhancement
- Mobile-first responsive design
- WCAG 2.1 AA accessibility compliance

## Testing Strategy
- Playwright E2E tests
- Mock API for reliability
- Cross-browser validation
- Performance benchmarks
- Error scenario coverage

## Development Phases
1. Foundation (MVP) - Basic structure and mock data
2. API Integration - Real weather data
3. Enhanced Display - All layouts and interactions
4. Polish - Optimization and documentation

## Next Steps
- Create file structure
- Implement service provider
- Write first Playwright test
```

## Task File Template

```markdown
# Tasks for mod_unwetterwarnung
**Date**: YYYYMMDD_HHMMSS
**Plan File**: docs/YYYYMMDD_HHMMSS_plan.md

## Completed ✓
- [x] Basic file structure created
- [x] Module manifest (mod_unwetterwarnung.xml)
- [x] Service provider setup
- [x] Dispatcher skeleton

## In Progress 🔄
- [ ] Helper implementation (60% complete)
  - [x] Basic structure
  - [x] Mock data method
  - [ ] API integration
- [ ] Default template (30% complete)
  - [x] Basic HTML structure
  - [ ] Data binding
  - [ ] Styling

## Pending ⏳
- [ ] WeatherApiClient class
- [ ] Caching implementation
- [ ] Carousel layout
- [ ] Map layout
- [ ] Playwright tests
- [ ] Language files
- [ ] Documentation

## Blockers 🚫
- Need API key for testing real integration
- Clarification needed on warning severity levels

## Notes
- Using OpenWeatherMap One Call API 3.0
- Cache time default: 10 minutes
- Consider rate limiting (1000 calls/day free tier)
- Map requires Leaflet 1.9.x

## Time Tracking
- Phase 1: 4 hours (estimated: 3 hours)
- Phase 2: In progress
```

## TASKS.md Template

```markdown
# mod_unwetterwarnung Development Progress

Last Updated: YYYY-MM-DD HH:MM

## Overview
Weather warning module for Joomla 4.x+ with real-time alerts and interactive map display.

## Current Status: Phase 2 - API Integration

### ✅ Completed
- Basic module structure
- Service provider with DI
- Module manifest with parameters
- Basic dispatcher implementation
- Mock data helper methods
- Default template structure
- First Playwright test

### 🔄 In Progress
- WeatherApiClient implementation
- API error handling
- Caching mechanism

### 📋 Upcoming
- Location field type
- Carousel layout
- Map integration
- Multilingual support

## Test Coverage
- Basic rendering: ✅
- API integration: ⏳
- Error handling: ❌
- Layouts: ❌
- Performance: ❌

## Known Issues
1. API key validation not implemented
2. Cache clearing needs manual intervention
3. Map markers need optimization for multiple warnings

## Performance Metrics
- Module load time: < 200ms
- API response (cached): < 50ms
- API response (fresh): < 500ms
- Map render: < 1s

## Next Milestone
Complete API integration with full error handling and caching
Target: YYYY-MM-DD
```

## README.md Template

```markdown
# Weather Warning Module (mod_unwetterwarnung)

A Joomla 4.x+ module for displaying real-time weather warnings with interactive visualizations.

## Features
- 🌩️ Real-time weather alerts from OpenWeatherMap
- 🗺️ Interactive map with warning overlays
- 🎠 Multiple display layouts (list, carousel, map)
- 🌍 Multilingual support (EN, DE)
- ⚡ Performance optimized with caching
- 📱 Fully responsive design

## Requirements
- Joomla 4.x or 5.x
- PHP 8.0+
- OpenWeatherMap API key
- Modern browser with JavaScript enabled

## Installation
1. Download the module package
2. Install via Joomla Extension Manager
3. Configure API key in module settings
4. Publish module to desired position

## Configuration
- **API Key**: Your OpenWeatherMap API key (required)
- **Location**: City name or coordinates
- **Layout**: Choose from default, carousel, or map
- **Cache Time**: API cache duration (60-3600 seconds)
- **Warning Types**: Select which warnings to display

## Development
See [TASKS.md](./TASKS.md) for development progress.

### Testing
```bash
npx playwright test tests/playwright/mod_unwetterwarnung/
```

## License
GPL v2 or later

## Support
- Documentation: [/docs](./docs)
- Issues: GitHub Issues
- Forum: Joomla Community Forum
```
