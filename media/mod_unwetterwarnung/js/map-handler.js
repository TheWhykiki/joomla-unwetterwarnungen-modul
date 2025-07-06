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

/**
 * Weather Map Handler
 * 
 * Handles interactive weather map functionality using Leaflet.js
 * Provides weather alerts visualization, layer switching, and user interaction
 */
class WeatherMapHandler {
    
    /**
     * Constructor
     * 
     * @param {Object} config Map configuration
     */
    constructor(config) {
        this.config = {
            containerId: config.containerId || 'weather-map',
            lat: config.lat || 51.1657,
            lon: config.lon || 10.4515,
            zoom: config.zoom || 10,
            provider: config.provider || 'openstreetmap',
            showControls: config.showControls !== false,
            showLayers: config.showLayers !== false,
            moduleId: config.moduleId || 1
        };
        
        this.map = null;
        this.alertMarkers = [];
        this.alertLayers = {};
        this.currentLayer = 'alerts';
        this.weatherLayers = {};
        this.isInitialized = false;
    }
    
    /**
     * Initialize the map
     */
    init() {
        if (this.isInitialized) {
            return;
        }
        
        const container = document.getElementById(this.config.containerId);
        if (!container) {
            console.error('Map container not found:', this.config.containerId);
            return;
        }
        
        try {
            // Initialize Leaflet map
            this.map = L.map(this.config.containerId, {
                center: [this.config.lat, this.config.lon],
                zoom: this.config.zoom,
                zoomControl: this.config.showControls,
                attributionControl: true
            });
            
            // Add base layer
            this.addBaseLayer();
            
            // Add weather layers
            this.initWeatherLayers();
            
            // Set up event handlers
            this.setupEventHandlers();
            
            this.isInitialized = true;
            
            // Hide loading indicator
            const loadingElement = container.querySelector('.map-loading');
            if (loadingElement) {
                loadingElement.style.display = 'none';
            }
            
        } catch (error) {
            console.error('Error initializing map:', error);
            this.showError('Failed to initialize map');
        }
    }
    
    /**
     * Add base map layer
     */
    addBaseLayer() {
        let tileUrl, attribution;
        
        switch (this.config.provider) {
            case 'openstreetmap':
                tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                attribution = '© OpenStreetMap contributors';
                break;
            case 'cartodb':
                tileUrl = 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
                attribution = '© OpenStreetMap contributors, © CARTO';
                break;
            case 'satellite':
                tileUrl = 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
                attribution = '© Esri, © OpenStreetMap contributors';
                break;
            default:
                tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                attribution = '© OpenStreetMap contributors';
        }
        
        L.tileLayer(tileUrl, {
            attribution: attribution,
            maxZoom: 18
        }).addTo(this.map);
    }
    
    /**
     * Initialize weather layers
     */
    initWeatherLayers() {
        // Alert markers layer
        this.alertLayers.alerts = L.layerGroup().addTo(this.map);
        
        // Weather overlay layer (OpenWeatherMap)
        this.weatherLayers.weather = L.tileLayer(
            'https://tile.openweathermap.org/map/clouds_new/{z}/{x}/{y}.png?appid={apikey}',
            {
                attribution: '© OpenWeatherMap',
                opacity: 0.6,
                apikey: 'YOUR_API_KEY' // This should be set dynamically
            }
        );
        
        // Radar overlay layer (OpenWeatherMap)
        this.weatherLayers.radar = L.tileLayer(
            'https://tile.openweathermap.org/map/precipitation_new/{z}/{x}/{y}.png?appid={apikey}',
            {
                attribution: '© OpenWeatherMap',
                opacity: 0.6,
                apikey: 'YOUR_API_KEY' // This should be set dynamically
            }
        );
    }
    
    /**
     * Add weather alerts to map
     * 
     * @param {Array} alerts Array of alert objects
     */
    addAlerts(alerts) {
        if (!this.map || !alerts || alerts.length === 0) {
            return;
        }
        
        // Clear existing markers
        this.clearAlerts();
        
        alerts.forEach((alert, index) => {
            const marker = this.createAlertMarker(alert, index);
            if (marker) {
                this.alertMarkers.push(marker);
                this.alertLayers.alerts.addLayer(marker);
            }
        });
        
        // Fit map to show all markers
        if (this.alertMarkers.length > 0) {
            const group = new L.featureGroup(this.alertMarkers);
            this.map.fitBounds(group.getBounds().pad(0.1));
        }
    }
    
    /**
     * Create alert marker
     * 
     * @param {Object} alert Alert data
     * @param {number} index Alert index
     * @returns {L.Marker} Leaflet marker
     */
    createAlertMarker(alert, index) {
        const lat = alert.lat || this.config.lat;
        const lon = alert.lon || this.config.lon;
        
        // Create custom icon based on severity
        const icon = this.createSeverityIcon(alert.severity);
        
        // Create marker
        const marker = L.marker([lat, lon], {
            icon: icon,
            title: alert.event,
            alt: `${alert.event} - ${alert.severity}`
        });
        
        // Create popup content
        const popupContent = this.createPopupContent(alert, index);
        marker.bindPopup(popupContent, {
            maxWidth: 300,
            className: `alert-popup severity-${alert.severity}`
        });
        
        // Add click handler
        marker.on('click', () => {
            this.onMarkerClick(alert, index);
        });
        
        // Store alert data
        marker.alertData = alert;
        marker.alertIndex = index;
        
        return marker;
    }
    
    /**
     * Create severity-based icon
     * 
     * @param {string} severity Severity level
     * @returns {L.Icon} Leaflet icon
     */
    createSeverityIcon(severity) {
        const colors = {
            extreme: '#8B0000',
            severe: '#FF4500',
            moderate: '#FFA500',
            minor: '#FFD700',
            unknown: '#808080'
        };
        
        const color = colors[severity] || colors.unknown;
        
        return L.divIcon({
            className: `alert-marker severity-${severity}`,
            html: `<div class="marker-icon" style="background-color: ${color};">
                     <i class="fas fa-exclamation-triangle"></i>
                   </div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -30]
        });
    }
    
    /**
     * Create popup content for alert
     * 
     * @param {Object} alert Alert data
     * @param {number} index Alert index
     * @returns {string} HTML content
     */
    createPopupContent(alert, index) {
        const startDate = new Date(alert.start * 1000).toLocaleString();
        const endDate = new Date(alert.end * 1000).toLocaleString();
        
        return `
            <div class="alert-popup-content">
                <h6 class="alert-title">${this.escapeHtml(alert.event)}</h6>
                <div class="alert-severity">
                    <span class="badge severity-${alert.severity}">${alert.severity.toUpperCase()}</span>
                </div>
                <div class="alert-description">
                    ${this.escapeHtml(alert.description).substring(0, 150)}${alert.description.length > 150 ? '...' : ''}
                </div>
                <div class="alert-times">
                    <div><strong>Start:</strong> ${startDate}</div>
                    <div><strong>End:</strong> ${endDate}</div>
                </div>
                <div class="alert-actions">
                    <button class="btn btn-sm btn-primary" onclick="this.scrollToAlert(${index})">
                        Show Details
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Switch map layer
     * 
     * @param {string} layerType Layer type (alerts, weather, radar)
     */
    switchLayer(layerType) {
        if (this.currentLayer === layerType) {
            return;
        }
        
        // Remove current weather layer
        if (this.currentLayer !== 'alerts' && this.weatherLayers[this.currentLayer]) {
            this.map.removeLayer(this.weatherLayers[this.currentLayer]);
        }
        
        // Add new layer
        switch (layerType) {
            case 'alerts':
                // Alerts are always visible
                break;
            case 'weather':
                if (this.weatherLayers.weather) {
                    this.map.addLayer(this.weatherLayers.weather);
                }
                break;
            case 'radar':
                if (this.weatherLayers.radar) {
                    this.map.addLayer(this.weatherLayers.radar);
                }
                break;
        }
        
        this.currentLayer = layerType;
    }
    
    /**
     * Focus on specific alert
     * 
     * @param {number} alertIndex Alert index
     */
    focusOnAlert(alertIndex) {
        const marker = this.alertMarkers[alertIndex];
        if (marker) {
            this.map.setView(marker.getLatLng(), Math.max(this.map.getZoom(), 12));
            marker.openPopup();
        }
    }
    
    /**
     * Highlight specific alert
     * 
     * @param {number} alertIndex Alert index
     */
    highlightAlert(alertIndex) {
        // Remove previous highlights
        this.clearHighlight();
        
        const marker = this.alertMarkers[alertIndex];
        if (marker) {
            // Add highlight class
            const markerElement = marker.getElement();
            if (markerElement) {
                markerElement.classList.add('highlighted');
            }
        }
    }
    
    /**
     * Clear all highlights
     */
    clearHighlight() {
        this.alertMarkers.forEach(marker => {
            const markerElement = marker.getElement();
            if (markerElement) {
                markerElement.classList.remove('highlighted');
            }
        });
    }
    
    /**
     * Clear all alert markers
     */
    clearAlerts() {
        this.alertMarkers.forEach(marker => {
            this.alertLayers.alerts.removeLayer(marker);
        });
        this.alertMarkers = [];
    }
    
    /**
     * Set up event handlers
     */
    setupEventHandlers() {
        // Map click handler
        this.map.on('click', (e) => {
            this.onMapClick(e);
        });
        
        // Resize handler
        window.addEventListener('resize', () => {
            if (this.map) {
                this.map.invalidateSize();
            }
        });
    }
    
    /**
     * Handle marker click
     * 
     * @param {Object} alert Alert data
     * @param {number} index Alert index
     */
    onMarkerClick(alert, index) {
        // Scroll to corresponding alert in list
        this.scrollToAlert(index);
        
        // Trigger custom event
        const event = new CustomEvent('alertMarkerClick', {
            detail: { alert, index }
        });
        document.dispatchEvent(event);
    }
    
    /**
     * Handle map click
     * 
     * @param {Object} e Leaflet event
     */
    onMapClick(e) {
        // Close all popups
        this.map.closePopup();
        
        // Clear highlights
        this.clearHighlight();
    }
    
    /**
     * Scroll to alert in list
     * 
     * @param {number} index Alert index
     */
    scrollToAlert(index) {
        const alertElement = document.querySelector(`[data-alert-id="${index}"]`);
        if (alertElement) {
            alertElement.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            
            // Highlight the alert item
            alertElement.classList.add('highlighted');
            setTimeout(() => {
                alertElement.classList.remove('highlighted');
            }, 2000);
        }
    }
    
    /**
     * Show error message
     * 
     * @param {string} message Error message
     */
    showError(message) {
        const container = document.getElementById(this.config.containerId);
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    ${message}
                </div>
            `;
        }
    }
    
    /**
     * Escape HTML characters
     * 
     * @param {string} text Text to escape
     * @returns {string} Escaped text
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    /**
     * Destroy map instance
     */
    destroy() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
        this.isInitialized = false;
    }
}

// Make class available globally
window.WeatherMapHandler = WeatherMapHandler; 