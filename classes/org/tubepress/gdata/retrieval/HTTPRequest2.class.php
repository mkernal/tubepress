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

/**
 *
 */
class org_tubepress_gdata_retrieval_HTTPRequest2 extends org_tubepress_gdata_retrieval_AbstractFeedRetrievalService
{
    protected function _fetchFromNetwork($request) {
    	$data = "";
    	$request = new Net_URL2($request);
    	$req = new net_php_pear_HTTP_Request2($request);
    	$req->setAdapter(new net_php_pear_HTTP_Request2_Adapter_Socket());

    	$response = $req->send();
       	$data = $response->getBody();
        return $data;
    }
}