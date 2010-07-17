<?php
/**
 * Copyright 2006 - 2010 Eric D. Hough (http://ehough.com)
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

function_exists('tubepress_load_classes')
    || require dirname(__FILE__) . '/../../../tubepress_classloader.php';
tubepress_load_classes(array('org_tubepress_ioc_IocService',
    'org_tubepress_options_category_Display',
    'org_tubepress_log_Log',
    'org_tubepress_template_SimpleTemplate'));

/**
 * A TubePress theme
 */
class org_tubepress_theme_Theme
{
    const LOG_PREFIX = 'Theme';
    
    public static function getTemplateInstance(org_tubepress_ioc_IocService $ioc, $pathToTemplate)
    {
        org_tubepress_log_Log::log(self::LOG_PREFIX, 'Loading template instance at "%s"', $pathToTemplate);
        
        $tpom         = $ioc->get(org_tubepress_ioc_IocService::OPTIONS_MANAGER);
        $currentTheme = $tpom->get(org_tubepress_options_category_Display::THEME);
        if ($currentTheme == '') {
            $currentTheme = 'default';
        }
        
        org_tubepress_log_Log::log(self::LOG_PREFIX, 'Current theme is "%s"', $currentTheme);
        
        $tubepressInstallationPath = realpath(dirname(__FILE__) . '/../../../..');
        $filePath                  = "$tubepressInstallationPath/content/themes/$currentTheme/$pathToTemplate";
        
        if (!is_readable($filePath)) {
            org_tubepress_log_Log::log(self::LOG_PREFIX, '%s is not readable. Checking ui/themes instead.', $filePath);
            
            $filePath = "$tubepressInstallationPath/ui/themes/$currentTheme/$pathToTemplate";
            if (!is_readable($filePath)) {
                throw new Exception("Cannot read template at $pathToTemplate");
            }
        }
        
        org_tubepress_log_Log::log(self::LOG_PREFIX, 'Loading template from %s', $filePath);
        $template = new org_tubepress_template_SimpleTemplate();
        $template->setPath($filePath);
        return $template;
    }
}
