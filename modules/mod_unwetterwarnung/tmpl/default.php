<?php

declare(strict_types=1);

/**
 *    __          __ _    _ __     __ _  __ _____  _  __ _____
 *     \ \        / /| |  | |\ \   / /| |/ /|_   _|| |/ /|_   _|
 *      \ \  /\  / / | |__| | \ \_/ / | ' /   | |  | ' /   | |
 *       \ \/  \/ /  |  __  |  \   /  |  <    | |  |  <    | |
 *        \  /\  /   | |  | |   | |   | . \  _| |_ | . \  _| |_
 *         \/  \/    |_|  |_|   |_|   |_|\_\|_____||_|\_\|_____|
 *
 * @package     Whykiki.Module
 * @subpackage  mod_unwetterwarnung
 * @copyright   Copyright (C) 2025 Whykiki
 * @author      Kiki Schuelling
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0.0
 */

// Direktzugriff verhindern
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;

// Type safety für Template-Variablen
/** @var WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();

/** @var \Joomla\Registry\Registry $params */
/** @var array $warnings */
/** @var string|null $error */
/** @var bool $show_severity */
/** @var bool $auto_refresh */

// Assets nur laden wenn Warnungen vorhanden sind oder Auto-Refresh aktiviert ist
if (!empty($warnings) || $auto_refresh) {
    $wa->useScript('mod_unwetterwarnung.default');
    $wa->useStyle('mod_unwetterwarnung.default');
}

// CSS-Klassen für Module-Container
$moduleClass = 'mod-unwetterwarnung mod-unwetterwarnung-default';
if (!empty($warnings)) {
    $moduleClass .= ' has-warnings';
}
if ($auto_refresh) {
    $moduleClass .= ' auto-refresh';
}

?>
<div class="<?php echo $moduleClass; ?>" id="mod-unwetterwarnung-<?php echo $module->id; ?>">
    
    <?php if ($error) : ?>
        <div class="alert alert-danger" role="alert">
            <span class="icon-exclamation-triangle" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('ERROR'); ?></span>
            <?php echo $this->escape($error); ?>
        </div>
    <?php elseif (empty($warnings)) : ?>
        <div class="alert alert-success" role="alert">
            <span class="icon-check-circle" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('MOD_UNWETTERWARNUNG_SUCCESS'); ?></span>
            <?php echo Text::_('MOD_UNWETTERWARNUNG_NO_WARNINGS'); ?>
        </div>
    <?php else : ?>
        
        <div class="warnings-container">
            <div class="warnings-header">
                <h3 class="warnings-title">
                    <span class="icon-exclamation-triangle text-warning" aria-hidden="true"></span>
                    <?php echo Text::_('MOD_UNWETTERWARNUNG_ACTIVE_WARNINGS'); ?>
                    <span class="badge badge-warning"><?php echo count($warnings); ?></span>
                </h3>
            </div>
            
            <ul class="warnings-list list-unstyled" role="list">
                <?php foreach ($warnings as $warning) : ?>
                    <li class="warning-item warning-<?php echo $this->escape($warning['severity']); ?>" 
                        data-warning-id="<?php echo $this->escape($warning['id']); ?>"
                        role="listitem">
                        
                        <div class="warning-header">
                            <?php if ($show_severity) : ?>
                                <span class="warning-severity severity-<?php echo $this->escape($warning['severity']); ?>"
                                      aria-label="<?php echo Text::_('MOD_UNWETTERWARNUNG_SEVERITY_' . strtoupper($warning['severity'])); ?>">
                                    <?php echo Text::_('MOD_UNWETTERWARNUNG_SEVERITY_' . strtoupper($warning['severity'])); ?>
                                </span>
                            <?php endif; ?>
                            
                            <h4 class="warning-title">
                                <?php echo $this->escape($warning['title']); ?>
                            </h4>
                        </div>
                        
                        <?php if (!empty($warning['description'])) : ?>
                            <div class="warning-description">
                                <?php echo HTMLHelper::_('string.truncate', 
                                    $this->escape($warning['description']), 
                                    200, 
                                    true, 
                                    false
                                ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="warning-meta">
                            <?php if (!empty($warning['sender'])) : ?>
                                <span class="warning-sender">
                                    <span class="icon-user" aria-hidden="true"></span>
                                    <?php echo $this->escape($warning['sender']); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($warning['start'])) : ?>
                                <span class="warning-time">
                                    <span class="icon-clock" aria-hidden="true"></span>
                                    <time datetime="<?php echo date('c', $warning['start']); ?>">
                                        <?php echo HTMLHelper::_('date', $warning['start'], Text::_('DATE_FORMAT_LC2')); ?>
                                    </time>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($warning['end'])) : ?>
                                <span class="warning-end">
                                    <span class="icon-clock-o" aria-hidden="true"></span>
                                    <time datetime="<?php echo date('c', $warning['end']); ?>">
                                        <?php echo HTMLHelper::_('date', $warning['end'], Text::_('DATE_FORMAT_LC2')); ?>
                                    </time>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($warning['tags'])) : ?>
                            <div class="warning-tags">
                                <?php foreach ($warning['tags'] as $tag) : ?>
                                    <span class="badge badge-secondary warning-tag">
                                        <?php echo $this->escape($tag); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="warnings-footer">
                <small class="text-muted">
                    <span class="icon-info-circle" aria-hidden="true"></span>
                    <?php echo Text::_('MOD_UNWETTERWARNUNG_POWERED_BY'); ?>
                </small>
                
                <?php if ($auto_refresh) : ?>
                    <small class="text-muted auto-refresh-info">
                        <span class="icon-refresh" aria-hidden="true"></span>
                        <?php echo Text::_('MOD_UNWETTERWARNUNG_AUTO_REFRESH'); ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
        
    <?php endif; ?>
    
</div>

<?php if ($auto_refresh) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const moduleId = 'mod-unwetterwarnung-<?php echo $module->id; ?>';
            const refreshInterval = <?php echo (int) $params->get('cache_time', 1800) * 1000; ?>;
            
            // Auto-refresh functionality
            if (window.ModUnwetterwarnungAutoRefresh) {
                window.ModUnwetterwarnungAutoRefresh.init(moduleId, refreshInterval);
            }
        });
    </script>
<?php endif; ?> 