<?php

namespace Craft;

class DownloadOrdersPlugin extends BasePlugin
{
 
    public function init()
    {   
        // only load includes if in control panel and logged in
        if( craft()->request->isCpRequest() && craft()->userSession->isLoggedIn() ) {

            require_once __DIR__ . '/vendor/PHPExcel/PHPExcel.php';

            craft()->templates->includeCssResource('downloadorders/css/DownloadOrders_Styles.css');
            craft()->templates->includeJsResource('downloadorders/js/DownloadOrders_Scripts.js');
        }
    }

    /**
     * Returns the user-facing name.
     *
     * @return mixed
     */
    public function getName()
    {
        return Craft::t('Download and Bulk Select Orders');
    }

    /**
     * Plugins can have descriptions of themselves displayed on the Plugins page by adding a getDescription() method
     * on the primary plugin class:
     *
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Create and download a CSV of orders. Bulk select and change order statuses');
    }

    /**
     * Returns the version number.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1';
    }

    /**
     * As of Craft 2.5, Craft no longer takes the whole site down every time a plugin’s version number changes, in
     * case there are any new migrations that need to be run. Instead plugins must explicitly tell Craft that they
     * have new migrations by returning a new (higher) schema version number with a getSchemaVersion() method on
     * their primary plugin class:
     *
     * @return string
     */
    public function getSchemaVersion()
    {
        return '1';
    }

    /**
     * Returns the developer’s name.
     *
     * @return string
     */
    public function getDeveloper()
    {
        return 'Kevin Douglass';
    }

    /**
     * Returns the developer’s website URL.
     *
     * @return string
     */
    public function getDeveloperUrl()
    {
        return '#';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }
}