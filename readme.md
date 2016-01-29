# WPML to MultilingualPress

Convert posts from an existing WPML multilingual site via XLIFF Export/Import for MultilingualPress

## Description
WPML stores each entry as a separate post and uses some custom tables to connect the translations. If you de-activate the plugin or have issues with functionality related to version updates, you get one site with multilingual confusion. This plugin migrate all data from WPML to [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) plugin, also the [Premium](http://multilingualpress.pro/) Version.

## Requirements

 * PHP >= 5.4.0
 * WP  >= 4.4.0

### Attention
This plugin is not permanently under maintenance, use and tests. You can use the plugin, but **make a backup safely**. Please give us feedback if you see problems.
The best way for feedback is an issue on the repository on [github.com/inpsyde/wpml2mlp](https://github.com/inpsyde/wpml2mlp).

This migration plugin:

* Converts the WPML translation records to default core posts or post types
* It creates the relationship about the languages inside the network of the Multisite with the [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) plugin
* Helps to export/import your data to your new WPMS site.
* Use XLIFF (XML Localisation Interchange File Format) format as Export file
* Restores the meta data back to posts and their meta data so that [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) will recognize all your translations.

[MultilingualPress](https://wordpress.org/plugins/multilingual-press/) connects multiple sites as language alternatives in a multisite. Use a customizable widget or Nav Menu to link to all sites.

We cannot guarantee free ad hoc support. Please be patient, we are a small team.
You can follow our progress and development notices on our [developer blog](http://make.marketpress.com/multilingualpress/).

## Installation

### Requirements
 * WordPress Multisite 3.4+
 * PHP 5.2.4, newer PHP versions will work faster.
 * [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) plugin, also the [Premium](http://multilingualpress.pro/) Version

Use the installer via back-end of your install or ...

 1. Unpack the download-package.
 2. Upload the files to the `/wp-content/plugins/` directory.
 3. Single Site: Activate the plugin through the **Plugins** menu in WordPress and click **Activate**
 4. Multisite: Activate the plugin through the **Network/Plugins** menu in WordPress and click **Network Activate**.
 5. Go to **Settings** on Multisite or **Tools** on single site, **WPML2MLP**, then start the export of the current WPML stuff.

## Action & Filter Reference
### Actions
* `w2m_import_term_error` in `W2M\Import\Service\WpTermImporter::import_term()`
* `w2m_import_missing_term_ancestor` in `W2M\Import\Service\WpTermImporter::import_term()`
* `w2m_term_imported` in `W2M\Import\Service\WpTermImporter::import_term()`

* `w2m_import_post_error` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_import_missing_post_ancestor` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_import_missing_post_local_user_id` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_import_set_post_terms_error` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_import_update_post_meta_error` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_post_imported` in `W2M\Import\Service\WpPostImporter::import_post()`

* `w2m_import_attachment_missing_origin_attachment_url` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_import_attachment_mkdir_error` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_import_request_attachment_error` in `W2M\Import\Service\WpPostImporter::import_post()`
* `w2m_attachment_imported`in `W2M\Import\Service\WpPostImporter::import_post()`

* `w2m_import_user_error` in `W2M\Import\Service\WpUserImporter::import_user()`
* `w2m_user_imported` in `W2M\Import\Service\WpUserImporter::import_user()`

* `w2m_import_comment_error` in `W2M\Import\Service\WpCommentImporter::import_comment()`
* `w2m_import_missing_comment_ancestor` in `W2M\Import\Service\WpCommentImporter::import_comment()`
* `w2m_import_update_comment_meta_error` in `W2M\Import\Service\WpCommentImporter::import_comment()`
* `w2m_comment_imported` in `W2M\Import\Service\WpCommentImporter::import_comment()`

* `w2m_import_parse_term_error` in `W2M\Import\Service\WpTermParser::propagate_error()`
* `w2m_import_parse_post_error` in `W2M\Import\Service\WpPostParser::propagate_error()`
* `w2m_import_parse_user_error` in `W2M\Import\Service\WpUserParser::propagate_error()`
* `w2m_import_parse_comment_error` in `W2M\Import\Service\WpCommentParser::propagate_error()`

* (Deprecated) `w2m_import_set_user_id` in `W2M\Import\Type\WpImportUser::id()`
* (Deprecated) `w2m_import_set_post_id` in `W2M\Import\Type\WpImportPost::id()`
* (Deprecated) `w2m_import_set_term_id` in `W2M\Import\Type\WpImportTerm::id()`
* (Deprecated) `w2m_import_set_comment_id` in `W2M\Import\Type\WpImportComment::id()`

* `w2m_import_posts_done` in `W2M\Import\Service\PostProcessor::process_elements()`
* `w2m_import_users_done` in `W2M\Import\Service\UserProcessor::process_elements()`
* `w2m_import_terms_done` in `W2M\Import\Service\TermProcessor::process_elements()`
* `w2m_import_comments_done` in `W2M\Import\Service\CommentProcessor::process_elements()`

* `w2m_import_xml_parser_error` in `W2M\Import\Iterator\SimpleXmlItemWrapper::propagate_error()`

## Other Notes

### Made by [Inpsyde](http://inpsyde.com) &middot; We love WordPress
Have a look at the premium plugins in our [market](http://marketpress.com).

### Bugs, technical hints or contribute
Please give me feedback, contribute and file technical bugs on this 
[GitHub Repo](https://github.com/inpsyde/wpml2mlp/issues), use Issues.

### License
Good news, this plugin is free for everyone! Since it's released under the GPL, 
you can use it free of charge on your personal or commercial blog.

### Contact & Feedback
Please let us know if you like the plugin or you hate it or whatever ... 
Please fork it, add an issue for ideas and bugs.
