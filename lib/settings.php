<?php
namespace Plugins\Boilerplate\Settings;

use Plugins\Boilerplate as Plugin;
use Plugins\Boilerplate\AdminActions as AdminActions;

function getSections()
{
    // Add Setting sections here
    return array(
        "section_name" => array(
            "title" => "Section Title",
            "description" => "Optional section description"
        )
    );
}

function getFields()
{
    // Add fields here, if there are very many, array_merging from many included
    // files might make sense.
    return array(
        array(
            "section" => "section_name",
            "type" => "text",
            "name" => "field-name-in-markup",
            "title" => "Field title",
            "default" => "default value, also used as placeholder",
            "description" => "Optional field description"
        ),
    );
}

// If version migrations need work done, here's the place to do it
function migrateVersion($values, $fromVersion, $toVersion)
{
    return $values;
}

/* There should be very little need to edit anything below this line */

add_action("admin_init", __NAMESPACE__ . "\\registerSettings");

function isValidField($field)
{
    return is_array($field) &&
        isset($field["name"]) &&
        isset($field["title"]) &&
        isset($field["section"]);
}

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
    $pluginData = get_plugin_data(Plugin\BASE_NAME);
    $settings = getSettings();
    $option = get_option($settings["setting_name"]);
    $values = array(
        "plugin_version" => is_array($option) && array_key_exists("plugin_version", $option)
            ? $option["plugin_version"]
            : $pluginData["Version"]
    );

    foreach ($settings["fields"] as $attribs) {
        if ($section && $section != $attribs["section"]) {
            continue;
        }

        $key = $attribs["section"] . ":" . $attribs["name"];
        $exportKey = false != $section ? $attribs["name"] : $key;
        $values[$exportKey] = null;

        if (is_array($option) && array_key_exists($key, $option) && !empty($option[$key])) {
            $values[$exportKey] = $option[$key];
        } elseif ($setDefault) {
            $values[$exportKey] = $attribs["default"];
        }
    }

    return $values;
}

function sanitize($input)
{
    $pluginData = get_plugin_data(Plugin\BASE_NAME);

    $settings = getSettings();
    $values = getFieldValues();
    $output = array(
        "plugin_version" => $pluginData["Version"]
    );

    // Filter and validate incoming data
    foreach ($settings["fields"] as $attribs) {
        $key = $attribs["section"] . ":" . $attribs["name"];
        $validator = null;

        // Skip any fields that don't exists
        if (! array_key_exists($key, $input)) {
            continue;
        }

        // Validate field validator
        if (array_key_exists("validate", $attribs)) {
            if (is_callable($attribs["validate"])) {
                $validator = $attribs["validate"];
            } else {
                error_log("Validator for field " . $attribs["name"] . " is invalid: " . $attribs["validate"]);
            }
        }

        // Call validator if set
        if (null != $validator) {
            $output[$key] = call_user_func($validator, $input[$key]);
        } else {
            // ____no_selection____ is the default value placeholder in selects
            $output[$key] = $input[$key] === "____no_selection____" ? null : $input[$key];
        }
    }

    // When version numbers don't match, do a migration
    if ($values["plugin_version"] !== $output["plugin_version"]) {
        $output = migrateVersion($output, $values["plugin_version"], $output["plugin_version"]);
    }

    return $output;
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
        "description" => null,
        "setting_name" => $settings["setting_name"]
    );

    foreach ($settings["fields"] as $attribs) {
        $fieldName = $attribs["section"] . ":" . $attribs["name"];
        $renderingArgs = array_merge(
            $renderDefaults,
            $attribs,
            array(
                "value" => $values[$fieldName],
                "label_for" => $fieldName,
                "field_name" => $fieldName
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
