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

- name
- description (optional)

#### Example

```php
const SECTION_DEFAULT = "default";

function getSections()
{
    return array(
        SECTION_DEFAULT => array(
            "name" => "Global settings",
            "descriptions" => "These settings apply when more specific settings don't exist"
        )
    );
}
```

### Settings Fields

The function `getFields` in `lib/settings.php` should return an array of field definitions.

#### Field Properties

For a field to be valid, it must have a title, name, and section.

- section
- name
- title

Other properties are:

- type
- description
- placeholder
- options
- default
- validate (callback)
- sanitize (callback)

#### Field Types

- text (`FIELD_TEXT`)
- textarea (`FIELD_TEXT_MULTILINE`)
- number (`FIELD_NUMBER`)
- email (`FIELD_EMAIL`)
- url (`FIELD_URL`)
- date/datetime/time (`FIELD_DATE`, `FIELD_DATETIME`, `FIELD_TIME`)

The following types require an `options` property. If it is an associative array, the value returned from `getFieldValues()` will be the key and not the value.

- select (`FIELD_SELECT`)
- radio (`FIELD_RADIO`)
- checkbox (`FIELD_CHECKBOX`)

You should use the constants defined in `settings.php` to avoid typos.

#### Example

```php
function getFields()
{
    return array(
        array(
            "section" => SECTION_DEFAULT,
            "type" => FIELD_TEXT_MULTILINE,
            "name" => "post-footer",
            "title" => "Post footer text",
            "description" => "A short text displayed after each post"
        )
    );
}
```

## Settings Fields Sanitising And Validation

Each field has separate properties for validation and sanitising to enable general purpose input sanitisers, e.g. string/number/url/date etc, while still retaining the option to have strict field specific validation.

The sanitize callback receives as arguments only the field's value, while the validator also receives the fields attributes.

Invoking order is sanitize -> validate.

## Version Updates

If you need to do work between version updates, add your work to the `migrateVersion()` function in `settings.php`. It is invoked when settings are saved and the previous version differs from the current version. The default function is a no-op.

## License

MIT
