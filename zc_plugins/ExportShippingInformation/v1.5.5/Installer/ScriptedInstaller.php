<?php
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        global $sniffer;
        
        zen_deregister_admin_pages(['shipping_export2']);
        zen_register_admin_page(
            'shipping_export2', 'BOX_TOOLS_SHIPPING_EXPORT2', 'FILENAME_SHIPPING_EXPORT2', '', 'tools', 'Y');

        if ($sniffer->field_exists(TABLE_ORDERS, 'downloaded_ship') !== true) {
        $this->executeInstallerSql("ALTER TABLE " . TABLE_ORDERS . " ADD downloaded_ship ENUM( 'yes', 'no' ) NOT NULL DEFAULT 'no'");
        }
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['shipping_export2']);
        //$this->executeInstallerSql("ALTER TABLE " . TABLE_ORDERS . " DROP COLUMN downloaded_ship"); // Uncomment if you want to delete this extra field when uninstalling. Data in this column will be lost.
    }
}
