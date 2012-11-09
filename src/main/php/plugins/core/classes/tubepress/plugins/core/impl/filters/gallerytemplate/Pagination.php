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
 * Handles applying pagination to the gallery template.
 */
class tubepress_plugins_core_impl_filters_gallerytemplate_Pagination
{
    const DOTS = '<span class="tubepress_pagination_dots">...</span>';

    public function onGalleryTemplate(tubepress_api_event_TubePressEvent $event)
    {
        $context        = tubepress_impl_patterns_sl_ServiceLocator::getExecutionContext();
        $pm             = tubepress_impl_patterns_sl_ServiceLocator::getEventDispatcher();
        $providerResult = $event->getArgument('videoGalleryPage');
        $template       = $event->getSubject();

        $pagination    = $this->_getHtml($providerResult->getTotalResultCount());

        $event = new tubepress_api_event_TubePressEvent($pagination);

        $pm->dispatch(

            tubepress_api_const_event_CoreEventNames::PAGINATION_HTML_CONSTRUCTION,
            $event
        );

        $pagination = $event->getSubject();

        if ($context->get(tubepress_api_const_options_names_Thumbs::PAGINATE_ABOVE)) {

            $template->setVariable(tubepress_api_const_template_Variable::PAGINATION_TOP, $pagination);
        }

        if ($context->get(tubepress_api_const_options_names_Thumbs::PAGINATE_BELOW)) {

            $template->setVariable(tubepress_api_const_template_Variable::PAGINATION_BOTTOM, $pagination);
        }
    }

    /**
     * Get the HTML for pagination.
     *
     * @param int $vidCount The total number of results in this gallery
     *
     * @return string The HTML for the pagination.
     */
    private function _getHtml($vidCount)
    {
        $context        = tubepress_impl_patterns_sl_ServiceLocator::getExecutionContext();
        $messageService = tubepress_impl_patterns_sl_ServiceLocator::getMessageService();
        $qss            = tubepress_impl_patterns_sl_ServiceLocator::getQueryStringService();
        $hrps           = tubepress_impl_patterns_sl_ServiceLocator::getHttpRequestParameterService();

        $currentPage = $hrps->getParamValueAsInt(tubepress_spi_const_http_ParamName::PAGE, 1);
        $vidsPerPage = $context->get(tubepress_api_const_options_names_Thumbs::RESULTS_PER_PAGE);

        $newurl = new ehough_curly_Url($qss->getFullUrl($_SERVER));
        $newurl->unsetQueryVariable('tubepress_page');

        $result = $this->_diggStyle($vidCount, $messageService, $currentPage, $vidsPerPage, 1, $newurl->toString(), 'tubepress_page');

        /* if we're using Ajax for pagination, remove all the hrefs */
        if ($context->get(tubepress_api_const_options_names_Thumbs::AJAX_PAGINATION)) {

            $result = preg_replace('/rel="nofollow" href="[^"]*tubepress_page=([0-9]+)[^"]*"/', 'rel="page=${1}"', $result);
        }

        return $result;
    }

    /**
     * Does the heavy lifting of generating pagination.
     *
     * @param int    $totalitems The total items in this gallery.
     * @param int    $page       The current page number.
     * @param int    $limit      How many videos per page.
     * @param int    $adjacents  How many adjacents.
     * @param string $targetpage The target page
     * @param string $pagestring The query parameter controlling the page number.
     *
     * @return The HTML for the pagination
     */
    private function _diggStyle($totalitems, tubepress_spi_message_MessageService $messageService, $page = 1, $limit = 15, $adjacents = 1, $targetpage = '/', $pagestring = '?page=')
    {
        $prev       = $page - 1;
        $next       = $page + 1;
        $lastpage   = ceil($totalitems / $limit);
        $lpm1       = $lastpage - 1;
        $pagination = '';

        $url = new ehough_curly_Url($targetpage);

        if ($lastpage > 1) {
            $pagination .= '<div class="pagination">';
            if ($page > 1) {
                $url->setQueryVariable($pagestring, $prev);
                $newurl      = $url->toString();
                $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">&laquo; " .
                    $messageService->_('prev') .                                     //>(translatable)<
                	'</a>';
            }

            if ($lastpage < 7 + ($adjacents * 2)) {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $page) {
                        $pagination .= "<span class=\"current\">$counter</span>";
                    } else {
                        $url->setQueryVariable($pagestring, $counter);
                        $newurl      = $url->toString();
                        $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">$counter</a>";
                    }
                }
            } elseif ($lastpage >= 7 + ($adjacents * 2)) {

                if ($page < 1 + ($adjacents * 3)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $page) {
                            $pagination .= "<span class=\"current\">$counter</span>";
                        } else {
                            $url->setQueryVariable($pagestring, $counter);
                            $newurl      = $url->toString();
                            $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">$counter</a>";
                        }
                    }
                    $pagination .= self::DOTS;
                    $url->setQueryVariable($pagestring, $lpm1);
                    $newurl      = $url->toString();
                    $pagination .= " <a rel=\"nofollow\" href=\"$newurl\">$lpm1</a>";
                    $url->setQueryVariable($pagestring, $lastpage);
                    $newurl      = $url->toString();
                    $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">$lastpage</a>";
                } elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                    $url->setQueryVariable($pagestring, 1);
                    $newurl      = $url->toString();
                    $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">1</a>";
                    $url->setQueryVariable($pagestring, 2);
                    $newurl      = $url->toString();
                    $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">2</a>";
                    $pagination .= self::DOTS;

                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                        if ($counter == $page) {
                            $pagination .= "<span class=\"current\">$counter</span>";
                        } else {
                            $url->setQueryVariable($pagestring, $counter);
                            $newurl      = $url->toString();
                            $pagination .= " <a rel=\"nofollow\" href=\"$newurl\">$counter</a>";
                        }
                    }
                    $pagination .= self::DOTS;

                    $url->setQueryVariable($pagestring, $lpm1);
                    $newurl      = $url->toString();
                    $pagination .= " <a rel=\"nofollow\" href=\"$newurl\">$lpm1</a>";
                    $url->setQueryVariable($pagestring, $lastpage);
                    $newurl      = $url->toString();
                    $pagination .= " <a rel=\"nofollow\" href=\"$newurl\">$lastpage</a>";

                } else {
                    $url->setQueryVariable($pagestring, 1);
                    $newurl = $url->toString();
                    $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">1</a>";
                    $url->setQueryVariable($pagestring, 2);
                    $newurl = $url->toString();
                    $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">2</a>";
                    $pagination .= self::DOTS;

                    for ($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++) {
                        if ($counter == $page) {
                            $pagination .= "<span class=\"current\">$counter</span>";
                        } else {
                            $url->setQueryVariable($pagestring, $counter);
                            $newurl = $url->toString();
                            $pagination .= " <a rel=\"nofollow\" href=\"$newurl\">$counter</a>";
                        }
                    }
                }
            }
            if ($page < $counter - 1) {
                $url->setQueryVariable($pagestring, $next);
                $newurl      = $url->toString();
                $pagination .= "<a rel=\"nofollow\" href=\"$newurl\">" .
                    $messageService->_('next') .             //>(translatable)<
                	' &raquo;</a>';
            } else {
                $pagination .= '<span class="disabled">' .
                    $messageService->_('next') .             //>(translatable)<
                	' &raquo;</span>';
            }
            $pagination .= "</div>\n";
        }
        return $pagination;
    }
}
