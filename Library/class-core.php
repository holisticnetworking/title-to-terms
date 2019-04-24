<?php
/**
 * The Title To Tags main class.
 * PHP Version >5.6
 *
 * @category WordPress_Plugin
 * @package  Title_To_Terms
 * @author   Thomas J Belknap <tbelknap@holisticnetworking.net>
 * @license  GPL2.0
 * @link     http://holisticnetworking.net/
 */

namespace Title_To_Terms;

class Core {

	// List of WP-specific stop words (draft, etc)
	private $ignored_statuses = array( 'draft', 'auto' );

	private $version = '4.1';
	protected $stop_words = array();
	protected $append = false;
	protected $types = array();

	// Get out there and rock and roll the bones:
	public function __construct() {
		$this->set_stop_words();
		$this->set_append_tags();
		$this->set_types();
		add_action( 'save_post', [ &$this, 'convert_post_title' ] );
		add_action( 'admin_menu', [ &$this, 'add_menu' ] );
		add_action( 'admin_notices', [ &$this, 'check_version' ] );
	}

	// Convert titles to tags on save:
	public function convert_post_title( $post_id ) {
		error_log('dude. totally.');
		$post = get_post( wp_is_post_revision( $post_id ) ? wp_is_post_revision( $post_id ) : $post_id );
		// If we have a record for this post type and if the post has a title, it's go time:
		if ( $this->is_type( $post->post_type ) && isset( $post->post_title ) ) :
			error_log('awe, yeah.');
			$tax         = $this->get_type( $post->post_type );
			$terms       = array();
			$title_words = explode( ' ', $post->post_title );
			foreach ( $title_words as $word ) :
				// $term = preg_replace( '/[^a-z\d]+/i', '', $word );
				$term = sanitize_text_field( $word );
				$slug = $this->lower_no_punc( $word );
				if ( ! $this->is_stop_word( $slug ) && ! $this->is_ignored_status( $slug ) ) :
					wp_insert_term(
						$term,
						$tax,
						array(
							'slug' => $slug,
						)
					);
					$terms[] = $slug;
				endif;
			endforeach;
			// Append or complete. Do not replace:
			if ( $this->append_tags() ) :
				wp_set_object_terms( $post_id, $terms, $tax, true );
			elseif ( ! $this->has_terms( $post_id, $tax ) ) :
				wp_set_object_terms( $post_id, $terms, $tax, true );
			endif;
		endif;
	}

	private function has_terms( $post_id, $tax ) {
		$terms = wp_get_post_terms( $post_id, $tax );
		if ( empty( $terms ) ) :
			return false;
		elseif ( 'category' == $tax ) :
			$default_cat = get_option( 'default_category' );
			if ( count( $terms ) == 1 && $terms[0]->term_id == $default_cat ) :
				wp_set_object_terms( $post_id, array(), $tax );

				return false;
			endif;
		endif;

		return true;
	}

	// Display options page:
	public function add_menu() {
		add_settings_field(
			'stop_words',
			'Title to Terms: Ignored Words',
			[ &$this, 'settings_stop_words' ],
			'writing'
		);
		add_settings_field(
			't2t_append',
			'Title to Terms: Append Tags',
			[ &$this, 'settings_append' ],
			'writing'
		);
		add_settings_field(
			't2t_taxonomies',
			'Title to Terms: Taxonomies and Post Types',
			[ &$this, 'settings_taxonomies' ],
			'writing'
		);
		register_setting( 'writing', 'stop_words' );
		register_setting( 'writing', 't2t_append' );
		register_setting( 'writing', 't2t_taxonomies' );
		register_setting( 'writing', 't2t_version' );
	}

	public function settings_stop_words() {
		$values = get_option( 'stop_words' );
		if ( empty( $values ) ) :
			$values = implode( ',', $this->get_stop_words() );
		endif;
		echo '
		<style type="text/css">.t2t_settings { width: 0; height: 0; }</style>
		<p><a name="t2t_settings" class="t2t_settings">&nbsp;</a>These words will be ignored by Title to Terms
		 (punctuation removed). <em>To reset, simply delete all values here and the default list will be
		 restored.</em></p>
		<textarea rows="6" cols="100" name="stop_words" id="stop_words">' . $values . '</textarea>
		';
		echo sprintf(
			'<input type="hidden" name="t2t_version" value="%s" />',
			$this->version
		);
	}

	public function settings_append() {
		$value   = get_option( 't2t_append' );
		$checked = ( $value ) ? 'checked="checked"' : '';
		echo '<p>Choose whether to add tags to untagged content, or to append new Title 2 Tags, even if there are tags 
            already present.</p>
		<input type="checkbox" name="t2t_append" id="t2t_append" ' . $checked . ' /> append Title to Terms to 
		    preexisting tags.';
	}

	public function settings_taxonomies() {
		$types    = get_post_types( null, 'objects' );
		$settings = get_option( 't2t_taxonomies' );
		// print_r( $settings );
		echo '<style type="text/css">
			fieldset.t2t_cpt {
				margin: 20px;
				border: 2px solid #aaa;
				padding: 8px;
			}
		</style>';
		foreach ( $types as $type ) :
			if ( ! in_array( $type->name, array( 'revision', 'nav_menu_item' ) ) ) :
				echo '<fieldset class="t2t_cpt"><legend>' . $type->labels->name . '</legend>';
				$taxes = get_object_taxonomies( $type->name, 'objects' );
				if ( ! empty( $taxes ) ) :
					$none = empty( $settings[ $type->name ] ) ? 'checked="checked"' : '';
					echo sprintf(
						'<input %s type="radio" value="" id="%s-none" name="t2t_taxonomies[%s]"><label 
                            for="%s-none">none</label><br />',
						$none,
						$type->name,
						$type->name,
						$type->name
					);
					foreach ( $taxes as $tax ) :
						if ( ! in_array( $tax->name, array( 'post_format' ) ) ) :
							$checked = $settings[ $type->name ] == $tax->name ? 'checked="checked"' : '';
							echo sprintf(
								'<input %s type="radio" value="%s" id="%s-%s" name="t2t_taxonomies[%s]"><label 
                                    for="%s-%s">%s</label><br />',
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

	// Converts all words into lower-case words, sans punctuation or possessives.
	private function lower_no_punc( $werd ) {
		$werd = strtolower( trim( preg_replace( '#[^\p{L}\p{N}]+#u', '', $werd ) ) );

		return $werd;
	}

	// Version update messages:
	public function check_version() {
		if ( get_site_option( 't2t_version' ) != $this->version ) {
			include plugin_dir_path( __FILE__ ) . '/fragments/update.php';
			update_site_option( 't2t_version', $this->version );
		}
	}

	/**
	 * @return array
	 */
	public function is_ignored_status( $status ) {
		return in_array( $this->ignored_statuses, $status );
	}

	/**
	 * @return array
	 */
	public function get_stop_words() {
		return $this->stop_words;
	}

	/**
	 *
	 */
	public function set_stop_words() {
		$stop_words = array();
		// Try the current options first:
		$vals = get_option( 'stop_words' );
		// Otherwise, grab the default list:
		if ( empty( $vals ) ) :
			$file = dirname( __FILE__ ) . '/stopwords.txt';
			$vals = file_get_contents( $file );
		endif;

		// Explode the list and trim values:
		$vals = explode( ',', $vals );
		foreach ( $vals as $word ) :
			$stop_words[] = $this->lower_no_punc( $word );
		endforeach;

		$this->stop_words = $stop_words;
	}

	public function is_stop_word( $word ) {
		return in_array( $this->stop_words, $word );
	}

	/**
	 * @return bool
	 */
	public function append_tags() {
		return $this->append;
	}

	/**
	 * @param bool $append
	 */
	public function set_append_tags() {
		$this->append = get_option( 't2t_append' );
	}

	/**
	 * @return array
	 */
	public function get_types() {
		return $this->types;
	}

	/**
	 * @param array $types
	 */
	public function set_types() {
		$this->types = get_option( 't2t_taxonomies' );
	}

	/**
	 * @return boolean
	 */
	public function is_type( $post_type ) {
		return in_array( $post_type, $this->types );
	}

	/**
	 * @return mixed bool/string
	 */
	public function get_type( $post_type ) {
		return in_array( $post_type, $this->types ) ? $this->types[ $post_type ] : false;
	}
}