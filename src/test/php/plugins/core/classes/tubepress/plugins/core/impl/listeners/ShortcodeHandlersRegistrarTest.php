<?php
/**
 * Copyright 2006 - 2012 Eric D. Hough (http://ehough.com)
 *
 * This file is part of TubePress (http://tubepress.org)
 *
 * TubePress is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TubePress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TubePress.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
class tubepress_plugins_core_impl_listeners_ShortcodeHandlersRegistrarTest extends TubePressUnitTest
{
    private $_sut;

    private $_mockServiceCollectionsRegistry;

    public function setUp()
    {
        $this->_sut = new tubepress_plugins_core_impl_listeners_ShortcodeHandlersRegistrar();

        $this->_mockServiceCollectionsRegistry = Mockery::mock(tubepress_spi_patterns_sl_ServiceCollectionsRegistry::_);

        tubepress_impl_patterns_ioc_KernelServiceLocator::setServiceCollectionsRegistry($this->_mockServiceCollectionsRegistry);
    }

    public function testBoot()
    {
        $this->_mockServiceCollectionsRegistry->shouldReceive('registerService')->times(5)->with(

            tubepress_spi_shortcode_PluggableShortcodeHandlerService::_,
            Mockery::on(function ($arg) {

                return $arg instanceof tubepress_plugins_core_impl_shortcode_SearchInputPluggableShortcodeHandlerService
                    || $arg instanceof tubepress_plugins_core_impl_shortcode_SearchOutputPluggableShortcodeHandlerService
                    || $arg instanceof tubepress_plugins_core_impl_shortcode_SingleVideoPluggableShortcodeHandlerService
                    || $arg instanceof tubepress_plugins_core_impl_shortcode_SoloPlayerPluggableShortcodeHandlerService
                    || $arg instanceof tubepress_plugins_core_impl_shortcode_ThumbGalleryPluggableShortcodeHandlerService;
            })
        );


        $this->_sut->onBoot(new tubepress_api_event_TubePressEvent());

        $this->assertTrue(true);
    }
}
