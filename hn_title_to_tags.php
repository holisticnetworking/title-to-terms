<?php
/*
Plugin Name: Title to Tags
Plugin URI: http://holisticnetworking.net/plugins/2008/01/25/the-titles-to-tags-plugin/
Description: Creates tags for posts based on the post title on update or publish.
Version: 3.3
Author: Thomas J. Belknap
Author URI: http://holisticnetworking.net
*/

/*  Copyright 2013  Thomas J Belknap  (email : tbelknap@holisticnetworking.net)

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
new titleToTags;

class titleToTags {	
	
	// Convert titles to tags on save:
	function convert( $post_id ) {
		$stopwords	= $this->getStopWords();
		$append	 	= get_option( 't2t_append' );
		
		$post		= get_post( wp_is_post_revision( $post_id ) );
		// No title? No point in going any further:
		if( isset( $post->post_title ) ) :
			// Setup our tag data:
			$title_to_tags	= array();
			$title_werdz	= explode( ' ', $post->post_title );
			foreach( $title_werdz as $werd ) :
				$werd = $this->lowerNoPunc( $werd );
				if(!in_array( $werd, $stopwords ) && !in_array( $werd, $this->wp_stop ) ) :
					$title_to_tags[] = $werd;
				endif;
			endforeach;
			
			// Append or complete. Do not replace:
			if( $append ) :
				wp_set_post_tags( $post_id, $title_to_tags, true );
			elseif( !wp_get_post_tags( $post_id ) ) :
				wp_set_post_tags( $post_id, $title_to_tags, true );
			endif;
		endif;
	}
	
	// Display options page:
	function addMenu() {
		add_settings_field(
			'stop_words',
			"Title to Tags: ignored words",
			array( &$this, 'stop_words' ),
			'writing'
		);
		add_settings_field(
			't2t_append',
			"Title to Tags: append tags",
			array( &$this, 'append' ),
			'writing'
		);
		add_settings_field(
			't2t_taxonomies',
			"Title to Tags: Taxonomies and Post Types",
			array( &$this, 'taxonomies' ),
			'writing'
		);
		register_setting( 'writing', 'stop_words' );
		register_setting( 'writing', 't2t_append' );
		register_setting( 'writing', 't2t_taxonomies' );
	}
	
	function stop_words() {
		$values	= get_option( 'stop_words' );
		if( empty( $values ) ) :
			$values	= implode( ',', $this->getStopWords() );
		endif;
		echo '
		<p>These words will be ignored by Title to Tags (punctuation removed). <em>To reset, simply delete all values here and the default list will be restored.</em></p>
		<textarea rows="6" cols="100" name="stop_words" id="stop_words">' . $values . '</textarea>
		';
	}
	
	function append() {
		$value		= get_option( 't2t_append' );
		$checked	= ( $value ) ? 'checked="checked"' : '';
		echo '<p>Choose whether to add tags to untagged content, or to append new Title 2 Tags, even if there are tags already present.</p>
		<input type="checkbox" name="t2t_append" id="t2t_append" ' . $checked . ' /> append Title to Tags to preexisting tags.';
	}
	
	function taxonomies() {
		$types		= get_post_types( null, 'objects' );
		$settings	= get_option( 't2t_taxonomies' );
		// print_r( $settings );
		echo '<style type="text/css">
			fieldset.t2t_cpt {
				margin: 20px;
				border: 2px solid #aaa;
				padding: 8px;
			}
		</style>';
		foreach( $types as $type ) :
			if( !in_array( $type->name, array( 'revision', 'nav_menu_item' ) ) ) :
				echo '<fieldset class="t2t_cpt"><legend>' . $type->labels->name . '</legend>';
				$taxes	= get_object_taxonomies( $type->name, 'objects' );
				if( !empty( $taxes ) ) :
					foreach( $taxes as $tax ) :
						if( !in_array( $tax->name, array( 'post_format' ) ) ) :	
							$checked	= empty( $settings[$type->name][$tax->name] ) ? '' : 'checked="checked"';
							echo sprintf(
								'<input %s type="checkbox" id="%s-%s" name="t2t_taxonomies[%s][%s]"><label for="%s-%s">%s</label><br />',
								$checked,
								$type->name,
								$tax->name,
								$type->name,
								$tax->name,
								$type->name,
								$tax->name,
								$tax->labels->name
							);
						endif;
					endforeach;
				else :
					echo 'No taxonomies for this post type';
				endif;
				echo '</fieldset>';
			endif;
		endforeach;
	}
	
	// Gets the stop word list:
	private function getStopWords() {
		$stopwords	= array();
		// Try the current options first:
		$vals		= get_option( 'stop_words' );
		// Otherwise, grab the default list:
		if( empty( $vals ) ) :
			$file 			= dirname( __FILE__ ) . '/stopwords.txt';
			$vals			= file_get_contents( $file );
		endif;
		
		// Explode the list and trim values:
		$vals		= explode( ',', $vals );
		foreach($vals as $word) :
			$stopwords[]	= $this->lowerNoPunc( $word );
		endforeach;
		
		return $stopwords;
	}
	
	// Converts all words into lower-case words, sans punctuation or possessives.
	private function lowerNoPunc( $werd ) {
		$werd = strtolower( trim( preg_replace( '#[^\p{L}\p{N}]+#u', '', $werd ) ) );
		return $werd;
	}
	
	// List of WP-specific stop words (draft, etc)
	private $wp_stop	= array('draft', 'auto');
	
	// Get out there and rock and roll the bones:
	function __construct() {
		add_action( 'save_post', array( &$this, 'convert' ) );
		add_action( 'admin_menu', array( &$this, 'addMenu' ) );
	}
}
?>
