<?php
/**
 * Copyright 2006 - 2011 Eric D. Hough (http://ehough.com)
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

class_exists('org_tubepress_impl_classloader_ClassLoader') || require(dirname(__FILE__) . '/../classloader/ClassLoader.class.php');
org_tubepress_impl_classloader_ClassLoader::loadClasses(array(
    'org_tubepress_api_url_UrlBuilder',
    'org_tubepress_api_patterns_cor_Chain',
    'org_tubepress_api_provider_ProviderCalculator',
    'org_tubepress_impl_ioc_IocContainer',
));

/**
 * Builds URLs based on the current provider
 */
class org_tubepress_impl_url_UrlBuilderChain implements org_tubepress_api_url_UrlBuilder
{
    /**
     * Builds a URL for a list of videos
     *
     * @return string The request URL for this gallery
     */
    public function buildGalleryUrl($currentPage)
    {
        return self::_build($currentPage, false);
    }

    /**
     * Builds a request url for a single video
     *
     * @param string $id The video ID to search for
     */
    public function buildSingleVideoUrl($id)
    {
        return self::_build($id, true);
    }

    private static function _build($arg, $single)
    {
        $ioc = org_tubepress_impl_ioc_IocContainer::getInstance();
        $pc  = $ioc->get('org_tubepress_api_provider_ProviderCalculator');
        $sm  = $ioc->get('org_tubepress_api_patterns_cor_Chain');

        //TODO: what if this bails?
        $providerName = $pc->calculateCurrentVideoProvider();

        $context = $sm->createContextInstance();
        $context->providerName = $providerName;
        $context->single = $single;
        $context->arg = $arg;

        /* let the commands do the heavy lifting */
        //TODO: what if this bails?
        $status = $sm->execute($context, array(
            'org_tubepress_impl_url_commands_YouTubeUrlBuilderCommand',
            'org_tubepress_impl_url_commands_VimeoUrlBuilderCommand'
        ));

        if ($status === false) {
            throw new Exception('No commands could build a URL');
        }

        return $context->returnValue;
    }
}
