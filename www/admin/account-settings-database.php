<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

// Require the initialisation file
require_once '../../init.php';

// Required files
require_once MAX_PATH . '/lib/OA/Admin/Option.php';
require_once MAX_PATH . '/lib/OA/Admin/Settings.php';

require_once MAX_PATH . '/lib/max/Admin/Redirect.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';
require_once MAX_PATH . '/www/admin/config.php';

// Security check
OA_Permission::enforceAccount(OA_ACCOUNT_ADMIN);

// Create a new option object for displaying the setting's page's HTML form
$oOptions = new OA_Admin_Option('settings');

// Prepare an array for storing error messages
$aErrormessage = array();

// If the settings page is a submission, deal with the form data
if (isset($_POST['submitok']) && $_POST['submitok'] == 'true') {
    // Prepare an array of the HTML elements to process, and the
    // location to save the values in the settings configuration
    // file
    $aElements = array();
    // Database Server Settings
    $aElements += array(
        'database_type'     => array('database' => 'type'),
        'database_host'     => array('database' => 'host'),
        'database_port'     => array('database' => 'port'),
        'database_username' => array('database' => 'username'),
        'database_password' => array('database' => 'password'),
        'database_name'     => array('database' => 'name')
    );
    // Database Optimision Settings
    $aElements += array(
        'database_persistent' => array(
            'database' => 'persistent',
            'bool'     => true
        )
    );
    // Set the database type, as can never be submitted by the form
    $database_type = $GLOBALS['_MAX']['CONF']['database']['type'];
    // Test the database connectivity
    phpAds_registerGlobal(
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'database_name'
    );
    $aDsn = array();
    $aDsn['database']['type']     = $database_type;
    $aDsn['database']['host']     = $database_host;
    $aDsn['database']['port']     = $database_port;
    $aDsn['database']['username'] = $database_username;
    $aDsn['database']['password'] = $database_password;
    $aDsn['database']['name']     = $database_name;
    $dsn = OA_DB::getDsn($aDsn);
    $oDbh = OA_DB::singleton($dsn);
    if (!PEAR::isError($oDbh)) {
        // Create a new settings object, and save the settings!
        $oSettings = new OA_Admin_Settings();
        $result = $oSettings->processSettingsFromForm($aElements);
        if ($result) {
            // The settings configuration file was written correctly,
            // go to the "next" settings page from here
            MAX_Admin_Redirect::redirect('account-settings-debug.php');
        }
        // Could not write the settings configuration file, store this
        // error message and continue
        $aErrormessage[0][] = $strUnableToWriteConfig;
    } else {
        $aErrormessage[0][] = $strCantConnectToDb;
    }
}

// Display the settings page's header and sections
phpAds_PageHeader("5.2");
phpAds_ShowSections(array("5.1", "5.2", "5.4", "5.5", "5.3", "5.7"));

// Set the correct section of the settings pages and display the drop-down menu
$oOptions->selection('database');

// Prepare an array of HTML elements to display for the form, and
// output using the $oOption object
$oSettings = array (
    array (
        'text'  => $strDatabaseServer,
        'items' => array (
            array (
                'type'       => 'select',
                'name'       => 'database_type',
                'text'       => $strDbType,
                'items'      => array($GLOBALS['_MAX']['CONF']['database']['type'] => $GLOBALS['_MAX']['CONF']['database']['type']),
                'enabled'    => true,
            ),
            array (
                'type'       => 'break'
            ),
            array (
                'type'       => 'text',
                'name'       => 'database_host',
                'text'       => $strDbHost,
                'req'        => true,
            ),
            array (
                'type'       => 'break'
            ),
            array (
                'type'       => 'text',
                'name'       => 'database_port',
                'text'       => $strDbPort,
                'req'        => true,
            ),
            array (
                'type'       => 'break'
            ),
            array (
                'type'       => 'text',
                'name'       => 'database_username',
                'text'       => $strDbUser,
                'req'        => true,
            ),
            array (
                'type'       => 'break'
            ),
            array (
                'type'       => 'password',
                'name'       => 'database_password',
                'text'       => $strDbPassword,
                'req'        => false,
            ),
            array (
                'type'       => 'break'
            ),
            array (
                'type'       => 'text',
                'name'       => 'database_name',
                'text'       => $strDbName,
                'req'        => true,
            )
        )
    ),
    array (
        'text'  => $strDatabaseOptimalisations,
        'items' => array (
            array (
                'type'      => 'checkbox',
                'name'      => 'database_persistent',
                'text'      => $strPersistentConnections
            )
        )
    )
);
$oOptions->show($oSettings, $aErrormessage);

// Display the page footer
phpAds_PageFooter();

?>