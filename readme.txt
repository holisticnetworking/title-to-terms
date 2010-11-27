=== Titles to Tags ===
Contributors: dragonflyeye
Tags: tags, titles, automation
Requires at least: 2.7
Tested up to: 3.1
Stable tag: 3.0

This plugin automatically converts keywords in a post title to tags, while ignoring a user-editable list of words.

== Description ==

This plugin automatically converts keywords in a post title to tags upon saving.  It includes a user-editable list of words you want the plugin to ignore, which by default includes the more obviously-useless words like "I" or "wasn't."  You can also reset the list back to defaults by checking a box in the config page.

*   Converts keywords in post titles to tags
*   Includes user-editable list of words to be ignored
*   Ignore list can be reset to default at any time
*   Converts on save, not publish.
*   Does not convert if there are already tags assigned.

WPMU adminstrators take note: this plugin is especially helpful if you're building a community-based site where tagging is important and your bloggers are not always diligent about tagging.

== Installation ==

Very simple, very easy:

1.  Extract the zip file and upload both the hn_title_to_tags.php file and the hn_t2t directory to your web server
   a.  In WordPress, upload to /plugins
   b.  In WPMU, upload to the /mu-plugins for automatic site-wide activation.  Obviously, skip step 2
2.  Activate plugin by going to the Plugins section of your Control Panel
3.  Configure the plugin by going to Options > Title 2 Tags in your Control Panel

== Frequently Asked Questions ==

= Does this plugin convert all words in the title? =

No.  There is a user-configurable list of stop words, pre-populated with a host of common words, which can be used to fine-tune the resulting tags.  Words like "and" or "myself" are excluded by default, but there might be other words which are germane to your blog which don't make for good tags.  You might, for example, want to add the name of your blog to the list, to avoid an over loaded tag with no real meaning.

= Will this plugin overwrite my existing tags? =

No.  The plugin checks for the existence of tags, and if there are none, writes them based on the title.

= When are the tags added?  At publish or save? =

Tags are added to the post the first time it is saved, but not when the post is autosaved.  In many cases, the first save may well be publish.  In this case, the tags are added at that time.

= Is there a way to reset the list of stop words back to the original? =

Yes.  In the Options page for the plugin, there is a check box for "Reset all ignore words to defaults."  Clicking that checkbox and hitting "submit" will restore the original list of ignored words.  **BE CAREFUL!**  There is no way to undo this process.

= Will this plugin put tags on my old posts? =

This plugin will put tags on any post that is opened in the Write screen and then saved, assuming there aren't already tags on the post.  However, there is no system in place to do this as a batch file, since in some instances, this would probably be more strain on the server than it's really worth.  If you really need to get this done, I'm sure there's a way to create another script that opens and saves each un-tagged post, but that's not something I'm looking into.

== Screenshots ==

1. The Plugins page showing the Title to Tags plugin pre-activation.
2. The Title to Tags Options page.

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

== Acknowledgements ==

* This plugin would not have been nearly as cool without the stop words list.  That list was originally from Adam Whippy's awesome plugin, [Clean Trunks] (http://www.york-newyork.com/seo-plugin-wordpress-urls/ "Automated SEO Friendly URL plugin for Wordpress")
