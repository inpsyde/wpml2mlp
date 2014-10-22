=== WPML to MultilingualPress ===
Contributors: inpsyde, Bueltge, nullbyte
Tags: WPML, convert, export, import, migration, l10n, i18n, bilingual, international, internationalization, lang, language, localization,  multilanguage, multi language, multilingual, multi lingual, multisite, switcher, translation, website translation, wordpress translation,  network, categories, taxonomy, xliff
Requires at least: 3.8
Tested up to: 4.1-alpha
Stable tag: trunk

Convert posts from an existing WPML multilingual site via XLIFF Import/Export for MultilingualPress

== Description ==
WPML stores each entry as a separate post and uses some custom tables to connect the translations. If you de-activate the plugin or have issues with functionality related to version updates, you get one site with multilingual confusion. 

This migration plugin:

* Converts the WPML translation records to default core posts or post types
* It creates the relationship about the languages inside the network of the Multisite
* Helps to export/import your data to your new WPMS site.
* Use XLIFF (XML Localisation Interchange File Format) format as Export file
* Restores the meta data back to posts and their meta data so that (MultilingualPress)[https://wordpress.org/plugins/multilingual-press/] will recognize all your translations.

MultilingualPress connects multiple sites as language alternatives in a multisite. Use a customizable widget to link to all sites.

We cannot guarantee free ad hoc support. Please be patient, we are a small team.
You can follow our progress and development notices on our [developer blog](http://make.marketpress.com/multilingualpress/).


== Installation ==

= Requirements =
* WordPress Multisite 3.4+
* PHP 5.2.4, newer PHP versions will work faster.

Use the installer via back-end of your install or ...

1. Unpack the download-package.
2. Upload the files to the `/wp-content/plugins/` directory.
3. Activate the plugin through the **Network/Plugins** menu in WordPress and click **Network Activate**.
4. Go to **Settings**, **WPML2MLP**, then start the export of the current WPML stuff.

== Screenshots ==

1. Language Manager

== Changelog ==

= 1.0.0 =
 * First public release