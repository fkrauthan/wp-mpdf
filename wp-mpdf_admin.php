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

function mpdf_admin_display() {
	echo '<h1>Configure wp-mpdf</h1>';
	echo '<table style="width: 100%;" border="0">';
	echo '<tr><td style="vertical-align: top;">';
	mpdf_admin_options();
	echo '<br />';
	mpdf_admin_stats();
	echo '</td><td style="vertical-align: top;">';
	mpdf_admin_allowedprintedpages();
	echo '<br />';
	mpdf_admin_loginneededpages();
	echo '</td><td style="vertical-align: top;">';
	mpdf_admin_cache();
	echo '<br />';
	mpdf_admin_pdfname();
	echo '</td></tr></table>';
}

function mpdf_admin_find_themes() {
	$result        = [];
	$existingFiles = [];
	$themes_path   = [
		WP_CONTENT_DIR . '/wp-mpdf-themes/',
		plugin_dir_path( __FILE__ ) . '/themes/',
	];

	foreach ( $themes_path as $path ) {
		if ( is_dir( $path ) && $dir = opendir( $path ) ) {
			while ( $file = readdir( $dir ) ) {
				if ( is_dir( $path . $file ) || $file === '.' || $file === '..' ) {
					continue;
				}

				if ( mpdf_extension( $file ) !== 'php' || in_array( $file, $existingFiles ) ) {
					continue;
				}

				$filename        = mpdf_filename( $file );
				$existingFiles[] = $file;

				$result[] = $filename;
			}
		}
	}

	return $result;
}

function mpdf_admin_find_users() {
	global $wpdb;
	$userIds = $wpdb->get_results( 'SELECT ID FROM ' . $wpdb->users . ' ORDER BY user_nicename ASC' );

	$result = array();
	foreach ( $userIds as $iUserID ) {
		$result[] = $iUserID->ID;
	}

	return $result;
}

function mpdf_admin_options() {
	echo '<h2>Options</h2>';

	$themes           = mpdf_admin_find_themes();
	$CODEPAGES_ARRAY  = [
		'utf-8',
		'win-1251',
		'win-1252',
		'iso-8859-2',
		'iso-8859-4',
		'iso-8859-7',
		'iso-8859-9',
		'big5',
		'gbk',
		'uhc',
		'shift_jis',
	];
	$aUsersID         = mpdf_admin_find_users();
	$allowedSchedules = wp_get_schedules();

	if ( isset( $_POST['save_options'] ) ) {
		if ( ! isset( $_POST['wp_mpdf_noncename'] ) || ! wp_verify_nonce( $_POST['wp_mpdf_noncename'], plugin_basename( __FILE__ ) ) ) {
			return 'Illegal Access!';
		}

		if ( in_array( $_POST['theme'], $themes ) ) {
			update_option( 'mpdf_theme', $_POST['theme'] );
		}
		if ( in_array( $_POST['codepage'], $CODEPAGES_ARRAY ) ) {
			update_option( 'mpdf_code_page', $_POST['codepage'] );
		}
		if ( in_array( $_POST['cron_user'], array_merge( array( '', 'auto' ), $aUsersID ) ) ) {
			update_option( 'mpdf_cron_user', $_POST['cron_user'] );
		}
		update_option( 'mpdf_caching', isset( $_POST['caching'] ) );
		update_option( 'mpdf_geshi', isset( $_POST['geshi'] ) );
		update_option( 'mpdf_geshi_linenumbers', isset( $_POST['geshi_linenumbers'] ) );
		update_option( 'mpdf_stats', isset( $_POST['stats'] ) );
		update_option( 'mpdf_debug', isset( $_POST['debug'] ) );

		if ( ! isset( $_POST['enable_cron'] ) ) {
			wp_clear_scheduled_hook( 'mpdf_generate_pdfs_hook' );
		} else {
			if ( array_key_exists( $_POST['enable_cron'], $allowedSchedules ) ) {
				wp_schedule_event( time(), $_POST['enable_cron'], 'mpdf_generate_pdfs_hook' );
			}
		}

		if ( isset( $_POST['allow_all'] ) ) {
			update_option( 'mpdf_allow_all', true );
		} else {
			if ( is_numeric( $_POST['use_list_as'] ) ) {
				update_option( 'mpdf_allow_all', $_POST['use_list_as'] );
			}
		}

		if ( ! isset( $_POST['need_login'] ) ) {
			update_option( 'mpdf_need_login', false );
		} else {
			if ( is_numeric( $_POST['login_use_list_as'] ) ) {
				update_option( 'mpdf_need_login', $_POST['login_use_list_as'] );
			}
		}

		echo '<p style="color: green;">Options Saved</p>';
	}

	echo '<form action="?page=' . esc_attr($_GET['page']) . '" method="post">';
	echo '<input type="hidden" name="wp_mpdf_noncename" id="wp_mpdf_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
	echo '<table border="0">';
	echo '<tr><td>Theme: </td><td>';
	echo '<select name="theme">';

	// Search for Themes
	foreach ( $themes as $theme ) {
		echo '<option value="' . $theme . '" ' . selected( get_option( 'mpdf_theme' ), $theme, false ) . '>';
		echo str_replace( '_', ' ', $theme ) . '</option>';
	}

	echo '</select>';
	echo '</td></tr>';

	echo '<tr><td>Codepage: </td><td>';
	echo '<select name="codepage">';
	$cur_cp = get_option( 'mpdf_code_page' );
	if ( $cur_cp == '' ) {
		$cur_cp = 'utf-8';
	}
	foreach ( $CODEPAGES_ARRAY as $cp ) {
		echo '<option value="' . $cp . '" ';
		if ( $cur_cp == $cp ) {
			echo 'selected="selected"';
		}
		echo '>' . $cp . '</option>';
	}
	echo '</select>';
	echo '</td></tr>';

	echo '<tr><td>Caching: </td><td><input type="checkbox" name="caching" ';
	if ( get_option( 'mpdf_caching' ) == true ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';
	echo '<tr><td>Download stats: </td><td><input type="checkbox" name="stats" ';
	if ( get_option( 'mpdf_stats' ) == true ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';
	echo '<tr><td>Geshi Parsing: </td><td><input type="checkbox" name="geshi" ';
	if ( get_option( 'mpdf_geshi' ) == true ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';
	echo '<tr><td>Geshi Line numbers: </td><td><input type="checkbox" name="geshi_linenumbers" ';
	if ( get_option( 'mpdf_geshi_linenumbers' ) == true ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';
	echo '<tr><td>Allow to Print all Pages: </td><td><input type="checkbox" name="allow_all" ';
	if ( get_option( 'mpdf_allow_all' ) == 1 ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';
	echo '<tr><td>If not use list as: </td><td><select name="use_list_as">';
	echo '<option value="2" ';
	if ( get_option( 'mpdf_allow_all' ) == 2 ) {
		echo 'selected="selected"';
	}
	echo '>Whitelist</option>';
	echo '<option value="3" ';
	if ( get_option( 'mpdf_allow_all' ) == 3 ) {
		echo 'selected="selected"';
	}
	echo '>Blacklist</option>';
	echo '</select></td></tr>';


	echo '<tr><td>Need login: </td><td><input type="checkbox" name="need_login" ';
	if ( get_option( 'mpdf_need_login' ) != 0 ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';

	echo '<tr><td>If checked use list as: </td><td><select name="login_use_list_as">';
	echo '<option value="2" ';
	if ( get_option( 'mpdf_need_login' ) == 2 ) {
		echo 'selected="selected"';
	}
	echo '>Whitelist</option>';
	echo '<option value="3" ';
	if ( get_option( 'mpdf_need_login' ) == 3 ) {
		echo 'selected="selected"';
	}
	echo '>Blacklist</option>';
	echo '</select></td></tr>';

	echo '<tr><td>Enable Debuging: </td><td><input type="checkbox" name="debug" ';
	if ( get_option( 'mpdf_debug' ) == true ) {
		echo 'checked="checked"';
	}
	echo '/></td></tr>';

	//Enable cron job
	echo '<tr><td>Enable pdf generating cron job: </td><td>';
	if ( ! wp_next_scheduled( 'mpdf_generate_pdfs_hook' ) ) {
		echo '<select name="enable_cron"><option value="" selected="selected">Off</option>';
		foreach ( $allowedSchedules as $schedule => $scheduleInfo ) {
			echo '<option value="' . $schedule . '">' . $scheduleInfo['display'] . '</option>';
		}
		echo '</select>';
	} else {
		echo '<input type="checkbox" name="enable_cron" checked=checked />';
	}
	echo '</td></tr>';

	//Cron generating User
	echo '<tr><td>User for generating per Cron: </td><td><select name="cron_user">';
	echo '<option value="" ';
	if ( get_option( 'mpdf_cron_user' ) == '' ) {
		echo 'selected="selected"';
	}
	echo '>None</option>';
	echo '<option value="auto" ';
	if ( get_option( 'mpdf_cron_user' ) == 'auto' ) {
		echo 'selected="selected"';
	}
	echo '>Auto</option>';
	foreach ( $aUsersID as $iUserID ) {
		$user = get_userdata( $iUserID );

		echo '<option value="' . $iUserID . '" ';
		if ( $iUserID == get_option( 'mpdf_cron_user' ) ) {
			echo 'selected="selected"';
		}
		echo '>' . esc_html( $user->user_nicename ) . '</option>';
	}
	echo '</select></td></tr>';

	echo '</table>';
	echo '<input type="submit" value="Save" name="save_options" /> <input type="reset" />';
	echo '</form>';
}

function mpdf_admin_listposts() {
	echo '<select name="post">';
	echo '<optgroup label="Draft">';
	$posts = get_posts( 'numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=draft' );
	foreach ( $posts as $post ) {
		if ( $post->post_type == 'attachment' ) {
			continue;
		}

		echo '<option value="' . esc_attr( $post->ID ) . '">' . esc_html( $post->post_title ) . '</option>';
	}
	echo '</optgroup>';
	echo '<optgroup label="Future">';
	$posts = get_posts( 'numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=future' );
	foreach ( $posts as $post ) {
		if ( $post->post_type == 'attachment' ) {
			continue;
		}

		echo '<option value="' . esc_attr( $post->ID ) . '">' . esc_html( $post->post_title ) . '</option>';
	}
	echo '</optgroup>';
	echo '<optgroup label="Private">';
	$posts = get_posts( 'numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=private' );
	foreach ( $posts as $post ) {
		if ( $post->post_type == 'attachment' ) {
			continue;
		}

		echo '<option value="' . esc_attr( $post->ID ) . '">' . esc_html( $post->post_title ) . '</option>';
	}
	echo '</optgroup>';
	echo '<optgroup label="Publish">';
	$posts = get_posts( 'numberposts=-1&order=ASC&orderby=title&post_type=any&post_status=publish' );
	foreach ( $posts as $post ) {
		if ( $post->post_type == 'attachment' ) {
			continue;
		}

		echo '<option value="' . esc_attr( $post->ID ) . '">' . esc_html( $post->post_title ) . '</option>';
	}
	echo '</optgroup>';
	echo '</select>';
}

function mpdf_admin_contains_key( $source, $keys ) {
	foreach ( $keys as $key ) {
		if ( array_key_exists( $key, $source ) ) {
			return true;
		}
	}

	return false;
}

function mpdf_admin_check_nonce_if_param_exists( $source, $paramsPresent ) {
	if ( ! mpdf_admin_contains_key( $source, $paramsPresent ) ) {
		return true;
	}

	if ( ! isset( $source['wp_mpdf_noncename'] ) || ! wp_verify_nonce( $source['wp_mpdf_noncename'], plugin_basename( __FILE__ ) ) ) {
		return false;
	}

	return true;
}

function check_nonce_if_param_exists_in_request( $getParams, $postParams ) {
	if ( ! mpdf_admin_check_nonce_if_param_exists( $_POST, $postParams ) ) {
		return false;
	}

	return mpdf_admin_check_nonce_if_param_exists( $_GET, $getParams );
}

function mpdf_admin_allowedprintedpages() {
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;

	echo '<h2>Black/White List - Printed Pages</h2>';

	if ( ! check_nonce_if_param_exists_in_request( array(
		'delallowedprintedpage',
		'clearallowedpage'
	), array( 'addallowedpage' ) ) ) {
		echo '<p style="color: red;">Illegal Access!</p>';

		return;
	}
	if ( isset( $_GET['delallowedprintedpage'] ) && is_numeric( $_GET['delallowedprintedpage'] ) ) {
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . $table_name . ' SET general=0 WHERE id=%d LIMIT 1', $_GET['delallowedprintedpage'] ) );

		echo '<p style="color: green;">Delete allowed Page with id "' . esc_html($_GET['delallowedprintedpage']) . '"</p>';
	}
	if ( isset( $_GET['clearallowedpage'] ) ) {
		$wpdb->query( 'UPDATE ' . $table_name . ' SET general=0' );

		echo '<p style="color: green;">All posts are deleted from the black/white list.</p>';
	}
	if ( isset( $_POST['addallowedpage'] ) ) {
		$page = get_post( $_POST['post'] );
		if ( $page != null ) {
			$sql   = 'SELECT id FROM ' . $table_name . ' WHERE post_id=%d AND post_type=%s LIMIT 1';
			$db_id = $wpdb->get_var( $wpdb->prepare( $sql, $page->ID, $page->post_type ) );
			if ( $db_id == null ) {
				$sql = 'INSERT INTO ' . $table_name . ' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, 1, 0, "", 0)';
				$wpdb->query( $wpdb->prepare( $sql, $page->post_type, $page->ID ) );
			} else {
				$sql = 'UPDATE ' . $table_name . ' SET general=1 WHERE id=%d LIMIT 1';
				$wpdb->query( $wpdb->prepare( $sql, $db_id ) );
			}

			echo '<p style="color: green;">Post has been added to the database.</p>';
		} else {
			echo '<p style="color: red;">Post not found.</p>';
		}
	} else {
		if ( isset( $_GET['addallowedpage'] ) ) {
			echo '<form action="?page=' . esc_attr($_GET['page']) . '" method="post">';
			echo 'Post: ';
			mpdf_admin_listposts();
			echo '<br />';
			echo '<input type="hidden" name="wp_mpdf_noncename" id="wp_mpdf_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
			echo '<input type="submit" value="Add Entry" name="addallowedpage" />';
			echo '</form>';
			echo '<br />';
		}
	}

	$nonceURL = 'wp_mpdf_noncename=' . wp_create_nonce( plugin_basename( __FILE__ ) );
	echo '<a href="?page=' . esc_attr($_GET['page']) . '&amp;addallowedpage=1">New Entry</a> <a href="?page=' . esc_attr($_GET['page']) . '&amp;clearallowedpage=1&amp;' . $nonceURL . '">Clear All Entries</a>';
	echo '<table border="1">';
	$sql  = 'SELECT id,post_type,post_id FROM ' . $table_name . ' WHERE general=1';
	$data = $wpdb->get_results( $sql, OBJECT );
	for ( $i = 0; $i < count( $data ); $i ++ ) {
		echo '<tr>';
		echo '<td>' . esc_html( $data[ $i ]->post_type ) . '</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		if ( $data[ $i ]->post_type == 'post' ) {
			$post = get_post( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $post->post_title ) . '</td>';
		} else {
			$page = get_page( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $page->post_title ) . '</td>';
		}
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		echo '<td><a href="?page=' . esc_attr($_GET['page']) . '&amp;delallowedprintedpage=' . esc_attr( $data[ $i ]->id ) . '&amp;' . $nonceURL . '">Delete</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

function mpdf_admin_pdfname() {
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;

	echo '<h2>Custom pdf filenames</h2>';

	if ( ! check_nonce_if_param_exists_in_request( array(
		'delcustomname',
		'clearcustomname'
	), array( 'addcustomname' ) ) ) {
		echo '<p style="color: red;">Illegal Access!</p>';

		return;
	}
	if ( isset( $_GET['delcustomname'] ) && is_numeric( $_GET['delcustomname'] ) ) {
		$wpdb->query( $wpdb->prepare('UPDATE ' . $table_name . ' SET pdfname="" WHERE id=%d LIMIT 1', $_GET['delcustomname'] ) );

		echo '<p style="color: green;">Delete pdf name from page with id "' . esc_html($_GET['delcustomname']) . '"</p>';
	}
	if ( isset( $_GET['clearcustomname'] ) ) {
		$wpdb->query( 'UPDATE ' . $table_name . ' SET pdfname=""' );

		echo '<p style="color: green;">All pdf names from posts are deleted.</p>';
	}
	if ( isset( $_POST['addcustomname'] ) ) {
		$page = get_post( $_POST['post'] );
		if ( $page != null ) {
			$sql   = 'SELECT id FROM ' . $table_name . ' WHERE post_id=%d AND post_type=%s LIMIT 1';
			$db_id = $wpdb->get_var( $wpdb->prepare( $sql, $page->ID, $page->post_type ) );

			$pdfname = sanitize_file_name( $_POST['pdfname'] );
			if ( $db_id == null ) {
				$sql = 'INSERT INTO ' . $table_name . ' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, 0, 0, %s, 0)';
				$wpdb->query( $wpdb->prepare( $sql, $page->post_type, $page->ID, $pdfname ) );
			} else {
				$sql = 'UPDATE ' . $table_name . ' SET pdfname=%s WHERE id=%d LIMIT 1';
				$wpdb->query( $wpdb->prepare( $sql, $pdfname, $db_id ) );
			}

			echo '<p style="color: green;">Post has been added to the database.</p>';
		} else {
			echo '<p style="color: red;">Post not found.</p>';
		}
	} else {
		if ( isset( $_GET['addcustomname'] ) ) {
			echo '<form action="?page=' . esc_attr($_GET['page']) . '" method="post">';
			echo 'Post: ';
			mpdf_admin_listposts();
			echo '<br />';
			echo 'New pdf name: <input type="text" name="pdfname" value="" /><br />';
			echo '<input type="hidden" name="wp_mpdf_noncename" id="wp_mpdf_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
			echo '<input type="submit" value="Add Entry" name="addcustomname" />';
			echo '</form>';
			echo '<br />';
		}
	}

	$nonceURL = 'wp_mpdf_noncename=' . wp_create_nonce( plugin_basename( __FILE__ ) );
	echo '<a href="?page=' . esc_attr($_GET['page']) . '&amp;addcustomname=1">New Entry</a> <a href="?page=' . esc_attr($_GET['page']) . '&amp;clearcustomname=1&amp;' . $nonceURL . '">Clear All Entries</a>';
	echo '<table border="1">';
	$sql  = 'SELECT id,post_type,post_id,pdfname FROM ' . $table_name . ' WHERE pdfname!=""';
	$data = $wpdb->get_results( $sql, OBJECT );
	for ( $i = 0; $i < count( $data ); $i ++ ) {
		echo '<tr>';
		echo '<td>' . esc_html( $data[ $i ]->post_type ) . '</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		if ( $data[ $i ]->post_type == 'post' ) {
			$post = get_post( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $post->post_title ) . '</td>';
		} else {
			$page = get_page( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $page->post_title ) . '</td>';
		}
		echo '<td> -> </td>';
		echo '<td>' . esc_html( $data[ $i ]->pdfname ) . '</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		echo '<td><a href="?page=' . esc_attr($_GET['page']) . '&amp;delcustomname=' . esc_attr( $data[ $i ]->id ) . '&' . $nonceURL . '">Delete</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

function mpdf_admin_stats() {
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;

	echo '<h2>Statistic</h2>';

	if ( ! check_nonce_if_param_exists_in_request( array( 'resetstat', 'clearstats' ), array() ) ) {
		echo '<p style="color: red;">Illegal Access!</p>';

		return;
	}
	if ( isset( $_GET['resetstat'] ) && is_numeric( $_GET['resetstat'] ) ) {
		$wpdb->query( $wpdb->prepare('UPDATE ' . $table_name . ' SET downloads=0 WHERE id=%d LIMIT 1', $_GET['resetstat'] ) );

		echo '<p style="color: green;">Stats for page with id "' . esc_html($_GET['resetstat']) . '" is resetet</p>';
	}
	if ( isset( $_GET['clearstats'] ) ) {
		$wpdb->query( 'UPDATE ' . $table_name . ' SET downloads=0' );

		echo '<p style="color: green;">All stats are resetet.</p>';
	}

	$nonceURL = 'wp_mpdf_noncename=' . wp_create_nonce( plugin_basename( __FILE__ ) );
	echo '<a href="?page=' . esc_attr($_GET['page']) . '&amp;clearstats=1&amp;' . $nonceURL . '">Clear All</a>';
	echo '<table border="1">';
	$sql  = 'SELECT id,post_type,post_id,downloads FROM ' . $table_name . ' ORDER BY downloads DESC';
	$data = $wpdb->get_results( $sql, OBJECT );
	for ( $i = 0; $i < count( $data ); $i ++ ) {
		echo '<tr>';
		echo '<td>' . ( $i + 1 ) . '.&nbsp;(' . $data[ $i ]->downloads . ')</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		echo '<td>' . esc_html( $data[ $i ]->post_type ) . '</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		if ( $data[ $i ]->post_type == 'post' ) {
			$post = get_post( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $post->post_title ) . '</td>';
		} else {
			$page = get_page( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $page->post_title ) . '</td>';
		}
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		echo '<td><a href="?page=' . esc_attr($_GET['page']) . '&amp;resetstat=' . esc_attr( $data[ $i ]->id ) . '&amp;' . $nonceURL . '">Clear</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

function mpdf_admin_loginneededpages() {
	global $wpdb;
	$table_name = $wpdb->prefix . WP_MPDF_POSTS_DB;

	echo '<h2>Black/White List - Login needs</h2>';

	if ( ! check_nonce_if_param_exists_in_request( array(
		'delloginneededpages',
		'clearloginneededpages'
	), array( 'addneedloginpage' ) ) ) {
		echo '<p style="color: red;">Illegal Access!</p>';

		return;
	}
	if ( isset( $_GET['delloginneededpages'] ) && is_numeric( $_GET['delloginneededpages'] ) ) {
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . $table_name . ' SET login=0 WHERE id=%d LIMIT 1', $_GET['delloginneededpages'] ) );

		echo '<p style="color: green;">Delete allowed Page with id "' . esc_html($_GET['delloginneededpages']) . '"</p>';
	}
	if ( isset( $_GET['clearloginneededpages'] ) ) {
		$wpdb->query( 'UPDATE ' . $table_name . ' SET login=0' );

		echo '<p style="color: green;">All posts are deleted from the black/white list.</p>';
	}
	if ( isset( $_POST['addneedloginpage'] ) ) {
		$page = get_post( $_POST['post'] );
		if ( $page != null ) {
			$sql   = 'SELECT id FROM ' . $table_name . ' WHERE post_id=' . $page->ID . ' AND post_type="' . $page->post_type . '" LIMIT 1';
			$db_id = $wpdb->get_var( $sql );
			if ( $db_id == null ) {
				$sql = 'INSERT INTO ' . $table_name . ' (post_type, post_id, general, login, pdfname, downloads) VALUES (%s, %d, 0, 1, "", 0)';
				$wpdb->query( $wpdb->prepare( $sql, $page->post_type, $page->ID ) );
			} else {
				$sql = 'UPDATE ' . $table_name . ' SET login=1 WHERE id=%d LIMIT 1';
				$wpdb->query( $wpdb->prepare( $sql, $db_id ) );
			}

			echo '<p style="color: green;">Post has been added to the .</p>';
		} else {
			echo '<p style="color: red;">Post not found.</p>';
		}
	} else {
		if ( isset( $_GET['addneedloginpage'] ) ) {
			echo '<form action="?page=' . esc_attr($_GET['page']) . '" method="post">';
			mpdf_admin_listposts();
			echo '<br />';
			echo '<input type="hidden" name="wp_mpdf_noncename" id="wp_mpdf_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
			echo '<input type="submit" value="Add Entry" name="addneedloginpage" />';
			echo '</form>';
			echo '<br />';
		}
	}

	$nonceURL = 'wp_mpdf_noncename=' . wp_create_nonce( plugin_basename( __FILE__ ) );
	echo '<a href="?page=' . esc_attr($_GET['page']) . '&amp;addneedloginpage=1">New Entry</a> <a href="?page=' . esc_attr($_GET['page']) . '&amp;clearloginneededpages=1&amp;' . $nonceURL . '">Clear All Entries</a>';
	echo '<table border="1">';
	$sql  = 'SELECT id,post_type,post_id FROM ' . $table_name . ' WHERE login=1';
	$data = $wpdb->get_results( $sql, OBJECT );
	for ( $i = 0; $i < count( $data ); $i ++ ) {
		echo '<tr>';
		echo '<td>' . esc_html( $data[ $i ]->post_type ) . '</td>';
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		if ( $data[ $i ]->post_type == 'post' ) {
			$post = get_post( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $post->post_title ) . '</td>';
		} else {
			$page = get_page( $data[ $i ]->post_id );
			echo '<td>' . esc_html( $page->post_title ) . '</td>';
		}
		echo '<td>&nbsp;&nbsp;&nbsp;</td>';
		echo '<td><a href="?page=' . esc_attr($_GET['page']) . '&amp;delloginneededpages=' . esc_attr( $data[ $i ]->id ) . '&amp;' . $nonceURL . '">Delete</a></td>';
		echo '</tr>';
	}
	echo '</table>';
}

function mpdf_admin_cache() {
	echo '<h2>Cache</h2>';
	$path = plugin_dir_path( __FILE__ ) . 'cache/';

	if ( ! check_nonce_if_param_exists_in_request( array( 'delfile', 'clearcache' ), array() ) ) {
		echo '<p style="color: red;">Illegal Access!</p>';

		return;
	}
	if ( isset( $_GET['delfile'] ) ) {
		$files = array_diff( scandir( $path ), [ '.', '..' ] );
		if ( in_array( $_GET['delfile'], $files ) ) {
			unlink( $path . $_GET['delfile'] );
		}
		if ( in_array( $_GET['delfile'] . '.cache', $files ) ) {
			unlink( $path . $_GET['delfile'] . '.cache' );
		}

		echo '<p style="color: green;">Cache file "' . esc_html( $_GET['delfile'] ) . '" is deleted</p>';
	}
	if ( isset( $_GET['clearcache'] ) ) {
		if ( $dir = opendir( $path ) ) {
			while ( $file = readdir( $dir ) ) {
				if ( ! is_dir( $path . $file ) && $file != "." && $file != ".." ) {
					unlink( $path . $file );
				}
			}
		}

		echo '<p style="color: green;">Cache is cleared</p>';
	}


	$nonceURL = 'wp_mpdf_noncename=' . wp_create_nonce( plugin_basename( __FILE__ ) );
	echo '<p><a href="?page=' . esc_attr($_GET['page']) . '&amp;clearcache=1&amp;' . $nonceURL . '">Clear Cache</a></p>';

	echo '<table border="1">';
	if ( $dir = opendir( $path ) ) {
		while ( $file = readdir( $dir ) ) {
			if ( ! is_dir( $path . $file ) && $file != "." && $file != ".." ) {
				if ( strtolower( substr( $file, strlen( $file ) - 5 ) ) == 'cache' ) {
					$pdffilename = substr( $file, 0, strlen( $file ) - 6 );
					echo '<tr>';
					echo '<td style="padding: 5px;">' . esc_html( file_get_contents( plugin_dir_path( __FILE__ ) . 'cache/' . $file ) ) . '</td>';
					echo '<td style="padding: 5px;"><a href="' . esc_url( plugin_dir_url( __FILE__ ) . 'cache/' . $pdffilename ) . '">' . esc_html( $pdffilename ) . '</a></td>';
					echo '<td style="padding: 5px;"><a href="?page=' . esc_attr($_GET['page']) . '&amp;delfile=' . esc_attr( $pdffilename ) . '&amp;' . $nonceURL . '">Delete</a></td>';
					echo '</tr>';
				}
			}
		}
	}
	echo '</table>';
}
