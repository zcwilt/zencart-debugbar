<?php

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    private const CONFIG_GROUP_TITLE = 'Debug Bar';

    protected function executeInstall()
    {
        $cgi = $this->getOrCreateConfigGroupId(self::CONFIG_GROUP_TITLE, self::CONFIG_GROUP_TITLE . ' Settings');

        $this->addConfigurationKey('DEBUG_BAR_ENABLED', [
            'configuration_title' => 'Enable Debug Bar?',
            'configuration_value' => 'true',
            'configuration_description' => 'Enable the storefront debug bar output.',
            'configuration_group_id' => $cgi,
            'sort_order' => 10,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_ADMINS_ONLY', [
            'configuration_title' => 'Admins Only?',
            'configuration_value' => 'false',
            'configuration_description' => 'Restrict debug bar visibility to logged-in admin sessions only.',
            'configuration_group_id' => $cgi,
            'sort_order' => 20,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_IN_ADMIN', [
            'configuration_title' => 'Show in Admin?',
            'configuration_value' => 'true',
            'configuration_description' => 'Render the debug bar on Zen Cart admin pages as well as the storefront.',
            'configuration_group_id' => $cgi,
            'sort_order' => 25,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_TIMER', [
            'configuration_title' => 'Show Request Timer?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show simple request timing information in the debug bar.',
            'configuration_group_id' => $cgi,
            'sort_order' => 30,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_MEMORY', [
            'configuration_title' => 'Show Memory Usage?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show PHP memory usage information in the debug bar.',
            'configuration_group_id' => $cgi,
            'sort_order' => 40,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_SESSION', [
            'configuration_title' => 'Show Session Variables?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show a compact session summary and expandable session-variable dump in the debug bar.',
            'configuration_group_id' => $cgi,
            'sort_order' => 50,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_REQUEST', [
            'configuration_title' => 'Show Request/GET/POST?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show a compact request summary and expandable GET and POST variable dumps in the debug bar.',
            'configuration_group_id' => $cgi,
            'sort_order' => 60,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_SERVER', [
            'configuration_title' => 'Show Server Summary?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show a compact server/request-environment summary and expandable SERVER-variable dump in the debug bar.',
            'configuration_group_id' => $cgi,
            'sort_order' => 70,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_NOTIFIERS', [
            'configuration_title' => 'Show Notifier/Event Trace?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show a compact notifier summary and expandable event trace for the current request.',
            'configuration_group_id' => $cgi,
            'sort_order' => 80,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_DATABASE', [
            'configuration_title' => 'Show Database Query Summary?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show executed SQL query count and total query time for the current request.',
            'configuration_group_id' => $cgi,
            'sort_order' => 90,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        $this->addConfigurationKey('DEBUG_BAR_SHOW_MESSAGES', [
            'configuration_title' => 'Show Message Stack?',
            'configuration_value' => 'true',
            'configuration_description' => 'Show queued Zen Cart message-stack entries for the current request.',
            'configuration_group_id' => $cgi,
            'sort_order' => 100,
            'set_function' => 'zen_cfg_select_option([\'true\', \'false\'],',
        ]);

        zen_deregister_admin_pages(['configDebugBar']);
        zen_register_admin_page('configDebugBar', 'BOX_CONFIGURATION_DEBUG_BAR', 'FILENAME_CONFIGURATION', "gID=$cgi", 'configuration', 'Y');

        parent::executeInstall();
        return true;
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(['configDebugBar']);

        $this->deleteConfigurationKeys([
            'DEBUG_BAR_ENABLED',
            'DEBUG_BAR_ADMINS_ONLY',
            'DEBUG_BAR_SHOW_IN_ADMIN',
            'DEBUG_BAR_SHOW_TIMER',
            'DEBUG_BAR_SHOW_MEMORY',
            'DEBUG_BAR_SHOW_SESSION',
            'DEBUG_BAR_SHOW_REQUEST',
            'DEBUG_BAR_SHOW_SERVER',
            'DEBUG_BAR_SHOW_NOTIFIERS',
            'DEBUG_BAR_SHOW_DATABASE',
            'DEBUG_BAR_SHOW_MESSAGES',
        ]);

        parent::executeUninstall();
        return true;
    }
}
