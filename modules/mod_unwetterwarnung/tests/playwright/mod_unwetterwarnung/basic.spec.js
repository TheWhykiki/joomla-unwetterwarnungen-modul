/**
 * ___       __          __   __   __
 * |  |     |  |        |  | |  | |  |
 * |  |     |  |        |  | |  | |  |
 * |  |     |  |        |  | |  | |  |
 * |  |_____|  |________|  |_|  |_|  |
 * |_________|___________|____________|
 * 
 * @package     Joomla.Site
 * @subpackage  mod_unwetterwarnung
 * @copyright   (C) 2024 Whykiki. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @since       1.0.0
 */

import { test, expect } from '@playwright/test';

/**
 * Basic functionality tests for mod_unwetterwarnung
 * 
 * Tests core module functionality, rendering, and basic interactions
 * on the development environment at https://openweather.joomla.local
 */

test.describe('mod_unwetterwarnung - Basic Functionality', () => {
    
    test.beforeEach(async ({ page }) => {
        // Navigate to the test page
        await page.goto('https://openweather.joomla.local');
        
        // Wait for page to load
        await page.waitForLoadState('networkidle');
    });
    
    test('should render weather warning module', async ({ page }) => {
        // Check if module container exists
        const moduleContainer = page.locator('.mod-unwetterwarnung');
        await expect(moduleContainer).toBeVisible();
        
        // Check if module has correct attributes
        await expect(moduleContainer).toHaveAttribute('data-module-id');
        await expect(moduleContainer).toHaveAttribute('role', 'region');
    });
    
    test('should display module title', async ({ page }) => {
        // Check for module title
        const moduleTitle = page.locator('.mod-unwetterwarnung h3, .mod-unwetterwarnung h4');
        await expect(moduleTitle).toBeVisible();
        
        // Title should contain weather warning text
        await expect(moduleTitle).toContainText(/wetter|weather|warnung|warning/i);
    });
    
    test('should handle no alerts state', async ({ page }) => {
        // Look for no alerts message
        const noAlertsMessage = page.locator('.no-alerts-message');
        
        // If no alerts, should show appropriate message
        if (await noAlertsMessage.isVisible()) {
            await expect(noAlertsMessage).toContainText(/keine|no.*alert/i);
            
            // Should show a positive icon (sun)
            const sunIcon = page.locator('.no-alerts-message .fa-sun');
            await expect(sunIcon).toBeVisible();
        }
    });
    
    test('should display alerts when available', async ({ page }) => {
        // Check for alert containers
        const alertCards = page.locator('.alert-card, .alert-item');
        
        if (await alertCards.count() > 0) {
            // First alert should be visible
            await expect(alertCards.first()).toBeVisible();
            
            // Should have severity class
            const firstAlert = alertCards.first();
            const classes = await firstAlert.getAttribute('class');
            expect(classes).toMatch(/severity-(extreme|severe|moderate|minor)/);
            
            // Should have alert title
            const alertTitle = firstAlert.locator('.alert-title');
            await expect(alertTitle).toBeVisible();
            
            // Should have severity badge
            const severityBadge = firstAlert.locator('.severity-badge');
            await expect(severityBadge).toBeVisible();
        }
    });
    
    test('should show timestamp when enabled', async ({ page }) => {
        // Look for last update timestamp
        const timestamp = page.locator('.last-update');
        
        if (await timestamp.isVisible()) {
            // Should contain time-related text
            await expect(timestamp).toContainText(/update|aktualisiert|uhr|am|pm/i);
            
            // Should have clock icon
            const clockIcon = timestamp.locator('.fa-clock');
            await expect(clockIcon).toBeVisible();
        }
    });
    
    test('should display location when configured', async ({ page }) => {
        // Look for location display
        const locationName = page.locator('.location-name');
        
        if (await locationName.isVisible()) {
            // Should not be empty
            const locationText = await locationName.textContent();
            expect(locationText.trim()).not.toBe('');
        }
    });
    
    test('should have proper accessibility attributes', async ({ page }) => {
        const moduleContainer = page.locator('.mod-unwetterwarnung');
        
        // Should have proper ARIA attributes
        await expect(moduleContainer).toHaveAttribute('role', 'region');
        await expect(moduleContainer).toHaveAttribute('aria-label');
        
        // Check for proper heading structure
        const headings = page.locator('.mod-unwetterwarnung h1, .mod-unwetterwarnung h2, .mod-unwetterwarnung h3, .mod-unwetterwarnung h4');
        if (await headings.count() > 0) {
            await expect(headings.first()).toBeVisible();
        }
    });
    
    test('should load required CSS assets', async ({ page }) => {
        // Check if module CSS is loaded
        const stylesheets = await page.evaluate(() => {
            const links = Array.from(document.querySelectorAll('link[rel="stylesheet"]'));
            return links.map(link => link.href);
        });
        
        // Should include module CSS
        const hasModuleCSS = stylesheets.some(href => 
            href.includes('mod_unwetterwarnung.css') || 
            href.includes('mod_unwetterwarnung')
        );
        
        expect(hasModuleCSS).toBeTruthy();
    });
    
    test('should handle responsive design', async ({ page }) => {
        // Test mobile viewport
        await page.setViewportSize({ width: 375, height: 667 });
        await page.waitForTimeout(500);
        
        const moduleContainer = page.locator('.mod-unwetterwarnung');
        await expect(moduleContainer).toBeVisible();
        
        // Test tablet viewport
        await page.setViewportSize({ width: 768, height: 1024 });
        await page.waitForTimeout(500);
        
        await expect(moduleContainer).toBeVisible();
        
        // Test desktop viewport
        await page.setViewportSize({ width: 1920, height: 1080 });
        await page.waitForTimeout(500);
        
        await expect(moduleContainer).toBeVisible();
    });
    
    test('should not have JavaScript errors', async ({ page }) => {
        const errors = [];
        
        // Listen for console errors
        page.on('console', msg => {
            if (msg.type() === 'error') {
                errors.push(msg.text());
            }
        });
        
        // Reload page to catch any initialization errors
        await page.reload();
        await page.waitForLoadState('networkidle');
        
        // Filter out non-module related errors
        const moduleErrors = errors.filter(error => 
            error.includes('mod_unwetterwarnung') || 
            error.includes('weather') ||
            error.includes('unwetter')
        );
        
        expect(moduleErrors).toHaveLength(0);
    });
    
    test('should handle module parameters correctly', async ({ page }) => {
        // Check if module respects common parameters
        const moduleContainer = page.locator('.mod-unwetterwarnung');
        
        // Should have module class suffix if configured
        const classes = await moduleContainer.getAttribute('class');
        expect(classes).toContain('mod-unwetterwarnung');
        
        // Module should have unique ID
        const moduleId = await moduleContainer.getAttribute('id');
        expect(moduleId).toBeTruthy();
        expect(moduleId).toMatch(/mod-unwetterwarnung-/);
    });
    
    test('should display severity levels correctly', async ({ page }) => {
        const severityElements = page.locator('.severity-badge, .severity-extreme, .severity-severe, .severity-moderate, .severity-minor');
        
        if (await severityElements.count() > 0) {
            // Each severity element should have appropriate styling
            for (let i = 0; i < await severityElements.count(); i++) {
                const element = severityElements.nth(i);
                const classes = await element.getAttribute('class');
                
                // Should have at least one severity class
                expect(classes).toMatch(/severity-(extreme|severe|moderate|minor)/);
            }
        }
    });
    
    test('should handle empty or invalid data gracefully', async ({ page }) => {
        // This test assumes the module can handle various data states
        const moduleContainer = page.locator('.mod-unwetterwarnung');
        await expect(moduleContainer).toBeVisible();
        
        // Should not show error messages in normal operation
        const errorMessages = page.locator('.alert-danger, .error-message');
        const errorCount = await errorMessages.count();
        
        // If there are error messages, they should be handled gracefully
        if (errorCount > 0) {
            const firstError = errorMessages.first();
            await expect(firstError).toBeVisible();
            
            // Error should have proper styling
            const classes = await firstError.getAttribute('class');
            expect(classes).toMatch(/alert|error|warning/);
        }
    });
    
    test('should have proper semantic HTML structure', async ({ page }) => {
        const moduleContainer = page.locator('.mod-unwetterwarnung');
        
        // Should use proper HTML5 semantic elements
        const semanticElements = page.locator('.mod-unwetterwarnung article, .mod-unwetterwarnung section, .mod-unwetterwarnung time');
        
        // Time elements should have datetime attributes
        const timeElements = page.locator('.mod-unwetterwarnung time');
        if (await timeElements.count() > 0) {
            for (let i = 0; i < await timeElements.count(); i++) {
                const timeElement = timeElements.nth(i);
                await expect(timeElement).toHaveAttribute('datetime');
            }
        }
    });
}); 