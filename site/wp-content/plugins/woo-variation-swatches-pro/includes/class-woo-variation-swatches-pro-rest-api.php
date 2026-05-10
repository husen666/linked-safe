<?php
	defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woo_Variation_Swatches_Pro_REST_API' ) ) {
	class Woo_Variation_Swatches_Pro_REST_API {

		protected static $instance = null;

		protected $namespace                         = 'woo-variation-swatches/v1';
		protected $archive_editor_preview_rest_base  = '/archive-editor-preview/(?P<product_id>[\d]+)';
		protected $archive_product_rest_base         = '/archive-product/(?P<product_id>[\d]+)';
		protected $single_product_rest_base          = '/single-product/(?P<product_id>[\d]+)';
		protected $single_product_preview_rest_base  = '/single-product-preview';
		protected $archive_product_preview_rest_base = '/archive-product-preview';

		protected function __construct() {
			$this->includes();
			$this->hooks();
			$this->init();

			do_action( 'woo_variation_swatches_data_api_loaded', $this );
		}

		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		protected function includes() {
			require_once dirname( __FILE__ ) . '/class-woo-variation-swatches-pro-rest-api-wpml_support.php';
		}

		protected function hooks() {

			// /wp-json/woo-variation-swatches/v1/single-product/PRODUCT_ID

			add_action( 'rest_api_init', array( $this, 'process_extra_params' ), 1000 );
			add_action( 'rest_api_init', array( $this, 'register_archive_product_rest_route' ), 99 );
			add_action( 'rest_api_init', array( $this, 'register_archive_editor_preview_rest_route' ), 99 );
			add_action( 'rest_api_init', array( $this, 'register_single_product_rest_route' ), 99 );
			add_action( 'rest_api_init', array( $this, 'register_single_product_preview_rest_route' ) );
			add_action( 'rest_api_init', array( $this, 'register_archive_product_preview_rest_route' ) );
			add_filter( 'wp_rest_cache/allowed_endpoints', array( $this, 'rest_cache_allowed_endpoints' ) );
			add_action( 'litespeed_load_thirdparty', array( $this, 'litespeed_cache' ) );

			add_filter('rest_pre_serve_request', array($this, 'pre_serve_request'), 10, 4);
		}

		public function pre_serve_request($current, $response, $request, $server) {

			$cache_api_response = wc_string_to_bool(  woo_variation_swatches_pro()->get_option( 'cache_api_response_in_browser') );

			if ($request->get_method() === WP_REST_Server::READABLE && $cache_api_response) {
				$route = $request->get_route();


				if (str_starts_with($route, '/woo-variation-swatches/v1/archive-editor-preview')) {
					return $current;
				}

				if (str_starts_with($route, '/woo-variation-swatches/v1')) {
					$cache_api_max_age = absint(  woo_variation_swatches_pro()->get_option( 'cache_api_response_max_age') );

					$date_format = 'D, d M Y H:i:s';
					$expires     = MINUTE_IN_SECONDS * $cache_api_max_age;
					$server->send_header( 'Expires', gmdate( $date_format, time() + $expires ) . ' GMT' );
					$server->send_header( 'Cache-Control', 'public, max-age=' . $expires );
				}
			}

			return $current;
		}

		protected function init() {
			Woo_Variation_Swatches_Pro_REST_API_WPML_Support::instance();
		}

		public function get_args_params() {
			$params                 = array();
			$params[ 'product_id' ] = array(
				'description'       => esc_html__( 'Product ID.', 'woo-variation-swatches-pro' ),
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => array( $this, 'validate_request_arg' ),
			);

			return $params;
		}

		public function validate_request_arg( $param, $request, $key ) {
			return is_numeric( $param );
		}

		public function rest_cache_allowed_endpoints( $allowed_endpoints ) {

			if ( ! isset( $allowed_endpoints[ 'woo-variation-swatches/v1' ] ) ) {
				$allowed_endpoints[ 'woo-variation-swatches/v1' ][] = 'archive-product';
				$allowed_endpoints[ 'woo-variation-swatches/v1' ][] = 'single-product';
			}

			return $allowed_endpoints;
		}

		public function litespeed_cache() {
			add_action( 'litespeed_control_finalize', function () {

				if ( ! apply_filters( 'woo_variation_swatches_litespeed_cache_control', true ) ) {
					return;
				}
				// $is_rest = wc()->is_rest_api_request();
				if ( wp_is_json_request() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
					do_action( 'litespeed_control_set_cacheable', 'Woo Variation Swatches - REST API Set Cache' );
					do_action( 'litespeed_control_force_cacheable', 'Woo Variation Swatches - REST API Force Cache' );
				}
			}, 20 );
		}

		public function enable_cache_constant( $status = true ) {
			wc_maybe_define_constant( 'DONOTCACHEPAGE', ! $status );
			wc_maybe_define_constant( 'DONOTCACHEOBJECT', ! $status );
			wc_maybe_define_constant( 'DONOTCACHEDB', ! $status );
		}

		public function rest_header( $objects ) {

			$header = array(
				'X-Variation-Swatches-Header' => true,
			);

			return apply_filters( 'woo_variation_swatches_rest_api_headers', $header, $objects );
		}

		public function process_extra_params() {
			$extra_params_for_rest_uri = apply_filters( 'woo_variation_swatches_rest_add_extra_params', array() );
			if ( $extra_params_for_rest_uri ) {
				do_action( 'woo_variation_swatches_rest_process_extra_params', $extra_params_for_rest_uri );
			}
		}

		public function register_archive_product_rest_route() {
			register_rest_route( $this->namespace, $this->archive_product_rest_base, array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'archive_product_variations_for_response' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_args_params(),
			) );
		}

		public function register_archive_editor_preview_rest_route() {
			register_rest_route( $this->namespace, $this->archive_editor_preview_rest_base, array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'archive_editor_preview_for_response' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_args_params(),
			) );
		}

		public function register_single_product_rest_route() {
			register_rest_route( $this->namespace, $this->single_product_rest_base, array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'single_product_variations_for_response' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_args_params(),
			) );
		}

		public function register_single_product_preview_rest_route() {
			register_rest_route( $this->namespace, $this->single_product_preview_rest_base, array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'single_product_preview_for_response' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_args_params(),
			) );
		}

		public function register_archive_product_preview_rest_route() {
			register_rest_route( $this->namespace, $this->archive_product_preview_rest_base, array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'archive_product_preview_for_response' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_args_params(),
			) );
		}

		public function single_product_variations_for_response( WP_REST_Request $request ) {

			$product_id = absint( $request->get_param( 'product_id' ) );

			if ( WP_REST_Server::READABLE !== $request->get_method() ) {
				return new WP_Error( 'readble_only', 'We process only READABLE request', array( 'status' => 403 ) );
			}

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new WP_REST_Response( array(
												 'message' => 'Product was not found',
											 ), 404 );
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return new WP_REST_Response( array(
												 'message' => 'Only variable product is allowed',
											 ), 403 );
			}


			$response_objects = $product->get_available_variations();

			$headers  = $this->rest_header( $product );
			$response = rest_ensure_response( $response_objects );
			$response->set_headers( $headers );

			return $response;
		}

		public function single_product_preview_for_response( WP_REST_Request $request ) {

			$product_id = absint( $request->get_param( 'product_id' ) );

			if ( WP_REST_Server::READABLE !== $request->get_method() ) {
				return new WP_Error( 'readble_only', 'We process only READABLE request', array( 'status' => 403 ) );
			}

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new WP_REST_Response( array(
												 'message' => 'Product was not found',
											 ), 404 );
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return new WP_REST_Response( array(
												 'message' => 'Only variable product is allowed',
											 ), 403 );
			}


			$variation_id = woo_variation_swatches_pro()->get_frontend()->get_archive_page()->find_matching_product_variation( $product, wp_unslash( $request->get_params() ) );

			$response_objects = $variation_id ? woo_variation_swatches_pro()->get_frontend()->get_archive_page()->get_available_preview_variation( $variation_id, $product ) : false;

			$headers  = $this->rest_header( $product );
			$response = rest_ensure_response( $response_objects );
			$response->set_headers( $headers );

			return $response;
		}

		public function archive_product_variations_for_response( WP_REST_Request $request ) {

			$product_id = absint( $request->get_param( 'product_id' ) );

			if ( WP_REST_Server::READABLE !== $request->get_method() ) {
				return new WP_Error( 'readble_only', 'We process only READABLE request', array( 'status' => 403 ) );
			}

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new WP_REST_Response( array(
												 'message' => 'Product was not found',
											 ), 404 );
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return new WP_REST_Response( array(
												 'message' => 'Only variable product is allowed',
											 ), 403 );
			}


			$response_objects = woo_variation_swatches_pro()->get_frontend()->get_archive_page()->get_available_variations( $product );

			$headers  = $this->rest_header( $product );
			$response = rest_ensure_response( $response_objects );
			$response->set_headers( $headers );

			return $response;
		}

		public function archive_editor_preview_for_response( WP_REST_Request $request ) {

			$product_id = absint( $request->get_param( 'product_id' ) );

			if ( WP_REST_Server::READABLE !== $request->get_method() ) {
				return new WP_Error( 'readble_only', 'We process only READABLE request', array( 'status' => 403 ) );
			}

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new WP_REST_Response( array(
												 'message' => 'Product was not found',
											 ), 404 );
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return '<!-- Not Variable Product -->';
			}


			ob_start();
			woo_variation_swatches_pro()->get_frontend()->get_archive_page()->editor_preview_swatches( $product );
			$response_objects = ob_get_clean();


			$headers  = $this->rest_header( $product );
			$response = rest_ensure_response( $response_objects );
			$response->set_headers( $headers );

			return $response;
		}

		public function archive_product_preview_for_response( WP_REST_Request $request ) {

			$product_id = absint( $request->get_param( 'product_id' ) );

			if ( WP_REST_Server::READABLE !== $request->get_method() ) {
				return new WP_Error( 'readble_only', 'We process only READABLE request', array( 'status' => 403 ) );
			}

			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				return new WP_REST_Response( array(
												 'message' => 'Product was not found',
											 ), 404 );
			}

			if ( ! $product->is_type( 'variable' ) ) {
				return new WP_REST_Response( array(
												 'message' => 'Only variable product is allowed',
											 ), 403 );
			}


			$variation_id     = woo_variation_swatches_pro()->get_frontend()->get_archive_page()->find_matching_product_variation( $product, wp_unslash( $request->get_params() ) );
			$response_objects = $variation_id ? woo_variation_swatches_pro()->get_frontend()->get_archive_page()->get_available_preview_variation( $variation_id, $product ) : false;

			$headers  = $this->rest_header( $product );
			$response = rest_ensure_response( $response_objects );
			$response->set_headers( $headers );

			return $response;
		}
	}
}
