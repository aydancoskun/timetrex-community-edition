<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2020 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 ********************************************************************************/


/**
 * @package Core
 */
class UnitConvert {
	/*
		This class is used to convert units, ie:
		pounds (lbs) to grams (g)
		inches (in) to meters (m)
		miles (mi) to kiliometers (km)
	*/

	//Convert weight units to grams, first.
	//Convert dimension units to mm, first.
	//Handle square and cubic (exponent) calculations as well.
	static $units = [
		// 1 Unit = X G
		'oz'  => 28.349523125,
		'lb'  => 453.59237,
		'lbs' => 453.59237,
		'g'   => 1,
		'kg'  => 1000,

		//1 Unit = X MM
		'mm'  => 1,
		'in'  => 25.4,
		'cm'  => 10,
		'ft'  => 304.8,
		'm'   => 1000,
		'km'  => 1000000,
		'mi'  => 1609344,
	];

	//Only units in the same array can be converted to one another.
	static $valid_unit_groups = [
			'g'  => [ 'g', 'oz', 'lb', 'lbs', 'kg' ],
			'mm' => [ 'mm', 'in', 'cm', 'ft', 'm', 'km', 'mi' ],
	];

	/**
	 * @param $src_unit
	 * @param $dst_unit
	 * @param $measurement
	 * @param int $exponent
	 * @return bool|float|int
	 */
	static function convert( $src_unit, $dst_unit, $measurement, $exponent = 1 ) {
		$src_unit = strtolower( $src_unit );
		$dst_unit = strtolower( $dst_unit );

		if ( !isset( self::$units[$src_unit] ) ) {
			return false;
		}
		if ( !isset( self::$units[$dst_unit] ) ) {
			return false;
		}

		if ( $src_unit == $dst_unit ) {
			return $measurement;
		}

		//Make sure we can convert from one unit to another.
		$valid_conversion = false;
		foreach ( self::$valid_unit_groups as $valid_units ) {
			if ( in_array( $src_unit, $valid_units ) && in_array( $dst_unit, $valid_units ) ) {
				//Valid conversion
				$valid_conversion = true;
			}
		}

		if ( $valid_conversion == false ) {
			return false;
		}

		$base_measurement = ( pow( self::$units[$src_unit], $exponent ) * $measurement );
		//Debug::Text(' Base Measurement: '. $base_measurement, __FILE__, __LINE__, __METHOD__, 10);
		if ( $base_measurement != 0 ) {
			$retval = ( ( 1 / pow( self::$units[$dst_unit], $exponent ) ) * $base_measurement );

			return $retval;
		}

		return false;
	}
}

?>