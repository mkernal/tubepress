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

/**
 * Class for managing HTTP Transports and making HTTP requests.
 */
class tubepress_impl_http_DefaultHttpRequestParameterService implements tubepress_spi_http_HttpRequestParameterService
{
    /**
     * Gets the parameter value from PHP's $_REQUEST array.
     *
     * @param string $name The name of the parameter.
     *
     * @return mixed The raw value of the parameter. Can be anything that would
     *               otherwise be found in PHP's $_REQUEST array. Returns null
     *               if the parameter is not set on this request.
     */
    public final function getParamValue($name)
    {
        /** Are we sure we have it? */
        if (!($this->hasParam($name))) {

            return null;
        }

        $rawValue = $_REQUEST[$name];

        $eventDispatcher = tubepress_impl_patterns_sl_ServiceLocator::getEventDispatcher();

        $event = new tubepress_api_event_TubePressEvent(

            $rawValue,
            array('optionName' => $name)
        );

        $eventDispatcher->dispatch(

            tubepress_api_const_event_CoreEventNames::VARIABLE_READ_FROM_EXTERNAL_INPUT,
            $event
        );

        return $event->getSubject();
    }

    /**
     * Gets the parameter value from PHP's $_REQUEST array. If the hasParam($name) returs false, this
     *  behaves just like getParamvalue($name). Otherwise, if the raw parameter value is numeric, a conversion
     *  will be attempted.
     *
     * @param string $name    The name of the parameter.
     * @param int    $default The default value is the raw value is not integral.
     *
     * @return mixed The raw value of the parameter. Can be anything that would
     *                       otherwise be found in PHP's $_REQUEST array. Returns null
     *                       if the parameter is not set on this request.
     */
    public final function getParamValueAsInt($name, $default)
    {
        $raw = $this->getParamValue($name);

        /** Not numeric? */
        if (! is_numeric($raw) || ($raw < 1)) {

            return $default;
        }

        return (int) $raw;
    }

    /**
     * Determines if the parameter is set in PHP's $_REQUEST array.
     *
     * @param string $name The name of the parameter.
     *
     * @return mixed True if the parameter is found in PHP's $_REQUEST array, false otherwise.
     */
    public final function hasParam($name)
    {
        return array_key_exists($name, $_REQUEST);
    }

    /**
     * Returns a map of param name => param value for ALL parameters in the request.
     *
     * @return array A map of param name => param value for ALL parameters in the request.
     */
    public final function getAllParams()
    {
        $toReturn = array();

        foreach ($_REQUEST as $key => $value) {

            $toReturn[$key] = $this->getParamValue($key);
        }

        return $toReturn;
    }
}

