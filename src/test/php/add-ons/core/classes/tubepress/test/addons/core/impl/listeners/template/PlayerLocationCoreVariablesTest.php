<?php
/**
 * Copyright 2006 - 2014 TubePress LLC (http://tubepress.com)
 *
 * This file is part of TubePress (http://tubepress.com)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

/**
 * @covers tubepress_addons_core_impl_listeners_template_PlayerLocationCoreVariables
 */
class tubepress_test_addons_core_impl_listeners_template_PlayerLocationCoreVariablesTest extends tubepress_test_TubePressUnitTest
{
    /**
     * @var tubepress_addons_core_impl_listeners_template_PlayerLocationCoreVariables
     */
    private $_sut;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockExecutionContext;

    /**
     * @var ehough_mockery_mockery_MockInterface
     */
    private $_mockEmbeddedHtmlGenerator;

    public function onSetup()
    {
        $this->_sut = new tubepress_addons_core_impl_listeners_template_PlayerLocationCoreVariables();

        $this->_mockExecutionContext = $this->createMockSingletonService(tubepress_spi_context_ExecutionContext::_);

        $this->_mockEmbeddedHtmlGenerator = $this->createMockSingletonService(tubepress_spi_embedded_EmbeddedHtmlGenerator::_);
    }

    public function testAlterTemplate()
    {
        $video = new tubepress_api_video_Video();
        $video->setId('video-id');

        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_Advanced::GALLERY_ID)->andReturn('gallery-id');
        $this->_mockExecutionContext->shouldReceive('get')->once()->with(tubepress_api_const_options_names_Embedded::EMBEDDED_WIDTH)->andReturn(668);

        $this->_mockEmbeddedHtmlGenerator->shouldReceive('getHtml')->once()->with('video-id')->andReturn('embedded-html');

        $mockTemplate = ehough_mockery_Mockery::mock('ehough_contemplate_api_Template');
        $mockTemplate->shouldReceive('setVariable')->once()->with(tubepress_api_const_template_Variable::EMBEDDED_SOURCE, 'embedded-html');
        $mockTemplate->shouldReceive('setVariable')->once()->with(tubepress_api_const_template_Variable::GALLERY_ID, 'gallery-id');
        $mockTemplate->shouldReceive('setVariable')->once()->with(tubepress_api_const_template_Variable::VIDEO, $video);
        $mockTemplate->shouldReceive('setVariable')->once()->with(tubepress_api_const_template_Variable::EMBEDDED_WIDTH, 668);

        $event = new tubepress_spi_event_EventBase($mockTemplate);
        $event->setArguments(array(

            'playerName' => 'player-name',
            'providerName' => 'provider-name',
            'video' => $video
        ));

        $this->_sut->onPlayerTemplate($event);

        $this->assertEquals($mockTemplate, $event->getSubject());
    }
}