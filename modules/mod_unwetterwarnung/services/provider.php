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

// Direktzugriff verhindern
\defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The weather warning module service provider
 *
 * Registers all necessary services for the mod_unwetterwarnung module in the
 * dependency injection container. This includes the dispatcher factory, helper
 * factory, and module services following Joomla 5.x+ patterns.
 *
 * @see https://github.com/whykiki/mod_unwetterwarnung#service-provider
 * @since  1.0.0
 */
return new class () implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   5.1.0
	 */
    public function register(Container $container): void
    {
	    $container->registerServiceProvider(new ModuleDispatcherFactory('\\Whykiki\\Module\\Unwetterwarnung'));
	    $container->registerServiceProvider(new HelperFactory('\\Whykiki\\Module\\Unwetterwarnung\\Site\\Helper'));

	    $container->registerServiceProvider(new Module());

    }
};
