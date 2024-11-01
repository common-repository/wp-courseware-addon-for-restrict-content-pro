<?php
/**
 * WP Courseware Restrict Content Pro Membership Class.
 *
 *
 * @package WPCW_RCP_Addon/Includes
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCW_RCP_Membership' ) ) {
	/**
	 * Class WPCW_RCP_Membership.
	 *
	 * Class that handles the specifics of the Restrict Content Pro plugin and
	 * handling the data for memberships for that plugin.
	 *
	 * @since 1.0.0
	 */
	class WPCW_RCP_Membership extends WPCW_RCP_Members {

		/**
		 * @var string Add Version.
		 * @since 1.0.0
		 */
		protected $addon_version = '1.0.0';

		/**
		 * @var string Addon Id.
		 * @since 1.0.0
		 */
		protected $addon_id = 'WPCW_restrict_contnet_pro';

		/**
		 * @var string Addon Name.
		 * @since 1.0.0
		 */
		protected $addon_name = 'Restrict Content Pro';

		/**
		 * WPCW_MP_Membership constructor.
		 *
		 * @since 1.0.0
		 */
		function __construct() {
			parent::__construct( $this->addon_name, $this->addon_id, $this->addon_version );
		}

		/**
	 	* Remove RCP Metabox from course and unit edit screens.
		 */
		public function wpcw_rcp_exclude_post_types( $excluded_post_types )
		{
			$wpcw_post_types = array('course_unit','wpcw_course');
			if ( $excluded_post_types ){
				foreach( $wpcw_post_types as $post_type ){
					if( ! in_array( $post_type, $excluded_post_types ) ){
						$excluded_post_types[] = $post_type;
					}
				}
				return $excluded_post_types;
			}
		}


		/**
		 * Get a list of RCP Memberships.
		 */
		protected function getMembershipLevels()
		{
			//Get RCP Levels
		    $get_levels = new RCP_Levels();
			$levels = $get_levels->get_levels( array( 'status'  => 'active' ) );

			if ($levels && count($levels) > 0)
			{
				$levelDataStructured = array();
				
				// Format the data in a way that we expect and can process
				foreach ($levels as $level)
				{
					$levelItem = array();
					$levelItem['name'] 	= $level->name ;
					$levelItem['id'] 	= $level->id;
					$levelDataStructured[$levelItem['id']]  = $levelItem;
				}
				
				return $levelDataStructured;
			}
			
			return false;
		}


		/**
		 * Function called to attach hooks for handling when a user is updated or created.
		 */	
		protected function attach_updateUserCourseAccess()
		{
				// For RCP < 3.0
		    	add_action( 'rcp_set_status', array( $this, 'handle_updateUserCourseAccess_bc' ), 10, 4 );
		    	// For RCP > 3.0+
				add_action( 'rcp_transition_membership_status', array( $this, 'handle_updateUserCourseAccess'), 10 , 3 );
		}


		/**
			 * Assign selected courses to members of a paticular level.
			 * @param Level ID in which members will get courses enrollment adjusted.
			 */
		protected function retroactive_assignment($level_ID)
		{
			global $wpdb;

			$page = new PageBuilder( false );

			$batch = 50;
			$step  = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
			$count = isset( $_GET['count'] ) ? absint( $_GET['count'] ) : 0;
			$steps = isset( $_GET['steps'] ) ? wp_strip_all_tags( $_GET['steps'] ) : 'continue';

			$summary_url = add_query_arg( array( 'page' => $this->extensionID ), admin_url( 'admin.php' ) );
			$course_url  = add_query_arg( array( 'page' => $this->extensionID, 'level_id' => $level_ID ), admin_url( 'admin.php' ) );

			if ( 'finished' === $steps ) {
				$page->showMessage(
					esc_html__( 'Course access settings successfully updated.', 'wpcw-rcp-addon' )
					. '<br />' .
					esc_html__( 'All existing members were retroactively enrolled into the selected courses successfully.', 'wpcw-rcp-addon' )
					. '<br /><br />' .
					/* translators: %s - Summary Url. */
					sprintf( __( 'Want to return to the <a href="%s">Course Access Settings</a>?', 'wpcw-rcp-addon' ), $summary_url )
				);

				printf( '<br /><a href="%s" class="button-primary">%s</a>', $course_url, __( '&laquo; Return to Course', 'wpcw-rcp-addon' ) );

				return;
			}

			if ( isset( $_POST['retroactive_assignment'] ) ) {
				$step  = 1;
				$count = 0;
				$steps = 'continue';
			}

			//Get active membership levels for member
			$args = array(
						'object_id' => $level_ID,
						'status' => 'active',
						'number' => $batch,
						'offset' => $count,

			);
			$memberships = rcp_get_memberships( $args );

			if ( ! $memberships && ! isset( $_GET['action'] ) ) {
				$page->showMessage( esc_html__( 'No existing members found for the specified membership level.', 'wpcw-rcp-addon' ) );

				return;
			}

			if ( $memberships && 'continue' === $steps ) {
				if ( count( $memberships ) < $batch ) {
					$steps = 'finished';
				}

				foreach( $memberships as $membership ){
					//Get active membership levels for member
					$args = array(
								'customer_id' => $membership->get_customer_id(),
								'status' => 'active',

					);
					$membershipLevels = rcp_get_memberships( $args );

					//Build array to enroll into proper courses
					$userLevels = array();

						foreach( $membershipLevels as $key => $membershipLevel ) {
							$userLevels[$key] = $membershipLevel->get_object_id();
						}

					// Over to the parent class to handle the sync of data.
					parent::handle_courseSync( $membership->get_user_id(), $userLevels, 'sync' );
					$count += 1;
				}
			$step += 1;
			} else {
				$steps = 'finished';
			}

			$page->showMessage( esc_html__( 'Please wait. Retroactively updating existing members...', 'wpcw-rcp-addon' ) );

			$location_url = add_query_arg( array(
				'page'     => $this->extensionID,
				'level_id' => $level_ID,
				'step'     => $step,
				'count'    => $count,
				'steps'    => $steps,
				'action'   => 'retroactiveassignment'
			), admin_url( 'admin.php' ) );

			?>
			<script type="text/javascript">
				setTimeout( function () {
					document.location.href = "<?php echo $location_url; ?>";
				}, 1000 );
			</script>
			<?php

		}


		/**
		 * Function just for handling the membership callback, to interpret the parameters
		 * for the class to take over.
		 * 
		 * @param string $old_status old status of membership level
		 * @param string $new_status new status of membership level
		 * @param int $member_id customer ID
		 * @param object $member
		 */
		public function handle_updateUserCourseAccess_bc( $new_status, $member_id, $old_status, $member )
		{
			//Get user ID's
			$user_id = $member->ID;

			//Get active membership levels for member
			$args = array(
						'customer_id' => $member_id,
						'status' => 'active',

			);
			$membershipLevels = rcp_get_memberships( $args );

			//Build array to enroll into proper courses
			$userLevels = array();

				foreach( $membershipLevels as $key => $membershipLevel ) {
					$userLevels[$key] = $membershipLevel->get_object_id();
				}

			// Over to the parent class to handle the sync of data.
			parent::handle_courseSync( $user_id, $userLevels );
		}


		/**
		 * Function just for handling the membership callback, to interpret the parameters
		 * for the class to take over.
		 * 
		 * @param string $old_status old status of membership level
		 * @param string $new_status new status of membership level
		 * @param int $membership_id ID of the membership (transaction ID)
		 */
		public function handle_updateUserCourseAccess( $old_status, $new_status, $membership_id )
		{
			//Get member and user ID's
			$membership = rcp_get_membership( $membership_id );
			$customer_id = $membership->get_customer_id();
			$user_id = $membership->get_user_id();

			//Get active membership levels for member
			$args = array(
						'customer_id' => $customer_id,
						'status' => 'active',

			);
			$membershipLevels = rcp_get_memberships( $args );

			//Build array to enroll into proper courses
			$userLevels = array();

				foreach( $membershipLevels as $key => $membershipLevel ) {
					$userLevels[$key] = $membershipLevel->get_object_id();
				}

			// Over to the parent class to handle the sync of data.
			parent::handle_courseSync( $user_id, $userLevels );
		}

		/**
		 * Detect presence of the Restrict Content Pro plugin.
		 */
		public function found_membershipTool()
		{
			return class_exists('Restrict_Content_Pro');
		}
	}
}


if ( ! class_exists( 'WPCW_Members_Restrict_Content_Pro' ) ) {
	/**
	 * Class WPCW_Members_Restrict_Content_Pro.
	 *
	 * Included for compatability with old addon.
	 *
	 * @since 1.0.0
	 */
	class WPCW_Members_Restrict_Content_Pro extends WPCW_RCP_Membership {

	}
}