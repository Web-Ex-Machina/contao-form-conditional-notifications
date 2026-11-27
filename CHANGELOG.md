# Extension "Contao Form Conditional Notifications" for Contao Open Source CMS

## 2.0.0 - Unreleased
- IMPORTANT - Drop Contao 4.13 compatibility, Contao 5.7 and PHP 8.4 minimum
- IMPORTANT - Requires Notification Center 2.7 and DCA Wizard 5
- UPDATED - Modernize bundle structure (`config/`, `contao/` and `public/` at the bundle root)
- UPDATED - Hooks and DCA callbacks are registered with `#[AsHook]` / `#[AsCallback]` attributes
- UPDATED - Use Contao core services for countries and locales, `webexmachina/contao-utils` is no longer required
- UPDATED - The notification language is now passed to Notification Center 2
- UPDATED - Log through the Contao logger instead of `System::log()`
- UPDATED - Add `html_data`, `html_data_filled` and `file_*` tokens, same as Notification Center 2
- UPDATED - Update the icon
- FIXED - A condition on the first option group of a field never matched
- FIXED - Conditions on checkbox fields with several values selected never matched
- FIXED - Fatal errors when editing a condition on a select, radio or checkbox field
- ADDED - ECS & Rector configuration

## 1.0.0 - 2025-01-24
- Compatibility update with Contao 4.13

## 0.1.0 - 2018-09-10
- Init git repository
- Add the main features of the extension
