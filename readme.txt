=== Titles to Tags ===
Contributors: dragonflyeye
Tags: tags, titles, automation
Requires at least: 3.0
Tested up to: 4.4
Stable tag: 3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Keep forgetting to add tags to your posts? Let Title to Tags convert keywords automatically!

== Description ==

NEW REPOSITORY ON [GITHUB!](https://github.com/holisticnetworking/title-to-tags) Please consider making a contribution to good code.

This plugin automatically converts keywords in a post title to tags upon saving.  It includes a user-editable list of words you want the plugin to ignore, which by default includes the more obviously-useless words like "I" or "wasn't."  You can also reset the list back to defaults by deleting the current list.

*   Converts keywords in post titles to tags
*   Includes user-editable list of words to be ignored
*   Ignore list can be reset to default at any time
*   Converts on save, not publish.
*   Does not convert if there are already tags assigned.

Multi-blog adminstrators take note: this plugin is especially helpful if you're building a community-based site where tagging is important and your bloggers are not always diligent about tagging.

WP banner photo credit: [Sarah Bresnahan on Flickr](http://www.flickr.com/photos/sjbresnahan/4087585005/sizes/o/in/photolist-7ecWBx-7egQk9-6JvPPE-6JrJbB-8pnP89-6vTRJU-Fv4Vf-bUUTad-6UrYUY-8H8P4N-f7MqAR-6kcugT-ceuhxY-4YCw2W-e1tJ5X-7h6Si1-9SKEiC-6rPyjP-f4y8VP-29Xa3H-6w1QVu-bE8ArQ-bT3mtc-bT3m7p-bE8ANW-5fFT4a-PCDgy-PCDg5-bTdrSp-4stkJX-4sxnZo-8H8Qru-f82DeA-4VaRUY-nw9vn-f7Mv9e-81XHfC-bX7VFg-53weBC-zzoQA-aiZtUG-8kTNpR-68awX8-5mR9EQ-e1JMNH-4V6Co8-6eqCTy-4VaRRY-9Fafo-6KP53J-9Faf6/
) (CC licensed)

== Installation ==

Very simple, very easy:

1.  Extract the zip file and upload the title_to_tags folder to your /wp-content/plugins directory.
2.  Activate plugin by going to the Plugins section of your Control Panel.
3.  Configure the plugin by going to Settings -> Writing in your Control Panel.

== Frequently Asked Questions ==

=======
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

= Does this plugin work with versions less than 3.1? =

The most recent release of this plugin was designed to handle 3.0 and above. It has not been tested below 3.0, however, there is a branched version in SVN that will work with versions greater than 2.7:
http://plugins.svn.wordpress.org/title-to-tags/branches/2.1/trunk/

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
* 3.3 New version now on GitHub.
	  ~Version also checked to be compatible with WP 4.4

== Acknowledgements ==

* This plugin would not have been nearly as cool without the stop words list.  That list was originally from Adam Whippy's awesome plugin, [Clean Trunks] (http://www.york-newyork.com/seo-plugin-wordpress-urls/ "Automated SEO Friendly URL plugin for Wordpress")
