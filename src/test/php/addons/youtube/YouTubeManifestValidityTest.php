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
class_exists('tubepress_impl_addon_AbstractManifestValidityTest') ||
    require dirname(__FILE__) . '/../../classes/tubepress/impl/addon/AbstractManifestValidityTest.php';

class tubepress_addons_core_YouTubeManifestValidityTest extends tubepress_impl_addon_AbstractManifestValidityTest
{
    public function testManifest()
    {
        /**
         * @var $addon tubepress_spi_addon_Addon
         */
        $addon = $this->getAddonFromManifest(dirname(__FILE__) . '/../../../../main/php/addons/youtube/youtube.json');

        $this->assertEquals('tubepress-youtube-addon', $addon->getName());
        $this->assertEquals('1.0.0', $addon->getVersion());
        $this->assertEquals('YouTube', $addon->getTitle());
        $this->assertEquals(array('name' => 'TubePress LLC', 'url' => 'http://tubepress.org'), $addon->getAuthor());
        $this->assertEquals(array(array('type' => 'MPL-2.0', 'url' => 'http://www.mozilla.org/MPL/2.0/')), $addon->getLicenses());
        $this->assertEquals('Allows TubePress work with YouTube', $addon->getDescription());
        $this->assertEquals(TUBEPRESS_ROOT . '/src/main/php/addons/youtube/scripts/bootstrap.php', $addon->getBootstrap());
        $this->assertEquals(array('tubepress_addons_youtube' => TUBEPRESS_ROOT . '/src/main/php/addons/youtube/classes'), $addon->getPsr0ClassPathRoots());
        $this->assertEquals(array('tubepress_addons_youtube_impl_patterns_ioc_YouTubeIocContainerExtension'), $addon->getIocContainerExtensions());
        $this->validateClassMap($this->_getExpectedClassMap(), $addon->getClassMap());
    }

    private function _getExpectedClassMap()
    {
        return array(
            'tubepress_addons_youtube_api_const_options_names_Embedded' => 'classes/tubepress/addons/youtube/api/const/options/names/Embedded.php',
            'tubepress_addons_youtube_api_const_options_names_Feed' => 'classes/tubepress/addons/youtube/api/const/options/names/Feed.php',
            'tubepress_addons_youtube_api_const_options_names_GallerySource' => 'classes/tubepress/addons/youtube/api/const/options/names/GallerySource.php',
            'tubepress_addons_youtube_api_const_options_names_Meta' => 'classes/tubepress/addons/youtube/api/const/options/names/Meta.php',
            'tubepress_addons_youtube_api_const_options_values_GallerySourceValue' => 'classes/tubepress/addons/youtube/api/const/options/values/GallerySourceValue.php',
            'tubepress_addons_youtube_api_const_options_values_YouTube' => 'classes/tubepress/addons/youtube/api/const/options/values/YouTube.php',
            'tubepress_addons_youtube_impl_Bootstrap' => 'classes/tubepress/addons/youtube/impl/Bootstrap.php',
            'tubepress_addons_youtube_impl_embedded_YouTubePluggableEmbeddedPlayerService' => 'classes/tubepress/addons/youtube/impl/embedded/YouTubePluggableEmbeddedPlayerService.php',
            'tubepress_addons_youtube_impl_listeners_boot_YouTubeOptionsRegistrar' => 'classes/tubepress/addons/youtube/impl/listeners/boot/YouTubeOptionsRegistrar.php',
            'tubepress_addons_youtube_impl_listeners_http_YouTubeHttpErrorResponseListener' => 'classes/tubepress/addons/youtube/impl/listeners/http/YouTubeHttpErrorResponseListener.php',
            'tubepress_addons_youtube_impl_listeners_video_YouTubeVideoConstructionListener' => 'classes/tubepress/addons/youtube/impl/listeners/video/YouTubeVideoConstructionListener.php',
            'tubepress_addons_youtube_impl_options_ui_YouTubeOptionsPageParticipant' => 'classes/tubepress/addons/youtube/impl/options/ui/YouTubeOptionsPageParticipant.php',
            'tubepress_addons_youtube_impl_patterns_ioc_YouTubeIocContainerExtension' => 'classes/tubepress/addons/youtube/impl/patterns/ioc/YouTubeIocContainerExtension.php',
            'tubepress_addons_youtube_impl_provider_YouTubePluggableVideoProviderService' => 'classes/tubepress/addons/youtube/impl/provider/YouTubePluggableVideoProviderService.php',
            'tubepress_addons_youtube_impl_provider_YouTubeUrlBuilder' => 'classes/tubepress/addons/youtube/impl/provider/YouTubeUrlBuilder.php'
        );
    }
}