<?php
/*
 * This file is part of wp-mpdf.
 * wp-mpdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see <http://www.gnu.org/licenses/>.
 */

function mpdf_cron_generate_pdfs() {
	global $wpdb;

	//Check if Caching is enabled or not
	if ( get_option( 'mpdf_caching' ) != true ) {
		echo "No caching enabled\n";

		return;
	}

	$oldUser = wp_get_current_user();
	try {
		//Do login if is whished
		if ( get_option( 'mpdf_cron_user' ) != '' ) {
			$userId = get_option( 'mpdf_cron_user' );
			if ( get_option( 'mpdf_cron_user' ) == 'auto' ) {
				$aUsersID = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->users . ' LIMIT 1' ) );
				foreach ( $aUsersID as $iUserID ) {
					$userId = $iUserID;
				}
			}

			wp_set_current_user( $userId );
		}

		//Cache the posts
		$_GET['output'] = 'pdf';
		echo "Start cache creating\n";

		$posts = get_posts( 'numberposts=-1&order=ASC&orderby=title' );
		foreach ( $posts as $post ) {
			if ( $post->post_title == '' ) {
				echo "Skip post creating: No Title (" . $post->ID . ")\n";
				continue;
			}

			echo "Create cache for post (" . $post->ID . ")\n";


			query_posts( 'p=' . $post->ID );
			mpdf_exec( 'false' );
		}

		echo "Caching finished\n";
	} finally {
		wp_set_current_user( $oldUser->ID );
	}
}
