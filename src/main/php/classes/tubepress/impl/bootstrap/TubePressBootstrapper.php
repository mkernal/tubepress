<?php
/**
 * Copyright 2006 - 2013 TubePress LLC (http://tubepress.org)
 *
 * This file is part of TubePress (http://tubepress.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * Performs TubePress-wide initialization.
 */
class tubepress_impl_bootstrap_TubePressBootstrapper
{
    /**
     * @var boolean Did we already boot?
     */
    private static $_alreadyBooted = false;

    /**
     * @var ehough_epilog_Logger
     */
    private $_logger;

    /**
     * @var tubepress_impl_log_TubePressLoggingHandler
     */
    private $_loggingHandler;

    /**
     * @var bool Convenience variable for just this class.
     */
    private $_shouldLog = true;

    /**
     * @var ehough_pulsar_ComposerClassLoader The classloader.
     */
    private $_classLoader;

    /**
     * @var ehough_iconic_ContainerInterface The IoC container.
     */
    private $_iocContainer = null;

    /**
     * Performs TubePress-wide initialization.
     *
     * @var ehough_pulsar_ComposerClassLoader $classLoader The TubePress classloader.
     *
     * @return null
     */
    public final function boot(ehough_pulsar_ComposerClassLoader $classLoader)
    {
        /* don't boot twice! */
        if (self::$_alreadyBooted) {

            return;
        }

        $this->_classLoader = $classLoader;

        /*
         * Setup basic logging facilities.
         */
        $this->_loggingSetupPhaseOne();

        try {

            $this->_doBoot();

        } catch (Exception $e) {

            if ($this->_shouldLog) {

                $this->_logger->debug('Caught exception while booting: '.  $e->getMessage());

                //flush out log statements
                $this->_loggingHandler->setStatus(true);
            }
        }
    }

    /**
     * This is here strictly for testing :/
     *
     * @param ehough_iconic_ContainerInterface $iocContainer The IoC container.
     */
    public final function setIocContainer(ehough_iconic_ContainerInterface $iocContainer)
    {
        $this->_iocContainer = $iocContainer;
    }

    private function _doBoot()
    {
        /**
         * Keep track of how long this takes.
         */
        $then = microtime(true);

        if ($this->_iocContainer) {

            $coreIocContainer = $this->_iocContainer;

        } else {

            $coreIocContainer = new tubepress_impl_patterns_ioc_CoreIocContainer();
        }

        tubepress_impl_patterns_sl_ServiceLocator::setIocContainer($coreIocContainer);

        $envDetector = tubepress_impl_patterns_sl_ServiceLocator::getEnvironmentDetector();

        /* WordPress likes to keep control of the output */
        if ($envDetector->isWordPress()) {

            ob_start();
        }

        if ($this->_shouldLog) {

            $this->_logger->debug('Booting!');
        }

        $addonDiscoverer = tubepress_impl_patterns_sl_ServiceLocator::getAddonDiscoverer();
        $addonLoader     = tubepress_impl_patterns_sl_ServiceLocator::getAddonLoader();

        /* load add-ons */
        $systemAddons = $this->_findSystemAddons($addonDiscoverer);
        $userAddons   = $this->_findUserAddons($addonDiscoverer);
        $allAddons    = array_merge($systemAddons, $userAddons);

        if ($this->_shouldLog) {

            $this->_logger->debug(sprintf('Found %d add-ons (%d system and %d user)',
                count($allAddons), count($systemAddons), count($userAddons)));

            $this->_logger->debug('Now registering add-on class hints');
        }

        /**
         * Load classpaths.
         */
        $this->_registerAddonClasspaths($allAddons);

        if ($this->_shouldLog) {

            $this->_logger->debug('Done registering add-on class hints. Now registering add-on IoC container extensions.');
        }

        /**
         * Load IOC container extensions.
         */
        $this->_registerIocContainerExtensions($allAddons, $coreIocContainer);

        if ($this->_shouldLog) {

            $this->_logger->debug('Done registering add-on IoC container extensions. Now registering add-on IoC compiler passes.');
        }

        /*
         * Load IOC compiler passes.
         */
        $this->_registerIocCompilerPasses($allAddons, $coreIocContainer);

        if ($this->_shouldLog) {

            $this->_logger->debug('Done registering add-on IoC compiler passes. Now compiling IoC container.');
        }

        /**
         * Compile all our services.
         */
        $coreIocContainer->compile();

        /**
         * Now that we have a storage manager, let's enable or disable logging permanently.
         */
        $this->_loggingSetupPhaseTwo();

        if ($this->_shouldLog) {

            $this->_logger->debug('Done compiling IoC container. Now loading add-ons.');
        }

        $index = 1;
        $count = count($allAddons);

        /**
         * Load addons.
         */

        /**
         * @var $addon tubepress_spi_addon_Addon
         */
        foreach ($allAddons as $addon) {

            if ($this->_shouldLog) {

                $this->_logger->debug(sprintf('Attempting to load add-on %d of %d: %s',
                    $index, $count, $addon->getName()));
            }

            $addonLoader->load($addon);

            if ($this->_shouldLog) {

                $this->_logger->debug(sprintf('Done attempting to load add-on %d of %d: %s',
                    $index, $count, $addon->getName()));
            }

            $index++;
        }

        /**
         * Notify that we have loaded all plugins.
         */
        $eventDispatcher   = tubepress_impl_patterns_sl_ServiceLocator::getEventDispatcher();
        $eventDispatcher->dispatch(tubepress_api_const_event_EventNames::BOOT_COMPLETE);

        if ($this->_shouldLog) {

            $now = microtime(true);

            $this->_logger->debug(sprintf('Boot completed in %f milliseconds',
                (($now - $then) * 1000.0)));
        }

        /* remember that we booted. */
        self::$_alreadyBooted = true;
    }

     private function _registerIocCompilerPasses(array $addons, $coreIocContainer)
     {
         $index = 1;
         $count = count($addons);

         /**
          * @var $addon tubepress_spi_addon_Addon
          */
         foreach ($addons as $addon) {

             $compilerPasses = $addon->getIocContainerCompilerPasses();

             if (count($compilerPasses) === 0) {

                 if ($this->_shouldLog) {

                     $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Did not register any IoC compiler passes',
                         $index, $count, $addon->getName()));
                 }

                 $index++;

                 continue;
             }

             foreach ($compilerPasses as $compilerPass) {

                 if ($this->_shouldLog) {

                     $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Will attempt to load %s as an IoC compiler pass',
                         $index, $count, $addon->getName(), $compilerPass));
                 }

                 try {

                     $ref = new ReflectionClass($compilerPass);

                     /** @noinspection PhpParamsInspection */
                     $coreIocContainer->addCompilerPass($ref->newInstance());

                     if ($this->_shouldLog) {

                         $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Successfully loaded %s as an IoC compiler pass',
                             $index, $count, $addon->getName(), $compilerPass));
                     }

                 } catch (Exception $e) {

                     if ($this->_shouldLog) {

                         $this->_logger->warn(sprintf('(Add-on %d of %d: %s) Failed to load %s as an IoC compiler pass: %s',
                             $index, $count, $addon->getName(), $compilerPass, $e->getMessage()));
                     }
                 }
             }

             $index++;
         }
     }

    private function _registerIocContainerExtensions(array $addons, $coreIocContainer)
    {
        $index = 1;
        $count = count($addons);

        /**
         * @var $addon tubepress_spi_addon_Addon
         */
        foreach ($addons as $addon) {

            $extensions = $addon->getIocContainerExtensions();

            if (count($extensions) === 0) {

                if ($this->_shouldLog) {

                    $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Did not register any IoC container extensions',
                        $index, $count, $addon->getName()));
                }

                $index++;

                continue;
            }

            foreach ($extensions as $extension) {

                if ($this->_shouldLog) {

                    $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Will attempt to load %s as an IoC container extension',
                        $index, $count, $addon->getName(), $extension));
                }

                try {

                    $ref = new ReflectionClass($extension);

                    $coreIocContainer->registerExtension($ref->newInstance());

                    if ($this->_shouldLog) {

                        $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Successfully loaded %s as an IoC container extension',
                            $index, $count, $addon->getName(), $extension));
                    }

                } catch (Exception $e) {

                    if ($this->_shouldLog) {

                        $this->_logger->warn(sprintf('(Add-on %d of %d: %s) Failed to load %s as an IoC container extension: %s',
                            $index, $count, $addon->getName(), $extension, $e->getMessage()));
                    }
                }
            }

            $index++;
        }
    }

    private function _registerAddonClasspaths(array $addons)
    {
        $index = 1;
        $count = count($addons);

        /**
         * @var $addon tubepress_spi_addon_Addon
         */
        foreach ($addons as $addon) {

            $this->_registerPsr0Paths($addon, $index, $count);
            $this->_registerClassMap($addon, $index, $count);

            $index++;
        }
    }

    private function _registerClassMap(tubepress_spi_addon_Addon $addon, $index, $count)
    {
        $classMap = $addon->getClassMap();

        if (count($classMap) === 0) {

            if ($this->_shouldLog) {

                $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Did not define a classmap',
                    $index, $count, $addon->getName()));
            }

            return;
        }

        if ($this->_shouldLog) {

            $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Adding classmap of size %d to classloader',
                $index, $count, $addon->getName(), count($classMap)));
        }

        $this->_classLoader->addToClassMap($classMap);
    }

    private function _registerPsr0Paths(tubepress_spi_addon_Addon $addon, $index, $count)
    {
        $classPaths = $addon->getPsr0ClassPathRoots();

        if (count($classPaths) === 0) {

            if ($this->_shouldLog) {

                $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Did not define any PSR-0 classpaths',
                    $index, $count, $addon->getName()));
            }

            return;
        }

        if ($this->_shouldLog) {

            $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Adding %d PSR-0 path(s) to classloader',
                $index, $count, $addon->getName(), count($classPaths)));
        }

        foreach ($classPaths as $prefix => $path) {

            if ($this->_shouldLog) {

                $this->_logger->debug(sprintf('(Add-on %d of %d: %s) Registering %s as a PSR-0 classpath',
                    $index, $count, $addon->getName(), $path));
            }

            if ($prefix) {

                $this->_classLoader->registerPrefix($prefix, $path);
                $this->_classLoader->registerNamespace($prefix, $path);

            } else {

                $this->_classLoader->registerNamespaceFallback($path);
                $this->_classLoader->registerPrefixFallback($path);
            }
        }
    }

    private function _findUserAddons(tubepress_spi_addon_AddonDiscoverer $discoverer)
    {
        $environmentDetector = tubepress_impl_patterns_sl_ServiceLocator::getEnvironmentDetector();

        $userContentDir = $environmentDetector->getUserContentDirectory();
        $userAddonsDir = $userContentDir . '/addons';

        return $this->_findAddonsInDirectory($userAddonsDir,
            $discoverer, true);
    }

    private function _findSystemAddons(tubepress_spi_addon_AddonDiscoverer $discoverer)
    {
        $coreAddons = $this->_findAddonsInDirectory(TUBEPRESS_ROOT . '/src/main/php/addons',
            $discoverer, true);

        usort($coreAddons, array($this, '_coreAddonSorter'));

        return $coreAddons;
    }

    private function _findAddonsInDirectory($directory, tubepress_spi_addon_AddonDiscoverer $discoverer, $recursive)
    {
        if ($recursive) {

            $addons = $discoverer->findAddonsInDirectory(realpath($directory));

        } else {

            $addons = $discoverer->findAddonsInDirectory(realpath($directory));
        }

        return $addons;
    }

    private function _loggingSetupPhaseOne()
    {
        /*
         * All loggers will share this handler. This lets us control it nicely.
         */
        $loggingHandler   = new tubepress_impl_log_TubePressLoggingHandler();
        $loggingRequested = isset($_GET['tubepress_debug']) && strcasecmp($_GET['tubepress_debug'], 'true') === 0;

        if ($loggingRequested) {

            $loggingHandler->setLevel(ehough_epilog_Logger::DEBUG);

        } else {

            $loggingHandler->setLevel(ehough_epilog_Logger::WARNING);
        }

        $this->_shouldLog = $loggingRequested;

        ehough_epilog_LoggerFactory::setHandlerStack(array($loggingHandler));

        $this->_logger = ehough_epilog_LoggerFactory::getLogger('TubePress Bootstrapper');

        $this->_loggingHandler = $loggingHandler;
    }

    private function _loggingSetupPhaseTwo()
    {
        $context          = tubepress_impl_patterns_sl_ServiceLocator::getExecutionContext();
        $hrps             = tubepress_impl_patterns_sl_ServiceLocator::getHttpRequestParameterService();
        $loggingEnabled   = $context->get(tubepress_api_const_options_names_Advanced::DEBUG_ON);
        $loggingRequested = $hrps->hasParam('tubepress_debug') && strcasecmp($hrps->getParamValue('tubepress_debug'), 'true') === 0;
        $status           = $loggingEnabled && $loggingRequested;

        $this->_loggingHandler->setStatus($status);
        $this->_shouldLog = $status;
    }

    private function _coreAddonSorter(tubepress_spi_addon_Addon $first, tubepress_spi_addon_Addon $second)
    {
        $firstName  = $first->getName();
        $secondName = $second->getName();

        /*
         * The core add-on always gets loaded first.
         */

        if ($firstName === 'TubePress Core') {

            return -1;
        }

        if ($secondName === 'TubePress Core') {

            return 1;
        }

        /*
         * YouTube is more popular than Vimeo, so let's load them in that order.
         */

        if (strpos($firstName, 'YouTube') !== false && strpos($secondName, 'Vimeo') !== false) {

            return -1;
        }

        if (strpos($firstName, 'Vimeo') !== false && strpos($secondName, 'YouTube') !== false) {

            return 1;
        }

        return strcasecmp($firstName, $secondName);
    }
}
