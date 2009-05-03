<?php
/*
Plugin Name: Title to Tags
Plugin URI: http://holisticnetworking.net/plugins/2008/01/25/the-titles-to-tags-plugin/
Description: Creates tags for posts based on the post title on update or publish.
Version: 2.0
Author: Thomas J. Belknap
Author URI: http://holisticnetworking.net
*/

/*  Copyright 2006  Thomas J Belknap  (email : dragonfly@dragonflyeye.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
Change Log:
    * 1.0 ~ Initial public release
    * 1.1 ~ Corrected some meta data
    * 1.2 ~ Name collision with another plugin, FeedWordPress, corrected
    * 1.3 ~ Whoops! Didn't put the title in the meta data, how silly!
    * 1.4 ~ SVN commit to include tag
	* 2.0 ~ Revamped version based on experiments with these function in another plugin.
			~ using WP-style function notes
			~ designed to work with both WP and WPMU
			~ lowerNoPunc function now removes posessive 's from words
			~ addresses issue where tags are created even if they're not added to the post
*/

/**
 * Gather keywords from title and create post tags for them
 *
 * @uses wp_set_post_tags()
 *
 * @param int $post_id Post ID.
 */
function titleToTags($post_id) {
	// Fix for auto-save and revision IDs:
	if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)){
		return $post_id;
	} else {
		$post = get_post($post_id);
		if(wp_get_post_tags($post_id)) {
			return false;
		}
		$title = $post->post_title;
		$stopwords = get_option('hn_title_to_tags');
		// do we have stopwords in the db?  If not, use the default list:
		if ( !$stopwords ) {
			$file = dirname(__FILE__).'/hn_t2t/stopwords.txt';
			$defaults = file_get_contents($file);
			$stopwords = $defaults;
			update_option('hn_title_to_tags', $stopwords);
		}
		// get our array of stop words and words in the title.  Compare arrays and select tags:
		$verboten = explode(',', $stopwords);
		$title_werdz = explode(' ', $title);
		for($x = 0; $x < count($verboten); $x++) {
			$verboten[$x] = lowerNoPunc($verboten[$x]);
		}
		foreach ($title_werdz as $werd) {
			$werd = lowerNoPunc($werd); //trim(preg_replace('#[^\p{L}\p{N}]+#u', '', $werd));
			if(!in_array($werd, $verboten)) {
				$tags[] = $werd;
			}
		}
		// Finally, add the tags to the post
		wp_add_post_tags($post_id, $tags);
	}
}
	
	
	
/**
 * Convert words to all lower case, eliminate punctuation, remove posessives
 *
 * @param string $werd the word to be converted
 * @return string converted word
 */
function lowerNoPunc($werd) {
	if(stristr($werd, "'s")) {
		$sploded = explode("'", $werd);
		$werd = $sploded[0];
	}
	$werd = strtolower(trim(preg_replace('#[^\p{L}\p{N}]+#u', '', $werd)));
	return $werd;
}
	
	
	
function titleToTags_control() {

	// Get our options and see if we're handling a form submission.
	$options = get_option('hn_title_to_tags');
	if ( !is_array($options) ){
		$stopwords = dirname(__FILE__).'/hn_t2t/stopwords.txt';
		$defaults = file_get_contents($stopwords);
		$options = array('hnt2t_exceptions'=>$defaults);
	}
	if ( $_POST['hnt2t-submit'] ) {
		if ($_POST['hnt2t_reset'] == 1) {
			$stopwords = dirname(__FILE__).'/hn_t2t/stopwords.txt';
			$options['hnt2t_exceptions'] = file_get_contents($stopwords);	
		}
		else { $options['hnt2t_exceptions'] = strip_tags(stripslashes($_POST['hnt2t_exceptions'])); }
		update_option('hn_title_to_tags', $options);
		?><div id='message' class='updated fade'><p><strong>Title to Tags exception list updated!</strong></p></div><?php
	}

	// Be sure you format your options to be valid HTML attributes.
	$exceptions = htmlspecialchars($options['hnt2t_exceptions'], ENT_QUOTES);
	
	// Begin form output
?>
<div class="wrap">
<h2>Title to Tags</h2>
	<form id="hn_title_to_tags" name="hn_title_to_tags" method="post" action="options-general.php?page=hn_title_to_tags.php">
	<h3>Excluded Words</h3>
	<p>These words will be ignored by Title to Tags</p>
	<textarea rows="6" cols="100" name="hnt2t_exceptions" id="hnt2t_exceptions"><?php echo($exceptions); ?></textarea>
	<h3>Reset Excluded Words</h3>
	<p><strong>Warning!!</strong>  Setting this option will restore your ignore list to it's original state and delete any entries you may have made.  Please only use this option when you are <strong>sure</strong> you want to reset!</p>
	<input type="checkbox" name="hnt2t_reset" id="hnt2t_reset" value="1"><label for="hnt2t_reset">Reset all ignore words to defaults</label><br /><br />
	<input type="submit" name="submit" id="submit" value="submit" />
	<input type="hidden" id="hnt2t-submit" name="hnt2t-submit" value="1" />
	</form>
</div>
<?php

}

function hn_t2t_add_menu() {
	add_options_page('Title to Tags', 'Title 2 Tags', 8, basename(__FILE__),'titleToTags_control');
}
	
add_action('save_post', 'titleToTags', 2);
add_action('admin_menu', 'hn_t2t_add_menu');


?>