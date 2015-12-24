<?php
/*
Plugin Name: Title to Terms Ultimate
Plugin URI: http://holisticnetworking.github.io/title-to-tags/
Description: Creates tags for posts based on the post title on update or publish.
Version: 4.0
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
	// List of WP-specific stop words (draft, etc)
	private $wp_stop	= array('draft', 'auto');
	
	// Convert titles to tags on save:
	function convert( $post_id ) {
		$stopwords	= $this->getStopWords();
		$append	 	= get_option( 't2t_append' );
		$types		= get_option( 't2t_taxonomies' );
		
		$post		= get_post( wp_is_post_revision( $post_id ) ? wp_is_post_revision( $post_id ) : $post_id );
		// Check to see if the post type has title-to-tags settings:
		$tax		= isset( $types[$post->post_type] ) ? $types[$post->post_type] : 'post_tag';
		// No title? No point in going any further:
		if( isset( $post->post_title ) ) :
			// Setup our tag data:
			$terms			= array();
			$title_words	= explode( ' ', $post->post_title );
			foreach( $title_words as $word ) :  
				$term	= preg_replace('/[^a-z\d]+/i', '', $word );
				$slug	= $this->lowerNoPunc( $word );
				if( !in_array( $slug, $stopwords ) && !in_array( $slug, $this->wp_stop ) ) :
					wp_insert_term(
						$term,
						$tax,
						array(
							'slug'	=> $slug
						)
					);
					$terms[] = $slug;
				endif;
			endforeach;
			// Append or complete. Do not replace:
			if( $append ) :
				wp_set_object_terms( $post_id, $terms, $tax, true );
			elseif( !$this->has_terms( $post_id, $tax ) ) :
				wp_set_object_terms( $post_id, $terms, $tax, true );
			endif;
		endif;
	}
	
	function has_terms( $post_id, $tax ) {
		$terms	= wp_get_post_terms( $post_id, $tax );
		if( empty( $terms ) ) :
			return false;
		elseif( $tax == 'category' ) :
			$default_cat	= get_option('default_category');
			if( count( $terms ) == 1 && $terms[0]->term_id == $default_cat ) :
				wp_set_object_terms( $post_id, array(), $tax );
				return false;
			endif;
		endif;
		return true;
	}
	
	// Display options page:
	function addMenu() {
		add_settings_field(
			'stop_words',
			"Title to Terms: Ignored Words",
			array( &$this, 'stop_words' ),
			'writing'
		);
		add_settings_field(
			't2t_append',
			"Title to Terms: Append Tags",
			array( &$this, 'append' ),
			'writing'
		);
		add_settings_field(
			't2t_taxonomies',
			"Title to Terms: Taxonomies and Post Types",
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
		<p>These words will be ignored by Title to Terms (punctuation removed). <em>To reset, simply delete all values here and the default list will be restored.</em></p>
		<textarea rows="6" cols="100" name="stop_words" id="stop_words">' . $values . '</textarea>
		';
	}
	
	function append() {
		$value		= get_option( 't2t_append' );
		$checked	= ( $value ) ? 'checked="checked"' : '';
		echo '<p>Choose whether to add tags to untagged content, or to append new Title 2 Tags, even if there are tags already present.</p>
		<input type="checkbox" name="t2t_append" id="t2t_append" ' . $checked . ' /> append Title to Terms to preexisting tags.';
	}
	
	function taxonomies() {
		$types		= get_post_types( null, 'objects' );
		$settings	= get_option( 't2t_taxonomies' );
		print_r( $settings );
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
					$none	= empty( $settings[$type->name] ) ? 'checked="checked"' : '';
					echo sprintf(
						'<input %s type="radio" value="" id="%s-none" name="t2t_taxonomies[%s]"><label for="%s-none">none</label><br />',
						$none,
						$type->name,
						$type->name,
						$type->name
					);
					foreach( $taxes as $tax ) :
						if( !in_array( $tax->name, array( 'post_format' ) ) ) :	
							$checked	= $settings[$type->name] == $tax->name ? 'checked="checked"' : '';
							echo sprintf(
								'<input %s type="radio" value="%s" id="%s-%s" name="t2t_taxonomies[%s]"><label for="%s-%s">%s</label><br />',
								$checked,
								$tax->name,
								$type->name,
								$tax->name,
								$type->name,
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
	
	// Get out there and rock and roll the bones:
	function __construct() {
		add_action( 'save_post', array( &$this, 'convert' ) );
		// add_action( 'edit_post', array( &$this, 'convert' ) );
		add_action( 'admin_menu', array( &$this, 'addMenu' ) );
	}
}
?>
