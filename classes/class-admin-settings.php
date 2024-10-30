<?php
/*
 * Create Custom Rest API Endpoints
 */
class KairaSiteChat_Rest_Route {
	public function __construct() {
		add_action('rest_api_init', [$this, 'kaira_scp_create_rest_routes']);
	}

	/*
	 * Create REST API routes for get & save
	 */
	public function kaira_scp_create_rest_routes() {
		register_rest_route('wascrapi/v1', '/settings', [
			'methods' => 'GET',
			'callback' => [$this, 'kaira_scp_get_settings'],
			'permission_callback' => [$this, 'kaira_scp_get_settings_permission'],
		]);
		register_rest_route('wascrapi/v1', '/settings', [
			'methods' => 'POST',
			'callback' => [$this, 'kaira_scp_save_settings'],
			'permission_callback' => [$this, 'kaira_scp_save_settings_permission'],
		]);
		register_rest_route('wascrapi/v1', '/delete', [
			'methods' => 'DELETE',
			'callback' => [$this, 'kaira_scp_delete_settings'],
			'permission_callback' => [$this, 'kaira_scp_save_settings_permission'],
		]);
		register_rest_route( 'wascrapi/v1', '/pages', [
			'methods' => 'GET',
			'callback' => [$this, 'kaira_scp_get_posts'],
			'permission_callback' => [$this, 'kaira_scp_get_settings_permission'],
		]);
		register_rest_route( 'wascrapi/v1', '/image/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [$this, 'kaira_scp_get_imagebyid'],
			'permission_callback' => [$this, 'kaira_scp_get_settings_permission'],
		]);
	}

	/*
	 * Get saved options from database for /src/admin/settings/Settings.js
	 */
	public function kaira_scp_get_settings() {
		$wascPluginOptions = get_option('kaira_sitechat_options');

		if (!$wascPluginOptions)
			return;

		return rest_ensure_response($wascPluginOptions);
	}

	/*
	 * Allow permissions for get options
	 */
	public function kaira_scp_get_settings_permission() {
		return true;
	}

	/*
	 * Save settings as JSON string from /src/admin/settings/Settings.js
	 */
	public function kaira_scp_save_settings() {
		$req = file_get_contents('php://input');
		$reqData = json_decode($req, true);

		update_option('kaira_sitechat_options', $reqData);

		return rest_ensure_response('Success!');
	}

	/*
	 * Set save permissions for admin users
	 */
	public function kaira_scp_save_settings_permission() {
		return current_user_can('publish_posts') ? true : false;
	}

	/*
	 * Delete the plugin settings
	 */
	public function kaira_scp_delete_settings() {
		delete_option('kaira_sitechat_options');

		return rest_ensure_response('Success!');
	}

	// public function_sanitize_setting($value, $type) {
		
	// }
	
	/*
	 * Get & Sort posts for 'Post Select' Component
	 */
	function kaira_scp_get_posts($request) {
		// $post_types = explode("-", $request->get_param( 'post_type' ));
		$post_types = array("post", "page", "product");
		$posts_data = array();

		foreach($post_types as $post_type) {
			$posts_options = array();
			$posts = get_posts( array(
					'posts_per_page' => -1,
					'post_type' => $post_type,
				)
			);

			foreach( $posts as $post ) {
				$posts_options[] = array(
					'value' => $post->ID,
					'label' => $post->post_title,
				);
			}

			$posts_data[] = array(
				'label' => $post_type . 's',
				'options' => $posts_options,
			);
		}
		return $posts_data;
	}

	/*
	 * Get Image by ID for InputMediaUpload Component
	 */
	function kaira_scp_get_imagebyid($request) {
		$image_id = $request->get_param( 'id' );
		$image_src = wp_get_attachment_image_src( $image_id, 'medium' );

		$image_data = array(
			'id' => $image_id,
			'src' => $image_src[0],
		);

		return $image_data;
	}
}
new KairaSiteChat_Rest_Route();
