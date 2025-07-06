# Development Workflow

## Pre-Development Analysis

In `<module_planning>` tags within your thinking block:

### 1. Requirements Analysis
- Extract core functionality requirements
- Identify data sources (OpenWeatherMap API)
- Define display modes (default, carousel, map)
- List multilingual requirements

### 2. Architecture Planning
- Follow Joomla 5.x module structure
- Apply SOLID and DRY principles
- Plan helper methods and data flow
- Design error handling strategy

### 3. File Structure Validation
- Verify against recommended structure
- Identify any missing components
- Plan custom fields if needed

### 4. Task Breakdown
- Create development phases
- Define testable milestones
- Prioritize MVP features

## Development Order

### FOLLOW IN STRICT ORDER:
1. **Analyze**: Extract clear, minimal requirements
2. **Think**: ALWAYS consider the problem BEFORE coding
3. **Research**: Use MCPs for Joomla 5.x+ documentation
4. **Reference**: Use Joomla Core Modules as coding standard
5. **Reuse**: Use existing Joomla Functions and Helpers
6. **Standards**: ALWAYS follow Joomla 5 Coding Standards
7. **Plan**: Develop concise solution → prioritize MVP
8. **Document**: Save plan → `./docs/YYYYMMDD_HHMMSS_plan.md`
9. **Track**: Save tasks → `./tasks/YYYYMMDD_HHMMSS_task.md`
10. **Implement**: Use integration-first testing via Playwright
11. **Test**: Use Playwright MCP at `https://openweather.joomla.local`
12. **Validate**: All tests must pass before proceeding

## Key Workflow Rules

- **Simplify**: Extract module purpose clearly
- **Validate**: Check logic and Joomla 5.x+ compatibility
- **Detect**: Find contradictions → pause and clarify
- **Break down**: Complex tasks → manageable subtasks
- **Default**: Minimum Viable Product (MVP) first
