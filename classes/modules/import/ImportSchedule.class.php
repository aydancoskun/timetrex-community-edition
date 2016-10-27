<?php
/*********************************************************************************
 * TimeTrex is a Payroll and Time Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2014 TimeTrex Software Inc.
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
 * #292 Westbank, BC V4T 2E9, Canada or at email address info@timetrex.com.
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
/*
 * $Revision: 3387 $
 * $Id: ImportBranch.class.php 3387 2010-03-04 17:42:17Z ipso $
 * $Date: 2010-03-04 09:42:17 -0800 (Thu, 04 Mar 2010) $
 */


/**
 * @package Modules\Import
 */
class ImportSchedule extends Import {

	public $class_name = 'APISchedule';

	public $schedule_policy_options = FALSE;
	public $absence_policy_options = FALSE;
	public $branch_options = FALSE;
	public $branch_manual_id_options = FALSE;
	public $department_options = FALSE;
	public $department_manual_id_options = FALSE;

	public $job_options = FALSE;
	public $job_manual_id_options = FALSE;
	public $job_item_options = FALSE;
	public $job_item_manual_id_options = FALSE;

	function _getFactoryOptions( $name, $parent = NULL ) {

		$retval = NULL;
		switch( $name ) {
			case 'columns':
				$sf = TTNew('ScheduleFactory');
				$retval = Misc::prependArray( $this->getUserIdentificationColumns(), Misc::arrayIntersectByKey( array('status', 'date_stamp', 'start_time', 'end_time', 'branch', 'department', 'job', 'job_item', 'schedule_policy', 'note', 'absence_policy', ), Misc::trimSortPrefix( $sf->getOptions('columns') ) ) );

				$retval['schedule_policy'] = TTi18n::getText('Schedule Policy');
				$retval['absence_policy'] = TTi18n::getText('Absence Policy');

				$retval['start_time_stamp'] = TTi18n::getText('Start Date/Time');
				$retval['end_time_stamp'] = TTi18n::getText('End Date/Time');

				break;
			case 'column_aliases':
				//Used for converting column names after they have been parsed.
				$retval = array(
								'status' => 'status_id',
								'branch' => 'branch_id',
								'department' => 'department_id',
								'job' => 'job_id',
								'job_item' => 'job_item_id',
								'schedule_policy' => 'schedule_policy_id',
								'absence_policy' => 'absence_policy_id',
								);
				break;
			case 'import_options':
				$retval = array(
								'-1010-fuzzy_match' => TTi18n::getText('Enable smart matching.'),
								'-1010-overwrite' => TTi18n::getText('Overwrite existing shifts.'),
								);
				break;
			case 'parse_hint':
				$upf = TTnew('UserPreferenceFactory');

				$retval = array(
								'branch' => array(
													'-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												),
								'department' => array(
													'-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												),
								'job' => array(
													'-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												),
								'job_item' => array(
													'-1010-name' => TTi18n::gettext('Name'),
													'-1010-manual_id' => TTi18n::gettext('Code'),
												),
								'date_stamp' => $upf->getOptions('date_format'),
								'start_time' => $upf->getOptions('time_format'),
								'end_time' => $upf->getOptions('time_format'),
								);
				break;

		}

		return $retval;
	}


	function _preParseRow( $row_number, $raw_row ) {
		$retval = $this->getObject()->stripReturnHandler( $this->getObject()->getScheduleDefaultData() );

		return $retval;
	}

	function _postParseRow( $row_number, $raw_row ) {
		//Combine date/time columns together and convert all time_stamp columns into epochs.
		$column_map = $this->getColumnMap(); //Include columns that should always be there.

		$raw_row['user_id'] = $this->getUserIdByRowData( $raw_row );
		if ( $raw_row['user_id'] == FALSE ) {
			unset($raw_row['user_id']);
		}

		if ( !isset($column_map['start_time_stamp']) AND isset($column_map['date_stamp']) AND isset($column_map['start_time']) AND isset($column_map['end_time']) ) {
			Debug::Text('Parsing date_stamp/start_time/end_time', __FILE__, __LINE__, __METHOD__, 10);
			TTDate::setDateFormat( $column_map['date_stamp']['parse_hint'] );
			TTDate::setTimeFormat( $column_map['start_time']['parse_hint'] );

			$date_time_format = $column_map['date_stamp']['parse_hint'].'_'.$column_map['start_time']['parse_hint'];
			$raw_row['start_time'] = $raw_row['date_stamp'].' '.$raw_row['start_time'];
			$raw_row['end_time'] = $raw_row['date_stamp'].' '.$raw_row['end_time'];
		} elseif ( isset($column_map['start_time_stamp']) AND isset($column_map['end_time_stamp']) ) {
			Debug::Text('Parsing start_time_stamp/end_time_stamp', __FILE__, __LINE__, __METHOD__, 10);
			$raw_row['start_time'] = $raw_row['start_time_stamp'];
			$raw_row['end_time'] = $raw_row['end_time_stamp'];
			unset($raw_row['start_time_stamp'], $raw_row['end_time_stamp']);
		} else {
			Debug::Text('NOT Parsing start_time_stamp/end_time_stamp', __FILE__, __LINE__, __METHOD__, 10);
		}

		return $raw_row;
	}

	function _import( $validate_only ) {
		return $this->getObject()->setSchedule( $this->getParsedData(), $validate_only, $this->getImportOptions('overwrite') );
	}

	//
	// Generic parser functions.
	//
	function getSchedulePolicyOptions() {
		//Get schedule policies
		$splf = TTNew('SchedulePolicyListFactory');
		$splf->getByCompanyId( $this->company_id );
		$this->schedule_policy_options = (array)$splf->getArrayByListFactory( $splf, FALSE, TRUE );
		unset($aplf);

		return TRUE;
	}
	function getAbsencePolicyOptions() {
		//Get absence policies
		$aplf = TTNew('AbsencePolicyListFactory');
		$aplf->getByCompanyId( $this->company_id );
		$this->absence_policy_options = (array)$aplf->getArrayByListFactory( $aplf, FALSE, TRUE );
		unset($aplf);

		return TRUE;
	}

	function parse_schedule_policy( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0;
		}

		if ( !is_array( $this->schedule_policy_options ) ) {
			$this->getSchedulePolicyOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->schedule_policy_options );
		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function parse_absence_policy( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0;
		}

		if ( !is_array( $this->absence_policy_options ) ) {
			$this->getAbsencePolicyOptions();
		}

		$retval = $this->findClosestMatch( $input, $this->absence_policy_options );
		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function parse_status_id( $input, $default_value = NULL, $parse_hint = NULL, $raw_row = NULL ) {
		if ( strtolower( $input ) == 'w'
				OR strtolower( $input ) == 'work'
				OR strtolower( $input ) == 'working' ) {
			$retval = 10;
		} elseif ( strtolower( $input ) == 'a'
				OR strtolower( $input ) == 'absent' ) {
			$retval = 20;
		} else {
			$retval = (int)$input;
		}

		return $retval;
	}


	function getBranchOptions() {
		$this->branch_options = $this->branch_manual_id_options = array();
		$blf = TTNew('BranchListFactory');
		$blf->getByCompanyId( $this->company_id );
		if ( $blf->getRecordCount() > 0 ) {
			foreach( $blf as $b_obj ) {
				$this->branch_options[$b_obj->getId()] = $b_obj->getName();
				$this->branch_manual_id_options[$b_obj->getId()] = $b_obj->getManualId();
			}
		}
		unset($blf, $b_obj);

		return TRUE;
	}

	function parse_branch( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No branch
		}

		if ( !is_array( $this->branch_options ) ) {
			$this->getBranchOptions();
		}

		//Always fall back to searching by name unless we know for sure its by manual_id
		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->branch_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->branch_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function getDepartmentOptions() {
		//Get departments
		$this->department_options = $this->department_manual_id_options = array();
		$dlf = TTNew('DepartmentListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->department_options[$d_obj->getId()] = $d_obj->getName();
				$this->department_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}
	function parse_department( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No department
		}

		if ( !is_array( $this->department_options ) ) {
			$this->getDepartmentOptions();
		}

		//Always fall back to searching by name unless we know for sure its by manual_id
		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->department_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->department_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}

	function getJobOptions() {
		//Get jobs
		$this->job_options = $this->job_manual_id_options = array();
		$dlf = TTNew('JobListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->job_options[$d_obj->getId()] = $d_obj->getName();
				$this->job_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}
	function parse_job( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No job
		}

		if ( !is_array( $this->job_options ) ) {
			$this->getJobOptions();
		}

		//Debug::Text('Created new group name: '. $input .' ID: '. $parse_hint, __FILE__, __LINE__, __METHOD__, 10);
		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->job_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->job_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}


	function getJobItemOptions() {
		//Get job_items
		$this->job_item_options = $this->job_item_manual_id_options = array();
		$dlf = TTNew('JobItemListFactory');
		$dlf->getByCompanyId( $this->company_id );
		if ( $dlf->getRecordCount() > 0 ) {
			foreach( $dlf as $d_obj ) {
				$this->job_item_options[$d_obj->getId()] = $d_obj->getName();
				$this->job_item_manual_id_options[$d_obj->getId()] = $d_obj->getManualId();
			}
		}
		unset($dlf, $d_obj);

		return TRUE;
	}
	function parse_job_item( $input, $default_value = NULL, $parse_hint = NULL ) {
		if ( trim($input) == '' ) {
			return 0; //No job_item
		}

		if ( !is_array( $this->job_item_options ) ) {
			$this->getJobItemOptions();
		}

		if ( is_numeric( $input ) AND strtolower($parse_hint) == 'manual_id' ) {
			//Find based on manual_id/code.
			$retval = $this->findClosestMatch( $input, $this->job_item_manual_id_options, 90 );
		} else {
			$retval = $this->findClosestMatch( $input, $this->job_item_options );
		}

		if ( $retval === FALSE ) {
			$retval = -1; //Make sure this fails.
		}

		return $retval;
	}
}
?>
