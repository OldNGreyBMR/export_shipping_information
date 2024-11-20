<?php
use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    protected function executeInstall()
    {
        zen_deregister_admin_pages(['shipping_export2']);
        zen_register_admin_page(
            'shipping_export2', 'BOX_TOOLS_SHIPPING_EXPORT2', 'FILENAME_SHIPPING_EXPORT2', '', 'tools', 'Y');
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['shipping_export2']);
    }
}
