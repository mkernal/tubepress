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
class tubepress_test_impl_template_templates_optionspage_fields_DropdownFieldTemplateTest extends tubepress_test_TubePressUnitTest
{
    public function test()
    {
        $id = 'some-name';
        $value = 'poo';
        $choices = array('crack' => 'rock', 'poo' => 'some-value', 'pretzels' => 'jets');

        ob_start();
        include TUBEPRESS_ROOT . '/src/main/resources/options-gui/field-templates/dropdown.tpl.php';
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(tubepress_impl_util_StringUtils::removeEmptyLines($this->_expected()), tubepress_impl_util_StringUtils::removeEmptyLines($result));
    }

    private function _expected()
    {
        return <<<EOT
<select class="form-control" id="some-name" name="some-name">
	    <option value="crack" >rock</option>
	    <option value="poo" SELECTED>some-value</option>
	    <option value="pretzels" >jets</option>
</select>
EOT;
    }

}