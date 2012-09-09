<?php
/**
 * Copyright 2006 - 2012 Eric D. Hough (http://ehough.com)
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
 * Base class for HTML fields.
 */
abstract class tubepress_impl_options_ui_fields_AbstractOptionDescriptorBasedField extends tubepress_impl_options_ui_fields_AbstractField
{
    const TEMPLATE_VAR_VALUE = 'tubepress_impl_options_ui_fields_AbstractOptionDescriptorBasedField__value';

    /** Applicable providers. */
    private $_providerArray = array();

    /** Option descriptor. */
    private $_optionDescriptor;

    /** Options validator. */
    private $_optionsValidator;

    public function __construct(

        tubepress_spi_message_MessageService            $messageService,
        tubepress_spi_options_OptionDescriptorReference $optionDescriptorReference,
        tubepress_spi_options_StorageManager            $storageManager,
        tubepress_spi_options_OptionValidator           $optionValidator,
        tubepress_spi_http_HttpRequestParameterService  $hrps,
        tubepress_spi_environment_EnvironmentDetector   $environmentDetector,
        ehough_contemplate_api_TemplateBuilder          $templateBuilder,
        $name)
    {
        parent::__construct(

            $messageService,
            $hrps,
            $environmentDetector,
            $templateBuilder,
            $storageManager);

        $this->_optionsValidator = $optionValidator;
        $this->_optionDescriptor = $optionDescriptorReference->findOneByName($name);

        if ($this->_optionDescriptor === null) {

            throw new InvalidArgumentException(sprintf('Could not find option with name "%s"', $name));
        }

        if ($this->_optionDescriptor->isApplicableToVimeo()) {

            array_push($this->_providerArray, tubepress_spi_provider_Provider::VIMEO);
        }

        if ($this->_optionDescriptor->isApplicableToYouTube()) {

            array_push($this->_providerArray, tubepress_spi_provider_Provider::YOUTUBE);
        }
    }

    /**
     * Gets the providers to which this field applies.
     *
     * @return array An array of provider names to which this field applies. May be empty. Never null.
     */
    public final function getArrayOfApplicableProviderNames()
    {
        return $this->_providerArray;
    }

    /**
     * Get the untranslated title of this field.
     *
     * @return string The untranslated title of this field.
     */
    public final function getRawTitle()
    {
        return $this->_optionDescriptor->getLabel();
    }

    /**
     * Get the untranslated description of this field.
     *
     * @return string The untranslated description of this field.
     */
    public final function getRawDescription()
    {
        return $this->_optionDescriptor->getDescription();
    }

    /**
     * Gets whether or not this field is TubePress Pro only.
     *
     * @return boolean True if this field is TubePress Pro only. False otherwise.
     */
    public final function isProOnly()
    {
        return $this->_optionDescriptor->isProOnly();
    }

    /**
     * Generates the HTML for the options form.
     *
     * @return string The HTML for the options form.
     */
    public final function getHtml()
    {
        $basePath     = $this->getEnvironmentDetector()->getTubePressBaseInstallationPath();
        $template     = $this->getTemplateBuilder()->getNewTemplateInstance($basePath . '/' . $this->getTemplatePath());
        $currentValue = $this->getStorageManager()->get($this->_optionDescriptor->getName());

        $template->setVariable(self::TEMPLATE_VAR_NAME, $this->_optionDescriptor->getName());
        $template->setVariable(self::TEMPLATE_VAR_VALUE, $currentValue);

        $this->populateTemplate($template, $currentValue);

        return $template->toString();
    }

    /**
     * Handles form submission.
     *
     * @return array An array of failure messages if there's a problem, otherwise null.
     */
    public final function onSubmit()
    {
        if ($this->_optionDescriptor->isBoolean()) {

            return $this->_onSubmitBoolean();
        }

        return $this->_onSubmitSimple();
    }

    /**
     * Get the path to the template for this field, relative
     * to TubePress's root.
     *
     * @return string The path to the template for this field, relative
     *                to TubePress's root.
     */
    protected abstract function getTemplatePath();

    /**
     * Override point.
     *
     * Allows subclasses to perform additional modifications to this
     * field's template.
     *
     * @param ehough_contemplate_api_Template $template     The field's template.
     * @param string                          $currentValue The current value of this field.
     *
     * @return void
     */
    protected function populateTemplate($template, $currentValue)
    {
         //override point
    }

    protected final function getOptionDescriptor()
    {
        return $this->_optionDescriptor;
    }

    private function _onSubmitSimple()
    {
        $name = $this->_optionDescriptor->getName();

        if (! $this->getHttpRequestParameterService()->hasParam($name)) {

            /* not submitted. */
            return null;
        }

        $value = $this->getHttpRequestParameterService()->getParamValue($name);

        /* run it through validation. */
        if (! $this->_optionsValidator->isValid($name, $value)) {

            return array($this->_optionsValidator->getProblemMessage($name, $value));
        }

        return $this->_setToStorage($name, $value);
    }

    private function _onSubmitBoolean()
    {
        $name = $this->_optionDescriptor->getName();

        /* if the user checked the box, the option name will appear in the POST vars */
        return $this->_setToStorage($name, $this->getHttpRequestParameterService()->hasParam($name));
    }

    private function _setToStorage($name, $value)
    {
        $result = $this->getStorageManager()->set($name, $value);

        if ($result === true) {

            return null;
        }

        return array($result);
    }
}