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

namespace Whykiki\Module\Unwetterwarnung\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use RuntimeException;

/**
 * Dispatcher class for mod_unwetterwarnung
 *
 * Handles the module's execution flow and prepares data for rendering.
 * Implements helper factory pattern for clean dependency management
 * and follows Joomla 5.x+ architecture patterns.
 *
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data
     *
     * Prepares all necessary data for the module template including
     * weather warnings, module parameters, and display settings.
     * Validates configuration before attempting to fetch data.
     *
     * @return  array  Associative array containing all template variables
     *
     * @since   1.0.0
     * @throws  RuntimeException  When weather data cannot be retrieved
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        // Validate configuration before proceeding
        if (!$this->validateConfiguration()) {
            $data['warnings'] = [];
            $data['error'] = Text::_('MOD_UNWETTERWARNUNG_ERROR_CONFIGURATION');
            return $data;
        }

        try {
            // Get weather warnings through helper
            $helper = $this->getHelperFactory()->getHelper('UnwetterwarnungHelper');
            $data['warnings'] = $helper->getWarnings(
                (string) $data['params']->get('location', ''),
                $data['params']->toArray()
            );

            // Add additional layout data
            $data['layout_type'] = $data['params']->get('layout', 'default');
            $data['show_severity'] = (bool) $data['params']->get('show_severity', 1);
            $data['max_warnings'] = (int) $data['params']->get('max_warnings', 5);
            $data['auto_refresh'] = (bool) $data['params']->get('auto_refresh', 0);
            $data['error'] = null;

        } catch (RuntimeException $e) {
            // Log error for debugging
            Log::add(
                'Weather Warning Module Error: ' . $e->getMessage(),
                Log::ERROR,
                'mod_unwetterwarnung'
            );

            // Provide user-friendly error message
            $data['warnings'] = [];
            $data['error'] = Text::_('MOD_UNWETTERWARNUNG_ERROR_FETCH_DATA');

            // In debug mode, show detailed error
            if ((bool) $data['params']->get('debug_mode', 0)) {
                $data['error'] .= ' (' . $e->getMessage() . ')';
            }
        }

        return $data;
    }

    /**
     * Validates module configuration before execution
     *
     * Ensures all required parameters are present and valid.
     * Prevents module execution if critical configuration is missing.
     *
     * @return  bool  True if configuration is valid, false otherwise
     *
     * @since   1.0.0
     */
    protected function validateConfiguration(): bool
    {
        /** @var Registry $params */
        $params = $this->getParams();

        // Check for required API key
        if (empty($params->get('api_key'))) {
            /** @var SiteApplication $app */
            $app = $this->getApplication();
            $app->enqueueMessage(
                Text::_('MOD_UNWETTERWARNUNG_ERROR_NO_API_KEY'),
                'error'
            );
            return false;
        }

        // Check for required location
        if (empty($params->get('location'))) {
            /** @var SiteApplication $app */
            $app = $this->getApplication();
            $app->enqueueMessage(
                Text::_('MOD_UNWETTERWARNUNG_ERROR_NO_LOCATION'),
                'error'
            );
            return false;
        }

        return true;
    }

    /**
     * Checks if the module should be rendered
     *
     * Performs additional checks beyond basic configuration validation
     * to determine if the module should be displayed to the user.
     *
     * @return  bool  True if module should be rendered, false otherwise
     *
     * @since   1.0.0
     */
    protected function shouldRender(): bool
    {
        // Don't render if basic validation fails
        if (!$this->validateConfiguration()) {
            return false;
        }

        // Additional rendering checks can be added here
        // For example: user permissions, time-based visibility, etc.

        return true;
    }
}
