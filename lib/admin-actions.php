<?php
namespace Plugins\Boilerplate\AdminActions;

use Plugins\Boilerplate as Plugin;
use Plugins\Boilerplate\Settings as Settings;

// Add admin actions and handlers here



/* There should be very little need to edit anything below this line */
add_action("admin_init", __NAMESPACE__ . "\\enqueuePubResources");
add_action("admin_notices", __NAMESPACE__ . "\\adminNotices");
add_action("admin_menu", __NAMESPACE__ . "\\registerSettingsPage");
add_filter(
    "plugin_action_links_" . plugin_basename(Plugin\BASE_NAME),
    __NAMESPACE__ . "\\renderPluginActionsLinks"
);

function enqueuePubResources()
{
    $pluginVersion = Settings\getPluginVersion();
    $urlStem = plugin_dir_url(Plugin\BASE_NAME);

    wp_enqueue_script(
        Plugin\SETTING_NAME . "-helpers",
        $urlStem . "/pub/admin-helpers.js",
        null,
        $pluginVersion,
        true
    );

    wp_enqueue_style(
        Plugin\SETTING_NAME . "-styles",
        $urlStem . "/pub/admin-styles.css",
        null,
        $pluginVersion,
        "screen"
    );
}

function registerSettingsPage()
{
    $settings = Settings\getSettings();

    add_plugins_page(
        $settings["page_title"],
        $settings["menu_title"],
        $settings["require_caps"],
        $settings["page_name"],
        getPageRenderer()
    );
}

function adminNotices()
{
    $settings = Settings\getSettings();
    $errors = get_settings_errors();

    foreach ($errors as $error) {
        if ($error["type"] != "error" && $error["type"] != "updated") {
            continue;
        } elseif ($error["code"] === $settings["page_name"]) {
            ?>
            <div class="<?php echo $error["type"] ?>"><p><?php echo $error["message"] ?></p></div>
            <?php
        }
    }
}

function getPageRenderer()
{
    return __NAMESPACE__ . "\\renderSettingsPage";
}

function getSectionRenderer()
{
    return __NAMESPACE__ . "\\renderSection";
}

function getFieldRenderer()
{
    return __NAMESPACE__ . "\\renderField";
}

function renderPluginActionsLinks($links)
{
    $settings = Settings\getSettings();

    array_unshift(
        $links,
        sprintf(
            "<a href=\"plugins.php?page=%s\">%s</a>",
            $settings["page_name"],
            "Settings"
        )
    );

    return $links;
}

function renderSettingsPage()
{
    $settings = Settings\getSettings();

    include Plugin\HOME_DIR . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "plugin-settings.php";
}

function renderSection($section)
{
    $settings = Settings\getSettings();

    if (array_key_exists($section["id"], $settings["sections"])) {
        $attribs = $settings["sections"][$section["id"]];
        ?>
        <p id="<?php echo $section["id"] ?>"><?php echo $attribs["description"] ?></p>
        <?php
    }
}

function renderField($args)
{
    switch ($args["type"])
    {
        case "number":
        case "email":
        case "text":
        case "tel":
        case "url":
            renderTextField($args);
            break;

        case "textarea":
            renderTextField($args, true);
            break;

        case "select":
            renderSelect($args);
            break;

        case "radio":
            renderRadioButtons($args);
            break;

        case "checkbox":
            renderCheckbox($args);
            break;

        default:
            error_log("Unknown field type: " . $args["type"]);
            break;
    }

    if (array_key_exists("description", $args) && !empty($args["description"])) {
        printf("<p>%s</p>", $args["description"]);
    }
}

function renderTextField($args, $multiline = false)
{
    if (!$multiline) {
        ?>
        <input
            type="<?php $args["type"] ?>"
            name="<?php echo $args["setting_name"] ?>[<?php echo $args["field_name"] ?>]"
            value="<?php echo $args["value"] ?>"
            id="<?php echo $args["field_name"] ?>"
            placeholder="<?php echo $args["placeholder"] ?>"
            class="<?php echo $args["className"] ?> textinput"
        />
        <?php
    } else {
        ?>
        <textarea
            type="<?php $args["type"] ?>"
            name="<?php echo $args["setting_name"] ?>[<?php echo $args["field_name"] ?>]"
            id="<?php echo $args["field_name"] ?>"
            placeholder="<?php echo $args["placeholder"] ?>"
            class="<?php echo $args["className"] ?> textinput"
        ><?php echo $args["value"] ?></textarea>
        <?php
    }
}

function renderSelect($args)
{
    $useKeyForValue = isAssocArray($args["options"]);
    ?>
    <select
        name="<?php echo $args["setting_name"] ?>[<?php echo $args["field_name"] ?>]"
        id="<?php echo $args["field_name"] ?>"
        class="<?php echo $args["className"] ?>"
    >

    <?php if (!is_null($args["default"])): ?>
        <option value="____no_selection____"><?php echo $args["default"] ?></option>
    <?php endif; ?>

    <?php foreach ($args["options"] as $value => $label): ?>
        <?php
        $selected = $args["value"] === ($useKeyForValue ? $value : $label);
        ?>

        <?php if ($useKeyForValue): ?>
            <option
                value="<?php echo $value ?>"
                <?php if ($selected): ?>selected<?php endif; ?>
            ><?php echo $label ?></option>
        <?php else: ?>
            <option
                <?php if ($selected): ?>selected<?php endif; ?>
            ><?php echo $label ?></option>
        <?php endif; ?>
    <?php endforeach; ?>

    </select>
    <?php
}

function renderRadioButtons($args)
{
    $useKeyForValue = isAssocArray($args["options"]);

    foreach ($args["options"] as $value => $label):
        $selected = $args["value"] === ($useKeyForValue ? $value : $label);
        $fieldValue = $useKeyForValue ? $value : $label;
        $isDefault = $fieldValue === $args["default"];
        ?>

        <label>
        <input
            type="radio"
            name="<?php echo $args["setting_name"] ?>[<?php echo $args["field_name"] ?>]"
            id="<?php echo $args["field_name"] ?>"
            class="<?php echo $args["className"] ?>"
            value="<?php echo $fieldValue ?>"
            <?php if ($selected): ?>checked<?php endif; ?>
        >

        <?php echo $label ?></label> <?php if ($isDefault): ?>(default)<?php endif; ?><br>
    <?php
    endforeach;
    ?>
    <a href="#" class="cloak" data-action="clear" data-input="<?php echo $args["setting_name"] ?>[<?php echo $args["field_name"] ?>]">Clear selection</a>
    <?php
}

function renderCheckbox($args)
{
    $useKeyForValue = isAssocArray($args["options"]);
    $valueFlipped = is_array($args["value"]) ? array_flip($args["value"]) : array();

    foreach ($args["options"] as $value => $label):
        $selected = array_key_exists($useKeyForValue ? $value : $label, $valueFlipped);
        $fieldValue = $useKeyForValue ? $value : $label;
        $isDefault = $fieldValue === $args["default"];
        ?>

        <input
            type="checkbox"
            name="<?php echo $args["setting_name"] ?>[<?php echo $args["field_name"] ?>][]"
            id="<?php echo $args["field_name"] ?>"
            class="<?php echo $args["className"] ?>"
            value="<?php echo $useKeyForValue ? $value : $label ?>"
            <?php if ($selected): ?>checked<?php endif; ?>
        >

        <?php echo $label ?> <?php if ($isDefault): ?>(default)<?php endif; ?><br>
    <?php
    endforeach;
}

function isAssocArray($arr)
{
    return is_array($arr) && array_keys($arr) !== range(0, count($arr) - 1);
}
