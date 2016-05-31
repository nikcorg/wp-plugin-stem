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

- text
- textarea
- number
- email
- url
- date/datetime/time

The following types require an `options` property. If it is an associative array, the value returned from `getFieldValues()` will be the key and not the value.

- select
- radio
- checkbox

### Sanitising and validation

Separate fields for validation and sanitising is to enable using general purpose input sanitisers, e.g. string/number/url/date etc, while still retaining the option to have strict field specific validation.

The sanitize callback receives as arguments only the field's value, while the validator also receives the fields attributes.

Invoking order is sanitize -> validate.

## License

MIT
