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

## License

MIT
