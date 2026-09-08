---
name: complex-manager-development
description: Maintain the Complex Manager WordPress plugin, especially when changing backend or frontend UI labels, while preserving compatibility for existing sites.
---

# Complex Manager Development

Use this skill for changes to the Complex Manager plugin.

## Localization

- Use English as the source language for every new or edited backend UI-field label.
- Make every backend UI-field label translatable with the plugin's `complexmanager` text domain.
- When adding or editing a standardized frontend label, make it translatable in the same manner.
- Maintain translations only for the supported locales: `de_DE`, `en_US`, `fr_FR`, and `it_IT`.

## Release Version Consistency

When changing the Complex Manager plugin version, use the `Version` header and `VERSION` constant in `complex-manager.php` as the canonical value. Update the following release metadata in the same change before committing or publishing:

- `complex-manager.php`: the `Version` header and `VERSION` constant
- `README.md`: `Tested up to` and the matching release notes when maintained
- `distribution/wp.casasoft.com/complex-manager/update.php`: `$obj->version`, `$obj->new_version`, `$obj->tested`, and `$obj->last_updated`

Set `$obj->last_updated` to the change date as a `YYYY-MM-DD` string. Verify the latest stable WordPress release from the official WordPress download page, then use that exact version for both `README.md`'s `Tested up to` field and `$obj->tested`; Complex Manager changes are tested against the current WordPress release. Do not publish if any synchronized version or compatibility declarations differ.

## Compatibility First

This plugin is installed on hundreds of existing websites. Preserve all observable existing behavior unless the user explicitly authorizes a breaking change.

- Treat public PHP functions, hooks, shortcodes, option keys, stored metadata, generated markup, and established field names as compatibility-sensitive.
- Before implementing a change, assess its effect on existing installations, saved data, integrations, themes, and translations.
- If a requested implementation could break functionality or compatibility cannot be confidently assured, explain the concrete risk and stop. Do not proceed until the user chooses or authorizes a compatible alternative.
- Prefer additive, backward-compatible implementations, including fallbacks and migrations that retain prior behavior.
