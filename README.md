# Boilerplate Stuff For WordPress Plugin Development

I've had the dubious pleasure of creating a few WordPress plugins. Setting them up was never something to look forward to, so I gathered recurring features into a single place. A lot of this code is code I believe should be a shared dependency, or part of WordPress core, but this is probably the second best option.

Very little here is completely original code, but has rather been compiled and paraphrased from many sources here and there. I'd give due credit, but having waded through what seems like gigabytes of WordPress related blog posts to find all that I need, I'm truly not capable to do so: Credits go to the entire WordPress community.

## What's In This Repo

The boilerplate contains what's required for setting up a plugin settings page and running admin and public actions, i.e. just the basic harness to build the rest upon.

## Howto

1. Check and modify the `namespace` in `/index.php` and `/lib/*.php`
2. Add settings to `/lib/settings.php#getFields()` as required
3. Add settings sections to `/lib/settings.php#getSections()` as required
4. Hook on to public actions in `/lib/actions.php`

## Debug Mode And Logging

If `WP_DEBUG` is set and `true` the current settings values is by default displayed on the settings page.

For invalid settings errors are logged using `error_log`. This happens regardless of the value of `WP_DEBUG`.

## Consumption API

The `Settings` namespace really only provides two functions for consuming settings:

- `Settings\getSettings()`
    - returns all plugin settings in a single array structure
- `Settings\getFieldValues($setDefault = false, $section = null)`
    - returns field values, with optionally default value set for empty values (`empty($value) === true`)
    - if all sections are queried, the field name is prefixed with the section identifier follow by a colon (`:`)

## Defining Settings

Field and section definitions are added to `lib/settings.php`. Add sections to the return value of `getSections()` and fields to the return value of `getFields()`.

### Settings Sections

The function `getSections` in `lib/settings.php` should return an associative array of section definitions, where the array _key_ is the _section identifier_. It is recommended to define the section identifiers as constants, to avoid bugs introduced by typos because of "magic strings".

#### Section Properties

- name (`PROP_NAME`)
- description (`PROP_DESCRIPTION`) (optional)

#### Example

```php
const SECTION_DEFAULT = "default";

function getSections()
{
    return array(
        SECTION_DEFAULT => array(
            PROP_NAME => "Global settings",
            PROP_DESCRIPTION => "These settings apply when more specific settings don't exist"
        )
    );
}
```

### Settings Fields

The function `getFields` in `lib/settings.php` should return an array of field definitions.

#### Field Properties

For a field to be valid, it must have a title, name, and section.

- section (`PROP_SECTION`)
- name (`PROP_NAME`)
- title (`PROP_TITLE`)

Other properties are:

- type (`PROP_TYPE`)
- description (`PROP_DESCRIPTION`)
- placeholder (`PROP_PLACEHOLDER`)
- options (`PROP_OPTIONS`)
- default (`PROP_DEFAULT`)
- validate (`PROP_VALIDATE`) (callback)
- sanitize (`PROP_SANITIZE`) (callback)

#### Field Types

- text (`FIELD_TEXT`)
- textarea (`FIELD_TEXT_MULTILINE`)
- number (`FIELD_NUMBER`)
- email (`FIELD_EMAIL`)
- url (`FIELD_URL`)
- date/datetime/time (`FIELD_DATE`, `FIELD_DATETIME`, `FIELD_TIME`)
- toggle/boolean (`FIELD_TOGGLE`)

The following types require an `options` property. If it is an associative array, the value returned from `getFieldValues()` will be the key and not the value.

- select (`FIELD_SELECT`)
- radio (`FIELD_RADIO`)
- checkbox (`FIELD_CHECKBOX`)

A toggle/boolean type field will also use its `options` property when present, but it will default to Off/On. In case of boolean values, the values will require it's arguments in off state/on state order. Keys are ignored.

#### Example

```php
function getFields()
{
    return array(
        array(
            PROP_SECTION => SECTION_DEFAULT,
            PROP_TYPE => FIELD_TEXT_MULTILINE,
            PROP_NAME => "post-footer",
            PROP_TITLE => "Post footer text",
            PROP_DESCRIPTION => "A short text displayed after each post"
        )
    );
}
```

## Settings Fields Sanitising And Validation

Each field has separate properties for validation and sanitising to enable general purpose input sanitisers, e.g. ensure a field is only number, or e.g. the WordPress provided filters such as [`wp_filter_nohtml_kses`](https://codex.wordpress.org/Function_Reference/wp_filter_nohtml_kses), while still retaining the option to have strict field specific validation.

The sanitize callback receives as arguments only the field's value, while the validator also receives the fields attributes and an error callback.

Invoking order is sanitize -> validate, i.e. validate get's the output from sanitize. The value returned from sanitize is the value stored.

### Showing Validation Errors

The validation callback receives as it's third argument a callback that takes an error message and error type (recognized values: "error", "update") as params. These validation errors are displayed during WordPress' `adminNotices` action.

### Example

```php
function getFields() {
    return array(
        array(
            PROP_NAME => "my-text-field",
            PROP_TITLE => "A text field",
            PROP_DESCRIPTION => "Please keep content length between 20 and 40 characters.",
            PROP_VALIDATE => "validateLength"
        )
    );
}

function validateLength($str, $attribs, $errorCb) {
    if (20 > strlen($str) || strlen($str) > 40) {
        $errorCb("The text should be between 20 and 40 characters in length.");
    }

    return $str;
}
```

## Change settings page location in the menu

By default, [`add_plugins_page()`](https://developer.wordpress.org/reference/functions/add_plugins_page/) is used for registering the settings page. This places your settings page under the Plugins menu which you may or may not want. Should you want to change the location of the settings page, switch to [`add_submenu_page()`](https://developer.wordpress.org/reference/functions/add_submenu_page/). 

```php
add_plugins_page(
  $settings[Settings\S_PAGE_TITLE],
  $settings[Settings\S_MENU_TITLE],
  $settings[Settings\S_REQUIRE_CAPS],
  $settings[Settings\S_PAGE_NAME],
  getPageRenderer()
);

// becomes 

add_submenu_page(
  'edit.php?post_type=YOUR_CUSTOM_POST_TYPE', // the parent slug which your settings page will use
  $settings[Settings\S_PAGE_TITLE],
  $settings[Settings\S_MENU_TITLE],
  $settings[Settings\S_REQUIRE_CAPS],
  $settings[Settings\S_PAGE_NAME],
  getPageRenderer()
);
```

All applicable options for the parent slug can be found in [user contributed notes - add_submenu_page()](https://developer.wordpress.org/reference/functions/add_submenu_page/#user-contributed-notes).

## Version Updates

If you need to do work between version updates, add your work to the `migrateVersion()` function in `settings.php`. It is invoked when settings are saved and the previous version differs from the current version. The default function is a no-op.

## License

MIT
