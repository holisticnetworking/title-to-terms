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
	/**
	 * These statuses show up as slugs for posts and must be ignored.
	 * @var array
	 */
	private $ignored_statuses = array( 'draft', 'auto' );
	/**
	 * These post types can be ignored.
	 * @var array
	 */
	private $ignored_types = array( 'revision', 'nav_menu_item' );
	/**
	 * These taxonomies are internal to WordPress and can be ignored.
	 * @var array
	 */
	private $ignored_taxonomies = array( 'post_format', 'nav_menu' );
	/**
	 * The current plugin version.
	 * @var string
	 */
	private $version = '4.1';
	/**
	 * Unimportant words that can be ignored when creating terms.
	 * @var array
	 */
	protected $stop_words = array();
	/**
	 * Whether to append new terms or to just ignore posts with terms already set.
	 * @var bool
	 */
	protected $append = false;
	/**
	 * An array of key/value pairs, post type:taxonomy
	 * @var array
	 */
	protected $types = array();

	/**
	 * Core constructor.
	 */
	public function __construct() {
		$this->set_stop_words();
		$this->set_append_tags();
		$this->set_types();
		add_action( 'save_post', [ &$this, 'convert_post_title' ] );
		add_action( 'admin_menu', [ &$this, 'add_menu' ] );
		add_action( 'admin_notices', [ &$this, 'check_version' ] );
	}

	/**
	 * Convert titles to tags on save:
	 * @param $post_id
	 */
	public function convert_post_title( $post_id ) {
		$post = get_post( wp_is_post_revision( $post_id ) ? wp_is_post_revision( $post_id ) : $post_id );
		// If we have a record for this post type and if the post has a title, it's go time:
		if ( $this->is_type( $post->post_type ) && isset( $post->post_title ) ) {
			$tax         = $this->get_type_taxonomy( $post->post_type );
			$terms       = array();
			$title_words = explode( ' ', $post->post_title );
			foreach ( $title_words as $word ) {
				$term = sanitize_text_field( $word );
				$slug = $this->lower_no_punc( $word );
				if ( ! $this->is_stop_word( $slug ) && ! $this->is_ignored_status( $slug ) ) {
					$new_term = wp_insert_term(
						$term,
						$tax,
						array(
							'slug' => $slug,
						)
					);
					$terms[]  = $slug;
				}
			}
			// Append or complete. Do not replace:
			if ( $this->append_tags() ) {
				wp_set_object_terms( $post_id, $terms, $tax, true );
			} elseif ( ! $this->has_terms( $post_id, $tax ) ) {
				wp_set_object_terms( $post_id, $terms, $tax, true );
			}
		}
	}

	/**
	 * Does the current post already have terms applied to it?
	 * @param $post_id
	 * @param $tax
	 *
	 * @return bool
	 */
	private function has_terms( $post_id, $tax ) {
		$terms = wp_get_post_terms( $post_id, $tax );
		if ( empty( $terms ) ) {
			return false;
		} elseif ( 'category' == $tax ) {
			$default_cat = get_option( 'default_category' );
			if ( count( $terms ) == 1 && $terms[0]->term_id == $default_cat ) {
				wp_set_object_terms( $post_id, array(), $tax );
				return false;
			}
		}

		return true;
	}

	/**
	 * Add admin settings page
	 */
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

	/**
	 * Settings API callback for stop words
	 */
	public function settings_stop_words() {
		$values = get_option( 'stop_words' );
		if ( empty( $values ) ) {
			$values = implode( ',', $this->get_stop_words() );
		}
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

	/**
	 * Settings API callback for appending terms
	 */
	public function settings_append() {
		$value   = get_option( 't2t_append' );
		$checked = ( $value ) ? 'checked="checked"' : '';
		echo '<p>Choose whether to add tags to untagged content, or to append new Title 2 Tags, even if there are tags 
            already present.</p>
		<input type="checkbox" name="t2t_append" id="t2t_append" ' . $checked . ' /> append Title to Terms to 
		    preexisting tags.';
	}

	/**
	 * Settings API callback for the taxonomy/posts matrix.
	 */
	public function settings_taxonomies() {
		$post_types = get_post_types( null, 'objects' );
		$settings   = get_option( 't2t_taxonomies' );
		echo '<style type="text/css">
			fieldset.t2t_cpt {
				margin: 20px;
				border: 2px solid #aaa;
				padding: 8px;
			}
		</style>';
		foreach ( $post_types as $type ) {
			if ( ! $this->is_ignored_type( $type->name ) ) {
				echo '<fieldset class="t2t_cpt"><legend>' . $type->labels->name . '</legend>';
				$post_taxonomies = get_object_taxonomies( $type->name, 'objects' );
				if ( ! empty( $post_taxonomies ) ) {
					foreach ( $post_taxonomies as $tax ) {
						if ( ! $this->is_ignored_taxonomy( $tax->name ) ) {
							$checked = $settings[ $type->name ] == $tax->name ? 'checked="checked"' : '';
							echo sprintf(
								'<input %1$s type="radio" value="%2$s" id="%3$s-%2$s" name="t2t_taxonomies[%3$s]"><label 
                                    for="%3$s-%2$s">%4$s</label><br />',
								$checked,
								$tax->name,
								$type->name,
								$tax->labels->name
							);
						}
					}
				} else {
					echo 'No taxonomies found for this post type.';
				}
				echo '</fieldset>';
			}
		}
	}

	/**
	 * As advertised, convert term to a lower case, no punctuation work
	 * @param $werd
	 *
	 * @return string
	 */
	private function lower_no_punc( $werd ) {
		$werd = strtolower( trim( preg_replace( '#[^\p{L}\p{N}]+#u', '', $werd ) ) );
		return $werd;
	}

	/**
	 * If admins should receive notifications upon updating this particular version,
	 * that announcement is made here.
	 */
	public function check_version() {
		if ( get_site_option( 't2t_version' ) != $this->version ) {
			include plugin_dir_path( __FILE__ ) . '/fragments/update.php';
			update_site_option( 't2t_version', $this->version );
		}
	}

	/**
	 * Is the status ignored?
	 * @param $status
	 *
	 * @return bool
	 */
	public function is_ignored_status( $status ) {
		return in_array( $status, $this->ignored_statuses );
	}

	/**
	 * Is this post type ignored?
	 * @param $type
	 *
	 * @return bool
	 */
	public function is_ignored_type( $type ) {
		return in_array( $type, $this->ignored_types );
	}

	/**
	 * Is this taxonomy ignored?
	 * @param $tax
	 *
	 * @return bool
	 */
	public function is_ignored_taxonomy( $tax ) {
		return in_array( $tax, $this->ignored_taxonomies );
	}

	/**
	 * Return the list of stop words.
	 * @return array
	 */
	public function get_stop_words() {
		return $this->stop_words;
	}

	/**
	 * Pulls a list of stop words either from the database or from a default list.
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

	/**
	 * Does this word exist in our list of stop words?
	 * @param $word
	 *
	 * @return bool
	 */
	public function is_stop_word( $word ) {
		return in_array( $word, $this->stop_words );
	}

	/**
	 * Does the user intend for new terms to be appended to the current list?
	 * @return bool
	 */
	public function append_tags() {
		return $this->append;
	}

	/**
	 * Pull the value from the database.
	 */
	public function set_append_tags() {
		$this->append = get_option( 't2t_append' );
	}

	/**
	 * Return our list of post types.
	 * @return array
	 */
	public function get_types() {
		return $this->types;
	}

	/**
	 * Return the taxonomy for which the given type is to be checked.
	 * @param $type
	 *
	 * @return mixed
	 */
	public function get_type_taxonomy( $type ) {
		return $this->types[ $type ];
	}

	/**
	 * Pull the current list of taxonomies and types from the database.
	 */
	public function set_types() {
		$this->types = get_option( 't2t_taxonomies' );
	}

	/**
	 * Is this post type one we're creating terms for?
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function is_type( $post_type ) {
		return key_exists( $post_type, $this->types );
	}

	/**
	 * Return the entry for the given post type.
	 * @param $post_type
	 *
	 * @return bool|mixed
	 */
	public function get_type( $post_type ) {
		return array_key_exists( $post_type, $this->types ) ? $this->types[ $post_type ] : false;
	}
}
