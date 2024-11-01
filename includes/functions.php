<?php
/**
 * Restrict Content Pro Addon Functions.
 *
 * @package WPCW_RCP_Addon/Includes
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Restrict Content Pro Addon - Get Courses.
 *
 * @since 1.0.0
 *
 * @return array $courses The courses array.
 */
function wpcw_rcp_addon_get_courses() {
	$courses = array();

	if ( function_exists( 'wpcw' ) && isset( wpcw()->courses ) ) {
		$current_user_id = get_current_user_id();
		$course_args     = array( 'status' => 'publish' );

		if ( ! user_can( $current_user_id, 'manage_wpcw_settings' ) ) {
			$course_args['course_author'] = $current_user_id;
		}

		$course_objects = wpcw()->courses->get_courses( $course_args, true );
	} else {
		$course_objects = WPCW_courses_getCourseList( false );
	}

	if ( ! empty( $course_objects ) ) {
		foreach ( $course_objects as $course_object ) {
			if ( ! empty( $course_object->course_title ) ) {
				$courses[ $course_object->course_id ] = $course_object->course_title;
			}
		}
	}

	return $courses;
}