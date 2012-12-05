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
class tubepress_plugins_core_impl_shortcode_SearchOutputPluggableShortcodeHandlerServiceTest extends TubePressUnitTest
{
    /**
     * @var tubepress_plugins_core_impl_shortcode_SearchOutputPluggableShortcodeHandlerService
     */
    private $_sut;

    private $_mockExecutionContext;

    private $_mockHttpRequestParameterService;

    private $_mockThumbGalleryShortcodeHandler;

    function onSetup()
    {

        $this->_mockExecutionContext = $this->createMockSingletonService(tubepress_spi_context_ExecutionContext::_);
        $this->_mockThumbGalleryShortcodeHandler = $this->createMockPluggableService(tubepress_spi_shortcode_PluggableShortcodeHandlerService::_);
        $this->_mockHttpRequestParameterService = $this->createMockSingletonService(tubepress_spi_http_HttpRequestParameterService::_);

        $this->_sut = new tubepress_plugins_core_impl_shortcode_SearchOutputPluggableShortcodeHandlerService($this->_mockThumbGalleryShortcodeHandler);

    }


    function testCantExecute()
    {
        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_Output::OUTPUT)->andReturn(tubepress_api_const_options_values_OutputValue::SEARCH_INPUT);

        $this->assertFalse($this->_sut->shouldExecute());
    }

    function testExecuteVimeo()
    {
        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_InteractiveSearch::SEARCH_PROVIDER)->andReturn('vimeo');
        $this->_mockExecutionContext->shouldReceive('set')->once()->with(tubepress_api_const_options_names_Output::GALLERY_SOURCE, tubepress_plugins_vimeo_api_const_options_values_GallerySourceValue::VIMEO_SEARCH);
        $this->_mockExecutionContext->shouldReceive('set')->once()->with(tubepress_plugins_vimeo_api_const_options_names_GallerySource::VIMEO_SEARCH_VALUE, "(#@@!!search (())(())((terms*$$#")->andReturn(true);

        $this->_mockHttpRequestParameterService->shouldReceive('getParamValue')->once()->with(tubepress_spi_const_http_ParamName::SEARCH_TERMS)->andReturn("(#@@!!search (())(())((terms*$$#");

        $this->_mockThumbGalleryShortcodeHandler->shouldReceive('getHtml')->once()->andReturn('foobar');

        $this->assertEquals('foobar', $this->_sut->getHtml());
    }


    function testExecuteYouTube()
    {
        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_InteractiveSearch::SEARCH_PROVIDER)->andReturn('youtube');
        $this->_mockExecutionContext->shouldReceive('set')->once()->with(tubepress_api_const_options_names_Output::GALLERY_SOURCE, tubepress_plugins_youtube_api_const_options_values_GallerySourceValue::YOUTUBE_SEARCH);
        $this->_mockExecutionContext->shouldReceive('set')->once()->with(tubepress_plugins_youtube_api_const_options_names_GallerySource::YOUTUBE_TAG_VALUE, "(#@@!!search (())(())((terms*$$#")->andReturn(true);

        $this->_mockHttpRequestParameterService->shouldReceive('getParamValue')->once()->with(tubepress_spi_const_http_ParamName::SEARCH_TERMS)->andReturn("(#@@!!search (())(())((terms*$$#");

        $this->_mockThumbGalleryShortcodeHandler->shouldReceive('getHtml')->once()->andReturn('xyz');

        $this->assertEquals('xyz', $this->_sut->getHtml());
    }

    function testExecuteHasToShowSearchResultsNotSearching()
    {
        $this->_mockHttpRequestParameterService->shouldReceive('getParamValue')->once()->with(tubepress_spi_const_http_ParamName::SEARCH_TERMS)->andReturn("");

        $this->assertEquals('', $this->_sut->getHtml());
    }

    function testExecuteDoesntHaveToShowSearchResultsNotSearching()
    {
        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_Output::OUTPUT)->andReturn(tubepress_api_const_options_values_OutputValue::SEARCH_RESULTS);
        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_InteractiveSearch::SEARCH_RESULTS_ONLY)->andReturn(false);

        $this->_mockHttpRequestParameterService->shouldReceive('getParamValue')->once()->with(tubepress_spi_const_http_ParamName::SEARCH_TERMS)->andReturn("");

        $this->assertFalse($this->_sut->shouldExecute());
    }
}
