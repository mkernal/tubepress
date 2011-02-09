<?php
require_once dirname(__FILE__) . '/../../../../../../../../classes/org/tubepress/api/const/options/names/Display.class.php';
require_once dirname(__FILE__) . '/../../../../../../TubePressUnitTest.php';

class org_tubepress_api_const_options_names_DisplayTest extends TubePressUnitTest {
    
    function testConstants()
    {
        $expected = array('ajaxPagination', 'playerLocation', 'descriptionLimit', 'orderBy', 'relativeDates', 
            'resultsPerPage', 'thumbHeight', 'thumbWidth', 'paginationAbove', 'paginationBelow',
            'hqThumbs', 'randomize_thumbnails', 'theme');

        org_tubepress_api_const_ClassConstantTestUtility::performTest('org_tubepress_api_const_options_names_Display', $expected);
    }
}
?>
