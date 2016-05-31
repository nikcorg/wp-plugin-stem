# Boilerplate stuff for WordPress plugin development

I've had the dubious pleasure of creating a few WordPress plugins. Setting them up was never something to look forward to, so I gathered recurring features into a single place. A lot of this code is code I believe should be a shared dependency, or part of WordPress core, but this is probably the second best option.

Very little here is completely original code, but has rather been compiled and paraphrased from many sources here and there. I'd give due credit, but having waded through what seems like gigabytes of WordPress related blog posts to find all that I need, I'm truly not capable to do so: Credits go to the entire WordPress community.

## Contents

The boilerplate contains what's required for setting up a plugin settings page and running admin and public actions, i.e. just the basic harness to build the rest upon.

## Howto

1. Check and modify the `namespace` in `/index.php` and `/lib/*.php`
2. Add settings to `/lib/settings.php#getFields()` as required
3. Add settings sections to `/lib/settings.php#getSections()` as required
4. Hook on to public actions in `/lib/actions.php`

## Settings

Field and section definitions are added to `lib/settings.php`. Add sections to the return value of `getSections()` and fields to the return value of `getFields()`.

## Sections

The function `getSections` in `lib/settings.php` should return an associative array of section definitions, where the array keys is the section identifier. It is recommended to define the section identifiers as constants, to avoid bugs introduced by typos because of "magic strings".

### Section properties

- name
- description (optional)

### Example

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

## Fields

The function `getFields` in `lib/settings.php` should return an array of field definitions.

### Field properties

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

### Field types

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

### Example

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

### Sanitising and validation

Separate fields for validation and sanitising is to enable using general purpose input sanitisers, e.g. string/number/url/date etc, while still retaining the option to have strict field specific validation.

The sanitize callback receives as arguments only the field's value, while the validator also receives the fields attributes.

Invoking order is sanitize -> validate.

## License

MIT
