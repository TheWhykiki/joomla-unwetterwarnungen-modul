<?php

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

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

/**
 * Dispatcher class for mod_unwetterwarnung
 *
 * Handles the module's main dispatching logic and data preparation
 * for template rendering using modern Joomla 5.x architecture.
 *
 * @see https://github.com/whykiki/mod_unwetterwarnung#dispatcher
 * @since  1.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data for template rendering
     *
     * EMERGENCY FIX: Completely bypass all complex logic
     * Just return parent data to ensure basic rendering works
     *
     * @return  array  Layout data array for template rendering
     *
     * @since   1.0.0
     */
    protected function getLayoutData(): array
    {
	    $data = parent::getLayoutData();

		/*
	    $cacheparams               = new \stdClass();
	    $cacheparams->cachemode    = 'safeuri';
	    $cacheparams->class        = $this->getHelperFactory()->getHelper('UnwetterwarnungHelper');
	    $cacheparams->method       = 'getWarnings';
	    $cacheparams->methodparams = $data['params'];
	    //$cacheparams->modeparams   = ['id' => 'array', 'Itemid' => 'int'];

	    $data['list']          = ModuleHelper::moduleCache($this->module, $data['params'], $cacheparams);
	    $data['display_count'] = $data['params']->get('display_count', 0);*/

	    $data['warnings']       = $this->getHelperFactory()->getHelper('UnwetterwarnungHelper')->getWarnings($data['params'], $this->getApplication());

	    return $data;

    }
}
