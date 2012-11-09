<?php
/**
 * Copyright 2006 - 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of TubePress (http://tubepress.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * Sets some base parameters to send to TubePressGallery.init().
 */
class tubepress_plugins_core_impl_filters_galleryinitjs_GalleryInitJsBaseParams
{
    private static $_PROPERTY_NVPMAP = 'nvpMap';

    private static $_PROPERTY_JSMAP = 'jsMap';

    private static $_NAME_PARAM_PLAYERJSURL          = 'playerJsUrl';
    private static $_NAME_PARAM_PLAYER_PRODUCES_HTML = 'playerLocationProducesHtml';

    /**
     * The following options are required by JS, so we explicity set them:
     *
     *  ajaxPagination
     *  autoNext
     *  embeddedHeight
     *  embeddedWidth
     *  fluidThumbs
     *  httpMethod
     *  playerLocation
     *
     * The following options are JS-specific
     *
     *  playerJsUrl
     *  playerLocationProducesHtml
     *
     * Otherwise, we simply set any "custom" options so they can be passed back in via Ajax operations.
     */
    public function onGalleryInitJs(tubepress_api_event_TubePressEvent $event)
    {
        $context = tubepress_impl_patterns_sl_ServiceLocator::getExecutionContext();

        $args = $event->getSubject();

        $requiredNvpMap = $this->_buildRequiredNvpMap($context);
        $jsMap          = $this->_buildJsMap($context);
        $customNvpMap   = $context->getCustomOptions();

        $nvpMap = array_merge($requiredNvpMap, $customNvpMap);

        $newArgs = array(

            self::$_PROPERTY_NVPMAP => $this->_convertBooleans($nvpMap),
            self::$_PROPERTY_JSMAP  => $jsMap
        );

        $event->setSubject(array_merge($args, $newArgs));
    }

    private function _buildJsMap(tubepress_spi_context_ExecutionContext $context)
    {
        $toReturn = array();

        $playerLocation = $this->_findPlayerLocation($context);

        if ($playerLocation !== null) {

            $toReturn[self::$_NAME_PARAM_PLAYERJSURL]          = $this->_getPlayerJsUrl($playerLocation);
            $toReturn[self::$_NAME_PARAM_PLAYER_PRODUCES_HTML] = (bool) $playerLocation->producesHtml();
        }

        return $toReturn;
    }

    private function _buildRequiredNvpMap(tubepress_spi_context_ExecutionContext $context)
    {
        $toReturn = array();

        $requiredOptions = array(

            tubepress_api_const_options_names_Thumbs::AJAX_PAGINATION,
            tubepress_api_const_options_names_Embedded::AUTONEXT,
            tubepress_api_const_options_names_Embedded::EMBEDDED_HEIGHT,
            tubepress_api_const_options_names_Embedded::EMBEDDED_WIDTH,
            tubepress_api_const_options_names_Thumbs::FLUID_THUMBS,
            tubepress_api_const_options_names_Advanced::HTTP_METHOD,
            tubepress_api_const_options_names_Embedded::PLAYER_LOCATION
        );

        foreach ($requiredOptions as $optionName) {

            $toReturn[$optionName] = $context->get($optionName);
        }

        return $toReturn;
    }

    private function _findPlayerLocation(tubepress_spi_context_ExecutionContext $context)
    {
        $playerLocations     = tubepress_impl_patterns_sl_ServiceLocator::getPlayerLocations();
        $requestedPlayerName = $context->get(tubepress_api_const_options_names_Embedded::PLAYER_LOCATION);

        foreach ($playerLocations as $playerLocation) {

            if ($playerLocation->getName() === $requestedPlayerName) {

                return $playerLocation;
            }
        }

        return null;
    }

    private function _getPlayerJsUrl(tubepress_spi_player_PluggablePlayerLocationService $player)
    {
        global $tubepress_base_url;

        return $tubepress_base_url . '/' . $player->getRelativePlayerJsUrl();
    }

    private function _convertBooleans($map)
    {
        $optionDescriptorReference = tubepress_impl_patterns_sl_ServiceLocator::getOptionDescriptorReference();

        foreach ($map as $key => $value) {

            $optionDescriptor = $optionDescriptorReference->findOneByName($key);

            if ($optionDescriptor === null || !$optionDescriptor->isBoolean()) {

                continue;
            }

            $map[$key] = $value ? true : false;
        }

        return $map;
    }
}