<?php
namespace Plugins\Boilerplate\Settings;

use Plugins\Boilerplate as Plugin;
use Plugins\Boilerplate\AdminActions as AdminActions;

const SECTION_DEFAULT = "default";

function getSections()
{
    // Add Setting sections here
    return array(
        SECTION_DEFAULT => array(
            "title" => "Section Title",
            "description" => "Optional section description"
        )
    );
}

function getFields()
{
    // Add fields here
    return array(
        array(
            "section" => SECTION_DEFAULT,
            "type" => FIELD_TEXT,
            "name" => "field-name-in-markup",
            "title" => "Field title",
            "default" => "default value, also used as placeholder",
            "description" => "Optional field description",
            "placeholder" => "Field content placeholder"
        ),
    );
}

// If version migrations need work done, here's the place to do it
function migrateVersion($values, $fromVersion, $toVersion)
{
    return $values;
}

/* There should be very little need to edit anything below this line */

const FIELD_TEXT = "text";
const FIELD_TEXT_MULTILINE = "textarea";
const FIELD_URL = "url";
const FIELD_EMAIL = "email";
const FIELD_DATE = "date";
const FIELD_TIME = "time";
const FIELD_DATETIME = "datetime";
const FIELD_NUMBER = "number";
const FIELD_SELECT = "select";
const FIELD_RADIO = "radio";
const FIELD_CHECKBOX = "checkbox";

add_action("admin_init", __NAMESPACE__ . "\\registerSettings");

function getSettings()
{
    return array(
        "setting_name" => Plugin\SETTING_NAME,
        "page_name" => Plugin\PAGE_NAME,
        "page_title" => Plugin\PAGE_TITLE,
        "menu_title" => Plugin\MENU_TITLE,
        "description" => Plugin\PAGE_DESCRIPTION,
        "require_caps" => Plugin\REQUIRE_CAPS,
        "sections" => getSections(),
        "fields" => array_filter(getFields(), __NAMESPACE__ . "\\isValidField")
    );
}

function getFieldValues($setDefault = false, $section = false)
{
    $settings = getSettings();
    $option = get_option($settings["setting_name"]);
    $values = array(
        "plugin_version" => is_array($option) && array_key_exists("plugin_version", $option)
            ? $option["plugin_version"]
            : getPluginVersion()
    );

    foreach ($settings["fields"] as $attribs) {
        if ($section && $section != $attribs["section"]) {
            continue;
        }

        $fieldName = $attribs["section"] . ":" . $attribs["name"];

        // Prefix the export key with the section name when the section argument is unset
        $exportKey = false === $section
            ? $fieldName
            : $attribs["name"];

        if (is_array($option) && array_key_exists($fieldName, $option) && !empty($option[$fieldName])) {
            $values[$exportKey] = $option[$fieldName];
        } elseif ($setDefault) {
            $values[$exportKey] = $attribs["default"];
        } else {
            $values[$exportKey] = null;
        }
    }

    return $values;
}

function isValidField($field)
{
    if (!is_array($field)) {
        error_log("A field definition must be an array");
        return false;
    }

    $hasRequiredProps = isset($field["name"]) && isset($field["title"]) && isset($field["section"]);

    if (!$hasRequiredProps) {
            error_log("A field is missing one or more required property. Required properties are: name, title, section.");
            return false;
    } elseif ($hasRequiredProps && !array_key_exists($field["section"], getSections())) {
        error_log(sprintf(
            "Field `%s` is assigned to an undefined section `%s`",
            $field["name"],
            $field["section"]
        ));
        return false;
    }

    return true;
}

function getPluginVersion()
{
    $pluginVersion = "";
    $pluginData = null;

    if (function_exists("get_plugin_data")) {
        $pluginData = \get_plugin_data(Plugin\BASE_NAME);
    } else {
        if (!function_exists("get_plugins")) {
            require_once(ABSPATH . "wp-admin/includes/plugin.php");
        }

        $pluginData = \get_plugins(DIRECTORY_SEPARATOR . \plugin_basename(Plugin\HOME_DIR));

        if (array_key_exists(basename(Plugin\BASE_NAME), $pluginData)) {
            $pluginData = $pluginData[basename(Plugin\BASE_NAME)];
        }
    }

    if (null != $pluginData && array_key_exists("Version", $pluginData)) {
        $pluginVersion = $pluginData["Version"];
    }

    return $pluginVersion;
}

function identity($value)
{
    return $value;
}

function sanitize($input)
{
    $settings = getSettings();
    $values = getFieldValues();
    $output = array(
        "plugin_version" => getPluginVersion()
    );

    // Filter and validate incoming data
    foreach ($settings["fields"] as $attribs) {
        $fieldName = $attribs["section"] . ":" . $attribs["name"];

        // Skip any fields that don't exists
        if (!array_key_exists($fieldName, $input)) {
            continue;
        }

        $transientValue = $input[$fieldName];

        // ____no_selection____ is the default value placeholder in selects
        if ($transientValue === "____no_selection____") {
            $transientValue = null;
        }

        $validator = array_key_exists("validate", $attribs) && is_callable($attribs["validate"])
            ? $attribs["validate"]
            : __NAMESPACE__ . "\\identity";
        $sanitizer = array_key_exists("sanitize", $attribs) && is_callable($attribs["sanitize"])
            ? $attribs["sanitize"]
            : __NAMESPACE__ . "\\identity";

        $transientValue = call_user_func($validator, call_user_func($sanitizer, $transientValue), $attribs);

        $output[$fieldName] = $transientValue;
    }

    // When version numbers don't match, do a migration
    if ($values["plugin_version"] !== $output["plugin_version"]) {
        $output = migrateVersion($output, $values["plugin_version"], $output["plugin_version"]);
    }

    return $output;
}

function normaliseAttribNames($attribs)
{
    if (array_key_exists("sanitise", $attribs)) {
        error_log(sprintf(
            "Use spelling `sanitize` instead of `sanitise` for setting `%s` in section `%s`",
            $attribs["name"], $attribs["section"]
        ));

        $attribs["sanitize"] = $attribs["sanitise"];
        unset($attribs["sanitise"]);
    }

    return $attribs;
}

function registerSettings()
{
    $settings = getSettings();
    $values = getFieldValues();

    register_setting(
        $settings["setting_name"],
        $settings["setting_name"],
        __NAMESPACE__ . "\\sanitize"
    );

    foreach ($settings["sections"] as $section => $attribs) {
        add_settings_section(
            $section,
            $attribs["title"],
            AdminActions\getSectionRenderer(),
            $settings["page_name"]
        );
    }

    $renderDefaults = array(
        "type" => "text",
        "className" => "",
        "options" => null,
        "default" => null,
        "description" => null
    );

    foreach ($settings["fields"] as $attribs) {
        $fieldName = $attribs["section"] . ":" . $attribs["name"];
        $renderingArgs = array_merge(
            $renderDefaults,
            normaliseAttribNames($attribs),
            array(
                "value" => $values[$fieldName],
                "label_for" => $fieldName,
                "field_name" => $fieldName,
                "setting_name" => $settings["setting_name"]
            )
        );

        add_settings_field(
            $fieldName,
            $attribs["title"],
            AdminActions\getFieldRenderer(),
            $settings["page_name"],
            $attribs["section"],
            $renderingArgs
        );
    }
}
