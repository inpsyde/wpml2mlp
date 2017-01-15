# WPML to MultilingualPress

Convert posts from an existing WPML multilingual site via WXR Export/Import for MultilingualPress

### Please note
This plugin is considered a **tool for developers and advanced users**. It is not a feature plugin for daily use. You can use the plugin, but **make a full backup** of your entire system and make sure you can role back easily! Please give us feedback if you see problems. The best way for feedback is an issue on the repository on [github.com/inpsyde/wpml2mlp](https://github.com/inpsyde/wpml2mlp/issues).

At the moment there's a **known issue** with the scalability which is addressed in the ongoing refactoring. If you're in doubt whether to use the plugin or not, please ask before.

**Please note**: This is **not WPML**! We are not the developer of WPML itself and thus we can't provide any kind of support for WPML.

### What and why?
If a WordPress site is translated with the WPML Plugin, each translated entry is stored as a separate posts and some custom tables are used to connect the translations. Now if you de-activate the WPML Plugin or have issues with functionality related to version updates, you get a WordPress site with multilingual confusion. This plugin migrates all data from WPML and creates a new WordPress network site out of each single language with use of [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) plugin and also with the possibilty of a [Premium Support](http://multilingualpress.org/).

This migration plugin:

* Converts the WPML translation records to default core posts or post types
* It creates the relationship about the languages inside the network of the Multisite with the [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) plugin
* Helps to export/import your data to your new WPMS site.
* Use WXR (WordPress eXtended Rss) format as Export file
* Restores the meta data back to posts and their meta data so that [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) will recognize all your translations.

[MultilingualPress](https://wordpress.org/plugins/multilingual-press/) connects multiple sites as language alternatives in a multisite. Use a customizable widget or Nav Menu to link to all sites.

We cannot guarantee free ad hoc support. Please be patient, we are a small team.

## Installation

### Requirements
 * WordPress Multisite 4.4+
 * PHP 5.4*, newer PHP versions will work faster.
 * [MultilingualPress](https://wordpress.org/plugins/multilingual-press/) plugin

Use the installer via back-end of your install or ...

 1. Unpack the download-package.
 2. Upload the files to the `/wp-content/plugins/` directory.
 3. Single Site: Activate the plugin through the **Plugins** menu in WordPress and click **Activate**
 4. Multisite: Activate the plugin through the **Network/Plugins** menu in WordPress and click **Network Activate**.
 5. Go to **Settings** on Multisite or **Tools** on single site, **WPML2MLP**, then start the export of the current WPML stuff.

### How to use
See the screencast at [vimeo](http://vimeo.com/199317177) and read the [Wiki](https://github.com/inpsyde/wpml2mlp/wiki).

## Action & Filter Reference
### Actions
* `w2m_import_term_error` in `W2M\Import\Service\Importer\WpTermImporter::import_term()`
* `w2m_import_missing_term_ancestor` in `W2M\Import\Service\Importer\WpTermImporter::import_term()`
* `w2m_term_imported` in `W2M\Import\Service\Importer\WpTermImporter::import_term()`

* `w2m_import_post_error` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_import_missing_post_ancestor` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_import_missing_post_local_user_id` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_import_set_post_terms_error` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_import_update_post_meta_error` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_post_imported` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`

* `w2m_import_attachment_missing_origin_attachment_url` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_import_attachment_mkdir_error` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_import_request_attachment_error` in `W2M\Import\Service\Importer\WpPostImporter::import_post()`
* `w2m_attachment_imported`in `W2M\Import\Service\Importer\WpPostImporter::import_post()`

* `w2m_import_user_error` in `W2M\Import\Service\Importer\WpUserImporter::import_user()`
* `w2m_user_imported` in `W2M\Import\Service\Importer\WpUserImporter::import_user()`

* `w2m_import_comment_error` in `W2M\Import\Service\Importer\WpCommentImporter::import_comment()`
* `w2m_import_missing_comment_ancestor` in `W2M\Import\Service\Importer\WpCommentImporter::import_comment()`
* `w2m_import_update_comment_meta_error` in `W2M\Import\Service\Importer\WpCommentImporter::import_comment()`
* `w2m_comment_imported` in `W2M\Import\Service\Importer\WpCommentImporter::import_comment()`

* `w2m_import_parse_term_error` in `W2M\Import\Service\Parser\WpTermParser::propagate_error()`
* `w2m_import_parse_post_error` in `W2M\Import\Service\Parser\WpPostParser::propagate_error()`
* `w2m_import_parse_user_error` in `W2M\Import\Service\Parser\WpUserParser::propagate_error()`
* `w2m_import_parse_comment_error` in `W2M\Import\Service\Parser\WpCommentParser::propagate_error()`

* (Deprecated) `w2m_import_set_user_id` in `W2M\Import\Type\WpImportUser::id()`
* (Deprecated) `w2m_import_set_post_id` in `W2M\Import\Type\WpImportPost::id()`
* (Deprecated) `w2m_import_set_term_id` in `W2M\Import\Type\WpImportTerm::id()`
* (Deprecated) `w2m_import_set_comment_id` in `W2M\Import\Type\WpImportComment::id()`

* `w2m_import_posts_start` in `W2M\Import\Service\PostProcessor::process_elements()`
* `w2m_import_posts_done` in `W2M\Import\Service\PostProcessor::process_elements()`
* `w2m_import_users_start` in `W2M\Import\Service\UserProcessor::process_elements()`
* `w2m_import_users_done` in `W2M\Import\Service\UserProcessor::process_elements()`
* `w2m_import_terms_start` in `W2M\Import\Service\TermProcessor::process_elements()`
* `w2m_import_terms_done` in `W2M\Import\Service\TermProcessor::process_elements()`
* `w2m_import_comments_start` in `W2M\Import\Service\CommentProcessor::process_elements()`
* `w2m_import_comments_done` in `W2M\Import\Service\CommentProcessor::process_elements()`

* `w2m_import_xml_parser_error` in `W2M\Import\Iterator\SimpleXmlItemWrapper::propagate_error()`

* `w2m_import_process_done` in `W2M\Import\Module\ElementImporter::process_elements()`

* `w2m_import_post_ancestor_resolver_error` in `W2M\Import\Service\PostAncestorResolver::propagate_error()`
* `w2m_import_post_ancestor_resolved` in `W2M\Import\Service\PostAncestorResolver::resolve_relation()`
* `w2m_import_term_ancestor_resolver_error` in `W2M\Import\Service\TermAncestorResolver::propagate_error()`
* `w2m_import_term_ancestor_resolved` in `W2M\Import\Service\TermAncestorResolver::resolve_relation()`
* `w2m_import_post_ancestor_resolving_start` in `W2M\Import\Module\ResolvingPendingRelations::resolving_posts()`
* `w2m_import_term_ancestor_resolving_start` in `W2M\Import\Module\ResolvingPendingRelations::resolving_terms()`


## Other Notes


```
  ___                           _      
 |_ _|_ __  _ __  ___ _   _  __| | ___ 
  | || '_ \| '_ \/ __| | | |/ _` |/ _ \
  | || | | | |_) \__ \ |_| | (_| |  __/
 |___|_| |_| .__/|___/\__, |\__,_|\___|
           |_|        |___/            
```

The team at [Inpsyde](https://inpsyde.com) is engineering the Web since 2006.

### Bugs, technical hints or contribute
Please give me feedback, contribute and file technical bugs on this 
[GitHub Repo](https://github.com/inpsyde/wpml2mlp/issues), use Issues.

### License
Good news, this plugin is free for everyone! Since it's released under the GPL, 
you can use it free of charge on your personal or commercial blog.

### Contact & Feedback
Please let us know if you like the plugin or you hate it or whatever ... 
Please fork it, add an issue for ideas and bugs.
