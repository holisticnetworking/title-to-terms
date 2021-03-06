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

	/**
	 * The current plugin version.
	 * @var string
	 */
	private $version = '4.1';
	/**
	 * The pattern that matches what Title to Terms Ultimate regards as "a word".
	 * @var string
	 * @see https://regex101.com/r/84JOiX/2
	 */
	private $post_term_regex = '/[\p{L}\p{N}-_\'.@]{%d,}/';
	/**
	 * Use to find and remove punctuation from slugs.
	 * @see https://regex101.com/r/PMQ6UF/1
	 * @var string
	 */
	private $post_term_punc_regex = '/[^\p{L}\p{N}\-_]{1,1}/';
	/**
	 * Used to convert spaces and dashes to snake_case slugs
	 * @xee https://regex101.com/r/mkMgWi/1/
	 * @var string
	 */
	private $post_term_snake_regex = '/[_\- ]+/';
	/**
	 * Punctuation need not apply.
	 * @var string
	 */
	private $post_trim_characters = '".?!"';
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
	 * Auto and Draft.
	 * @var array
	 */
	private $ignored_terms = array( 'auto', 'draft' );
	/**
	 * Unimportant words that can be ignored when creating terms.
	 * @var array
	 */
	protected $stop_words = array();
	/**
	 * The minimum character count in a taggable word.
	 * @var int
	 */
	protected $character_count = 3;

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
	 * How we will deal with possessive nouns.
	 * @var string
	 */
	protected $possessives = null;
	/**
	 * The current post object.
	 * @var mixed null|object
	 */
	protected $post = null;

	/**
	 * Core constructor.
	 */
	public function __construct() {
		$this->set_stop_words();
		$this->set_character_count();
		$this->set_append_tags();
		$this->set_types();
		$this->set_possessives();
		// add_action( 'save_post', [ &$this, 'convert_post_title' ] );
		add_action( 'transition_post_status', [ &$this, 'convert_post_title' ], 10, 3 );
		add_action( 'admin_menu', [ &$this, 'add_menu' ] );
		add_action( 'admin_notices', [ &$this, 'check_version' ] );
		add_action( 'admin_init', [ &$this, 'admin_enqueue' ] );
	}

	/**
	 * Adding styles to the admin areas
	 */
	public function admin_enqueue() {
		if ( is_admin() ) {
			wp_enqueue_style(
				't2t_style',
				plugins_url( '/Resource/css/admin.css', dirname( __FILE__ ) )
			);
		}
	}

	/**
	 * Convert titles to tags on post transition
	 * @param $new_status
	 * @param $old_status
	 * @param $post
	 */
	public function convert_post_title( $new_status, $old_status, $post ) {
		$this->post = $post;
		// If we have a record for this post type and if the post has a title, it's go time:
		if ( $this->maybe_convert_post( $new_status ) ) {
			$tax   = $this->get_type_taxonomy( $this->post->post_type );
			$terms = array();
			preg_match_all( $this->post_term_regex, $this->post->post_title, $title_words );
			if ( ! empty( $title_words ) ) {
				foreach ( $title_words[0] as $word ) {
					// Removes ending punctuation:
					$word = trim( $word, $this->post_trim_characters );
					if ( 'remove' == $this->possessives ) {
						$word = preg_replace( "/'s/", '', $word );
					}
					$slug = $this->simplify_term( $word );
					if ( ! in_array( $slug, $this->stop_words ) && ! in_array( $slug, $this->ignored_terms ) ) {
						$added = wp_insert_term(
							$word,
							$tax,
							array( 'slug' => $slug )
						);
						if ( is_wp_error( $added ) ) {
							$added = get_term_by( 'name', $word, $tax, ARRAY_A );
						}
						$terms[] = $added['term_id'];
					}
				}
				// Append or complete. Do not replace:
				if ( $this->append_tags() ) {
					wp_set_object_terms( $this->post->ID, $terms, $tax, true );
				} else {
					wp_set_object_terms( $this->post->ID, $terms, $tax, false );
				}
			}
		}
	}

	/**
	 * Determines if we need to do anything with the post, or not.
	 * @param $status
	 *
	 * @return bool
	 */
	private function maybe_convert_post( $status ) {
		// Don't save autodrafts, empty post titles or
		// post types we aren't configured to accept:
		if (
			! in_array( $status, array( 'auto-draft', 'inherit', 'trash' ) ) &&
			$this->is_type( $this->post->post_type ) &&
			! empty( $this->post->post_title ) ) {
			$post_id = $this->post->ID;
			$tax     = $this->get_type_taxonomy( $this->post->post_type );
			if ( ! $this->has_terms( $post_id, $tax ) || $this->append_tags() ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Convert term to a lower case, no punctuation work
	 * @param $werd
	 *
	 * @return string
	 */
	private function simplify_term( $werd ) {
		$werd = preg_replace(
			array(
				$this->post_term_punc_regex,
				$this->post_term_snake_regex,
			),
			array(
				'',
				'_',
			),
			$werd
		);
		return strtolower( $werd );
	}

	/**
	 * Does the current post already have terms applied to it?
	 * Note that for categories, a default category is assigned by WP
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
			'character_count',
			'Title to Terms: Minimum characters',
			[ &$this, 'settings_character_count' ],
			'writing'
		);
		add_settings_field(
			't2t_append',
			'Title to Terms: Append Tags',
			[ &$this, 'settings_append' ],
			'writing'
		);

		add_settings_field(
			't2t_possessives',
			'Title to Terms: Possessive Nouns',
			[ &$this, 'settings_possessives' ],
			'writing'
		);
		add_settings_field(
			't2t_taxonomies',
			'Title to Terms: Taxonomies and Post Types',
			[ &$this, 'settings_types' ],
			'writing'
		);
		register_setting( 'writing', 'stop_words' );
		register_setting( 'writing', 't2t_character_count' );
		register_setting( 'writing', 't2t_append' );
		register_setting( 'writing', 't2t_taxonomies' );
		register_setting( 'writing', 't2t_version' );
		register_setting( 'writing', 't2t_possessives' );
	}

	/**
	 * Settings API callback for stop words
	 */
	public function settings_stop_words() {
		$values = implode( ',', $this->get_stop_words() );
		echo '<p><a name="t2t_settings" class="t2t_settings">&nbsp;</a>These words will be ignored by Title to Terms
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
	public function settings_character_count() {
		$value   = $this->get_character_count();
		$checked = ! empty( $value ) ? 'checked="checked"' : '';
		?>
		<label for="t2t_append">Do not tag words smaller than <input name="t2t_character_count" id="t2t_character_count" size="4" value="<?php echo $value; ?>"> characters. (clear for no minimum.)</label>
		<?php
	}

	/**
	 * Settings API callback for appending terms
	 */
	public function settings_append() {
		$value   = $this->append_tags();
		$checked = ! empty( $value ) ? 'checked="checked"' : '';
		?>
		<p>When Title to Terms encounters a post with terms already applied to it:</p>
		<label for="t2t_append"><input value="1" type="radio" name="t2t_append" id="t2t_append"<?php if ( ! empty( $value ) ) { echo ' checked '; } ?>/>Append terms to the list of tags.</label>
		<label for="t2t_append"><input value="0" type="radio" name="t2t_append" id="t2t_append"<?php if ( empty( $value ) ) { echo ' checked '; } ?>/>Do nothing</label>
		<?php
	}

	/**
	 * Settings API callback for the taxonomy/posts matrix.
	 */
	public function settings_types() {
		$post_types = get_post_types( null, 'objects' );
		$settings   = $this->types;
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
	 * Settings API callback for appending terms
	 */
	public function settings_possessives() {
		?>
		<p>When Title to Tags encounters a possessive noun, it will:</p>
		<label for="t2t_possessives"><input <?php if ( 'preserve' == $this->possessives ) { echo 'checked'; } ?> type="radio" name="t2t_possessives" value="preserve">Preserve the 's</label>
		<label for="t2t_possessives"><input <?php if ( 'remove' == $this->possessives ) { echo 'checked'; } ?> type="radio" name="t2t_possessives" value="remove">Remove the 's</label>
		<?php
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
			$stop_words[] = $this->simplify_term( $word );
		endforeach;

		// Add our plugin-wide watch terms:
		$stop_words = array_merge( $stop_words, $this->ignored_terms );

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
	 * @return int
	 */
	public function get_character_count() {
		return $this->character_count;
	}

	/**
	 *
	 */
	public function set_character_count() {
		$this->character_count = intval( get_option( 't2t_character_count', 3 ) );
		$this->post_term_regex = sprintf(
			$this->post_term_regex,
			$this->character_count
		);
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
		$this->append = get_option( 't2t_append'. false );
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
		$this->types = get_option( 't2t_taxonomies', array() );
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

	/**
	 * @return string
	 */
	public function get_possessives() {
		return $this->possessives;
	}

	/**
	 * @param string $possessives
	 */
	public function set_possessives() {
		$this->possessives = get_option( 't2t_possessives', 'remove' );
	}
}
