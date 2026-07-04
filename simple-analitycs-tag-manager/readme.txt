=== Simple Google Analytics Tag Manager ===
Contributors: fuentes7
Tags: google analytics, ga4, google tag manager, gtm, consent mode
Requires at least: 5.2
Tested up to: 7.0
Requires PHP: 7.0
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds Google Analytics GA4 and Google Tag Manager snippets with simple privacy-friendly controls.

== Description ==

Simple Google Analytics Tag Manager lets you add Google Analytics GA4 and Google Tag Manager to a WordPress site without editing theme files.

The plugin supports the current Google tag setup for IDs such as `G-XXXXXXXXXX`, `GT-XXXXXXXXX`, or `AW-XXXXXXXXX`, plus Google Tag Manager containers such as `GTM-XXXXXXX`.

== Features ==

* Add or remove a Google Analytics GA4 ID.
* Add or remove a Google Tag Manager container ID.
* Automatically print GA4 when a valid GA4 ID is saved.
* Automatically print Google Tag Manager when a valid container ID is saved.
* Disable the automatic GA4 `page_view` event when needed.
* Exclude all logged-in users from tracking.
* Exclude specific WordPress roles from tracking.
* Print Google Consent Mode v2 default values before Google tags load.
* Keep existing Google Tag Manager `noscript` support through the `wp_body_open` hook.

== Privacy ==

This plugin does not collect, store, or send personal data by itself.

When Google Analytics GA4 or Google Tag Manager is enabled, the plugin prints Google scripts on the public site. Those scripts may collect data according to the configuration of your Google account, your Google tags, and your consent implementation.

Consent Mode v2 settings in this plugin define default consent states only. A cookie banner or Consent Management Platform should update consent after the visitor makes a choice.

== Installation ==

From the WordPress admin:

1. Go to **Plugins > Add New**.
2. Search for **Simple Google Analytics Tag Manager**.
3. Click **Install Now**.
4. Click **Activate**.
5. Go to **Settings > Simple GA4 / GTM**.
6. Add a GA4 Measurement ID, a Google Tag Manager container ID, or both.
7. Save your settings.

Manual installation:

1. Upload the `simple-analitycs-tag-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Go to **Settings > Simple GA4 / GTM**.
4. Add a GA4 Measurement ID, a Google Tag Manager container ID, or both.
5. Save your settings.

== Frequently Asked Questions ==

= Does this support GA4? =

Yes. Add your GA4 Measurement ID, usually `G-XXXXXXXXXX`. Advanced Google tag IDs such as `GT-XXXXXXXXX` or `AW-XXXXXXXXX` are also accepted.

= Does this support Universal Analytics IDs that start with UA-? =

No. Universal Analytics is no longer supported in the new GA4 field. Use a GA4 Measurement ID instead.

= Should I enable both GA4 and GTM? =

Only if you intentionally need both snippets. If GA4 is already configured inside your Google Tag Manager container, use only the Google Tag Manager container ID to avoid duplicate page views.
To disable one snippet, clear its ID field and save the settings.

= Why is the folder still named simple-analitycs-tag-manager? =

That is the original WordPress.org plugin slug. The public plugin name can be corrected, but changing the plugin folder or main file name after release can break active installations because WordPress stores the activated plugin path.

== Screenshots ==

1. Settings screen for GA4, Google Tag Manager, exclusions, and Consent Mode v2.

== Changelog ==

= 2.0.0 =

* Added Google Analytics GA4 ID support.
* Added automatic GA4 and Google Tag Manager activation when valid IDs are saved.
* Added Consent Mode v2 defaults.
* Added logged-in user and role-based exclusions.
* Fixed PHP warnings when settings were empty.
* Fixed spelling and naming in the admin UI.
* Improved sanitization and escaping.

= 1.0.0 =

* Initial release.

== Upgrade Notice ==

= 2.0.0 =

This version replaces the old Universal Analytics-oriented field with a modern Google tag field. Review your settings after updating.
