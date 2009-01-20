<?php
/**
 * Copyright 2006, 2007, 2008, 2009 Eric D. Hough (http://ehough.com)
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

/* we need this function for pagination */
function_exists("diggstyle_getPaginationString")
    || require dirname(__FILE__) . "/../../../../lib/diggstyle_function.php";

/**
 * General purpose cache for TubePress
 */
class org_tubepress_pagination_DiggStylePaginationService implements org_tubepress_pagination_PaginationService
{
	private $_messageService;
	private $_tpom;
	private $_queryStringService;
	
	public function getHtml($vidCount)
	{
		$currentPage = $this->_queryStringService->getPageNum($_GET);
        $vidsPerPage = $this->_tpom->
            get(TubePressDisplayOptions::RESULTS_PER_PAGE);
    
        $newurl = new Net_URL2($this->_queryStringService->getFullUrl($_SERVER));
        $newurl->unsetQueryVariable("tubepress_page");

        return diggstyle_getPaginationString($this->_messageService, $currentPage, $vidCount,
            $vidsPerPage, 1, $newurl->getURL(), 
                "tubepress_page");
	}
	
	public function setMessageService(org_tubepress_message_MessageService $messageService) { $this->_messageService = $messageService; }
	public function setQueryStringService(org_tubepress_querystring_QueryStringService $queryStringService) { $this->_queryStringService = $queryStringService; }
	public function setOptionsManager(TubePressOptionsManager $tpom) { $this->_tpom = $tpom; }
}