<?php
/**
 * Simple Lead Gen Form metabox class.
 *
 * @since   0.0.1
 * @package SLGF
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Simple Lead Gen Form metabox base class.
 *
 * @since 0.0.1
 */
class SLGF_Base_Metabox {

	/**
	 * Metabox arguments.
	 *
	 * @var array
	 */
	protected $args = array();

	/**
	 * Nonce input name.
	 *
	 * @since    0.0.1
	 * @var string
	 */
	private $nonce_input = '_slgf_metabox_nonce';

	/**
	 * Nonce action name.
	 *
	 * @since    0.0.1
	 * @var string
	 */
	private $nonce_action = 'slgf_metabox_nonce';

	/**
	 * HTTP Request $_POST data.
	 *
	 * @since    0.0.1
	 * @var array
	 */
	private $data_post;

	/**
	 * Constructor
	 *
	 * @param array $args Metabox arguments.
	 * @since 0.0.1
	 */
	public function __construct( $args = array() ) {
		$this->args = wp_parse_args(
			$args, array(
				'id'            => '',
				'title'         => '',
				'callback'      => null,
				'screen'        => null,
				'context'       => 'normal',
				'priority'      => 'default',
				'callback_args' => null,
				'layout'        => 'table',
			)
		);

		if ( empty( $this->args['id'] ) ) {
			wp_die( esc_html__( 'It is required to pass ID of metabox', 'slgf' ) );
		}

		if ( empty( $this->args['title'] ) ) {
			wp_die( esc_html__( 'It is required to pass title of metabox', 'slgf' ) );
		}

		if ( is_admin() ) {
			add_action( 'load-post.php', array( $this, 'init_metabox' ) );
			add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
		}
	}

	/**
	 * Meta box initialization.
	 */
	public function init_metabox() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 3 );
	}

	/**
	 * Adds the meta box.
	 */
	public function add_metabox() {
		add_meta_box(
			$this->args['id'],
			$this->args['title'],
			is_null( $this->args['callback'] ) || ! is_callable( $this->args['callback'] ) ? array( $this, 'render_metabox' ) : $this->args['callback'],
			$this->args['screen'],
			$this->args['context'],
			$this->args['priority'],
			$this->args['callback_args']
		);
	}

	/**
	 * Renders the meta box.
	 *
	 * @param WP_Post $post Post object.
	 * @param array   $callback_args Extra callback arguments.
	 */
	public function render_metabox( $post, $callback_args = null ) {
		// Bail early if there is no fields registered.
		if ( ! $this->metabox_fields() ) {
			return;
		}

		// Add nonce for security and authentication.
		$this->nonce_field();

		$fields = array();
		foreach ( $this->metabox_fields() as $key => $field ) {
			$field          = $this->normalize_field( $field );
			$field['id']    = $key;
			$fields[ $key ] = $field;
		}
		?>
		<table class="form-table">
			<tbody>
				<?php foreach ( $fields as $key => $field ) : ?>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
					<td><?php call_user_func( $field['render_cb'], $field, $post, $callback_args ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Handles saving the meta box.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update Whether this is an existing post being updated or not.
	 * @return null
	 */
	public function save_metabox( $post_id, $post, $update = false ) {

		// Check if nonce is set.
		if ( ! $this->verify_nonce() ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$core_fields = array();
		foreach ( $this->metabox_fields() as $key => $field ) {
			// Get posted data.
			$value = $this->data_post( $key, $field['sanitize_cb'] );
			if ( $value && ! empty( $field['value_presave_cb'] ) && is_callable( $field['value_presave_cb'] ) ) {
				$value = call_user_func( $field['value_presave_cb'], $value, $field, $post, $update );
			}
			update_post_meta( $post_id, $key, $value );
			if ( ! empty( $field['core_field'] ) ) {
				$core_fields[ $field['core_field'] ] = $value;
			}
		}

		if ( $core_fields ) {
			remove_action( 'save_post', array( $this, 'save_metabox' ), 10 );
			$core_fields['ID'] = $post_id;
			wp_update_post( $core_fields );
			add_action( 'save_post', array( $this, 'save_metabox' ), 10, 3 );
		}
	}

	/**
	 * Get metabox fields.
	 *
	 * @since  0.1.0
	 * @return array Fields data array.
	 */
	protected function metabox_fields() {
		// Placeholder method. Shoulds be overridden by subclass.
		return array();
	}

	/**
	 * Get metabox fields.
	 *
	 * @since  0.1.0
	 * @param array $field Field data array.
	 * @return array Normalized field data array.
	 */
	protected function normalize_field( $field ) {
		$field = wp_parse_args(
			$field, array(
				'label'            => '',
				'type'             => 'text',
				'sanitize_cb'      => null,
				'render_cb'        => null,
				'value_input_cb'  => null,
				'value_presave_cb' => null,
				'core_field'       => null,
				'default_value'    => '',
				'options'          => array(),
			)
		);

		if ( is_null( $field['sanitize_cb'] ) ) {
			$field['sanitize_cb'] = 'sanitize_' . str_replace( '-', '_', $field['type'] ) . '_field';
		}

		if ( ! is_callable( $field['sanitize_cb'] ) ) {
			$field['sanitize_cb'] = 'sanitize_text_field';
		}

		if ( is_null( $field['render_cb'] ) ) {
			$field['render_cb'] = array( $this, 'render_' . str_replace( '-', '_', $field['type'] ) . '_field' );
		}

		if ( ! is_callable( $field['render_cb'] ) ) {
			$field['render_cb'] = array( $this, 'render_text_field' );
		}

		if ( $field['value_input_cb'] && ! is_callable( $field['value_input_cb'] ) ) {
			$field['value_input_cb'] = null;
		}

		if ( $field['value_presave_cb'] && ! is_callable( $field['value_presave_cb'] ) ) {
			$field['value_presave_cb'] = null;
		}

		return $field;
	}

	/**
	 * Render metabox field: text
	 *
	 * @since  0.1.0$field, $post, $callback_args
	 * @param array   $field Field data array.
	 * @param WP_Post $post Post object.
	 * @param array   $callback_args Extra callback arguments.
	 * @return void.
	 */
	protected function render_text_field( $field, $post, $callback_args ) {
		$value = get_post_meta( $post->ID, $field['id'], true );
		if ( is_null( $value ) ) {
			$value = $field['default_value'];
		}
		if ( $value && $field['value_input_cb'] ) {
			$value = call_user_func( $field['value_input_cb'], $value, $field, $post, $callback_args );
		}
		$field_type = in_array(
			$field['type'], array(
				'email',
				'url',
				'number',
				'date',
				'time',
				'datetime-local',
				'email',
				'color',
				'tel',
				'month',
				'range',
				'search',
				'weeks',
			), true
		) ? $field['type'] : 'text';
		?>
		<input type="<?php echo esc_attr( $field_type ); ?>" name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
		<?php
	}

	/**
	 * Render metabox field: textarea
	 *
	 * @since  0.1.0$field, $post, $callback_args
	 * @param array   $field Field data array.
	 * @param WP_Post $post Post object.
	 * @param array   $callback_args Extra callback arguments.
	 * @return void.
	 */
	protected function render_textarea_field( $field, $post, $callback_args ) {
		$value = get_post_meta( $post->ID, $field['id'], true );
		if ( is_null( $value ) ) {
			$value = $field['default_value'];
		}
		if ( $value && $field['value_input_cb'] ) {
			$value = call_user_func( $field['value_input_cb'], $value, $field, $post, $callback_args );
		}
		?>
		<textarea name="<?php echo esc_attr( $field['id'] ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" class="large-text" cols="50" rows="10"><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

	/**
	 * HTTP POST request data
	 *
	 * @since 0.0.1
	 * @param string $key Request data key.
	 * @param mixed  $sanitize_cb Callback to sanitize input data.
	 * @return mixed
	 */
	public function data_post( $key, $sanitize_cb = 'sanitize_text_field' ) {
		$value = isset( $this->data_post[ $key ] ) ? $this->data_post[ $key ] : null;

		if ( $sanitize_cb && is_callable( $sanitize_cb ) ) {
			$value = call_user_func( $sanitize_cb, $value );
		}

		return $value;
	}

	/**
	 * Set AJAX response data.
	 *
	 * @since    0.0.1
	 * @param string $key Response data key.
	 * @param mixed  $value Response data value.
	 */
	public function set_response_data( $key, $value ) {
		$this->response_data[ $key ] = $value;
	}

	/**
	 * Set nonce input.
	 *
	 * @since  0.0.1
	 * @param string $nonce_input Nonce input name.
	 */
	public function set_nonce_input( $nonce_input ) {
		$this->nonce_input = $nonce_input;
	}

	/**
	 * Get nonce input.
	 *
	 * @since  0.0.1
	 * @return string
	 */
	public function nonce_input() {
		return $this->nonce_input;
	}

	/**
	 * Set nonce action.
	 *
	 * @since  0.0.1
	 * @param string $nonce_action Nonce action name.
	 */
	public function set_nonce_action( $nonce_action ) {
		$this->nonce_action = $nonce_action;
	}

	/**
	 * Get nonce action.
	 *
	 * @since  0.0.1
	 * @return string
	 */
	public function nonce_action() {
		return $this->nonce_action;
	}

	/**
	 * Print hidden nonce field.
	 *
	 * @since  0.0.1
	 * @return void
	 */
	public function nonce_field() {
		wp_nonce_field( $this->nonce_action, $this->nonce_input );
	}

	/**
	 * Verify nonce.
	 *
	 * @since 0.0.1
	 */
	public function verify_nonce() {
		if ( isset( $_POST[ $this->nonce_input ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $this->nonce_input ] ) ), $this->nonce_action ) ) {
			$this->data_post = wp_unslash( $_POST );
			return true;
		}

		return false;
	}
}
