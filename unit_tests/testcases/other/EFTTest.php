<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2018 TimeTrex Software Inc.
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
 * @group Browser
 */
class EFTTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	public function tearDown() {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	function testSingleDebitOnlyACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-01') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );


		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5225SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
62777777777088888888         0000012409               EmployeeName            0878787870000001
822500000100777777770000000124090000000000000123456789                         878787870000001
9000001000001000000010077777777000000012409000000000000                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testMultipleDebitOnlyACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-01') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471232.67' );
		$record->setDueDate( strtotime('2020-04-01') ); //Same day as above, so it goes into the same batch.
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '421.99' );
		$record->setDueDate( strtotime('2020-04-02') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );


		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5225SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
62777777777088888888         0000012409               EmployeeName            0878787870000001
62777777777088888888         0847123267               EmployeeName            0878787870000002
822500000201555555540008471356760000000000000123456789                         878787870000001
5225SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200402   1878787870000002
62777777778088888889         0000042199               EmployeeName            0878787870000003
822500000100777777780000000421990000000000000123456789                         878787870000002
9000002000001000000030233333332000847177875000000000000                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testSingleCreditOnlyACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-01') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );


		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5220SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
65577777777088888888         0000012409               EmployeeName            0878787870000001
822000000100777777770000000000000000000124090123456789                         878787870000001
9000001000001000000010077777777000000000000000000012409                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testMultipleCreditOnlyACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-01') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471232.67' );
		$record->setDueDate( strtotime('2020-04-01') ); //Same day as above, so it goes into the same batch.
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '421.99' );
		$record->setDueDate( strtotime('2020-04-02') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );


		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5220SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
65577777777088888888         0000012409               EmployeeName            0878787870000001
65577777777088888888         0847123267               EmployeeName            0878787870000002
822000000201555555540000000000000008471356760123456789                         878787870000001
5220SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200402   1878787870000002
65577777778088888889         0000042199               EmployeeName            0878787870000003
822000000100777777780000000000000000000421990123456789                         878787870000002
9000002000001000000030233333332000000000000000847177875                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testMultipleCreditOnlyWithOffsetACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-01') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471232.67' );
		$record->setDueDate( strtotime('2020-04-01') ); //Same day as above, so it goes into the same batch.
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '421.99' );
		$record->setDueDate( strtotime('2020-04-01') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		//OFFSET Record
		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471778.75' );
		$record->setDueDate( strtotime('2020-04-01') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'OFFSET' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$record->setOriginatorReferenceNumber( 'OFFSET' ); //Offset Transaction Only
		$eft->setRecord( $record );


		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5200SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
65577777777088888888         0000012409               EmployeeName            0878787870000001
65577777777088888888         0847123267               EmployeeName            0878787870000002
65577777778088888889         0000042199               EmployeeName            0878787870000003
62777777778088888889         0847177875OFFSET         OFFSET                  0878787870000004
820000000403111111100008471778750008471778750123456789                         878787870000001
9000001000001000000040311111110000847177875000847177875                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testMultipleDebitAndCreditACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-01') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471232.67' );
		$record->setDueDate( strtotime('2020-04-01') ); //Same day as above, so it goes into the same batch.
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '421.99' );
		$record->setDueDate( strtotime('2020-04-01') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '601.56' );
		$record->setDueDate( strtotime('2020-04-01') ); //Different day than above, so its a different batch.
		$record->setInstitution( '557' );
		$record->setTransit( '77777788' );
		$record->setAccount( '88888899' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5200SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
65577777777088888888         0000012409               EmployeeName            0878787870000001
65577777777088888888         0847123267               EmployeeName            0878787870000002
62777777778088888889         0000042199               EmployeeName            0878787870000003
62777777788088888899         0000060156               EmployeeName            0878787870000004
820000000403111111200000001023550008471356760123456789                         878787870000001
9000001000001000000040311111120000000102355000847135676                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testMultipleDebitAndCreditDifferentDaysACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-02') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471232.67' );
		$record->setDueDate( strtotime('2020-04-01') ); //Same day as above, so it goes into the same batch.
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '421.99' );
		$record->setDueDate( strtotime('2020-04-02') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '601.56' );
		$record->setDueDate( strtotime('2020-04-01') ); //Different day than above, so its a different batch.
		$record->setInstitution( '557' );
		$record->setTransit( '77777788' );
		$record->setAccount( '88888899' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5200SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
65577777777088888888         0847123267               EmployeeName            0878787870000001
62777777788088888899         0000060156               EmployeeName            0878787870000002
820000000201555555650000000601560008471232670123456789                         878787870000001
5200SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200402   1878787870000002
65577777777088888888         0000012409               EmployeeName            0878787870000003
62777777778088888889         0000042199               EmployeeName            0878787870000004
820000000201555555550000000421990000000124090123456789                         878787870000002
9000002000001000000040311111120000000102355000847135676                                       
';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}

	function testMultipleDebitAndCreditSplitACH() {
		//Setup file level settings.
		$eft = new EFT();
		$eft->split_debit_credit_batches = true;
		$eft->setFileFormat( 'ACH' );
		$eft->setBusinessNumber( '123456789' ); //ACH
		$eft->setOriginatorID( '123456789' );
		$eft->setFileCreationDate( strtotime('2020-04-01') );
		$eft->setFileCreationNumber( '1001' );
		$eft->setInitialEntryNumber( '87878787' ); //ACH
		$eft->setDataCenter( '5566' );
		$eft->setDataCenterName( 'DataCenter' ); //ACH

		$eft->setOtherData( 'originator_long_name', 'LongCompanyName' );                  //Originator Long name based on company name. It will be trimmed automatically in EFT class.
		$eft->setOriginatorShortName( 'ShortCompanyName' );
		$eft->setCurrencyISOCode( 'USD' );

		//Add records
		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '124.09' );
		$record->setDueDate( strtotime('2020-04-02') );
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'C' );
		$record->setCPACode( 200 );
		$record->setAmount( '8471232.67' );
		$record->setDueDate( strtotime('2020-04-01') ); //Same day as above, so it goes into the same batch.
		$record->setInstitution( '555' );
		$record->setTransit( '77777777' );
		$record->setAccount( '88888888' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '421.99' );
		$record->setDueDate( strtotime('2020-04-02') ); //Different day than above, so its a different batch.
		$record->setInstitution( '556' );
		$record->setTransit( '77777778' );
		$record->setAccount( '88888889' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$record = new EFT_Record();
		$record->setType( 'D' );
		$record->setCPACode( 200 );
		$record->setAmount( '601.56' );
		$record->setDueDate( strtotime('2020-04-01') ); //Different day than above, so its a different batch.
		$record->setInstitution( '557' );
		$record->setTransit( '77777788' );
		$record->setAccount( '88888899' );
		$record->setName( 'EmployeeName' );
		$record->setOriginatorShortName( 'ShortCompanyName' );
		$record->setOriginatorLongName( 'LongCompanyName' );
		$eft->setRecord( $record );

		$eft->compile();
		$eft_data = str_replace("\r\n", "\n", $eft->getCompiledData() ); //Convert line ending to UNIX so we can compare against lines saved in this file using UNIX endings.
		//var_dump($eft_data);

		$expected_eft_data_file = '101 000005566 12345678920040100000094101DATACENTER             SHORTCOMPANYNAME       1001    
5220SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000001
65577777777088888888         0847123267               EmployeeName            0878787870000001
822000000100777777770000000000000008471232670123456789                         878787870000001
5220SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200402   1878787870000002
65577777777088888888         0000012409               EmployeeName            0878787870000002
822000000100777777770000000000000000000124090123456789                         878787870000002
5225SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200401   1878787870000003
62777777788088888899         0000060156               EmployeeName            0878787870000003
822500000100777777880000000601560000000000000123456789                         878787870000003
5225SHORTCOMPANYNAME                    0123456789PPDPAYROLL   200401200402   1878787870000004
62777777778088888889         0000042199               EmployeeName            0878787870000004
822500000100777777780000000421990000000000000123456789                         878787870000004
9000004000002000000040311111120000000102355000847135676                                       
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999
9999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999999';

		$this->assertEquals($eft_data, $expected_eft_data_file );

		return true;
	}
}

?>