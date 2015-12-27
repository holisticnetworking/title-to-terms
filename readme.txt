=== Title to Terms Ultimate ===
Contributors: dragonflyeye
Tags: automation, automate, automatic, taxonomy, category, categories, tag, tags, admin, analytics, posts, pages, custom post type, cpt
Requires at least: 3.0
Tested up to: 4.4
Stable tag: 4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically add tags, categories and more to your posts, pages and custom post types.

== Description ==

NEW REPOSITORY ON [GITHUB!](https://github.com/holisticnetworking/title-to-tags) Please consider making a contribution to good code.

The ultimate auto-tagging plugin for WordPress.

Title to Terms Ultimate is for every WordPress admin that needs tags, categories and other taxonomies filled out automatically. For each post type in your WordPress installation, T2TU will allow you to assign a taxonomy to be automatically updated with keywords pulled from the title of the post.

Every time a post gets saved or updated, T2TU analyzes the title of the post for usable keywords. A list of user-configurable "Stop Words" is checked by the parser, so that words like "I" or "going" don't get made into ultimately useless tags.

T2TU can be configured to either add new tags to a post or append additional tags.

*   Converts keywords in post titles to tags
*   Includes user-editable list of words to be ignored
*   Ignore list can be reset to default at any time
*   Converts on save, not publish.
*   Does not convert if there are already tags assigned.

== Download and Installation ==
* Download the plugin from the WordPress repository, [here](https://wordpress.org/plugins/title-to-tags/ "Titles to Tags").
* Unzip and upload the file to your /wp-content/plugins directory.
* In the Dashboard, go to Plugins->All Plugins and activate.
* Configure T2TU from the Settings->Writing screen

== Frequently Asked Questions ==

= How Can I Contribute? =
The plugin is now being maintained on GitHub, so please do contribute all your questions, comments, suggestions, fixes and improvements with me [here](https://github.com/holisticnetworking/title-to-tags).

= Does this plugin convert all words in the title? =

No.  There is a user-configurable list of stop words, pre-populated with a host of common words, which can be used to fine-tune the resulting tags.

= Will this plugin overwrite my existing tags? =

No.  The plugin checks for the existence of tags, and if there are none, writes them based on the title.

= When are the tags added?  At publish or save? =

Tags are added to the post when it is saved, whether as a draft, update or by publishing, but not when the post is autosaved.

= Is there a way to reset the list of ignored words back to the original? =

Yes. Simply delete the current list of stop words, and Title to Tags will replace the list with its default stop words collection (stopwords.txt)

= Will this plugin put tags on my old posts? =

Maybe. It will not handle the process automatically, but if you open any untagged post and save it, Titles to Tags will work.

== Screenshots ==

1. The Plugins page showing the Title to Tags plugin pre-activation.
2. The Title to Tags settings on the Writing page.

== Version History ==

* 1.0 ~ Initial public release
* 1.1 ~ Corrected some meta data
* 1.2 ~ Name collision with another plugin, FeedWordPress, corrected
* 1.3 ~ Whoops!  Didn't put the title in the meta data, how silly!
* 1.4 ~ SVN commit to include tag
* 2.0 ~ Revamped version based on experiments with these function in another plugin.
		~ using WP-style function notes
		~ designed to work with both WP and WPMU
		~ lowerNoPunc function now removes posessive 's from words
		~ addresses issue where tags are created even if they're not added to the post
* 2.1 ~ Two bug fixes:
		~ Use of the deprecated number-based roles in the add_options_menu() call has been fixed.
		~ Added isset() check to form submission check. Whoops! Error checking not turned on.
		~ NOTE: I am aware of the issue with this plugin not working in 3.0 and the next release will correct this issue. This will probably make the plugin inoperable for <3.0. Please upgrade your system to continue using this plugin.
* 3.0 ~ Converted to WordPress 3.0-compatible code. Not sure how this will affect the 2.x users out there, but will make a branch in SVN just in case.
* 3.0.1 ~ Noticed some improvements to be made to the efficiency of the code. getStopWords is now the only function used to get the stop words anywhere they appear (formerly using get_option directly).
* 3.2 ~ Rearranging a lot of the code, user-defined stop words were being ignored.
* 3.3 ~ New version now on GitHub.
		~ Version also checked to be compatible with WP 4.4
* 4.0 ~ Changed name to Titles to Terms Ultimate, reflecting newly updated code.
		~ T2TU now allows one taxonomy per post type to be auto-populated by title-generated terms.
		~ User-selectable taxonomy for each post type registered to WordPress, and each taxonomy registered to that post type.

== Acknowledgements ==

* This plugin would not have been nearly as cool without the stop words list.  That list was originally from Adam Whippy's awesome plugin, [Clean Trunks] (http://www.york-newyork.com/seo-plugin-wordpress-urls/ "Automated SEO Friendly URL plugin for Wordpress")
* The WP Plugin Repository icon and banner were both made with the kind contribution of: [Muharrem Fevzi Çelik @ The Noun Project](https://thenounproject.com/search/?q=taxonomy&i=165760) (CC licensed)