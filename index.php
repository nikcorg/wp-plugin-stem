<?php
/**
 * Plugin name: Plugin Stem
 * Description: Not really a plugin in itself, but a starting point.
 * Author: Niklas Lindgren <nikc@iki.fi>
 * Version: 1.2.0
 */
namespace Plugins\Boilerplate;

// Value for get_option (database name)
const SETTING_NAME = "plugin-stem";

// Settings page slug
const PAGE_NAME = "plugin-stem-settings";

// Settings page title
const PAGE_TITLE = "Plugin Stem Options";

// Settings page description
const PAGE_DESCRIPTION = "Settings for Plugin Stem";

// Menu title (in plugins menu)
const MENU_TITLE = "Plugin Stem";

// Helper for absolute path references
const HOME_DIR = __DIR__;

// Helpers for use get_plugin_data
const BASE_NAME = __FILE__;

// Capability required for managing settings
const REQUIRE_CAPS = "manage_options";

// Include settings and actions
require HOME_DIR . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "settings.php";
require HOME_DIR . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "admin-actions.php";
require HOME_DIR . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "actions.php";
