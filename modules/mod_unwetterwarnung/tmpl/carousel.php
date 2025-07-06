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
 */

// Load module assets
HTMLHelper::_('behavior.core');
$document = $this->getDocument();
$document->getWebAssetManager()->useScript('mod_unwetterwarnung.carousel');

// Generate unique ID for this module instance
$moduleId = 'mod-unwetterwarnung-carousel-' . $module->id;
$carouselId = $moduleId . '-carousel';

// Carousel settings
$autoplay = $params->get('carousel_autoplay', 1);
$interval = $params->get('carousel_interval', 5000);
$showIndicators = $params->get('carousel_indicators', 1);
$showControls = $params->get('carousel_controls', 1);
$pauseOnHover = $params->get('carousel_pause_hover', 1);

?>

<div class="mod-unwetterwarnung mod-unwetterwarnung-carousel <?php echo $moduleclass; ?>" 
     id="<?php echo $moduleId; ?>" 
     data-module-id="<?php echo $module->id; ?>"
     role="region" 
     aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_ARIA_LABEL'); ?>">

    <?php if ($alertCount > 0) : ?>
        
        <!-- Carousel Header -->
        <div class="carousel-header">
            <h3 class="carousel-title">
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

        <!-- Carousel Container -->
        <div class="carousel slide weather-carousel" 
             id="<?php echo $carouselId; ?>" 
             data-bs-ride="<?php echo $autoplay ? 'carousel' : 'false'; ?>"
             data-bs-interval="<?php echo $interval; ?>"
             data-bs-pause="<?php echo $pauseOnHover ? 'hover' : 'false'; ?>"
             role="img"
             aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_CONTENT_ARIA_LABEL'); ?>">

            <?php if ($showIndicators && count($alerts) > 1) : ?>
                <!-- Carousel Indicators -->
                <div class="carousel-indicators">
                    <?php foreach ($alerts as $index => $alert) : ?>
                        <button type="button" 
                                data-bs-target="#<?php echo $carouselId; ?>" 
                                data-bs-slide-to="<?php echo $index; ?>" 
                                <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?>
                                aria-label="<?php echo Text::sprintf('MOD_UNWETTERWARNUNG_CAROUSEL_SLIDE_LABEL', $index + 1); ?>">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Carousel Items -->
            <div class="carousel-inner">
                <?php foreach ($alerts as $index => $alert) : ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>" 
                         data-severity="<?php echo $alert['severity']; ?>">
                        
                        <div class="alert-card severity-<?php echo $alert['severity']; ?>">
                            
                            <!-- Alert Header -->
                            <div class="alert-header">
                                <div class="alert-icon">
                                    <i class="<?php echo $this->getSeverityIcon($alert['severity']); ?>" 
                                       aria-hidden="true"></i>
                                </div>
                                
                                <div class="alert-meta">
                                    <h4 class="alert-title">
                                        <?php echo htmlspecialchars($alert['event']); ?>
                                    </h4>
                                    
                                    <div class="alert-badges">
                                        <span class="badge severity-badge severity-<?php echo $alert['severity']; ?>">
                                            <?php echo Text::_('MOD_UNWETTERWARNUNG_SEVERITY_' . strtoupper($alert['severity'])); ?>
                                        </span>
                                        
                                        <?php if ($alert['urgency']) : ?>
                                            <span class="badge urgency-badge urgency-<?php echo $alert['urgency']; ?>">
                                                <?php echo Text::_('MOD_UNWETTERWARNUNG_URGENCY_' . strtoupper($alert['urgency'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert Body -->
                            <div class="alert-body">
                                <?php if ($alert['description']) : ?>
                                    <div class="alert-description">
                                        <?php echo nl2br(htmlspecialchars($alert['description'])); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Alert Timeline -->
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
                                
                                <!-- Alert Source -->
                                <?php if ($alert['sender_name']) : ?>
                                    <div class="alert-source">
                                        <small class="text-muted">
                                            <?php echo Text::sprintf('MOD_UNWETTERWARNUNG_SOURCE', htmlspecialchars($alert['sender_name'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($showControls && count($alerts) > 1) : ?>
                <!-- Carousel Controls -->
                <button class="carousel-control-prev" 
                        type="button" 
                        data-bs-target="#<?php echo $carouselId; ?>" 
                        data-bs-slide="prev"
                        aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_PREV'); ?>">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_PREV'); ?></span>
                </button>
                
                <button class="carousel-control-next" 
                        type="button" 
                        data-bs-target="#<?php echo $carouselId; ?>" 
                        data-bs-slide="next"
                        aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_NEXT'); ?>">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden"><?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_NEXT'); ?></span>
                </button>
            <?php endif; ?>
        </div>

        <!-- Carousel Footer -->
        <div class="carousel-footer">
            <div class="alert-summary">
                <span class="alert-count">
                    <?php echo Text::sprintf('MOD_UNWETTERWARNUNG_ALERT_COUNT', $alertCount); ?>
                </span>
                
                <?php if ($severity) : ?>
                    <span class="max-severity">
                        <?php echo Text::sprintf('MOD_UNWETTERWARNUNG_MAX_SEVERITY', 
                            Text::_('MOD_UNWETTERWARNUNG_SEVERITY_' . strtoupper($severity))); ?>
                    </span>
                <?php endif; ?>
            </div>
            
            <!-- Carousel Progress Bar -->
            <?php if ($autoplay && count($alerts) > 1) : ?>
                <div class="carousel-progress">
                    <div class="progress-bar" 
                         style="animation-duration: <?php echo $interval; ?>ms;"
                         role="progressbar" 
                         aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_CAROUSEL_PROGRESS'); ?>">
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else : ?>
        
        <!-- No Alerts Message -->
        <div class="no-alerts-message">
            <div class="no-alerts-icon">
                <i class="fas fa-sun" aria-hidden="true"></i>
            </div>
            
            <h4><?php echo Text::_('MOD_UNWETTERWARNUNG_NO_ALERTS_TITLE'); ?></h4>
            <p><?php echo Text::_('MOD_UNWETTERWARNUNG_NO_ALERTS_MESSAGE'); ?></p>
            
            <?php if ($location) : ?>
                <small class="location-name"><?php echo htmlspecialchars($location); ?></small>
            <?php endif; ?>
            
            <?php if ($showTimestamp && $lastUpdate) : ?>
                <div class="last-update">
                    <small class="text-muted">
                        <i class="fas fa-clock" aria-hidden="true"></i>
                        <?php echo Text::sprintf('MOD_UNWETTERWARNUNG_LAST_UPDATE', $lastUpdate); ?>
                    </small>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize carousel with custom settings
    const carousel = document.getElementById('<?php echo $carouselId; ?>');
    if (carousel) {
        const bsCarousel = new bootstrap.Carousel(carousel, {
            interval: <?php echo $autoplay ? $interval : 'false'; ?>,
            pause: <?php echo $pauseOnHover ? "'hover'" : 'false'; ?>,
            wrap: true,
            keyboard: true,
            touch: true
        });
        
        // Handle progress bar animation
        const progressBar = carousel.querySelector('.progress-bar');
        if (progressBar) {
            carousel.addEventListener('slide.bs.carousel', function() {
                progressBar.style.animation = 'none';
                progressBar.offsetHeight; // Trigger reflow
                progressBar.style.animation = 'carousel-progress <?php echo $interval; ?>ms linear';
            });
        }
        
        // Pause on focus for accessibility
        carousel.addEventListener('focusin', function() {
            bsCarousel.pause();
        });
        
        carousel.addEventListener('focusout', function() {
            if (<?php echo $autoplay ? 'true' : 'false'; ?>) {
                bsCarousel.cycle();
            }
        });
    }
});
</script> 