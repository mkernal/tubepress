<?php
require_once dirname(__FILE__) . '/../../../includes/TubePressUnitTestSuite.php';
require_once 'VersionTest.php';

class org_tubepress_api_version_VersionApiTestSuite
{
	public static function suite()
	{
		return new TubePressUnitTestSuite(array(

			'org_tubepress_api_version_VersionTest'
		));
	}
}
