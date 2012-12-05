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
class tubepress_impl_options_ui_fields_ThemeFieldTest extends tubepress_impl_options_ui_fields_DropdownFieldTest
{
    protected function _buildSut($name)
    {
        return new tubepress_impl_options_ui_fields_ThemeField($name, 'theme');
    }

    protected function _performAdditionGetDescriptionSetup()
    {
        $this->getMockEnvironmentDetector()->shouldReceive('getUserContentDirectory')->once()->andReturn('<<user content dir>>');

    }
}
