<?php

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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Variables available in this template:
 * @var array  $alerts        Weather alerts data
 * @var object $module        Module object
 * @var object $params        Module parameters
 * @var string $moduleclass   Module class suffix
 * @var string $location      Location name
 * @var int    $alertCount    Number of alerts
 * @var string $lastUpdate   Last update timestamp
 * @var bool   $showTimestamp Show timestamp setting
 * @var string $severity      Highest severity level
 * @var float  $latitude      Location latitude
 * @var float  $longitude     Location longitude
 */

// Load module assets
HTMLHelper::_('behavior.core');
$document = $this->getDocument();
$document->getWebAssetManager()->useScript('mod_unwetterwarnung.map');

// Generate unique ID for this module instance
$moduleId = 'mod-unwetterwarnung-map-' . $module->id;
$mapId = $moduleId . '-map';

// Map settings
$mapHeight = $params->get('map_height', 400);
$mapZoom = $params->get('map_zoom', 10);
$showControls = $params->get('map_controls', 1);
$showLayers = $params->get('map_layers', 1);
$mapProvider = $params->get('map_provider', 'openstreetmap');

// Default coordinates (Germany center if no specific location)
$defaultLat = $latitude ?? 51.1657;
$defaultLon = $longitude ?? 10.4515;

?>

<div class="mod-unwetterwarnung mod-unwetterwarnung-map <?php echo $moduleclass; ?>" 
     id="<?php echo $moduleId; ?>" 
     data-module-id="<?php echo $module->id; ?>"
     role="region" 
     aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_ARIA_LABEL'); ?>">

    <!-- Map Header -->
    <div class="map-header">
        <h3 class="map-title">
            <?php echo Text::_('MOD_UNWETTERWARNUNG_TITLE'); ?>
            <?php if ($location) : ?>
                <small class="location-name"><?php echo htmlspecialchars($location); ?></small>
            <?php endif; ?>
        </h3>
        
        <?php if ($showTimestamp && $lastUpdate) : ?>
            <div class="last-update">
                <small class="text-muted">
                    <i class="fas fa-clock" aria-hidden="true"></i>
                    <?php echo Text::sprintf('MOD_UNWETTERWARNUNG_LAST_UPDATE', $lastUpdate); ?>
                </small>
            </div>
        <?php endif; ?>
    </div>

    <!-- Map Container -->
    <div class="map-container">
        <div class="weather-map" 
             id="<?php echo $mapId; ?>" 
             style="height: <?php echo $mapHeight; ?>px;"
             data-lat="<?php echo $defaultLat; ?>"
             data-lon="<?php echo $defaultLon; ?>"
             data-zoom="<?php echo $mapZoom; ?>"
             data-provider="<?php echo $mapProvider; ?>"
             data-controls="<?php echo $showControls ? '1' : '0'; ?>"
             data-layers="<?php echo $showLayers ? '1' : '0'; ?>"
             role="img"
             aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_CONTENT_ARIA_LABEL'); ?>">
            
            <!-- Loading indicator -->
            <div class="map-loading">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden"><?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_LOADING'); ?></span>
                </div>
                <p><?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_LOADING_MESSAGE'); ?></p>
            </div>
        </div>
        
        <?php if ($showLayers) : ?>
            <!-- Map Layer Controls -->
            <div class="map-layers-control">
                <div class="btn-group" role="group" aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_LAYERS'); ?>">
                    <input type="radio" class="btn-check" name="map-layer-<?php echo $module->id; ?>" 
                           id="layer-alerts-<?php echo $module->id; ?>" value="alerts" checked>
                    <label class="btn btn-outline-primary btn-sm" for="layer-alerts-<?php echo $module->id; ?>">
                        <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                        <?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_LAYER_ALERTS'); ?>
                    </label>
                    
                    <input type="radio" class="btn-check" name="map-layer-<?php echo $module->id; ?>" 
                           id="layer-weather-<?php echo $module->id; ?>" value="weather">
                    <label class="btn btn-outline-primary btn-sm" for="layer-weather-<?php echo $module->id; ?>">
                        <i class="fas fa-cloud" aria-hidden="true"></i>
                        <?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_LAYER_WEATHER'); ?>
                    </label>
                    
                    <input type="radio" class="btn-check" name="map-layer-<?php echo $module->id; ?>" 
                           id="layer-radar-<?php echo $module->id; ?>" value="radar">
                    <label class="btn btn-outline-primary btn-sm" for="layer-radar-<?php echo $module->id; ?>">
                        <i class="fas fa-satellite-dish" aria-hidden="true"></i>
                        <?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_LAYER_RADAR'); ?>
                    </label>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Alert List (alongside map) -->
    <div class="map-alerts-list">
        <?php if ($alertCount > 0) : ?>
            <div class="alerts-summary">
                <h4><?php echo Text::sprintf('MOD_UNWETTERWARNUNG_ALERT_COUNT', $alertCount); ?></h4>
                
                <?php if ($severity) : ?>
                    <span class="max-severity-badge severity-<?php echo $severity; ?>">
                        <?php echo Text::_('MOD_UNWETTERWARNUNG_SEVERITY_' . strtoupper($severity)); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="alerts-list">
                <?php foreach ($alerts as $index => $alert) : ?>
                    <div class="alert-item severity-<?php echo $alert['severity']; ?>" 
                         data-alert-id="<?php echo $index; ?>"
                         data-lat="<?php echo $defaultLat; ?>"
                         data-lon="<?php echo $defaultLon; ?>">
                        
                        <div class="alert-header">
                            <div class="alert-icon">
                                <i class="<?php echo $this->getSeverityIcon($alert['severity']); ?>" 
                                   aria-hidden="true"></i>
                            </div>
                            
                            <div class="alert-meta">
                                <h5 class="alert-title">
                                    <?php echo htmlspecialchars($alert['event']); ?>
                                </h5>
                                
                                <div class="alert-badges">
                                    <span class="badge severity-badge severity-<?php echo $alert['severity']; ?>">
                                        <?php echo Text::_('MOD_UNWETTERWARNUNG_SEVERITY_' . strtoupper($alert['severity'])); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <button class="btn btn-sm btn-outline-secondary toggle-alert" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#alert-details-<?php echo $index; ?>" 
                                    aria-expanded="false" 
                                    aria-controls="alert-details-<?php echo $index; ?>"
                                    aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_TOGGLE_DETAILS'); ?>">
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                        </div>
                        
                        <div class="collapse alert-details" id="alert-details-<?php echo $index; ?>">
                            <div class="alert-body">
                                <?php if ($alert['description']) : ?>
                                    <div class="alert-description">
                                        <?php echo nl2br(htmlspecialchars($alert['description'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="alert-timeline">
                                    <div class="timeline-item">
                                        <strong><?php echo Text::_('MOD_UNWETTERWARNUNG_STARTS'); ?>:</strong>
                                        <time datetime="<?php echo date('c', $alert['start']); ?>">
                                            <?php echo HTMLHelper::_('date', $alert['start'], Text::_('DATE_FORMAT_LC2')); ?>
                                        </time>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <strong><?php echo Text::_('MOD_UNWETTERWARNUNG_ENDS'); ?>:</strong>
                                        <time datetime="<?php echo date('c', $alert['end']); ?>">
                                            <?php echo HTMLHelper::_('date', $alert['end'], Text::_('DATE_FORMAT_LC2')); ?>
                                        </time>
                                    </div>
                                </div>
                                
                                <?php if ($alert['sender_name']) : ?>
                                    <div class="alert-source">
                                        <small class="text-muted">
                                            <?php echo Text::sprintf('MOD_UNWETTERWARNUNG_SOURCE', htmlspecialchars($alert['sender_name'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="alert-actions">
                                    <button class="btn btn-sm btn-primary focus-on-map" 
                                            data-alert-id="<?php echo $index; ?>"
                                            type="button">
                                        <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                        <?php echo Text::_('MOD_UNWETTERWARNUNG_FOCUS_ON_MAP'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
        <?php else : ?>
            
            <!-- No Alerts Message -->
            <div class="no-alerts-message">
                <div class="no-alerts-icon">
                    <i class="fas fa-sun" aria-hidden="true"></i>
                </div>
                
                <h4><?php echo Text::_('MOD_UNWETTERWARNUNG_NO_ALERTS_TITLE'); ?></h4>
                <p><?php echo Text::_('MOD_UNWETTERWARNUNG_NO_ALERTS_MESSAGE'); ?></p>
            </div>
            
        <?php endif; ?>
    </div>
</div>

<!-- Map initialization script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Map configuration
    const mapConfig = {
        containerId: '<?php echo $mapId; ?>',
        lat: <?php echo $defaultLat; ?>,
        lon: <?php echo $defaultLon; ?>,
        zoom: <?php echo $mapZoom; ?>,
        provider: '<?php echo $mapProvider; ?>',
        showControls: <?php echo $showControls ? 'true' : 'false'; ?>,
        showLayers: <?php echo $showLayers ? 'true' : 'false'; ?>,
        moduleId: <?php echo $module->id; ?>
    };
    
    // Alert data for map markers
    const alertData = <?php echo json_encode($alerts); ?>;
    
    // Initialize map when map handler is loaded
    if (typeof WeatherMapHandler !== 'undefined') {
        const mapHandler = new WeatherMapHandler(mapConfig);
        mapHandler.init();
        mapHandler.addAlerts(alertData);
        
        // Handle layer switching
        const layerControls = document.querySelectorAll('input[name="map-layer-<?php echo $module->id; ?>"]');
        layerControls.forEach(control => {
            control.addEventListener('change', function() {
                mapHandler.switchLayer(this.value);
            });
        });
        
        // Handle alert focus
        const focusButtons = document.querySelectorAll('.focus-on-map');
        focusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alertId = this.getAttribute('data-alert-id');
                mapHandler.focusOnAlert(alertId);
            });
        });
        
        // Handle alert item hover
        const alertItems = document.querySelectorAll('.alert-item');
        alertItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                const alertId = this.getAttribute('data-alert-id');
                mapHandler.highlightAlert(alertId);
            });
            
            item.addEventListener('mouseleave', function() {
                mapHandler.clearHighlight();
            });
        });
    } else {
        // Show error if map handler is not loaded
        const mapContainer = document.getElementById('<?php echo $mapId; ?>');
        if (mapContainer) {
            mapContainer.innerHTML = '<div class="alert alert-warning">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                '<?php echo Text::_('MOD_UNWETTERWARNUNG_MAP_ERROR_LOADING'); ?>' +
                '</div>';
        }
    }
    
    // Handle toggle buttons
    const toggleButtons = document.querySelectorAll('.toggle-alert');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            } else {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        });
    });
    
    // Handle collapse events
    const collapseElements = document.querySelectorAll('.alert-details');
    collapseElements.forEach(element => {
        element.addEventListener('show.bs.collapse', function() {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (button) {
                const icon = button.querySelector('i');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            }
        });
        
        element.addEventListener('hide.bs.collapse', function() {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            if (button) {
                const icon = button.querySelector('i');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    });
});
</script> 