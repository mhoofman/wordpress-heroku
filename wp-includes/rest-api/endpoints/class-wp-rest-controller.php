<?php
/**
 * REST API: WP_REST_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * Core base controller for managing and interacting with REST API items.
 *
 * @since 4.7.0
 */
abstract class WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @since 4.7.0
	 * @access protected
	 * @var string
	 */
	protected $namespace;

	/**
	 * The base of this controller's route.
	 *
	 * @since 4.7.0
	 * @access protected
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 * @access public
	 */
	public function register_routes() {
		_doing_it_wrong( 'WP_REST_Controller::register_routes', __( 'The register_routes() method must be overridden' ), '4.7' );
	}

	/**
	 * Checks if a given request has access to get items.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Checks if a given request has access to get a specific item.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool True if the request has read access for the item, WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Checks if a given request has access to create items.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool True if the request has access to create items, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Checks if a given request has access to update a specific item.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Checks if a given request has access to delete a specific item.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool True if the request has access to delete the item, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function delete_item( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Prepares one item for create or update operation.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|object The prepared item, or WP_Error object on failure.
	 */
	protected function prepare_item_for_database( $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Prepares the item for the REST response.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param mixed           $item    WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function prepare_item_for_response( $item, $request ) {
		return new WP_Error( 'invalid-method', sprintf( __( "Method '%s' not implemented. Must be overridden in subclass." ), __METHOD__ ), array( 'status' => 405 ) );
	}

	/**
	 * Prepares a response for insertion into a collection.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param WP_REST_Response $response Response object.
	 * @return array|mixed Response data, ready for insertion into collection data.
	 */
	public function prepare_response_for_collection( $response ) {
		if ( ! ( $response instanceof WP_REST_Response ) ) {
			return $response;
		}

		$data   = (array) $response->get_data();
		$server = rest_get_server();

		if ( method_exists( $server, 'get_compact_response_links' ) ) {
			$links = call_user_func( array( $server, 'get_compact_response_links' ), $response );
		} else {
			$links = call_user_func( array( $server, 'get_response_links' ), $response );
		}

		if ( ! empty( $links ) ) {
			$data['_links'] = $links;
		}

		return $data;
	}

	/**
	 * Filters a response based on the context defined in the schema.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param array  $data    Response data to fiter.
	 * @param string $context Context defined in the schema.
	 * @return array Filtered response.
	 */
	public function filter_response_by_context( $data, $context ) {

		$schema = $this->get_item_schema();

		foreach ( $data as $key => $value ) {
			if ( empty( $schema['properties'][ $key ] ) || empty( $schema['properties'][ $key ]['context'] ) ) {
				continue;
			}

			if ( ! in_array( $context, $schema['properties'][ $key ]['context'], true ) ) {
				unset( $data[ $key ] );
				continue;
			}

			if ( 'object' === $schema['properties'][ $key ]['type'] && ! empty( $schema['properties'][ $key ]['properties'] ) ) {
				foreach ( $schema['properties'][ $key ]['properties'] as $attribute => $details ) {
					if ( empty( $details['context'] ) ) {
						continue;
					}

					if ( ! in_array( $context, $details['context'], true ) ) {
						if ( isset( $data[ $key ][ $attribute ] ) ) {
							unset( $data[ $key ][ $attribute ] );
						}
					}
				}
			}
		}

		return $data;
	}

	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return $this->add_additional_fields_schema( array() );
	}

	/**
	 * Retrieves the item's schema for display / public consumption purposes.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {

		$schema = $this->get_item_schema();

		foreach ( $schema['properties'] as &$property ) {
			unset( $property['arg_options'] );
		}

		return $schema;
	}

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_collection_params() {
		return array(
			'context'                => $this->get_context_param(),
			'page'                   => array(
				'description'        => __( 'Current page of the collection.' ),
				'type'               => 'integer',
				'default'            => 1,
				'sanitize_callback'  => 'absint',
				'validate_callback'  => 'rest_validate_request_arg',
				'minimum'            => 1,
			),
			'per_page'               => array(
				'description'        => __( 'Maximum number of items to be returned in result set.' ),
				'type'               => 'integer',
				'default'            => 10,
				'minimum'            => 1,
				'maximum'            => 100,
				'sanitize_callback'  => 'absint',
				'validate_callback'  => 'rest_validate_request_arg',
			),
			'search'                 => array(
				'description'        => __( 'Limit results to those matching a string.' ),
				'type'               => 'string',
				'sanitize_callback'  => 'sanitize_text_field',
				'validate_callback'  => 'rest_validate_request_arg',
			),
		);
	}

	/**
	 * Retrieves the magical context param.
	 *
	 * Ensures consistent descriptions between endpoints, and populates enum from schema.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param array $args Optional. Additional arguments for context parameter. Default empty array.
	 * @return array Context parameter details.
	 */
	public function get_context_param( $args = array() ) {
		$param_details = array(
			'description'        => __( 'Scope under which the request is made; determines fields present in response.' ),
			'type'               => 'string',
			'sanitize_callback'  => 'sanitize_key',
			'validate_callback'  => 'rest_validate_request_arg',
		);

		$schema = $this->get_item_schema();

		if ( empty( $schema['properties'] ) ) {
			return array_merge( $param_details, $args );
		}

		$contexts = array();

		foreach ( $schema['properties'] as $attributes ) {
			if ( ! empty( $attributes['context'] ) ) {
				$contexts = array_merge( $contexts, $attributes['context'] );
			}
		}

		if ( ! empty( $contexts ) ) {
			$param_details['enum'] = array_unique( $contexts );
			rsort( $param_details['enum'] );
		}

		return array_merge( $param_details, $args );
	}

	/**
	 * Adds the values from additional fields to a data object.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param array           $object  Data object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array Modified data object with additional fields.
	 */
	protected function add_additional_fields_to_object( $object, $request ) {

		$additional_fields = $this->get_additional_fields();

		foreach ( $additional_fields as $field_name => $field_options ) {

			if ( ! $field_options['get_callback'] ) {
				continue;
			}

			$object[ $field_name ] = call_user_func( $field_options['get_callback'], $object, $field_name, $request, $this->get_object_type() );
		}

		return $object;
	}

	/**
	 * Updates the values of additional fields added to a data object.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param array           $object  Data Object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True on success, WP_Error object if a field cannot be updated.
	 */
	protected function update_additional_fields_for_object( $object, $request ) {
		$additional_fields = $this->get_additional_fields();

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['update_callback'] ) {
				continue;
			}

			// Don't run the update callbacks if the data wasn't passed in the request.
			if ( ! isset( $request[ $field_name ] ) ) {
				continue;
			}

			$result = call_user_func( $field_options['update_callback'], $request[ $field_name ], $object, $field_name, $request, $this->get_object_type() );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		return true;
	}

	/**
	 * Adds the schema from additional fields to a schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param array $schema Schema array.
	 * @return array Modified Schema array.
	 */
	protected function add_additional_fields_schema( $schema ) {
		if ( empty( $schema['title'] ) ) {
			return $schema;
		}

		// Can't use $this->get_object_type otherwise we cause an inf loop.
		$object_type = $schema['title'];

		$additional_fields = $this->get_additional_fields( $object_type );

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['schema'] ) {
				continue;
			}

			$schema['properties'][ $field_name ] = $field_options['schema'];
		}

		return $schema;
	}

	/**
	 * Retrieves all of the registered additional fields for a given object-type.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @param  string $object_type Optional. The object type.
	 * @return array Registered additional fields (if any), empty array if none or if the object type could
	 *               not be inferred.
	 */
	protected function get_additional_fields( $object_type = null ) {

		if ( ! $object_type ) {
			$object_type = $this->get_object_type();
		}

		if ( ! $object_type ) {
			return array();
		}

		global $wp_rest_additional_fields;

		if ( ! $wp_rest_additional_fields || ! isset( $wp_rest_additional_fields[ $object_type ] ) ) {
			return array();
		}

		return $wp_rest_additional_fields[ $object_type ];
	}

	/**
	 * Retrieves the object type this controller is responsible for managing.
	 *
	 * @since 4.7.0
	 * @access protected
	 *
	 * @return string Object type for the controller.
	 */
	protected function get_object_type() {
		$schema = $this->get_item_schema();

		if ( ! $schema || ! isset( $schema['title'] ) ) {
			return null;
		}

		return $schema['title'];
	}

	/**
	 * Retrieves an array of endpoint arguments from the item schema for the controller.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param string $method Optional. HTTP method of the request. The arguments for `CREATABLE` requests are
	 *                       checked for required values and may fall-back to a given default, this is not done
	 *                       on `EDITABLE` requests. Default WP_REST_Server::CREATABLE.
	 * @return array Endpoint arguments.
	 */
	public function get_endpoint_args_for_item_schema( $method = WP_REST_Server::CREATABLE ) {

		$schema            = $this->get_item_schema();
		$schema_properties = ! empty( $schema['properties'] ) ? $schema['properties'] : array();
		$endpoint_args     = array();

		foreach ( $schema_properties as $field_id => $params ) {

			// Arguments specified as `readonly` are not allowed to be set.
			if ( ! empty( $params['readonly'] ) ) {
				continue;
			}

			$endpoint_args[ $field_id ] = array(
				'validate_callback' => 'rest_validate_request_arg',
				'sanitize_callback' => 'rest_sanitize_request_arg',
			);

			if ( isset( $params['description'] ) ) {
				$endpoint_args[ $field_id ]['description'] = $params['description'];
			}

			if ( WP_REST_Server::CREATABLE === $method && isset( $params['default'] ) ) {
				$endpoint_args[ $field_id ]['default'] = $params['default'];
			}

			if ( WP_REST_Server::CREATABLE === $method && ! empty( $params['required'] ) ) {
				$endpoint_args[ $field_id ]['required'] = true;
			}

			foreach ( array( 'type', 'format', 'enum', 'items' ) as $schema_prop ) {
				if ( isset( $params[ $schema_prop ] ) ) {
					$endpoint_args[ $field_id ][ $schema_prop ] = $params[ $schema_prop ];
				}
			}

			// Merge in any options provided by the schema property.
			if ( isset( $params['arg_options'] ) ) {

				// Only use required / default from arg_options on CREATABLE endpoints.
				if ( WP_REST_Server::CREATABLE !== $method ) {
					$params['arg_options'] = array_diff_key( $params['arg_options'], array( 'required' => '', 'default' => '' ) );
				}

				$endpoint_args[ $field_id ] = array_merge( $endpoint_args[ $field_id ], $params['arg_options'] );
			}
		}

		return $endpoint_args;
	}

	/**
	 * Sanitizes the slug value.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @internal We can't use sanitize_title() directly, as the second
	 * parameter is the fallback title, which would end up being set to the
	 * request object.
	 *
	 * @see https://github.com/WP-API/WP-API/issues/1585
	 *
	 * @todo Remove this in favour of https://core.trac.wordpress.org/ticket/34659
	 *
	 * @param string $slug Slug value passed in request.
	 * @return string Sanitized value for the slug.
	 */
	public function sanitize_slug( $slug ) {
		return sanitize_title( $slug );
	}
}
