<?php
/*
Plugin Name: Title to Tags
Plugin URI: http://holisticnetworking.net/plugins/2008/01/25/the-titles-to-tags-plugin/
Description: Creates tags for posts based on the post title on update or publish.
Version: 3.0
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

// Get out there and rock and roll the bones:
$tt	= new titleToTags();

class titleToTags {

	/*
	// convert:				Does the business of converting titles to tags.
	// @var int $post_id:	The ID of the post whose title is in need of conversion.
	*/
	public function convert($post_id) {
		// Fix for auto-save and revision IDs:
		if(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) :
			return $post_id;
		else :
			// Get the post:
			$post = get_post($post_id);
			if($title = $post->post_title) :
				// This is a total cluge, but it works. There must be a better post status to use, but haven't found it yet.
				if($title != 'Auto Draft') :
					// We only commit new post tags if there are no previous post tags:
					if(!wp_get_post_tags($post_id)) :
						// Setup our tag data:
						$title_to_tags	= array();
						$stopwords		= $this->getStopWords();
						$title_werdz	= explode(' ', $title);
						foreach ($title_werdz as $werd) :
							$werd = $this->lowerNoPunc($werd);
							if(!in_array($werd, $stopwords)) :
								$title_to_tags[] = $werd;
							endif;
						endforeach;
						// Finally, add the tags to the post
						wp_add_post_tags($post_id, $title_to_tags);
					endif;
				endif;
			endif;
		endif;
	}
	
	
	public function control() {
		// Get our options and see if we're handling a form submission.
		$options = get_option('hn_title_to_tags');
		if ( !is_array($options) ) :
			$stopwords = dirname(__FILE__).'/hn_t2t/stopwords.txt';
			$defaults = file_get_contents($stopwords);
			$options = array('hnt2t_exceptions'=>$defaults);
		endif;
		if ( isset($_POST['hnt2t-submit']) ) :
			if (isset($_POST['hnt2t_reset'])) :
				$stopwords = dirname(__FILE__).'/hn_t2t/stopwords.txt';
				$options['hnt2t_exceptions'] = file_get_contents($stopwords);	
			else : 
				$options['hnt2t_exceptions'] = strip_tags(stripslashes($_POST['hnt2t_exceptions']));
			endif;
			update_option('hn_title_to_tags', $options);
			?><div id='message' class='updated fade'><p><strong>Title to Tags exception list updated!</strong></p></div><?php
		endif;

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
	
	public function addMenu() {
		add_options_page('Title to Tags', 'Title 2 Tags', 'edit_posts', basename(__FILE__), array( &$this, 'control'));
	}
	
	function __construct() {
		add_action('save_post', array(&$this, 'convert'));
		add_action('admin_menu', array(&$this, 'addMenu'));
	}
	
	/*
	// lowerNoPunc:				Converts all words into lower-case words, sans punctuation or possessives.
	*/
	private function lowerNoPunc($werd) {
		if(stristr($werd, "'s")) :
			$sploded = explode("'", $werd);
			$werd = $sploded[0];
		endif;
		$werd = strtolower(trim(preg_replace('#[^\p{L}\p{N}]+#u', '', $werd)));
		return $werd;
	}
	
	
	/*
	// getStopWords:			Gets and sets the current array of stop words. By default, grabs the included text file and produces.
	//							an array from that, to be saved to the options table.
	*/
	private function getStopWords() {
		// Do we have stopwords in the db?
		if($stopwords = get_option('hn_title_to_tags')) :
			return $stopwords;
		// If not, use the default list:
		else :
			$file 			= dirname(__FILE__).'/stopwords.txt';
			$stopwords		= file_get_contents($file);
			$verboten		= explode(',', $stopwords);
			for($x = 0; $x < count($verboten); $x++) :
				$verboten[$x]	= $this->lowerNoPunc($verboten[$x]);
			endfor;
			update_option('hn_title_to_tags', $verboten);
			return $verboten;
		endif;
	}
}
?>
