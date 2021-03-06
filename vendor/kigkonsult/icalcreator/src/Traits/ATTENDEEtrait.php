<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
*/

namespace Kigkonsult\Icalcreator\Traits;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * ATTENDEE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.27.8 - 2019-03-17
 */
trait ATTENDEEtrait
{
    /**
     * @var array component property ATTENDEE value
     */
    protected $attendee = null;

    /**
     * Return formatted output for calendar component property attendee
     *
     * @return string
     */
    public function createAttendee()
    {
        if( empty( $this->attendee )) {
            return null;
        }
        return CalAddressFactory::outputFormatAttendee(
            $this->attendee,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property attendee
     *
     * @param int   $propDelIx   specific property in case of multiply occurrence
     * @return bool
     * @since  2.27.1 - 2018-12-15
     */
    public function deleteAttendee( $propDelIx = null )
    {
        if( empty( $this->attendee )) {
            unset( $this->propDelIx[self::ATTENDEE] );
            return false;
        }
        return $this->deletePropertyM( $this->attendee, self::ATTENDEE, $propDelIx );
    }

    /**
     * Get calendar component property attendee
     *
     * @param int    $propIx specific property in case of multiply occurrence
     * @param bool   $inclParam
     * @return bool|array
     * @since  2.27.1 - 2018-12-12
     */
    public function getAttendee( $propIx = null, $inclParam = false )
    {
        if( empty( $this->attendee )) {
            unset( $this->propIx[self::ATTENDEE] );
            return false;
        }
        return $this->getPropertyM( $this->attendee, self::ATTENDEE, $propIx, $inclParam );
    }

    /**
     * Set calendar component property attendee
     *
     * @param string  $value
     * @param array   $params
     * @param integer $index
     * @return static
     * @throws InvalidArgumentException
     * @since  2.27.8 - 2019-03-17
     */
    public function setAttendee( $value = null, $params = [], $index = null )
    {
        if( empty( $value )) {
            $this->assertEmptyValue( $value, self::ATTENDEE );
            $value  = Util::$SP0;
            $params = [];
        }
        $value = CalAddressFactory::conformCalAddress( $value );
        if( ! empty( $value )) {
            CalAddressFactory::assertCalAddress( $value );
        }
        $params = array_change_key_case( (array) $params, CASE_UPPER );
        CalAddressFactory::sameValueAndEMAILparam( $value, $params );
        $params = CalAddressFactory::inputPrepAttendeeParams(
            $params,
            $this->getCompType(),
            $this->getConfig( self::LANGUAGE )
        );
        $this->setMval($this->attendee, $value, $params,null, $index );
        return $this;
    }
}
