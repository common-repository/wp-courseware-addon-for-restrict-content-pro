<?php
/**
 * WP Courseware Restrict Content Pro Add-on Class
 *
 * @package WPCW_RCP_Addon/Includes
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPCW_RCP_Addon' ) ) {
	/**
	 * Class WPCW_RCP_Addon.
	 *
	 * @since 1.0.0
	 */
	final class WPCW_RCP_Addon {

		/**
		 * @var bool Can Load Addon?
		 * @since 1.0.0
		 */
		public $can_load = false;

		/**
		 * @var WPCW_RCP_Membership $membership The membership object.
		 * @since 1.0.0
		 */
		public $membership;

		/**
		 * @var WPCW_RCP_Menu_Courses $menu_courses The menu courses object.
		 * @since 1.0.0
		 */
		//public $menu_courses;

		/**
		 * Innitalize.
		 *
		 * @since 1.0.0
		 *
		 * @return WPCW_RCP_Addon $rcp_addon The addon object.
		 */
		public static function init() {
			$rcp_addon = new self();

			$rcp_addon->membership   = $rcp_addon->load_membership();
			//$rcp_addon->menu_courses = $rcp_addon->load_menu_courses();

			/**
			 * Action: Initalize Restrict Content Pro Addon.
			 *
			 * @since 1.0.0
			 *
			 * @param WPCW_RCP_Addon $rcp_addon The WPCW_RCP_Addon object.
			 */
			do_action( 'wpcw_rcp_addon_init', $rcp_addon );

			return $rcp_addon;
		}

		/**
		 * Load Compatability.
		 *
		 * @since 1.0.0
		 *
		 * @return null|WPCW_RCP_Membership Null or WPCW_RCP_Membership class object.
		 */
		public function load_membership() {
			// Load Class.
			$rcp_membership = new WPCW_RCP_Membership();

			// Check for WP Courseware.
			if ( ! $rcp_membership->found_wpcourseware() ) {
				$rcp_membership->attach_showWPCWNotDetectedMessage();
				return;
			}

			// Check for Restrict Content Pro.
			if ( ! $rcp_membership->found_membershipTool() ) {
				$rcp_membership->attach_showToolNotDetectedMessage();
				return;
			}

			/**
			 * Filter: WPCW Restrict Content Pro Addon Can Load Flag.
			 *
			 * @since 1.0.0
			 *
			 * @param bool $can_load If the addon can load.
			 */
			$this->can_load = apply_filters( 'wpcw_rcp_addon_can_load', true );

			// Attach to tools.
			$rcp_membership->attachToTools();

			/**
			 * Action: Load Membership.
			 *
			 * @since 1.0.0
			 *
			 * @param WPCW_RCP_Membership $rcp_membership The WPCW_RCP_Membership class object.
			 * @param WPCW_RCP_Addon      $this The WPCW_RCP_Addon class object.
			 */
			do_action( 'wpcw_rcp_addon_load_membership', $rcp_membership, $this );

			return $rcp_membership;
		}

		/**
		 * Load Menu Courses.
		 *
		 * @since 1.0.0
		 *
		 * @return null|WPCW_WC_Menu_Courses Null or the WPCW_WC_Menu_Courses class object.
		 */
		public function load_menu_courses() {
			if ( ! $this->can_load ) {
				return;
			}

			// Initialize Plugin.
			$rcp_menu_courses = new WPCW_RCP_Menu_Courses();
			$rcp_menu_courses->hooks();

			/**
			 * Action: Load Menu Courses.
			 *
			 * @since 1.0.0
			 *
			 * @param WPCW_RCP_Menu_Courses $rcp_menu_courses The WPCW_RCP_Menu_Courses class object.
			 * @param WPCW_RCP_Addon        $this The WPCW_RCP_Addon class object.
			 */
			do_action( 'wpcw_rcp_addon_load_menu_courses', $rcp_menu_courses, $this );

			return $rcp_menu_courses;
		}
	}
}
