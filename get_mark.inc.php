<?php
/*
 * This file is part of wp-mpdf.
 * wp-mpdf is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free 		 * Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * wp-mpdf is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 	 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with wp-mpdf. If not, see <http://www.gnu.org/licenses/>.
 */


function get_mark( $string, $mark ) {
	$ausgabe  = array();
	$template = explode( "*", $mark );
	$mark     = $template[0];
	$end      = $template[1];
	$string   = strstr( $string, $mark );

	$temp = explode( $mark, $string );
	$a    = 1;
	foreach ( $temp as $tempx ) {
		$tempx = explode( $end, $tempx );
		$tempx = $tempx[0];
		if ( $tempx ) {
			array_push( $ausgabe, $tempx );
		}
	}

	return $ausgabe;
}
