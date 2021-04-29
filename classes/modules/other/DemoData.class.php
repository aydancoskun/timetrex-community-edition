<?php
/*********************************************************************************
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
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
 * @package Modules\Other
 */
class DemoData {

	protected $user_name_postfix = '1';
	protected $user_name_prefix = 'demo';
	protected $admin_user_name_prefix = 'demoadmin';
	protected $password = 'demo';
	protected $enable_quick_punch = true;
	protected $max_random_users = 0;

	public $create_data = [ 'schedule' => true, 'punch' => true, 'pay_stub' => true, 'invoice' => true, 'expense' => true, 'document' => true, 'hr' => true, ]; //Set an array of what data to create.


	protected $first_names = [
			'Sidney',
			'Vi',
			'Lena',
			'Carlee',
			'Mohammad',
			'Pat',
			'Lashell',
			'Denis',
			'Jeffry',
			'Cleo',
			'Nikia',
			'Vallie',
			'Shari',
			'Daniel',
			'Laurena',
			'Elbert',
			'Cortney',
			'Ferne',
			'Willetta',
			'Mitzi',
			'Stacey',
			'Mireya',
			'Reita',
			'Rivka',
			'Tu',
			'Hiram',
			'Giuseppina',
			'Reda',
			'Dion',
			'Izola',
			'Bobbye',
			'Chanelle',
			'Clemmie',
			'Karri',
			'Kylee',
			'Gillian',
			'Octavia',
			'Marielle',
			'Romelia',
			'Stephania',
			'Sherryl',
			'Malka',
			'Kristan',
			'Jolynn',
			'Star',
			'Cinthia',
			'Vern',
			'Junko',
			'Felipa',
			'Alayna',
			'Lorenzo',
			'Agnus',
			'Hyman',
			'Floretta',
			'Rosella',
			'Sabina',
			'Regan',
			'Yu',
			'Muoi',
			'Tomiko',
			'Ada',
			'Lyla',
			'Madelene',
			'Rosaura',
			'Berenice',
			'Georgine',
			'Vada',
			'Ray',
			'Martin',
			'Kathryn',
			'Dolly',
			'Clayton',
			'Arica',
			'Britany',
			'Rolland',
			'Mellissa',
			'Kymberly',
			'Claude',
			'Doyle',
			'Hector',
			'Arlen',
			'Debra',
			'Tami',
			'Catharine',
			'Su',
			'Danica',
			'Shandra',
			'Latrina',
			'Orval',
			'Clifton',
			'Jena',
			'Oliver',
			'Haydee',
			'Julie',
			'Xochitl',
			'Adrian',
			'Winfred',
			'Eldora',
			'Sook',
			'Antonette',
	];
	protected $last_names = [
			'Lecompte',
			'Jepko',
			'Godzik',
			'Bereda',
			'Lamers',
			'Errett',
			'Farm',
			'Adamski',
			'Fadri',
			'Gerhart',
			'Lubic',
			'Jost',
			'Manginelli',
			'Farris',
			'Otiz',
			'Huso',
			'Hutchens',
			'Mani',
			'Galland',
			'Laforest',
			'Labatt',
			'Burr',
			'Clemmens',
			'Gode',
			'Kapsner',
			'Harben',
			'Aumend',
			'Lauck',
			'Lassere',
			'Center',
			'Barlow',
			'Hudgens',
			'Fimbres',
			'Northcut',
			'Newstrom',
			'Floerchinger',
			'Goetting',
			'Binienda',
			'Dardagnac',
			'Graper',
			'Cadarette',
			'Castaneda',
			'Grosvenor',
			'Mccurren',
			'Feuerstein',
			'Parizek',
			'Haner',
			'Beyer',
			'Lollis',
			'Osten',
			'Baginski',
			'Fusca',
			'Hardiman',
			'Rechkemmer',
			'Ellerbrock',
			'Macvicar',
			'Golberg',
			'Benassi',
			'Hirons',
			'Lineberry',
			'Flamino',
			'Pickard',
			'Grohmann',
			'Parkers',
			'Hebrard',
			'Glade',
			'Haughney',
			'Levering',
			'Kudo',
			'Hoffschneider',
			'Mussa',
			'Fitzloff',
			'Matelic',
			'Maillard',
			'Carswell',
			'Becera',
			'Gonsior',
			'Qureshi',
			'Armel',
			'Broadnay',
			'Boulch',
			'Flamio',
			'Heaston',
			'Kristen',
			'Chambless',
			'Lamarch',
			'Jedan',
			'Fijal',
			'Jesmer',
			'Capraro',
			'Hemrich',
			'Prudente',
			'Cochren',
			'Karroach',
			'Guillotte',
			'Musinski',
			'Eflin',
			'Palumbo',
			'Legendre',
			'Afton',
	];

	protected $city_names = [
			'Richmond',
			'Southampton',
			'Stratford',
			'Wellington',
			'Jasper',
			'Flatrock',
			'Carleton',
			'Belmont',
			'Armstrong',
	];
	protected $institute = [
			'Harvard University',
			'Princeton University',
			'Yale University',
			'University of Pennsylvania',
			'Duke University',
			'Stanford University',
			'California Institute of Technology',
			'Massachusetts Inst. of Technology',
			'Columbia University',
			'Dartmouth College',
	];
	protected $major = [
			'Biological Engineering',
			'Public Management',
			'Vehicle Engineering',
			'Industrial Design',
			'Civil Engineering',
			'Communication Engineering',
			'Finance',
			'Financial Management',
	];
	protected $minor = [
			'Physical Education Section',
			'Arts and Design Department/Section',
			'Social Science Department/Section',
			'Foreign language and literature department',
			'Economics',
			'Automation',
			'Business Administration',
	];

	protected $coordinates = [ //NOTE: If these change update array_slice() calls on this to correspond.
							   [ 40.7331043902, -74.01004796505 ], // New York
							   [ 40.7314133588, -73.990341086388 ],
							   [ 40.727014849905, -74.007800092697 ],
							   [ 40.704895945126, -73.981407880783 ],
							   [ 40.722071261881, -74.010993574142 ],
							   [ 40.718688595346, -73.935745954514 ],
							   [ 40.763478722865, -73.930070400238 ],
							   [ 40.656778234208, -73.975642421722 ],
							   [ 40.723112047769, -74.058443098068 ],
							   [ 40.693963891348, -74.057968854904 ],

							   [ 47.773829, -122.38306 ], // Seattle
							   [ 47.473010966814, -122.11257896423 ],
							   [ 47.785653130062, -122.12741374969 ],
							   [ 47.708086640739, -121.9173002243 ],
							   [ 47.759983573294, -122.65153160095 ],
							   [ 47.765522347356, -122.021 ],
							   [ 47.721397737032, -122.30561971664 ],
							   [ 47.578552237571, -122.29298259735 ],
							   [ 47.503460631986, -122.25512981415 ],
							   [ 47.755367, -122.715569 ],
							   [ 47.537310405077, -122.8613948822 ],
							   [ 47.783635, -122.652912 ],

							   [ 34.256081384717, -100.73295593262 ], // outside New York, Seattle
							   [ 34.037866684604, -90.626561401367 ],
							   [ 31.858065081209, -92.281237182617 ],
							   [ 30.6931993, -95.6455915 ],
							   [ 29.4068375, -98.5181916 ],
							   [ 28.6415717, -106.0785596 ],
							   [ 33.474184, -112.1011934 ],
							   [ 40.8230805, -96.7074882 ],
							   [ 40.7160846, -111.9110708 ],
							   [ 35.4674472, -97.5480777 ],
							   [ 42.8376525, -106.3263868 ],
							   [ 39.5306376, -119.7937269 ],
							   [ 32.7949408, -96.9466828 ],
							   [ 34.7198707, -92.2848841 ],
							   [ 37.7802258, -81.1810323 ],
	];

	protected $generated_sins = [];

	/**
	 * DemoData constructor.
	 */
	function __construct() {
		$this->Validator = new Validator();

		return true;
	}

	/**
	 * @return bool|int
	 */
	function getDate() {
		if ( isset( $this->date ) ) {
			return $this->date;
		}

		return false;
	}

	/**
	 * Sets the date that is used to base all other dates from.
	 * @param $val
	 * @return bool
	 */
	function setDate( $val ) {
		if ( $val != '' ) {
			$this->date = $val;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getRandomSeed() {
		if ( isset( $this->random_seed ) ) {
			return $this->random_seed;
		}

		return false;
	}

	/**
	 * Sets the date that is used to base all other dates from.
	 * @param $val
	 * @return bool
	 */
	function setRandomSeed( $val ) {
		if ( $val != '' ) {
			$this->random_seed = (int)$val; //Must be an INT so we can add other values to it. Was: (int)($this->getDate() + $this->getUserNamePostfix())

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableQuickPunch() {
		if ( isset( $this->enable_quick_punch ) ) {
			return $this->enable_quick_punch;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setEnableQuickPunch( $val ) {
		$this->enable_quick_punch = (bool)$val;

		return true;
	}

	/**
	 * @return bool|int
	 */
	function getMaxRandomUsers() {
		if ( isset( $this->max_random_users ) ) {
			return $this->max_random_users;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setMaxRandomUsers( $val ) {
		if ( $val != '' ) {
			$this->max_random_users = $val;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getUserNamePostfix() {
		if ( isset( $this->user_name_postfix ) ) {
			return $this->user_name_postfix;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setUserNamePostfix( $val ) {
		if ( $val != '' ) {
			$this->user_name_postfix = $this->Validator->stripNonNumeric( trim( $val ) ); //Should be numeric only.
			Debug::Text( 'UserName Postfix: ' . $this->user_name_postfix, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getUserNamePrefix() {
		if ( isset( $this->user_name_prefix ) ) {
			return $this->user_name_prefix;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setUserNamePrefix( $val ) {
		if ( $val != '' ) {
			$this->user_name_prefix = $val;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getAdminUserNamePrefix() {
		if ( isset( $this->admin_user_name_prefix ) ) {
			return $this->admin_user_name_prefix;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setAdminUserNamePrefix( $val ) {
		if ( $val != '' ) {
			$this->admin_user_name_prefix = $val;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getPassword() {
		if ( isset( $this->password ) ) {
			return $this->password;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setPassword( $val ) {
		if ( $val != '' ) {
			$this->password = $val;

			return true;
		}

		return false;
	}

	/**
	 * @param $arr
	 * @return mixed
	 */
	function getRandomArrayValue( $arr ) {
		$rand = array_rand( $arr );

		return $arr[$rand];
	}

	/**
	 * @return bool|mixed
	 */
	function getRandomFirstName() {
		$rand = array_rand( $this->first_names );
		if ( isset( $this->first_names[$rand] ) ) {
			return $this->first_names[$rand];
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getRandomLastName() {
		$rand = array_rand( $this->last_names );
		if ( isset( $this->last_names[$rand] ) ) {
			return $this->last_names[$rand];
		}

		return false;
	}

	/**
	 * @param $legal_entity_id
	 * @return bool
	 */
	function getLegalEntityObject( $legal_entity_id ) {
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		$lelf->getByID( $legal_entity_id );
		if ( $lelf->getRecordCount() > 0 ) {
			$le_obj = $lelf->getCurrent();

			return $le_obj;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function createCompany() {
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */

		$cf->setStatus( 10 ); //Active
		$cf->setProductEdition( getTTProductEdition() );
		$cf->setName( 'ABC Company (' . $this->getUserNamePostfix() . ')', true ); //Must force this change due to demo mode being enabled.
		$cf->setShortName( substr( 'ABC' . $this->getUserNamePostfix(), 0, 15 ) ); //This must be unique to allow the recruitment portal to work properly.
		//$cf->setBusinessNumber( '123456789' );
		//$cf->setOriginatorID( $company_data['originator_id'] );
		//$cf->setDataCenterID($company_data['data_center_id']);
		$cf->setAddress1( '123 Main St' );
		$cf->setAddress2( 'Unit #123' );
		$cf->setCity( 'New York' );
		$cf->setCountry( 'US' );
		$cf->setProvince( 'NY' );
		$cf->setPostalCode( '12345' );
		$cf->setWorkPhone( '555-555-5555' );

		$cf->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
		$cf->setLongitude( $this->getRandomCoordinates( 'longitude' ) );

		//$cf->setEnableAddLegalEntity(FALSE);
		//$cf->setEnableAddCurrency(FALSE);

		$cf->setEnableAddLegalEntity( false );
		$cf->setEnableAddCurrency( false );
		$cf->setEnableAddPermissionGroupPreset( false );
		$cf->setEnableAddUserDefaultPreset( false );
		$cf->setEnableAddStation( false );
		$cf->setEnableAddPayStubEntryAccountPreset( false );
		$cf->setEnableAddCompanyDeductionPreset( false );
		$cf->setEnableAddRecurringHolidayPreset( false );

		$cf->setSetupComplete( true );
		if ( $cf->isValid() ) {
			$insert_id = $cf->Save();
			Debug::Text( 'Company ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Company!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	function createCurrency( $company_id, $type ) {
		$cf = TTnew( 'CurrencyFactory' ); /** @var CurrencyFactory $cf */
		$cf->setCompany( $company_id );
		$cf->setStatus( 10 );
		switch ( $type ) {
			case 10: //USD
				$cf->setName( 'US Dollar' );
				$cf->setISOCode( 'USD' );

				$cf->setConversionRate( '1.000000000' );
				$cf->setAutoUpdate( false );
				$cf->setBase( true );
				$cf->setDefault( true );

				break;
			case 20: //CAD
				$cf->setName( 'Canadian Dollar' );
				$cf->setISOCode( 'CAD' );

				$cf->setConversionRate( '1.200000000' );
				$cf->setAutoUpdate( true );
				$cf->setBase( false );
				$cf->setDefault( false );

				break;
			case 30: //EUR
				$cf->setName( 'Euro' );
				$cf->setISOCode( 'EUR' );

				$cf->setConversionRate( '1.300000000' );
				$cf->setAutoUpdate( true );
				$cf->setBase( false );
				$cf->setDefault( false );
				$cf->setRoundDecimalPlaces( 4 );
				break;
			case 40: //Pesos
				$cf->setName( 'Pesos' );
				$cf->setISOCode( 'MXN' );

				$cf->setConversionRate( '9755' ); //Some really large exchange rate.
				$cf->setAutoUpdate( true );
				$cf->setBase( false );
				$cf->setDefault( false );
				$cf->setRoundDecimalPlaces( 4 );
				break;
		}

		if ( $cf->isValid() ) {
			$insert_id = $cf->Save();
			Debug::Text( 'Currency ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Currency!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param $type
	 * @return bool
	 */
	function createDocument( $company_id, $object_type_id, $type ) {
		$df = TTnew( 'DocumentFactory' ); /** @var DocumentFactory $df */
		$df->setCompany( $company_id );
		$df->setStatus( 10 );
		$df->setDescription( '' );
		$df->setPrivate( false );
		$df->setTemplate( false );

		if ( $object_type_id == 100 ) { // Employee
			switch ( $type ) {
				case 10:
					$name = 'Resume';
					break;
				case 20:
					$name = 'Government-Form';
					break;
				case 30:
					$name = 'Employee-Contract';
					break;
				case 40:
					$name = 'Non-Disclosure';
					break;
				case 50:
					$name = 'Tax-Certificate';
					break;
				case 60:
					$name = 'Benefit-Plan-Application';
					break;
			}
		} else if ( $object_type_id == 60 ) { // Job
			switch ( $type ) {
				case 10:
					$name = 'Blueprints';
					break;
				case 20:
					$name = 'Quote';
					break;
				case 30:
					$name = 'Instructions';
					break;
			}
		} else if ( $object_type_id == 80 ) { // Client
			switch ( $type ) {
				case 10:
					$name = 'Contract';
					break;
				case 20:
					$name = 'Purchase-Order';
					break;
				case 30:
					$name = 'Non-Disclosure';
					break;
				case 40:
					$name = 'Quote';
					break;
			}
		} else if ( $object_type_id == 85 ) { // Client contact
			switch ( $type ) {
				case 10:
					$name = 'Client-Contact-Sheet';
					break;
				case 20:
					$name = 'Address-Book';
					break;
			}
		}
		$df->setName( $name );

		if ( $df->isValid() ) {
			$insert_id = $df->Save();
			Debug::Text( 'Document ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Document!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $company_id
	 * @param string $document_id UUID
	 * @param $object_type_id
	 * @param $type
	 * @param $revision
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 */
	function createDocumentRevision( $company_id, $document_id, $object_type_id, $type, $revision ) {
		$drf = TTnew( 'DocumentRevisionFactory' ); /** @var DocumentRevisionFactory $drf */
		$drf->setDocument( $document_id );
		switch ( $revision ) {
			case 1:
				$drf->setRevision( '1.0' );
				$drf->setChangeLog( 'Revision 1.0' );
				break;
			case 2:
				$drf->setRevision( '2.0' );
				$drf->setChangeLog( 'Revision 2.0' );
				break;
			case 3:
				$drf->setRevision( '3.0' );
				$drf->setChangeLog( 'Revision 3.0' );
				break;
			case 4:
				$drf->setRevision( '4.0' );
				$drf->setChangeLog( 'Revision 4.0' );
				break;
			case 5:
				$drf->setRevision( '5.0' );
				$drf->setChangeLog( 'Revision 5.0' );
				break;
			case 6:
				$drf->setRevision( '6.0' );
				$drf->setChangeLog( 'Revision 6.0' );
				break;
		}

		$drf->setLocalFileName(); //Permanent file on server.
		$dir = $drf->getStoragePath( $company_id );

		$remote_file_name = $this->createDocumentFilesByObjectType( $company_id, $object_type_id, $type, $dir );
		if ( $remote_file_name != '' ) {
			$drf->setRemoteFileName( $remote_file_name ); //Temporary file

			$drf->setMimeType( 'text/plain' );
			if ( $drf->isValid() ) {
				$insert_id = $drf->Save( false );
				Debug::Text( 'Document Revision ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $insert_id;
			}
		}

		Debug::Text( 'Failed Creating Document Revision!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $object_type_id
	 * @param $type
	 * @param $dir
	 * @return bool
	 */
	function createDocumentFilesByObjectType( $company_id, $object_type_id, $type, $dir ) {
		if ( $object_type_id == 100 ) { // Employee
			switch ( $type ) {
				case 10:
					$file_name = 'resume.txt';
					break;
				case 20:
					$file_name = 'government_form.txt';
					break;
				case 30:
					$file_name = 'employee_contract.txt';
					break;
				case 40:
					$file_name = 'non_disclosure.txt';
					break;
				case 50:
					$file_name = 'tax_certificate.txt';
					break;
				case 60:
					$file_name = 'benefit_plan_application.txt';
					break;
			}
		} else if ( $object_type_id == 60 ) { // Job
			switch ( $type ) {
				case 10:
					$file_name = 'blueprints.txt';
					break;
				case 20:
					$file_name = 'quote.txt';
					break;
				case 30:
					$file_name = 'instructions.txt';
					break;
			}
		} else if ( $object_type_id == 80 ) { // Client
			switch ( $type ) {
				case 10:
					$file_name = 'contract.txt';
					break;
				case 20:
					$file_name = 'purchase_order.txt';
					break;
				case 30:
					$file_name = 'non_disclosure.txt';
					break;
				case 40:
					$file_name = 'quote.txt';
					break;
			}
		} else if ( $object_type_id == 85 ) {
			switch ( $type ) {
				case 10:
					$file_name = 'client_contact_sheet.txt';
					break;
				case 20:
					$file_name = 'address_book.txt';
					break;
			}
		}

		if ( isset( $file_name ) ) {
			@mkdir( $dir, 0700, true );
			$local_full_path_file_name = $dir . DIRECTORY_SEPARATOR . $file_name;
			Debug::Text( 'Writing sample document file to: ' . $local_full_path_file_name, __FILE__, __LINE__, __METHOD__, 10 );
			if ( @file_put_contents( $local_full_path_file_name, 'Sample file created for DEMO purposes.' ) ) {
				return $file_name; //Return just base file name, as the path is prepended in DocumentRevisionFactory.
			}
		}

		Debug::Text( 'ERROR: Unable to write sample file...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $document_id UUID
	 * @param int $object_type_id
	 * @param string $object_id   UUID
	 * @return bool
	 */
	function createDocumentAttachment( $document_id, $object_type_id, $object_id ) {
		$daf = TTnew( 'DocumentAttachmentFactory' ); /** @var DocumentAttachmentFactory $daf */
		$daf->setDocument( $document_id );
		$daf->setObjectType( $object_type_id );
		$daf->setObject( $object_id );

		if ( $daf->isValid() ) {
			$insert_id = $daf->Save();
			Debug::Text( 'Document Attachment ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Document Attachment!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id    UUID
	 * @param $type
	 * @param string $geo_fence_ids UUID
	 * @return bool
	 */
	function createBranch( $company_id, $type, $geo_fence_ids = null ) {
		$bf = TTnew( 'BranchFactory' ); /** @var BranchFactory $bf */
		$bf->setCompany( $company_id );
		$bf->setGEOFenceIds( $geo_fence_ids );
		$bf->setStatus( 10 );
		switch ( $type ) {
			case 10: //Branch 1
				$bf->setName( 'New York' );
				$bf->setAddress1( '123 Main St' );
				$bf->setAddress2( 'Unit #123' );
				$bf->setCity( 'New York' );
				$bf->setCountry( 'US' );
				$bf->setProvince( 'NY' );

				$bf->setPostalCode( '12345' );
				$bf->setWorkPhone( '555-555-5555' );

				$bf->setManualId( 1 );
				break;
			case 20: //Branch 2
				$bf->setName( 'Seattle' );
				$bf->setAddress1( '321 Main St' );
				$bf->setAddress2( 'Unit #321' );
				$bf->setCity( 'Seattle' );
				$bf->setCountry( 'US' );
				$bf->setProvince( 'WA' );

				$bf->setPostalCode( '98105' );
				$bf->setWorkPhone( '555-555-5555' );

				$bf->setManualId( 2 );
				break;

			case 30: //Branch 3
				$bf->setName( 'Toronto' );
				$bf->setAddress1( '456 Main St' );
				$bf->setAddress2( 'Unit #456' );
				$bf->setCity( 'Toronto' );
				$bf->setCountry( 'CA' );
				$bf->setProvince( 'ON' );

				$bf->setPostalCode( 'M5E 1A1' );
				$bf->setWorkPhone( '555-555-5555' );

				$bf->setManualId( 3 );
				break;
			case 40: //Branch 4
				$bf->setName( 'Vancouver' );
				$bf->setAddress1( '654 Main St' );
				$bf->setAddress2( 'Unit #654' );
				$bf->setCity( 'Vancouver' );
				$bf->setCountry( 'CA' );
				$bf->setProvince( 'BC' );

				$bf->setPostalCode( 'V6G 1A1' );
				$bf->setWorkPhone( '555-555-5555' );

				$bf->setManualId( 4 );
				break;
		}

		$bf->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
		$bf->setLongitude( $this->getRandomCoordinates( 'longitude' ) );

		if ( $bf->isValid() ) {
			$insert_id = $bf->Save( false );
			if ( $geo_fence_ids != null ) {
				$bf->setGEOFenceIDs( $geo_fence_ids );
			}
			Debug::Text( 'Branch ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Branch!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id    UUID
	 * @param $type
	 * @param string $branch_ids    UUID
	 * @param string $geo_fence_ids UUID
	 * @return bool
	 */
	function createDepartment( $company_id, $type, $branch_ids = null, $geo_fence_ids = null ) {
		$df = TTnew( 'DepartmentFactory' ); /** @var DepartmentFactory $df */
		$df->setCompany( $company_id );
		$df->setGEOFenceIds( $geo_fence_ids );
		$df->setStatus( 10 );

		switch ( $type ) {
			case 10:
				$df->setName( 'Sales' );
				$df->setManualId( 1 );
				break;
			case 20:
				$df->setName( 'Construction' );
				$df->setManualId( 2 );
				break;
			case 30:
				$df->setName( 'Administration' );
				$df->setManualId( 3 );
				break;
			case 40:
				$df->setName( 'Inspection' );
				$df->setManualId( 4 );
				break;
		}

		if ( $df->isValid() ) {
			$insert_id = $df->Save( false );
			if ( $geo_fence_ids != null ) {
				$df->setGEOFenceIDs( $geo_fence_ids );
			}
			Debug::Text( 'Department ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Department!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $source
	 * @param string $station
	 * @return bool
	 */
	function createStation( $company_id, $source = 'ANY', $station = 'ANY' ) {
		$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
		$sf->setCompany( $company_id );

		$sf->setStatus( 20 );
		$sf->setType( 10 );
		$sf->setSource( $source );
		$sf->setStation( $station );
		$sf->setDescription( 'All stations' );

		$sf->setGroupSelectionType( 10 );
		$sf->setBranchSelectionType( 10 );
		$sf->setDepartmentSelectionType( 10 );

		if ( $sf->isValid() ) {
			$insert_id = $sf->Save();
			Debug::Text( 'Station ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Station!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function createPayStubAccount( $company_id ) {
		//$retval = PayStubEntryAccountFactory::addPresets( $company_id );
		$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
		$sp->setCompany( $company_id );

		$sp->PayStubAccounts();
		$sp->PayStubAccounts( 'us' );
		$sp->PayStubAccounts( 'us', 'ny' );

		$sp->PayStubAccounts( 'ca' );
		$retval = $sp->PayStubAccounts( 'ca', 'on' );
		if ( $retval == true ) {
			Debug::Text( 'Created Pay Stub Accounts!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( 'Failed Creating Pay Stub Accounts for Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @return bool
	 */
	function createTaxForms( $company_id, $user_id ) {
		$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
		$sp->setCompany( $company_id );
		$sp->setUser( $user_id );

		$sp->TaxForms();
		$sp->TaxForms( 'us' );
		$sp->TaxForms( 'us', 'ny' );

		$sp->TaxForms( 'ca' );
		$retval = $sp->TaxForms( 'ca', 'on' );
		if ( $retval == true ) {
			Debug::Text( 'Created TaxForm data!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( 'Failed Creating Tax Form for Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function createPayStubAccountLink( $company_id ) {
		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
		$pseallf->getByCompanyId( $company_id );
		if ( $pseallf->getRecordCount() == 1 ) {
			$psealf = $pseallf->getCurrent();
			Debug::Text( 'Found existing PayStubAccountLink record, ID: ' . $psealf->getID() . ' Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::Text( 'Creating new PayStubAccountLink record, Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );
			$psealf = TTnew( 'PayStubEntryAccountLinkFactory' ); /** @var PayStubEntryAccountLinkFactory $psealf */
		}

		$psealf->setCompany( $company_id );
		$psealf->setTotalGross( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 40, TTi18n::gettext( 'Total Gross' ) ) );
		$psealf->setTotalEmployeeDeduction( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 40, TTi18n::gettext( 'Total Deductions' ) ) );
		$psealf->setTotalEmployerDeduction( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 40, TTi18n::gettext( 'Employer Total Contributions' ) ) );
		$psealf->setTotalNetPay( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 40, TTi18n::gettext( 'Net Pay' ) ) );
		$psealf->setRegularTime( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Regular Time' ) ) );

		$psealf->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, 'EI' ) );
		$psealf->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 20, 'CPP' ) );

		if ( $psealf->isValid() ) {
			$insert_id = $psealf->Save();
			Debug::Text( 'Pay Stub Account Link ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Stub Account Links!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string $current_user_id UUID
	 * @return bool
	 */
	function createRecurringHolidays( $company_id, $current_user_id ) {
		//$retval = CompanyDeductionFactory::addPresets( $company_id );
		$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
		$sp->setCompany( $company_id );
		$sp->setUser( $current_user_id );

		//$sp->PayrollRemittanceAgencys();
		$sp->RecurringHolidays( 'us' );
		$sp->RecurringHolidays( 'us', 'ny' );

		$sp->RecurringHolidays( 'ca' );
		$retval = $sp->RecurringHolidays( 'ca', 'on' );
		if ( $retval == true ) {
			Debug::Text( 'Created Recurring Holidays!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( 'Failed Creating RecurringHolidays for Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string $current_user_id UUID
	 * @param $legal_entity_id
	 * @param string $country
	 * @return bool
	 */
	function createPayrollRemittanceAgency( $company_id, $current_user_id, $legal_entity_id, $country = 'US' ) {
		//$retval = CompanyDeductionFactory::addPresets( $company_id );
		$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
		$sp->setCompany( $company_id );
		$sp->setUser( $current_user_id );

		$le_obj = $this->getLegalEntityObject( $legal_entity_id );
		$sp->PayrollRemittanceAgencys( $le_obj->getCountry(), null, null, null, $legal_entity_id );
		$retval = $sp->PayrollRemittanceAgencys( $le_obj->getCountry(), $le_obj->getProvince(), null, null, $legal_entity_id );

		if ( $retval == true ) {
			Debug::Text( 'Created Payroll Remittance Agencys!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( 'Failed Creating Payroll Remittance Agencys for Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string $current_user_id UUID
	 * @param $legal_entity_id
	 * @return bool
	 */
	function createCompanyDeduction( $company_id, $current_user_id, $legal_entity_id ) {
		$sp = TTNew( 'SetupPresets' ); /** @var SetupPresets $sp */
		$sp->setCompany( $company_id );
		$sp->setUser( $current_user_id );

		$le_obj = $this->getLegalEntityObject( $legal_entity_id );

		$sp->CompanyDeductions( null, null, null, null, $legal_entity_id ); //This creates non-country specific records like loan repayment.
		$sp->CompanyDeductions( $le_obj->getCountry(), null, null, null, $legal_entity_id );
		$retval = $sp->CompanyDeductions( $le_obj->getCountry(), $le_obj->getProvince(), null, null, $legal_entity_id );

		if ( $retval == true ) {
			Debug::Text( 'Created Company Deductions!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( 'Failed Creating Company Deductions for Company ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	function createRoundingPolicy( $company_id, $type ) {
		$ripf = TTnew( 'RoundIntervalPolicyFactory' ); /** @var RoundIntervalPolicyFactory $ripf */
		$ripf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //In
				$ripf->setName( '5min [1]' );
				$ripf->setPunchType( 40 );        //In
				$ripf->setRoundType( 30 );        //Up
				$ripf->setInterval( ( 60 * 5 ) ); //5mins
				$ripf->setGrace( ( 60 * 3 ) );    //3min
				$ripf->setStrict( false );
				break;
			case 20: //Out
				$ripf->setName( '5min [2]' );
				$ripf->setPunchType( 50 );        //In
				$ripf->setRoundType( 10 );        //Down
				$ripf->setInterval( ( 60 * 5 ) ); //5mins
				$ripf->setGrace( ( 60 * 3 ) );    //3min
				$ripf->setStrict( false );
				break;
		}

		if ( $ripf->isValid() ) {
			$insert_id = $ripf->Save();
			Debug::Text( 'Rounding Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Rounding Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id UUID
	 * @param string $product_id UUID
	 * @param bool $include_area_policy_ids
	 * @param bool $exclude_area_policy_ids
	 * @return bool
	 */
	function createTaxPolicy( $company_id, $product_id, $include_area_policy_ids = false, $exclude_area_policy_ids = false ) {
		$tpf = TTnew( 'TaxPolicyFactory' ); /** @var TaxPolicyFactory $tpf */
		$tpf->setCompany( $company_id );
		$tpf->setProduct( $product_id );
		$tpf->setName( 'VAT' );
		$tpf->setCode( 'V' );
		$tpf->setPercent( 5.33 );

		if ( $tpf->isValid() ) {
			$insert_id = $tpf->Save( false );

			$tpf->setIncludeAreaPolicy( $include_area_policy_ids );
			$tpf->setExcludeAreaPolicy( $exclude_area_policy_ids );

			Debug::Text( 'Tax Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Tax Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	function createAccrualPolicyAccount( $company_id, $type ) {
		$apaf = TTnew( 'AccrualPolicyAccountFactory' ); /** @var AccrualPolicyAccountFactory $apaf */

		$apaf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Bank Time
				$apaf->setName( 'Bank Time' );
				break;
			case 20: //Calendar Based: Vacation/PTO
				$apaf->setName( 'Personal Time Off (PTO)/Vacation' );
				break;
			case 30: //Calendar Based: Vacation/PTO
				$apaf->setName( 'Sick Time' );
				break;
		}

		if ( $apaf->isValid() ) {
			$insert_id = $apaf->Save();
			Debug::Text( 'Accrual Policy Account ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Accrual Policy Account!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id                UUID
	 * @param $type
	 * @param string $accrual_policy_account_id UUID
	 * @return bool
	 */
	function createAccrualPolicy( $company_id, $type, $accrual_policy_account_id ) {
		$apf = TTnew( 'AccrualPolicyFactory' ); /** @var AccrualPolicyFactory $apf */

		$apf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Bank Time
				$apf->setName( 'Bank Time' );
				$apf->setType( 10 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 20: //Calendar Based: Vacation/PTO
				$apf->setName( 'Personal Time Off (PTO)/Vacation' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 );          //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 ); //1st of Month

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 30 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
			case 30: //Calendar Based: Vacation/PTO
				$apf->setName( 'Sick Time' );
				$apf->setType( 20 );

				$apf->setApplyFrequency( 30 );          //Monthly
				$apf->setApplyFrequencyDayOfMonth( 1 ); //1st of Month

				$apf->setMilestoneRolloverHireDate( true );

				$apf->setMinimumEmployedDays( 30 );
				$apf->setAccrualPolicyAccount( $accrual_policy_account_id );
				break;
		}

		if ( $apf->isValid() ) {
			$insert_id = $apf->Save();
			Debug::Text( 'Accrual Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$apmf = TTnew( 'AccrualPolicyMilestoneFactory' ); /** @var AccrualPolicyMilestoneFactory $apmf */
			if ( $type == 20 ) {
				$apmf->setAccrualPolicy( $insert_id );
				$apmf->setLengthOfService( 1 );
				$apmf->setLengthOfServiceUnit( 40 );
				$apmf->setAccrualRate( ( ( 3600 * 8 ) * 5 ) );
				$apmf->setMaximumTime( ( ( 3600 * 8 ) * 5 ) );

				if ( $apmf->isValid() ) {
					Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
					$apmf->Save();
				}

				$apmf->setAccrualPolicy( $insert_id );
				$apmf->setLengthOfService( 2 );
				$apmf->setLengthOfServiceUnit( 40 );
				$apmf->setAccrualRate( ( ( 3600 * 8 ) * 10 ) );
				$apmf->setMaximumTime( ( ( 3600 * 8 ) * 10 ) );

				if ( $apmf->isValid() ) {
					Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
					$apmf->Save();
				}

				$apmf->setAccrualPolicy( $insert_id );
				$apmf->setLengthOfService( 3 );
				$apmf->setLengthOfServiceUnit( 40 );
				$apmf->setAccrualRate( ( ( 3600 * 8 ) * 15 ) );
				$apmf->setMaximumTime( ( ( 3600 * 8 ) * 15 ) );

				if ( $apmf->isValid() ) {
					Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
					$apmf->Save();
				}
			} else if ( $type == 30 ) {
				$apmf->setAccrualPolicy( $insert_id );
				$apmf->setLengthOfService( 1 );
				$apmf->setLengthOfServiceUnit( 10 );
				$apmf->setAccrualRate( ( ( 3600 * 8 ) * 3 ) );
				$apmf->setMaximumTime( ( ( 3600 * 8 ) * 3 ) );

				if ( $apmf->isValid() ) {
					Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
					$apmf->Save();
				}

				$apmf->setAccrualPolicy( $insert_id );
				$apmf->setLengthOfService( 1 );
				$apmf->setLengthOfServiceUnit( 40 );
				$apmf->setAccrualRate( ( ( 3600 * 8 ) * 6 ) );
				$apmf->setMaximumTime( ( ( 3600 * 8 ) * 6 ) );

				if ( $apmf->isValid() ) {
					Debug::Text( 'Saving Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
					$apmf->Save();
				}
			}

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Accrual Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id       UUID
	 * @param $type
	 * @param string $taxes_policy_ids UUID
	 * @return bool
	 */
	function createExpensePolicy( $company_id, $type, $taxes_policy_ids = null ) {
		$epf = TTnew( 'ExpensePolicyFactory' ); /** @var ExpensePolicyFactory $epf */
		$epf->StartTransaction();
		$epf->setCompany( $company_id );
		switch ( $type ) {
			case 10:
				//$epf->setProduct('');
				$epf->setType( 10 ); //Flat Amount
				$epf->setRequireReceipt( 10 );
				$epf->setReimbursable( true );
				$epf->setName( 'Hotel' );
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 250.00 );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
			case 20:
				$epf->setType( 20 ); //Percent
				$epf->setRequireReceipt( 10 );
				$epf->setReimbursable( true );
				$epf->setName( 'Food' );
				$epf->setAmount( 50 ); //50%
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 50.00 );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
			case 30:
				$epf->setType( 30 ); //Per Unit
				$epf->setRequireReceipt( 10 );
				$epf->setReimbursable( true );
				$epf->setName( 'Vehicle Mileage' );
				$epf->setAmount( 0.25 );
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 100.00 );
				$epf->setUnitName( 'Kilometers' );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
			case 40:
				$epf->setType( 10 ); //Flat Amount
				$epf->setRequireReceipt( 10 );
				$epf->setReimbursable( true );
				$epf->setName( 'Air Travel' );
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 1000.00 );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
			case 100:
				$epf->setType( 40 ); //Tax Percent
				$epf->setRequireReceipt( 20 );
				$epf->setReimbursable( true );
				$epf->setName( 'HST' );
				$epf->setAmount( rand( 1, 3 ) );
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 0 );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
			case 110:
				$epf->setType( 40 ); //Tax Percent
				$epf->setRequireReceipt( 30 );
				$epf->setReimbursable( true );
				$epf->setName( 'VAT' );
				$epf->setAmount( rand( 1, 5 ) );
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 0 );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
			case 120:
				$epf->setType( 50 ); //Flat Amount
				$epf->setRequireReceipt( 10 );
				$epf->setReimbursable( true );
				$epf->setName( 'Airport Improvement Tax' );
				$epf->setAmount( rand( 1, 10 ) * 10 );
				$epf->setMinAmount( 0 );
				$epf->setMaxAmount( 0 );
				$epf->setPayStubEntryAccount( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, TTi18n::gettext( 'Expense Reimbursement' ) ) );
				break;
		}

		if ( $epf->isValid() ) {
			$insert_id = $epf->Save( false );

			if ( is_array( $taxes_policy_ids ) ) {
				$epf->setExpensePolicy( $taxes_policy_ids );
			} else {
				$epf->setExpensePolicy( [] );
			}
			if ( $epf->isValid() ) {
				$epf->Save();
				$epf->CommitTransaction();

				Debug::Text( 'Expense Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $insert_id;
			}
		}

		Debug::Text( 'Failed Creating Expense Policy!', __FILE__, __LINE__, __METHOD__, 10 );
		$epf->FailTransaction();
		$epf->CommitTransaction();

		return false;
	}


	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param bool $invoice_district_ids
	 * @return bool
	 */
	function createAreaPolicy( $company_id, $type, $invoice_district_ids = false ) {
		$apf = TTnew( 'AreaPolicyFactory' ); /** @var AreaPolicyFactory $apf */
		$apf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$apf->setName( 'Area Policy - CA' );
				if ( $apf->isValid() ) {
					$insert_id = $apf->Save( false );
					$apf->setCountry( [ 'CA' ] );
					$apf->setProvince( [ 'AB', 'ON' ] );
					$apf->setDistrict( $invoice_district_ids );
				}
				break;
			case 20:
				$apf->setName( 'Area Policy - US' );
				if ( $apf->isValid() ) {
					$insert_id = $apf->Save( false );
					$apf->setCountry( [ 'US' ] );
					$apf->setProvince( [ 'WA', 'NY' ] );
					$apf->setDistrict( $invoice_district_ids );
				}
				break;
		}

		if ( $apf->isValid() ) {
			$apf->Save();

			Debug::Text( 'Area Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Area Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $pay_formula_policy_id
	 * @return bool
	 */
	function createPayCode( $company_id, $type, $pay_formula_policy_id = 0 ) {
		if ( $pay_formula_policy_id === 0 ) {
			$pay_formula_policy_id = TTUUID::getZeroID();
		}

		$pcf = TTnew( 'PayCodeFactory' ); /** @var PayCodeFactory $pcf */
		$pcf->setCompany( $company_id );

		switch ( $type ) {
			case 100:
				$pcf->setName( 'Regular Time' );
				$pcf->setCode( 'REG' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Regular Time' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 101:
				$pcf->setName( 'Regular Time (B)' );
				$pcf->setCode( 'REG' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Regular Time' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 102:
				$pcf->setName( 'Regular Time (C)' );
				$pcf->setCode( 'REG' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Regular Time' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 190:
				$pcf->setName( 'Lunch Time' );
				$pcf->setCode( 'LNH' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Regular Time' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 192:
				$pcf->setName( 'Break Time' );
				$pcf->setCode( 'BRK' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Regular Time' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 200:
				$pcf->setName( 'Overtime Time (1.5x)' );
				$pcf->setCode( 'OT15' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Over Time 1' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 210:
				$pcf->setName( 'Overtime Time (2.0x)' );
				$pcf->setCode( 'OT20' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Over Time 2' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 300:
				$pcf->setName( 'Weekend' );
				$pcf->setCode( 'PRE1' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Premium 1' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 310:
				$pcf->setName( 'Evening' );
				$pcf->setCode( 'PRE2' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Premium 2' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 900:
				$pcf->setName( 'PTO/Vacation' );
				$pcf->setCode( 'PTO' );
				$pcf->setType( 10 ); //Paid
				$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Vacation - Accrual Release' ) );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 910:
				$pcf->setName( 'Bank Time' );
				$pcf->setCode( 'BANK' );
				$pcf->setType( 20 ); //Not Paid
				$pcf->setPayStubEntryAccountID( TTUUID::getZeroID() );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
			case 920:
				$pcf->setName( 'Sick Time' );
				$pcf->setCode( 'SICK' );
				$pcf->setType( 20 ); //Not Paid
				$pcf->setPayStubEntryAccountID( TTUUID::getZeroID() );
				$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
				break;
		}

		if ( $pcf->isValid() ) {
			$insert_id = $pcf->Save();
			Debug::Text( 'Pay Code ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Code!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $accrual_policy_account_id
	 * @return bool
	 */
	function createPayFormulaPolicy( $company_id, $type, $accrual_policy_account_id = 0 ) {
		if ( $accrual_policy_account_id === 0 ) {
			$accrual_policy_account_id = TTUUID::getZeroID();
		}
		$pfpf = TTnew( 'PayFormulaPolicyFactory' ); /** @var PayFormulaPolicyFactory $pfpf */
		$pfpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$pfpf->setName( 'None ($0)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 0 );
				break;
			case 100:
				$pfpf->setName( 'Regular' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 110:
				$pfpf->setName( 'Bank Time' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 120:
				$pfpf->setName( 'Vacation Time' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( -1.0 );
				break;
			case 130:
				$pfpf->setName( 'Sick Time' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( -1.0 );
				break;
			case 200:
				$pfpf->setName( 'OverTime (1.5x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.5 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 210:
				$pfpf->setName( 'OverTime (2.0x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 2.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 300:
				$pfpf->setName( 'Premium 1' );
				$pfpf->setPayType( 32 ); //Flat Hourly Rate
				$pfpf->setRate( 1.33 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 310:
				$pfpf->setName( 'Premium 2' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 0.50 );
				$pfpf->setWageGroup( $this->user_wage_groups[0] );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
		}

		if ( $pfpf->isValid() ) {
			$insert_id = $pfpf->Save();
			Debug::Text( 'Pay Formula Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Formula Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $pay_code_ids
	 * @return bool
	 */
	function createContributingPayCodePolicy( $company_id, $type, $pay_code_ids = 0 ) {
		if ( $pay_code_ids === 0 ) {
			$pay_code_ids = TTUUID::getZeroID();
		}

		$ctpf = TTnew( 'ContributingPayCodePolicyFactory' ); /** @var ContributingPayCodePolicyFactory $ctpf */
		$ctpf->setId( $ctpf->getNextInsertId() ); //Make sure we can define the pay codes before calling isValid()
		$ctpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$ctpf->setName( 'Regular Time' );
				break;
			case 12:
				$ctpf->setName( 'Regular Time + Meal/Break' );
				break;
			case 14:
				$ctpf->setName( 'Regular Time + Meal/Break + Absences' );
				break;
			case 20:
				$ctpf->setName( 'Regular Time + OverTime + Meal/Break' );
				break;
			case 90:
				$ctpf->setName( 'Absences' );
				break;
			case 99:
				$ctpf->setName( 'All Time' );
				break;
		}

		$ctpf->setPayCode( $pay_code_ids ); //Make sure we can define the pay codes before calling isValid()

		if ( $ctpf->isValid() ) {
			$insert_id = $ctpf->Save( true, true );
			Debug::Text( 'Contributing Pay Code Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Contributing Pay Code Policy: ' . $ctpf->getName(), __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id                      UUID
	 * @param $type
	 * @param string $contributing_pay_code_policy_id UUID
	 * @param string $holiday_policy_id               UUID
	 * @return bool
	 */
	function createContributingShiftPolicy( $company_id, $type, $contributing_pay_code_policy_id, $holiday_policy_id = null ) {
		$cspf = TTnew( 'ContributingShiftPolicyFactory' ); /** @var ContributingShiftPolicyFactory $cspf */
		$cspf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$cspf->setName( 'Regular Shifts' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 20:
				$cspf->setName( 'Regular Shifts + Meal/Break' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 30:
				$cspf->setName( 'Regular+Overtime' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 40:
				$cspf->setName( 'Regular+Overtime+Absence' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;

			case 90:
				$cspf->setName( 'Absences' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
		}

		if ( $cspf->isValid() ) {
			$insert_id = $cspf->Save( false );
			Debug::Text( 'Contributing Shift Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $holiday_policy_id != '' ) {
				$cspf->setHolidayPolicy( $holiday_policy_id );
				if ( $cspf->isValid() ) {
					$cspf->Save();
				}
			}

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Contributing Shift Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $contributing_shift_policy_id
	 * @param int $pay_code_id
	 * @return bool
	 */
	function createRegularTimePolicy( $company_id, $type, $contributing_shift_policy_id = 0, $pay_code_id = 0 ) {
		if ( $contributing_shift_policy_id === 0 ) {
			$contributing_shift_policy_id = TTUUID::getZeroID();
		}

		if ( $pay_code_id === 0 ) {
			$pay_code_id = TTUUID::getZeroID();
		}

		$rtpf = TTnew( 'RegularTimePolicyFactory' ); /** @var RegularTimePolicyFactory $rtpf */
		$rtpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$rtpf->setName( 'Regular Time' );
				$rtpf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$rtpf->setPayCode( $pay_code_id );
				$rtpf->setCalculationOrder( 9999 );
				break;
			case 20:
				$rtpf->setName( 'Regular Time (2)' );
				$rtpf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$rtpf->setPayCode( $pay_code_id );
				$rtpf->setCalculationOrder( 9999 );
				break;
		}

		if ( $rtpf->isValid() ) {
			$insert_id = $rtpf->Save();
			Debug::Text( 'Regular Time Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Regular Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $contributing_shift_policy_id
	 * @param int $pay_code_id
	 * @return bool
	 */
	function createOverTimePolicy( $company_id, $type, $contributing_shift_policy_id = 0, $pay_code_id = 0 ) {
		if ( $contributing_shift_policy_id === 0 ) {
			$contributing_shift_policy_id = TTUUID::getZeroID();
		}

		if ( $pay_code_id === 0 ) {
			$pay_code_id = TTUUID::getZeroID();
		}

		$otpf = TTnew( 'OverTimePolicyFactory' ); /** @var OverTimePolicyFactory $otpf */
		$otpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$otpf->setName( 'Daily (>8hrs)' );
				$otpf->setType( 10 );
				$otpf->setTriggerTime( ( 3600 * 8 ) );
				$otpf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$otpf->setPayCode( $pay_code_id );
				break;
			case 20:
				$otpf->setName( 'Daily (>10hrs)' );
				$otpf->setType( 10 );
				$otpf->setTriggerTime( ( 3600 * 10 ) );
				$otpf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$otpf->setPayCode( $pay_code_id );
				//$otpf->setRate( '1.0' );
				//$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				//$otpf->setAccrualPolicyId( $accrual_policy_id );
				//$otpf->setAccrualRate( '1.0' );
				break;
			case 30:
				$otpf->setName( 'Weekly (>40hrs)' );
				$otpf->setType( 20 );
				$otpf->setTriggerTime( ( 3600 * 40 ) );
				$otpf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$otpf->setPayCode( $pay_code_id );
				//$otpf->setRate( '1.0' );
				//$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				//$otpf->setAccrualPolicyId( $accrual_policy_id );
				//$otpf->setAccrualRate( '1.0' );
				break;
		}

		if ( $otpf->isValid() ) {
			$insert_id = $otpf->Save();
			Debug::Text( 'Overtime Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Overtime Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $contributing_shift_policy_id
	 * @param int $pay_code_id
	 * @return bool
	 */
	function createPremiumPolicy( $company_id, $type, $contributing_shift_policy_id = 0, $pay_code_id = 0 ) {
		if ( $contributing_shift_policy_id === 0 ) {
			$contributing_shift_policy_id = TTUUID::getZeroID();
		}

		if ( $pay_code_id === 0 ) {
			$pay_code_id = TTUUID::getZeroID();
		}

		$ppf = TTnew( 'PremiumPolicyFactory' ); /** @var PremiumPolicyFactory $ppf */
		$ppf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Simple weekend premium
				$ppf->setName( 'Weekend' );
				$ppf->setType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$ppf->setPayCode( $pay_code_id );
				//$ppf->setRate( '1.33' ); //$1.33 per hour
				//$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 1') );

				break;
			case 20: //Simple evening premium
				$ppf->setName( 'Evening' );
				$ppf->setType( 10 );

				$ppf->setIncludePartialPunch( true );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '5:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( true );
				$ppf->setSat( false );
				$ppf->setSun( false );

				$ppf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$ppf->setPayCode( $pay_code_id );
				//$ppf->setWageGroup( $this->user_wage_groups[0] );
				//$ppf->setRate( '1.50' );
				//$ppf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Premium 2') );

				break;
		}

		if ( $ppf->isValid() ) {
			$insert_id = $ppf->Save();
			Debug::Text( 'Premium Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Premium Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $pay_code_id
	 * @return bool
	 */
	function createAbsencePolicy( $company_id, $type, $pay_code_id = 0 ) {
		if ( $pay_code_id === 0 ) {
			$pay_code_id = TTUUID::getZeroID();
		}

		$apf = TTnew( 'AbsencePolicyFactory' ); /** @var AbsencePolicyFactory $apf */
		$apf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Vacation
				$apf->setName( 'PTO/Vacation' );
				$apf->setPayCode( $pay_code_id );
				break;
			case 11: //Vacation (B) -- Same paycode, just different name for testing.
				$apf->setName( 'PTO/Vacation (B)' );
				$apf->setPayCode( $pay_code_id );
				break;
			case 20: //Bank Time
				$apf->setName( 'Bank Time' );
				$apf->setPayCode( $pay_code_id );
				break;
			case 30: //Sick Time
				$apf->setName( 'Sick Time' );
				$apf->setPayCode( $pay_code_id );
				break;
		}

		if ( $apf->isValid() ) {
			$insert_id = $apf->Save();
			Debug::Text( 'Absence Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Absence Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $pay_code_id
	 * @return bool
	 */
	function createMealPolicy( $company_id, $pay_code_id = 0 ) {
		if ( $pay_code_id === 0 ) {
			$pay_code_id = TTUUID::getZeroID();
		}

		$mpf = TTnew( 'MealPolicyFactory' ); /** @var MealPolicyFactory $mpf */

		$mpf->setCompany( $company_id );
		$mpf->setName( 'One Hour Min.' );
		$mpf->setType( 20 );
		$mpf->setTriggerTime( ( 3600 * 5 ) );
		$mpf->setAmount( 3600 );
		$mpf->setStartWindow( ( 3600 * 4 ) );
		$mpf->setWindowLength( ( 3600 * 2 ) );
		$mpf->setPayCode( $pay_code_id );
		if ( $mpf->isValid() ) {
			$insert_id = $mpf->Save();
			Debug::Text( 'Meal Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Meal Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id     UUID
	 * @param string $meal_policy_id UUID
	 * @return bool
	 */
	function createSchedulePolicy( $company_id, $meal_policy_id ) {
		$spf = TTnew( 'SchedulePolicyFactory' ); /** @var SchedulePolicyFactory $spf */

		$spf->setCompany( $company_id );
		$spf->setName( 'One Hour Lunch' );
		$spf->setStartStopWindow( 1800 );

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save( false );

			$spf->setMealPolicy( $meal_policy_id );

			Debug::Text( 'Schedule Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Schedule Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_review_control_id UUID
	 * @param $type
	 * @param string $kpi_id                 UUID
	 * @return bool
	 */
	function createUserReview( $user_review_control_id, $type, $kpi_id ) {
		$urf = TTnew( 'UserReviewFactory' ); /** @var UserReviewFactory $urf */
		$urf->setUserReviewControl( $user_review_control_id );
		$urf->setKPI( $kpi_id );
		$urf->setNote( '' );
		switch ( $type ) {
			case 10:
				$urf->setRating( rand( 1, 10 ) );
				break;
			case 20:
				$urf->setRating( 1 );
				break;
		}

		if ( $urf->isValid() ) {
			$insert_id = $urf->Save();
			Debug::Text( 'User Review ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}
		Debug::Text( 'Failed Creating User Review!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function createExceptionPolicy( $company_id ) {
		$epcf = TTnew( 'ExceptionPolicyControlFactory' ); /** @var ExceptionPolicyControlFactory $epcf */

		$epcf->setCompany( $company_id );
		$epcf->setName( 'Default' );

		if ( $epcf->isValid() ) {
			$epc_id = $epcf->Save();
			Debug::Text( 'aException Policy Control ID: ' . $epc_id, __FILE__, __LINE__, __METHOD__, 10 );

			$data['exceptions'] = [
					'S1' => [
							'active'      => true,
							'severity_id' => 10,
					],
					'S2' => [
							'active'      => true,
							'severity_id' => 20,
					],
					'S3' => [
							'active'       => true,
							'severity_id'  => 10,
							'grace'        => 300,
							'watch_window' => 3600,
					],
					'S4' => [
							'active'       => true,
							'severity_id'  => 20,
							'grace'        => 300,
							'watch_window' => 3600,

					],
					'S5' => [
							'active'       => true,
							'severity_id'  => 20,
							'grace'        => 300,
							'watch_window' => 3600,

					],
					'S6' => [
							'active'       => true,
							'severity_id'  => 10,
							'grace'        => 300,
							'watch_window' => 3600,
					],
					'S7' => [
							'active'      => true,
							'severity_id' => 20,
					],
					'S8' => [
							'active'      => true,
							'severity_id' => 10,
					],
					'L3' => [
							'active'      => true,
							'severity_id' => 20,
					],

					//Switch all critical exceptions to HIGH so timesheets can be verified.
					'M1' => [
							'active'      => true,
							'severity_id' => 25,
					],
					'M2' => [
							'active'      => true,
							'severity_id' => 25,
					],
					'M3' => [
							'active'      => true,
							'severity_id' => 25,
					],
					'M4' => [
							'active'      => true,
							'severity_id' => 25,
					],
					'V1' => [
							'active'      => true,
							'severity_id' => 20,
					],
			];

			if ( count( $data['exceptions'] ) > 0 ) {
				foreach ( $data['exceptions'] as $code => $exception_data ) {
					Debug::Text( 'Looping Code: ' . $code, __FILE__, __LINE__, __METHOD__, 10 );

					$epf = TTnew( 'ExceptionPolicyFactory' ); /** @var ExceptionPolicyFactory $epf */
					$epf->setExceptionPolicyControl( $epc_id );
					if ( isset( $exception_data['active'] ) ) {
						$epf->setActive( true );
					} else {
						$epf->setActive( false );
					}
					$epf->setType( $code );
					$epf->setSeverity( $exception_data['severity_id'] );
					if ( isset( $exception_data['demerit'] ) && $exception_data['demerit'] != '' ) {
						$epf->setDemerit( $exception_data['demerit'] );
					}
					if ( isset( $exception_data['grace'] ) && $exception_data['grace'] != '' ) {
						$epf->setGrace( $exception_data['grace'] );
					}
					if ( isset( $exception_data['watch_window'] ) && $exception_data['watch_window'] != '' ) {
						$epf->setWatchWindow( $exception_data['watch_window'] );
					}
					if ( $epf->isValid() ) {
						$epf->Save();
					}
				}

				Debug::Text( 'Creating Exception Policy ID: ' . $epc_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $epc_id;
			}
		}

		Debug::Text( 'Failed Creating Exception Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id    UUID
	 * @param $type
	 * @param $rate_type
	 * @param string $kpi_group_ids UUID
	 * @return bool
	 */
	function createKPI( $company_id, $type, $rate_type, $kpi_group_ids = null ) {
		$kf = TTnew( 'KPIFactory' ); /** @var KPIFactory $kf */
		$kf->StartTransaction();
		$kf->setCompany( $company_id );
		switch ( $type ) {
			case 10:
				$kf->setName( 'Works well with others?' );
				$kf->setStatus( 10 );
				$kf->setType( 10 ); //Scale
				$kf->setDescription( '' );
				break;
			case 20:
				$kf->setName( 'Ability to manage time efficiently?' );
				$kf->setStatus( 10 );
				$kf->setType( 10 ); //Scale
				$kf->setDescription( '' );
				break;
			case 30:
				$kf->setName( 'Finishes projects on time?' );
				$kf->setStatus( 10 );
				$kf->setType( 10 ); //Scale
				$kf->setDescription( '' );
				break;
			case 40:
				$kf->setName( 'How satisified are you with your current position?' );
				$kf->setStatus( 15 );
				$kf->setType( 10 ); //Scale
				$kf->setDescription( '' );
				break;
			case 50:
				$kf->setName( 'Positive Attitude?' );
				$kf->setStatus( 15 );
				$kf->setType( 10 ); //Scale
				$kf->setDescription( '' );
				break;
			case 60:
				$kf->setName( 'What can I do to make you more successful?' );
				$kf->setStatus( 15 );
				$kf->setType( 30 ); //Text
				$kf->setDescription( '' );
				break;
			case 70:
				$kf->setName( 'How can you work better with your supervisor?' );
				$kf->setStatus( 10 );
				$kf->setType( 30 ); //Text
				$kf->setDescription( 'In the past 12 months tell me what you have learnt about the role you play in a group and how your supervisor can best work with you in that role.' );
				break;
		}

		if ( $rate_type != 60 && $rate_type != 70 ) {
			$kf->setMinimumRate( 1 );
			$kf->setMaximumRate( 10 );
		}

		if ( $kf->isValid() ) {
			$insert_id = $kf->Save( false );

			if ( is_array( $kpi_group_ids ) ) {
				$kf->setGroup( $kpi_group_ids );
			} else {
				$kf->setGroup( [] );
			}

			if ( $kf->isValid() ) {
				$kf->Save();
				$kf->CommitTransaction();

				Debug::Text( 'Creating KPI ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $insert_id;
			}
		}

		$kf->FailTransaction();
		$kf->CommitTransaction();

		Debug::Text( 'Failed Creating KPI!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id           UUID
	 * @param string $meal_policy_ids      UUID
	 * @param string $exception_policy_id  UUID
	 * @param string $holiday_policy_ids   UUID
	 * @param string $over_time_policy_ids UUID
	 * @param string $premium_policy_ids   UUID
	 * @param string $rounding_policy_ids  UUID
	 * @param string $user_ids             UUID
	 * @param string $break_policy_ids     UUID
	 * @param string $accrual_policy_ids   UUID
	 * @param string $expense_policy_ids   UUID
	 * @param string $absence_policy_ids   UUID
	 * @param string $regular_policy_ids   UUID
	 * @return bool
	 */
	function createPolicyGroup( $company_id, $meal_policy_ids = null, $exception_policy_id = null, $holiday_policy_ids = null, $over_time_policy_ids = null, $premium_policy_ids = null, $rounding_policy_ids = null, $user_ids = null, $break_policy_ids = null, $accrual_policy_ids = null, $expense_policy_ids = null, $absence_policy_ids = null, $regular_policy_ids = null ) {
		$pgf = TTnew( 'PolicyGroupFactory' ); /** @var PolicyGroupFactory $pgf */
		$pgf->StartTransaction();
		$pgf->setCompany( $company_id );
		$pgf->setName( 'Default' );

		if ( $exception_policy_id != '' ) {
			$pgf->setExceptionPolicyControlID( $exception_policy_id );
		}

		if ( $pgf->isValid() ) {
			$insert_id = $pgf->Save( false );

			if ( is_array( $regular_policy_ids ) ) {
				$pgf->setRegularTimePolicy( $regular_policy_ids );
			} else {
				$pgf->setRegularTimePolicy( [] );
			}

			if ( is_array( $meal_policy_ids ) ) {
				$pgf->setMealPolicy( $meal_policy_ids );
			} else {
				$pgf->setMealPolicy( [] );
			}

			if ( is_array( $break_policy_ids ) ) {
				$pgf->setBreakPolicy( $break_policy_ids );
			} else {
				$pgf->setBreakPolicy( [] );
			}

			if ( is_array( $over_time_policy_ids ) ) {
				$pgf->setOverTimePolicy( $over_time_policy_ids );
			} else {
				$pgf->setOverTimePolicy( [] );
			}

			if ( is_array( $premium_policy_ids ) ) {
				$pgf->setPremiumPolicy( $premium_policy_ids );
			} else {
				$pgf->setPremiumPolicy( [] );
			}

			if ( is_array( $rounding_policy_ids ) ) {
				$pgf->setRoundIntervalPolicy( $rounding_policy_ids );
			} else {
				$pgf->setRoundIntervalPolicy( [] );
			}

			if ( is_array( $holiday_policy_ids ) ) {
				$pgf->setHolidayPolicy( $holiday_policy_ids );
			} else {
				$pgf->setHolidayPolicy( [] );
			}

			if ( is_array( $expense_policy_ids ) ) {
				$pgf->setExpensePolicy( $expense_policy_ids );
			} else {
				$pgf->setExpensePolicy( [] );
			}

			if ( is_array( $accrual_policy_ids ) ) {
				$pgf->setAccrualPolicy( $accrual_policy_ids );
			} else {
				$pgf->setAccrualPolicy( [] );
			}

			if ( is_array( $absence_policy_ids ) ) {
				$pgf->setAbsencePolicy( $absence_policy_ids );
			} else {
				$pgf->setAbsencePolicy( [] );
			}

			if ( is_array( $user_ids ) ) {
				$pgf->setUser( $user_ids );
			} else {
				$pgf->setUser( [] );
			}

			if ( $pgf->isValid() ) {
				$pgf->Save();
				$pgf->CommitTransaction();

				Debug::Text( 'Creating Policy Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		$pgf->FailTransaction();
		$pgf->CommitTransaction();

		Debug::Text( 'Failed Creating Policy Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_ids   UUID
	 * @return bool
	 */
	function createPayPeriodSchedule( $company_id, $user_ids ) {
		$ppsf = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $ppsf */
		$ppsf->setCompany( $company_id );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );
		$ppsf->setTimeZone( TTDate::getTimeZone() );

		$anchor_date = TTDate::getBeginWeekEpoch( ( $this->getDate() - ( 86400 * 28 ) ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );
		$ppsf->setTransactionDateBusinessDay( true );
		$ppsf->setDayStartTime( 0 );
		$ppsf->setShiftAssignedDay( 10 );    //Day the shift starts on.
		$ppsf->setNewDayTriggerTime( ( 4 * 3600 ) );
		$ppsf->setMaximumShiftTime( ( 16 * 3600 ) );

		//Make sure timesheet verifications are enabled.
		$ppsf->setTimeSheetVerifyType( 40 ); //Employee & Supervisor
		$ppsf->setTimeSheetVerifyBeforeEndDate( 999 );
		$ppsf->setTimeSheetVerifyBeforeTransactionDate( -999 );

		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save( false );
			Debug::Text( 'Pay Period Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			//Dont create pay periods twice.
			$ppsf->setEnableInitialPayPeriods( false );
			$ppsf->setUser( $user_ids );
			$ppsf->Save();

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createUserGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$ugf = TTnew( 'UserGroupFactory' ); /** @var UserGroupFactory $ugf */
		$ugf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$ugf->setParent( $parent_id );
				$ugf->setName( 'Corporate' );

				break;
			case 20:
				$ugf->setParent( $parent_id );
				$ugf->setName( 'Executives' );

				break;
			case 30:
				$ugf->setParent( $parent_id );
				$ugf->setName( 'Human Resources' );

				break;
			case 40:
				$ugf->setParent( $parent_id );
				$ugf->setName( 'Hourly (Non-Exempt)' );

				break;
			case 50:
				$ugf->setParent( $parent_id );
				$ugf->setName( 'Salary (Exempt)' );

				break;
		}

		if ( $ugf->isValid() ) {
			$insert_id = $ugf->Save();
			Debug::Text( 'Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createKPIGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$kgf = TTnew( 'KPIGroupFactory' ); /** @var KPIGroupFactory $kgf */
		$kgf->setCompany( $company_id );
		switch ( $type ) {
			case 10:
				$kgf->setParent( $parent_id );
				$kgf->setName( 'Carpenter' );
				break;
			case 20:
				$kgf->setParent( $parent_id );
				$kgf->setName( 'Painter' );
				break;
			case 30:
				$kgf->setParent( $parent_id );
				$kgf->setName( 'General Laborer' );
				break;
			case 40:
				$kgf->setParent( $parent_id );
				$kgf->setName( 'Plumber' );
				break;
			case 50:
				$kgf->setParent( $parent_id );
				$kgf->setName( 'Electrician' );
				break;
		}
		if ( $kgf->isValid() ) {
			$insert_id = $kgf->Save();
			Debug::Text( 'Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			//$this->createQualification( $company_id, $type, $insert_id );
			return $insert_id;
		}

		Debug::Text( 'Failed Creating KPI Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createQualificationGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$qgf = TTnew( 'QualificationGroupFactory' ); /** @var QualificationGroupFactory $qgf */
		$qgf->setCompany( $company_id );
		switch ( $type ) {
			case 10:
				$qgf->setParent( $parent_id );
				$qgf->setName( 'Communication/People' );
				break;
			case 20:
				$qgf->setParent( $parent_id );
				$qgf->setName( 'Technical' );
				break;
			case 30:
				$qgf->setParent( $parent_id );
				$qgf->setName( 'Management/Leadership' );
				break;
			case 40:
				$qgf->setParent( $parent_id );
				$qgf->setName( 'Organizational' );
				break;
			case 50:
				$qgf->setParent( $parent_id );
				$qgf->setName( 'Time Management' );
				break;
		}

		if ( $qgf->isValid() ) {
			$insert_id = $qgf->Save();
			Debug::Text( 'Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			//$this->createQualification( $company_id, $type, $insert_id );
			return $insert_id;
		}

		Debug::Text( 'Failed Creating Qualification Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id             UUID
	 * @param $type
	 * @param string $qualification_group_id UUID
	 * @return bool
	 */
	function createQualification( $company_id, $type, $qualification_group_id ) {
		$qf = TTnew( 'QualificationFactory' ); /** @var QualificationFactory $qf */

		$qf->setCompany( $company_id );
		switch ( $type ) {

			//Skills
			case 10:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Carpentry' );
				$qf->setDescription( '' );
				break;
			case 20:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Bricklaying' );
				$qf->setDescription( '' );
				break;
			case 30:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Masonary' );
				$qf->setDescription( '' );
				break;
			case 40:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Electrical' );
				$qf->setDescription( '' );
				break;
			case 50:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Plumbing' );
				$qf->setDescription( '' );
				break;
			case 60:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Drywall' );
				$qf->setDescription( '' );
				break;
			case 70:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Heating, Ventilation, Air Conditioning' );
				$qf->setDescription( '' );
				break;
			case 71:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Communication' );
				$qf->setDescription( '' );
				break;
			case 72:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Works well with others' );
				$qf->setDescription( '' );
				break;
			case 73:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Leadership' );
				$qf->setDescription( '' );
				break;
			case 74:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Organization' );
				$qf->setDescription( '' );
				break;
			case 75:
				$qf->setType( 10 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Time Management' );
				$qf->setDescription( '' );
				break;

			//Licenses
			case 200:
				$qf->setType( 30 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'First Aid' );
				$qf->setDescription( '' );
				break;
			case 210:
				$qf->setType( 30 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Electrician' );
				$qf->setDescription( '' );
				break;
			case 220:
				$qf->setType( 30 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Plumber' );
				$qf->setDescription( '' );
				break;
			case 230:
				$qf->setType( 30 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Masonary' );
				$qf->setDescription( '' );
				break;
			case 240:
				$qf->setType( 30 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Heating, Ventilation, Air Conditioning' );
				$qf->setDescription( '' );
				break;

			//Education -
			case 300:
				$qf->setType( 20 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Trade School - Electrian' );
				$qf->setDescription( '' );
				break;
			case 310:
				$qf->setType( 20 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Trade School - Plumber' );
				$qf->setDescription( '' );
				break;
			case 320:
				$qf->setType( 20 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Trade School - Masonary' );
				$qf->setDescription( '' );
				break;
			case 330:
				$qf->setType( 20 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Trade School - HVAC' );
				$qf->setDescription( '' );
				break;
			case 340:
				$qf->setType( 20 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Bachelor of Engineering' );
				$qf->setDescription( '' );
				break;
			case 350:
				$qf->setType( 20 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Master of Engineering' );
				$qf->setDescription( '' );
				break;

			//Language -
			case 400:
				$qf->setType( 40 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'English' );
				$qf->setDescription( '' );
				break;
			case 410:
				$qf->setType( 40 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'French' );
				$qf->setDescription( '' );
				break;
			case 420:
				$qf->setType( 40 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Spanish' );
				$qf->setDescription( '' );
				break;

			//Memberships -
			case 500:
				$qf->setType( 50 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Electrians Union' );
				$qf->setDescription( '' );
				break;
			case 510:
				$qf->setType( 50 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Plumbers Union' );
				$qf->setDescription( '' );
				break;
			case 520:
				$qf->setType( 50 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Masonary Union' );
				$qf->setDescription( '' );
				break;
			case 530:
				$qf->setType( 50 );
				$qf->setGroup( $qualification_group_id );
				$qf->setName( 'Heating, Ventilation, Air Conditioning Union' );
				$qf->setDescription( '' );
				break;
		}

		if ( $qf->isValid() ) {
			$insert_id = $qf->Save();
			Debug::Text( 'Qualification ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Qualification!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	function createUserTitle( $company_id, $type ) {
		$utf = TTnew( 'UserTitleFactory' ); /** @var UserTitleFactory $utf */
		$utf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$utf->setName( 'Carpenter' );
				break;
			case 20:
				$utf->setName( 'Painter' );
				break;
			case 30:
				$utf->setName( 'General Laborer' );
				break;
			case 40:
				$utf->setName( 'Plumber' );
				break;
			case 50:
				$utf->setName( 'Electrician' );
				break;
			case 60:
				$utf->setName( 'Construction Manager' );
				break;
			case 70:
				$utf->setName( 'Heavy Equipment Operator' );
				break;
			case 80:
				$utf->setName( 'Landscaper' );
				break;
			case 90:
				$utf->setName( 'Engineer' );
				break;
		}

		if ( $utf->isValid() ) {
			$insert_id = $utf->Save();
			Debug::Text( 'Title ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Title!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	function createEthnicGroup( $company_id, $type ) {
		$egf = TTnew( 'EthnicGroupFactory' ); /** @var EthnicGroupFactory $egf */
		$egf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$egf->setName( 'White' );
				break;
			case 20:
				$egf->setName( 'African' );
				break;
			case 30:
				$egf->setName( 'Asian' );
				break;
			case 40:
				$egf->setName( 'Hispanic' );
				break;
			case 50:
				$egf->setName( 'Native American' );
				break;
		}

		if ( $egf->isValid() ) {
			$insert_id = $egf->Save();
			Debug::Text( 'Ethnic Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Ethnic Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $user_id UUID
	 * @return bool
	 */
	function createUserContact( $user_id ) {
		$ucf = TTnew( 'UserContactFactory' ); /** @var UserContactFactory $ucf */
		$ucf->setUser( $user_id );
		$ucf->setStatus( 10 );
		$ucf->setType( ( rand( 1, 7 ) * 10 ) );

		$first_name = $this->getRandomFirstName();
		$last_name = $this->getRandomLastName();
		if ( $first_name != '' && $last_name != '' ) {
			$ucf->setFirstName( $first_name );
			$ucf->setLastName( $last_name );
			$ucf->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
			$ucf->setAddress2( 'Unit #' . rand( 10, 999 ) );
			$ucf->setCity( $this->getRandomArrayValue( $this->city_names ) );

			$ucf->setCountry( 'US' );
			$ucf->setProvince( 'WA' );

			$ucf->setPostalCode( rand( 98000, 99499 ) );
			$ucf->setWorkPhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
			$ucf->setWorkPhoneExt( rand( 100, 1000 ) );
			$ucf->setHomePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
			$ucf->setMobilePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
			$ucf->setWorkEmail( $first_name . '.' . $last_name . '@abc-company.com' );
			$ucf->setSIN( $this->generateSIN( 'US' ) );
			$ucf->setBirthDate( strtotime( rand( 1970, 1990 ) . '-' . rand( 1, 12 ) . '-' . rand( 1, 28 ) ) );
		}
		unset( $first_name, $last_name );

		if ( $ucf->isValid() ) {
			$insert_id = $ucf->Save();
			Debug::Text( 'User Contact ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Contact!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $user_id           UUID
	 * @param string $expense_policy_id UUID
	 * @param int $default_branch_id
	 * @param int $default_department_id
	 * @param int $default_currency_id
	 * @param int $job_id
	 * @param int $job_item_id
	 * @param bool $reimburse
	 * @return bool
	 */
	function createUserExpense( $user_id, $expense_policy_id, $default_branch_id = 0, $default_department_id = 0, $default_currency_id = 0, $job_id = 0, $job_item_id = 0, $reimburse = true ) {
		if ( $default_branch_id === 0 ) {
			$default_branch_id = TTUUID::getZeroID();
		}

		if ( $default_department_id === 0 ) {
			$default_department_id = TTUUID::getZeroID();
		}

		if ( $default_currency_id === 0 ) {
			$default_currency_id = TTUUID::getZeroID();
		}

		if ( $job_id === 0 ) {
			$job_id = TTUUID::getZeroID();
		}

		if ( $job_item_id === 0 ) {
			$job_item_id = TTUUID::getZeroID();
		}

		$uef = TTnew( 'UserExpenseFactory' ); /** @var UserExpenseFactory $uef */
		$uef->setStatus( 20 ); //Pending authorization.
		$uef->setUser( $user_id );
		$uef->setExpensePolicy( $expense_policy_id );
		$uef->setBranch( $default_branch_id );
		$uef->setDepartment( $default_department_id );
		$uef->setJob( $job_id );
		$uef->setJobItem( $job_item_id );
		$uef->setCurrency( $default_currency_id );
		$uef->setPaymentMethod( ( rand( 2, 8 ) * 5 ) );
		$uef->setIncurredDate( ( $this->getDate() - ( 86400 * rand( 7, 10 ) ) ) );
		$uef->setReimbursable( $reimburse );
		$uef->setGrossAmount( rand( 1, 10 ) * 100 );
		if ( $uef->isValid() ) {
			$insert_id = $uef->Save();
			Debug::Text( 'User Expense ID: ' . $insert_id . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Expense!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id       UUID
	 * @param $type
	 * @param string $user_ids         UUID
	 * @param string $client_group_ids UUID
	 * @return bool
	 */
	function createClient( $company_id, $type, $user_ids = null, $client_group_ids = null ) {
		$cf = TTnew( 'ClientFactory' ); /** @var ClientFactory $cf */
		$cf->setCompany( $company_id );
		$cf->setStatus( 10 );
		$cf->setSalesContact( $this->getRandomArrayValue( (array)$user_ids ) );
		$cf->setSupportContact( $this->getRandomArrayValue( (array)$user_ids ) );
		//$cf->setGroup( array_rand((array)$client_group_ids));
		$cf->setNote( '' );

		switch ( $type ) {
			case 10:
				$cf->setCompanyName( 'L&A Home Builders' );
				$cf->setWebsite( 'www.aoya-hk.com' );
				$cf->setGroup( $client_group_ids[0] );
				break;
			case 20:
				$cf->setCompanyName( 'ACME Construction' );
				$cf->setWebsite( 'www.acme.net' );
				$cf->setGroup( $client_group_ids[0] );
				break;
			case 30:
				$cf->setCompanyName( 'SNC LAVALIN' );
				$cf->setWebsite( 'www.snclavalin.com' );
				$cf->setGroup( $client_group_ids[1] );
				break;
			case 40:
				$cf->setCompanyName( 'HATCH Building Supplies' );
				$cf->setWebsite( 'www.hatch.ca' );
				$cf->setGroup( $client_group_ids[2] );
				break;
			case 50:
				$cf->setCompanyName( 'BLVD Engineering' );
				$cf->setWebsite( 'www.blvd.com.cn' );
				$cf->setGroup( $client_group_ids[4] );
				break;
			case 60:
				$cf->setCompanyName( 'PCL Technology' );
				$cf->setWebsite( 'www.pcl.com' );
				$cf->setGroup( $client_group_ids[0] );
				break;
			case 70:
				$cf->setCompanyName( 'WALSH CONSTRUCTION' );
				$cf->setWebsite( 'www.walshgroup.com' );
				$cf->setGroup( $client_group_ids[0] );
				break;
			case 80:
				$cf->setCompanyName( 'CUC Technology' );
				$cf->setWebsite( 'canadianutility.com' );
				$cf->setGroup( $client_group_ids[2] );
				break;
			case 90:
				$cf->setCompanyName( 'AEON Construction' );
				$cf->setWebsite( 'www.aeon.com' );
				$cf->setGroup( $client_group_ids[1] );
				break;
			case 100:
				$cf->setCompanyName( 'WLK Landscaping' );
				$cf->setWebsite( 'www.wlklandscape.com' );
				$cf->setGroup( $client_group_ids[3] );
				break;
		}

		if ( $cf->isValid() ) {
			$insert_id = $cf->Save();
			Debug::Text( 'Client ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Client!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	function createInvoiceDistrict( $company_id, $type ) {
		$cidf = TTnew( 'InvoiceDistrictFactory' ); /** @var InvoiceDistrictFactory $cidf */
		$cidf->setCompany( $company_id );
		switch ( $type ) {
			case 10:
				$cidf->setName( 'US - New York' );
				$cidf->setCountry( 'US' );
				$cidf->setProvince( 'NY' );
				break;
			case 20:
				$cidf->setName( 'US - Washington' );
				$cidf->setCountry( 'US' );
				$cidf->setProvince( 'WA' );
				break;
			case 30:
				$cidf->setName( 'CA - Ontario' );
				$cidf->setCountry( 'CA' );
				$cidf->setProvince( 'ON' );
				break;
			case 40:
				$cidf->setName( 'CA - Alberta' );
				$cidf->setCountry( 'CA' );
				$cidf->setProvince( 'AB' );
				break;
		}
		if ( $cidf->isValid() ) {
			$insert_id = $cidf->Save();
			Debug::Text( 'Invoice District ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Invoice District!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $client_id            UUID
	 * @param $type
	 * @param string $invoice_district_ids UUID
	 * @param string $default_currency_id  UUID
	 * @return bool
	 */
	function createClientContact( $client_id, $type, $invoice_district_ids, $default_currency_id ) {
		$ccf = TTnew( 'ClientContactFactory' ); /** @var ClientContactFactory $ccf */

		$first_name = $this->getRandomFirstName();
		$last_name = $this->getRandomLastName();
		$ccf->setClient( $client_id );
		$ccf->setInvoiceDistrict( $this->getRandomArrayValue( (array)$invoice_district_ids ) );
		$ccf->setCurrency( $default_currency_id );
		$ccf->setStatus( 10 );
		$ccf->setType( $type );
		$ccf->setFirstName( $first_name );
		$ccf->setLastName( $last_name );
		$ccf->setDefault( true );
		$ccf->setUserName( $first_name . '.' . $last_name . $this->getUserNamePostfix() );
		$ccf->setEmail( $first_name . '.' . $last_name . '@abc-company.com' );
		$ccf->setWorkPhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$ccf->setWorkPhoneExt( rand( 100, 1000 ) );
		$ccf->setMobilePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$ccf->setFaxPhone( '' );
		$ccf->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
		$ccf->setAddress2( 'Unit #' . rand( 10, 999 ) );
		$ccf->setCity( $this->getRandomArrayValue( $this->city_names ) );
		$ccf->setCountry( 'US' );
		$ccf->setProvince( 'WA' );
		$ccf->setPostalCode( rand( 98000, 99499 ) );

		$ccf->setPassword( 'demo' );
		$ccf->setPasswordResetKey( '1234' );
		$ccf->setPasswordResetDate( ( $this->getDate() - ( 86400 * rand( 1, 30 ) ) ) );
		$ccf->setNote( '' );

		$ccf->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
		$ccf->setLongitude( $this->getRandomCoordinates( 'longitude' ) );

		if ( $ccf->isValid() ) {
			$insert_id = $ccf->Save();
			Debug::Text( 'Client Contact ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Client Contact!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id          UUID
	 * @param string $client_id           UUID
	 * @param string $currency_id         UUID
	 * @param $products
	 * @param int $status_id
	 * @param null $payments
	 * @param string $user_ids            UUID
	 * @param string $shipping_policy_ids UUID
	 * @return bool
	 */
	function createInvoice( $company_id, $client_id, $currency_id, $products, $status_id = 10, $payments = null, $user_ids = null, $shipping_policy_ids = null ) {

		$if = TTnew( 'InvoiceFactory' ); /** @var InvoiceFactory $if */
		$tf = TTnew( 'TransactionFactory' ); /** @var TransactionFactory $tf */
		$clf = TTnew( 'ClientListFactory' ); /** @var ClientListFactory $clf */
		$plf = TTnew( 'ProductListFactory' ); /** @var ProductListFactory $plf */

		if ( isset( $client_id ) && $client_id != '' ) {
			$clf->getByIdAndCompanyId( $client_id, $company_id );
			if ( $clf->getRecordCount() > 0 ) {
				$c_obj = $clf->getCurrent();
			}
		}

		$client_billing_contact_obj = $c_obj->getClientContactObject( $c_obj->getDefaultBillingContact() );
		if ( is_object( $client_billing_contact_obj ) ) {
			$default_currency = $client_billing_contact_obj->getCurrency();
		} else {
			$default_currency = $currency_id;
		}

		unset( $client_billing_contact_obj );

		$invoice_date = strtotime( TTDate::getYear() . '-' . rand( 1, 12 ) . '-' . rand( 1, 28 ) );

		$if->StartTransaction();

		$if->setClient( $client_id );
		$if->setStatus( $status_id );
		$if->setBillingContact( $c_obj->getDefaultBillingContact() );
		$if->setShippingContact( $c_obj->getDefaultShippingContact() );
		$if->setOtherContact( $c_obj->getDefaultOtherContact() );
		$if->setSalesContact( $this->getRandomArrayValue( (array)$user_ids ) );
		$if->setCurrency( $default_currency );
		$if->setOrderDate( $invoice_date );
		$if->setInvoiceDate( $if->getOrderDate() );

		if ( $shipping_policy_ids != null ) {
			$shipping_policy_id = $this->getRandomArrayValue( (array)$shipping_policy_ids );
		} else {
			$shipping_policy_id = TTUUID::getZeroID();
		}

		$combined_shipping_policy_arr = ShippingPolicyFactory::parseCombinedShippingPolicyServiceId( $shipping_policy_id );
		if ( isset( $combined_shipping_policy_arr['shipping_policy_id'] ) ) {
			$shipping_policy_id = $combined_shipping_policy_arr['shipping_policy_id'];
		}
		if ( isset( $combined_shipping_policy_arr['shipping_policy_service_id'] ) ) {
			$shipping_policy_service_id = $combined_shipping_policy_arr['shipping_policy_service_id'];
		} else {
			$shipping_policy_service_id = $this->getRandomArrayValue( (array)$shipping_policy_ids );
		}

		$if->setShippingPolicy( $shipping_policy_id );
		$if->setShippingPolicyService( $shipping_policy_service_id );

		$if->setPublicNote( '' );
		$if->setPrivateNote( '' );

		if ( $if->isValid() ) {
			$invoice_id = $if->Save( false );
		}

		if ( !is_array( $products ) ) {
			$products = (array)$products;
		}

		foreach ( $products as $key => $product_id ) {
			Debug::Text( 'Product Id: ' . $product_id, __FILE__, __LINE__, __METHOD__, 10 );
			$plf->getByIdAndCompanyId( $product_id, $company_id );
			if ( $plf->getRecordCount() > 0 ) {
				foreach ( $plf as $pf ) {
					$transactions[$key]['counter'] = $key;
					$transactions[$key]['type_id'] = 10;
					$transactions[$key]['product_id'] = $product_id;
					$transactions[$key]['product_type_id'] = $pf->getType();
					$transactions[$key]['product_part_number'] = $pf->getPartNumber();
					$transactions[$key]['product_name'] = $pf->getName();
					$transactions[$key]['description'] = '';
					$transactions[$key]['currency_id'] = $default_currency;
					$transactions[$key]['unit_price'] = $pf->getUnitPrice();
					$transactions[$key]['pro_rate_numerator'] = 1;
					$transactions[$key]['pro_rate_denominator'] = 1;
					$transactions[$key]['unit_cost'] = $pf->getUnitCost();
					$transactions[$key]['quantity'] = ( rand( 1, 10 ) * 10 );
					$transactions[$key]['amount'] = bcmul( $transactions[$key]['unit_price'], $transactions[$key]['quantity'] );
				}
			}
			if ( isset( $payments ) ) {
				$payments[$key]['counter'] = $key;
				$payments[$key]['payment_type_id'] = 10;
				$payments[$key]['currency_id'] = $default_currency;
				$payments[$key]['amount'] = Misc::MoneyRound( $transactions[$key]['amount'] );

				$taxes_arr = $if->calcTaxes( [ $transactions[$key] ] );

				//Debug::Arr($taxes_arr, 'Taxes...: ', __FILE__, __LINE__, __METHOD__, 10);
				if ( is_array( $taxes_arr ) ) {
					foreach ( $taxes_arr as $ptp_data ) {
						$payments[$key]['amount'] = Misc::MoneyRound( bcadd( $payments[$key]['amount'], $ptp_data['amount'] ) );
					}
				}
			}

			//Debug::Arr($transactions[$key], 'Single Transaction...: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($payments[$key], 'Single Payment...: ', __FILE__, __LINE__, __METHOD__, 10);

		}

		//Debug::Arr($transactions, 'Transaction...: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($payments, 'Payment...: ', __FILE__, __LINE__, __METHOD__, 10);


		$is_credit_transaction_valid = true;
		$is_debit_transaction_valid = true;

		//Add each transaction now.
		//Debug::Arr($transactions, 'Transactions: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( isset( $transactions ) && count( $transactions ) > 0 ) {
			Debug::Text( 'Inserting Transactions: ' . count( $transactions ), __FILE__, __LINE__, __METHOD__, 10 );

			foreach ( $transactions as $transaction_data ) {
				if ( $is_debit_transaction_valid == true && $if->setTransaction( $transaction_data ) == false ) {
					$is_debit_transaction_valid = false;
				}
			}
		}
		if ( isset( $payments ) && count( $payments ) > 0 ) {
			Debug::Text( 'Inserting Payments: ' . count( $payments ), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $payments as $payment_counter => $payment_data ) {
				$tf->setEffectiveDate( $this->getDate() );
				$tf->setClient( $client_id );
				$tf->setInvoice( $invoice_id );
				//$tf->setStatus( 10 ); //Don't set status, let preSave() handle that.
				$tf->setType( 20 ); //Credit

				$tf->setPaymentType( $payment_data['payment_type_id'] );
				/*
				if ( $payment_data['payment_type_id'] == 30 OR $payment_data['payment_type_id'] == 35 OR $payment_data['payment_type_id'] == 40 ) {
					$tf->setClientPayment($payment_data['client_payment_id']);
				}
				if ( $payment_data['payment_type_id'] != 10 ) {
					$tf->setConfirmNumber($payment_data['confirm_number']);
				}
				*/

				$tf->setCurrency( $payment_data['currency_id'] );
				$tf->setAmount( $payment_data['amount'] );
				if ( $is_credit_transaction_valid == true && $tf->isValid() == true ) {
					$save_result = $tf->Save();
//					if ( is_numeric($save_result) ) {
//						$payment_transaction_ids[$save_result] = $payment_counter;
//					}
				} else {
					$is_credit_transaction_valid = false;
				}

				unset( $payment_counter, $save_result );
			}
		}

		if ( $is_debit_transaction_valid == true && $is_credit_transaction_valid == true ) {
			if ( $if->getEnableCalcShipping() == true ) {
				Debug::Text( 'calculating shipping!!', __FILE__, __LINE__, __METHOD__, 10 );
				$if->insertShippingTransactions( $if->calcShipping() );
			}
			$if->insertTaxTransactions( $if->calcTaxes() );
			$if->CommitTransaction();

			//$if->setStatus( $if->determineStatus() );

			if ( $if->isValid() == true && $tf->Validator->isValid() == true ) {
				$if->Save( false );

				return $invoice_id;
			}
		} else {
			$if->FailTransaction();
			$if->CommitTransaction();
		}

		return false;
	}

	/**
	 * Generate a unique sin
	 * @param $company_id
	 * @param string $country
	 * @return int|string
	 */
	function generateSIN( $company_id, $country = 'CA' ) {
		//Get all sins from db once to ensure no duplicates

		if ( count( $this->generated_sins ) == 0 ) {
			$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
			$this->generated_sins = $uf->db->GetAll( 'select sin from users where company_id = ?', [ $company_id ] );
		}

		$sanity_count = 0;
		$sin = '';

		while ( ( $sin == '' || in_array( $sin, $this->generated_sins ) ) && $sanity_count < 30 ) {
			$sin = '';

			if ( $country == 'US' ) {
				for ( $x = 0; $x < 11; $x++ ) {
					if ( $x == 3 || $x == 7 ) {
						$sin .= '-';
					} else {
						$sin .= rand( 0, 9 );
					}
				}
			} else {
				//https://github.com/corbanworks/fng-sin-tools
				$valid_first_char = [ '1', '2', '3', '4', '5', '6', '7', '9' ];
				$sin = $valid_first_char[rand( 0, ( sizeof( $valid_first_char ) - 1 ) )];
				$length = 9;
				while ( strlen( $sin ) < ( $length - 1 ) ) {
					$sin .= rand( 0, 9 );
				}
				$sum = 0;
				$pos = 0;
				$reversedSIN = strrev( $sin );
				while ( $pos < $length - 1 ) {
					$odd = $reversedSIN[$pos] * 2;
					if ( $odd > 9 ) {
						$odd -= 9;
					}
					$sum += $odd;
					if ( $pos != ( $length - 2 ) ) {
						$sum += $reversedSIN[$pos + 1];
					}
					$pos += 2;
				}
				$checkdigit = ( ( floor( $sum / 10 ) + 1 ) * 10 - $sum ) % 10;
				$sin .= $checkdigit;
				$sin1 = substr( $sin, 0, 3 );
				$sin2 = substr( $sin, 3, 3 );
				$sin3 = substr( $sin, 6, 3 );
				$sin = $sin1 . ' ' . $sin2 . ' ' . $sin3;
			}

			$sanity_count++;
			if ( $sanity_count >= 10 ) {
				$sin = '';
			}
		}
		$this->generated_sins[] = $sin;

		return $sin;
	}

	/**
	 * @param string $company_id       UUID
	 * @param string $legal_entity_id  UUID
	 * @param $type
	 * @param int $policy_group_id
	 * @param int $default_branch_id
	 * @param int $default_department_id
	 * @param int $default_currency_id
	 * @param int $user_group_id
	 * @param int $user_title_id
	 * @param string $ethnic_group_ids UUID
	 * @param null $remittance_source_account_ids
	 * @param null $coordinates
	 * @param null $default_job_id
	 * @param null $default_job_item_id
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	function createUser( $company_id, $legal_entity_id, $type, $policy_group_id = null, $default_branch_id = null, $default_department_id = null, $default_currency_id = null, $user_group_id = null, $user_title_id = null, $ethnic_group_ids = null, $remittance_source_account_ids = null, $coordinates = null, $default_job_id = null, $default_job_item_id = null, $hire_date = null ) {
		//if ( $policy_group_id === null ) {
		//	$policy_group_id = TTUUID::getZeroID();
		//}

		if ( $default_branch_id === null ) {
			$default_branch_id = TTUUID::getZeroID();
		}

		if ( $default_department_id === null ) {
			$default_department_id = TTUUID::getZeroID();
		}

		if ( $default_job_id === null ) {
			$default_job_id = TTUUID::getZeroID();
		}

		if ( $default_job_item_id === null ) {
			$default_job_item_id = TTUUID::getZeroID();
		}

		if ( $default_currency_id === null ) {
			$default_currency_id = TTUUID::getZeroID();
		}

		if ( $user_group_id === null ) {
			$user_group_id = TTUUID::getZeroID();
		}

		if ( $user_title_id === null ) {
			$user_title_id = TTUUID::getZeroID();
		}

		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
		$uf->setId( $uf->getNextInsertId() ); //Because password encryption requires the user_id, we need to get it first when creating a new employee.
		$uf->setCompany( $company_id );
		$uf->setLegalEntity( $legal_entity_id );
		$uf->setStatus( 10 );

		if ( $default_currency_id == TTUUID::getZeroID() ) {
			Debug::Text( 'Get Default Currency...', __FILE__, __LINE__, __METHOD__, 10 );

			//Get Default.
			$crlf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $crlf */
			$crlf->getByCompanyIdAndDefault( $company_id, true );
			if ( $crlf->getRecordCount() > 0 ) {
				$default_currency_id = $crlf->getCurrent()->getId();
				Debug::Text( 'Default Currency ID: ' . $default_currency_id, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		srand( (int)($this->getRandomSeed() + $type) ); //Seed the random number the same for each createUser() call of the same type, so unit tests can rely on a constant hire date/employee wage.
		if ( $hire_date == '' ) {
			$hire_date = strtotime( rand( ( TTDate::getYear() - 10 ), ( TTDate::getYear() - 3 ) ) . '-' . rand( 1, 12 ) . '-' . rand( 1, 28 ) );
		}

		if ( empty( $ethnic_group_ids ) == false ) {
			$uf->setEthnicGroup( $this->getRandomArrayValue( (array)$ethnic_group_ids ) );
		}

		if ( is_object( $uf->getLegalEntityObject() ) ) {
			Debug::Text( '  Legal Entity: ' . $uf->getLegalEntityObject()->getTradeName() . ' Country: ' . $uf->getLegalEntityObject()->getCountry(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( strtoupper( $uf->getLegalEntityObject()->getCountry() ) == 'CA' ) {
				if ( ( $type >= 24 && $type <= 27 ) ) {
					$country = 'CA';
					$province = 'ON';
					$city = 'Toronto';
					$postal_code = 'M5E ' . rand( 1, 9 ) . 'A' . rand( 1, 9 );
				} else {
					$country = 'CA';
					$province = 'BC';
					$city = 'Vancouver';
					$postal_code = 'V5A ' . rand( 1, 9 ) . 'A' . rand( 1, 9 );
				}

				$sin = $this->generateSIN( $company_id, 'CA' );
			} else {
				if ( $type == 100 || ( $type >= 10 && $type <= 19 ) ) {
					$country = 'US';
					$province = 'NY';
					$city = 'New York';
					$postal_code = str_pad( rand( 400, 599 ), 5, 0, STR_PAD_LEFT );
				} else {
					$country = 'US';
					$province = 'WA';
					$city = 'Seattle';
					$postal_code = rand( 98000, 99499 );
				}

				$sin = $this->generateSIN( $company_id, 'US' );
			}
		} else {
			Debug::Text( '  ERROR: Legal entity is not defined or incorrect!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		switch ( $type ) {
			case 10: //John Doe
				$next_available_employee_number = $type;
				$uf->setUserName( 'john.doe' . $this->getUserNamePostfix() );

				//Set Phone ID/Password to test web quickpunch
				if ( $this->getEnableQuickPunch() == true ) {
					$uf->setPhoneId( '1235' . $this->getUserNamePostfix() );
					$uf->setPhonePassword( '1234' );
				}

				$uf->setFirstName( 'John' );
				$uf->setLastName( 'Doe' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Springfield St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 11: //Theodora	 Simmons
				$next_available_employee_number = $type;
				$uf->setUserName( 'theodora.simmons' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Theodora' );
				$uf->setLastName( 'Simmons' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Springfield St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 12: //Kitty  Nicholas
				$next_available_employee_number = $type;
				$uf->setUserName( 'kitty.nicholas' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Kitty' );
				$uf->setLastName( 'Nicholas' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Ethel St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 13: //Tristen	Braun
				$next_available_employee_number = $type;
				$uf->setUserName( 'tristen.braun' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Tristen' );
				$uf->setLastName( 'Braun' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Ethel St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 14: //Gale	 Mench
				$next_available_employee_number = $type;
				$uf->setUserName( 'gale.mench' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Gale' );
				$uf->setLastName( 'Mench' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Gordon St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 15: //Beau	 Mayers
				$next_available_employee_number = $type;
				$uf->setUserName( 'beau.mayers' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Beau' );
				$uf->setLastName( 'Mayers' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Gordon St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 16: //Ian	Schofield
				$next_available_employee_number = $type;
				$uf->setUserName( 'ian.schofield' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Ian' );
				$uf->setLastName( 'Schofield' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Sussex St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 17: //Gabe	 Hoffhants
				$next_available_employee_number = $type;
				$uf->setUserName( 'gabe.hoffhants' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Gabe' );
				$uf->setLastName( 'Hoffhants' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Sussex St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 18: //Franklin	 Mcmichaels
				$next_available_employee_number = $type;
				$uf->setUserName( 'franklin.mcmichaels' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Franklin' );
				$uf->setLastName( 'McMichaels' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Georgia St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 19: //Donald  Whitling
				$next_available_employee_number = $type;
				$uf->setUserName( 'donald.whitling' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Donald' );
				$uf->setLastName( 'Whitling' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Georgia St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 20: //Jane Doe
				$next_available_employee_number = $type;
				$uf->setUserName( 'jane.doe' . $this->getUserNamePostfix() );

				//Set Phone ID/Password to test web quickpunch
				if ( $this->getEnableQuickPunch() == true ) {
					$uf->setPhoneId( '1234' . $this->getUserNamePostfix() );
					$uf->setPhonePassword( '1234' );
				}

				$uf->setFirstName( 'Jane' );
				$uf->setLastName( 'Doe' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Ontario St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 21: //Tamera  Erschoff
				$next_available_employee_number = $type;
				$uf->setUserName( 'tamera.erschoff' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Tamera' );
				$uf->setLastName( 'Erschoff' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Ontario St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 22: //Redd	 Rifler
				$next_available_employee_number = $type;
				$uf->setUserName( 'redd.rifler' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Redd' );
				$uf->setLastName( 'Rifler' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Main St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 23: //Brent  Pawle
				$next_available_employee_number = $type;
				$uf->setUserName( 'brent.pawle' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Brent' );
				$uf->setLastName( 'Pawle' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Pandosy St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 24: //Heather	Grant
				$next_available_employee_number = $type;
				$uf->setUserName( 'heather.grant' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Heather' );
				$uf->setLastName( 'Grant' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Lakeshore St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 25: //Steph  Mench
				$next_available_employee_number = $type;
				$uf->setUserName( 'steph.mench' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Steph' );
				$uf->setLastName( 'Mench' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Dobbin St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 26: //Kailey  Klockman
				$next_available_employee_number = $type;
				$uf->setUserName( 'kailey.klockman' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Kailey' );
				$uf->setLastName( 'Klockman' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Spall St' );
				//$uf->setAddress2( 'Unit #123' );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 27: //Matt	 Marcotte
				$next_available_employee_number = $type;
				$uf->setUserName( 'matt.marcotte' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Matt' );
				$uf->setLastName( 'Marcotte' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Spall St' );
				//$uf->setAddress2( 'Unit #123' );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 28: //Nick	 Hanseu
				$next_available_employee_number = $type;
				$uf->setUserName( 'nick.hanseu' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Nick' );
				$uf->setLastName( 'Hanseu' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Gates St' );
				//$uf->setAddress2( 'Unit #123' );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 29: //Rich	 Wiggins
				$next_available_employee_number = $type;
				$uf->setUserName( 'rich.wiggins' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Rich' );
				$uf->setLastName( 'Wiggins' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Gates St' );
				//$uf->setAddress2( 'Unit #123' );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 30: //Mike Smith
				$next_available_employee_number = $type;
				$uf->setUserName( 'mike.smith' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'Mike' );
				$uf->setLastName( 'Smith' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Main St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 40: //John Hancock
				$next_available_employee_number = $type;
				$uf->setUserName( 'john.hancock' . $this->getUserNamePostfix() );

				$uf->setFirstName( 'John' );
				$uf->setLastName( 'Hancock' );
				$uf->setSex( 20 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Main St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 100: //Administrator
				$next_available_employee_number = $type;
				$hire_date = strtotime( '01-Jan-2001' ); //Force consistent hire date for the administrator, so other unit tests can rely on it.

				$uf->setUserName( 'demoadmin' . $this->getUserNamePostfix() );

				//Set Phone ID/Password to test web quickpunch
				if ( $this->getEnableQuickPunch() == true ) {
					$uf->setPhoneId( '1' . $this->getUserNamePostfix() . '34' );
					$uf->setPhonePassword( '1234' );
				}

				$uf->setFirstName( 'Mr.' );
				$uf->setLastName( 'Administrator' );
				$uf->setSex( 10 );
				$uf->setAddress1( rand( 100, 9999 ) . ' Main St' );
				$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$uf->setCity( $city );

				$uf->setCountry( $country );
				$uf->setProvince( $province );
				$uf->setPostalCode( $postal_code );
				break;
			case 999: //Random user
				$next_available_employee_number = $uf->getNextAvailableEmployeeNumber( $company_id );
				srand( (int)($this->getRandomSeed() + $type + $next_available_employee_number) ); //Re-seed random number otherwise all random users will be exactly the same.

				$first_name = $this->getRandomFirstName();
				$last_name = $this->getRandomLastName();
				if ( $first_name != '' && $last_name != '' ) {
					$uf->setUserName( $first_name . '.' . $last_name . '_' . $next_available_employee_number . '_' . $this->getUserNamePostfix() );

					$uf->setFirstName( $first_name );
					$uf->setLastName( $last_name );
					$uf->setSex( 20 );
					$uf->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
					$uf->setAddress2( 'Unit #' . rand( 10, 999 ) );
					$uf->setCity( $this->getRandomArrayValue( $this->city_names ) );

					$uf->setCountry( 'US' );
					$uf->setProvince( 'WA' );

					$uf->setPostalCode( rand( 98000, 99499 ) );
				}
				unset( $first_name, $last_name );

				break;
		}

		$uf->setDefaultBranch( $default_branch_id );
		$uf->setDefaultDepartment( $default_department_id );
		$uf->setDefaultJob( $default_job_id );
		$uf->setDefaultJobItem( $default_job_item_id );
		$uf->setCurrency( $default_currency_id );
		$uf->setGroup( $user_group_id );
		$uf->setTitle( $user_title_id );
		$uf->setHireDate( $hire_date );
		$uf->setEmployeeNumber( $next_available_employee_number );

		$uf->setWorkPhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$uf->setWorkPhoneExt( rand( 100, 1000 ) );
		$uf->setHomePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$uf->setWorkEmail( $uf->getUserName() . '@abc-company.com' );
		$uf->setSIN( $sin );
		$uf->setBirthDate( strtotime( rand( 1970, 1990 ) . '-' . rand( 1, 12 ) . '-' . rand( 1, 28 ) ) );

		$uf->setPassword( 'demo', null, true );

		if ( isset( $coordinates ) && is_array( $coordinates ) ) {
			$uf->setLatitude( $coordinates[0] );
			$uf->setLongitude( $coordinates[1] );
		} else {
			$uf->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
			$uf->setLongitude( $this->getRandomCoordinates( 'longitude' ) );
		}

		if ( $uf->isValid() ) {
			$insert_id = $uf->Save( true, true );
			Debug::Text( 'User ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$this->createUserPreference( $insert_id );

			if ( $type == 100 ) {
				$this->createUserPermission( $insert_id, 40 );
			} else if ( $type == 10 || $type == 11 || $type == 999 ) {
				$this->createUserPermission( $insert_id, 18 );
			} else {
				$this->createUserPermission( $insert_id, 10 );
			}


			if ( $type == 100 ) {
				//Admin user needs satic (non-random) wages as they are used in the unit tests.
				$this->createUserWage( $insert_id, '19.50', $hire_date );
				$this->createUserWage( $insert_id, '19.75', ( $hire_date + ( 86400 * 30 * 6 ) ) );
				$this->createUserWage( $insert_id, '20.15', ( $hire_date + ( 86400 * 30 * 12 ) ) );
				$this->createUserWage( $insert_id, '21.50', ( $hire_date + ( 86400 * 30 * 18 ) ) );


				$this->createUserWage( $insert_id, '10.00', $hire_date, $this->user_wage_groups[0] );
				$this->createUserWage( $insert_id, '20.00', $hire_date, $this->user_wage_groups[1] );
			} else {
				//Default wage group
				$this->createUserWage( $insert_id, '18.' . rand( 0, 99 ), $hire_date );
				$this->createUserWage( $insert_id, '19.' . rand( 0, 99 ), ( $hire_date + ( 86400 * 30 * 6 ) ) );
				$this->createUserWage( $insert_id, '20.' . rand( 0, 99 ), ( $hire_date + ( 86400 * 30 * 12 ) ) );
				$this->createUserWage( $insert_id, '21.' . rand( 0, 99 ), ( $hire_date + ( 86400 * 30 * 18 ) ) );

				$this->createUserWage( $insert_id, '10.' . rand( 0, 99 ), $hire_date, $this->user_wage_groups[0] );
				$this->createUserWage( $insert_id, '20.' . rand( 0, 99 ), $hire_date, $this->user_wage_groups[1] );
			}

			//Assign Taxes to user
			$this->createUserDeduction( $company_id, $legal_entity_id, $insert_id );

			if ( is_array( $remittance_source_account_ids ) ) {
				$this->createRemittanceDestinationAccount( $insert_id, $default_currency_id, $legal_entity_id, $remittance_source_account_ids[$legal_entity_id][0], 10 );
				$this->createRemittanceDestinationAccount( $insert_id, $default_currency_id, $legal_entity_id, $remittance_source_account_ids[$legal_entity_id][1], 20 );
				$this->createRemittanceDestinationAccount( $insert_id, $default_currency_id, $legal_entity_id, $remittance_source_account_ids[$legal_entity_id][2], 30 );
			}

			//5 contacts per user.
			$x = 1;
			while ( $x <= 5 ) {
				$this->createUserContact( $insert_id );
				$x++;
			}

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function createRecruitmentPortalConfig( $company_id ) {
		$rpc_obj = TTNew( 'APIRecruitmentPortalConfig' ); /** @var APIRecruitmentPortalConfig $rpc_obj */
		$rpc_obj->setRecruitmentPortalConfig( $rpc_obj->getRecruitmentPortalConfigDefaultData() );

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $user_title_id
	 * @param int $default_branch_id
	 * @param int $default_department_id
	 * @return bool
	 */
	function createJobVacancy( $company_id, $user_id, $user_title_id = 0, $default_branch_id = 0, $default_department_id = 0 ) {
		if ( $user_title_id === 0 ) {
			$user_title_id = TTUUID::getZeroID();
		}

		if ( $default_branch_id === 0 ) {
			$default_branch_id = TTUUID::getZeroID();
		}

		if ( $default_department_id === 0 ) {
			$default_department_id = TTUUID::getZeroID();
		}

		$jvf = TTnew( 'JobVacancyFactory' ); /** @var JobVacancyFactory $jvf */
		$jvf->setCompany( $company_id );
		$jvf->setUser( $user_id );
		$jvf->setBranch( $default_branch_id );
		$jvf->setDepartment( $default_department_id );
		$jvf->setTitle( $user_title_id );
		$jvf->setLevel( array_rand( $jvf->getOptions( 'level' ) ) );
		$jvf->setType( array_rand( $jvf->getOptions( 'type' ) ) );
		$jvf->setEmploymentStatus( array_rand( $jvf->getOptions( 'employment_status' ) ) );
		$jvf->setStatus( array_rand( [ 20 => null, 40 => null ] ) ); //Allways choose a status so the protal preview is available.
		$jvf->setWageType( array_rand( $jvf->getOptions( 'wage_type' ) ) );
		$jvf->setAvailability( array_rand( [ 20 => null, 30 => null ] ) ); //Always choose an external facing vacancy so the protal preview is available.
		$jvf->setMinimumWage( '' );
		$jvf->setMaximumWage( '' );
		$jvf->setName( $jvf->getTitleObject()->getName() );
		$jvf->setDescription( '' );
		$jvf->setPositions( 1 );
		$jvf->setPositionOpenDate( ( $this->getDate() - ( 86400 * rand( 30, 35 ) ) ) );
		$jvf->setPositionExpireDate( ( $this->getDate() - ( 86400 * rand( 1, 5 ) ) ) );
		$jvf->setSummaryDescription( 'Example job posting...' );
		$jvf->setDescription( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.' );

		if ( $jvf->isValid() ) {
			$insert_id = $jvf->Save();
			Debug::Text( 'Job Vacancy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Vacancy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @param string $job_vacancy_id   UUID
	 * @param string $user_id          UUID
	 * @return bool
	 */
	function createJobApplication( $job_applicant_id, $job_vacancy_id, $user_id ) {
		$jaf = TTnew( 'JobApplicationFactory' ); /** @var JobApplicationFactory $jaf */
		$jaf->setJobApplicant( $job_applicant_id );
		$jaf->setJobVacancy( $job_vacancy_id );
		$jaf->setStatus( array_rand( $jaf->getOptions( 'status' ) ) );
		$jaf->setType( array_rand( $jaf->getOptions( 'type' ) ) );
		$jaf->setPriority( array_rand( $jaf->getOptions( 'priority' ) ) );
		$jaf->setInterviewerUser( $user_id );
		$jaf->setNextActionDate( ( $this->getDate() + ( 86400 * rand( 1, 10 ) ) ) );
		$jaf->setInterviewDate( ( $this->getDate() + ( 86400 * rand( 11, 15 ) ) ) );
		$jaf->setNote( '' );

		if ( $jaf->isValid() ) {
			$insert_id = $jaf->Save();
			Debug::Text( 'Job Application ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Application!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $job_applicant_id UUID
	 * @return bool
	 */
	function createJobApplicantLocation( $job_applicant_id ) {
		$jal = TTnew( 'JobApplicantLocationFactory' ); /** @var JobApplicantLocationFactory $jal */
		$jal->setJobApplicant( $job_applicant_id );
		$jal->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
		$jal->setAddress2( 'Unit #' . rand( 10, 999 ) );
		$jal->setCity( $this->getRandomArrayValue( $this->city_names ) );
		$jal->setCountry( 'US' );
		$jal->setProvince( 'WA' );
		$jal->setPostalCode( rand( 98000, 99499 ) );
		$jal->setStartDate( ( $this->getDate() - ( 86400 * rand( 365, 730 ) ) ) );
		$jal->setEndDate( ( $this->getDate() - ( 86400 * rand( 100, 300 ) ) ) );
		$jal->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
		$jal->setLongitude( $this->getRandomCoordinates( 'longitude' ) );
		if ( $jal->isValid() ) {
			$insert_id = $jal->Save();
			Debug::Text( 'Job Applicant Location ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant Location!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @param $type
	 * @return bool
	 */
	function createJobApplicantEmployment( $job_applicant_id, $type ) {
		$jae = TTnew( 'JobApplicantEmploymentFactory' ); /** @var JobApplicantEmploymentFactory $jae */
		$jae->setJobApplicant( $job_applicant_id );
		switch ( $type ) {
			case 10:
				$jae->setCompanyName( 'ABC Company' );
				break;
			case 20:
				$jae->setCompanyName( 'XYZ Company' );
				break;
			case 30:
				$jae->setCompanyName( 'BMW Company' );
				break;
			case 40:
				$jae->setCompanyName( 'BYD Company' );
				break;
			case 50:
				$jae->setCompanyName( 'FYT Company' );
				break;
		}
		$jae->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
		$jae->setAddress2( 'Unit #' . rand( 10, 999 ) );
		$jae->setCity( $this->getRandomArrayValue( $this->city_names ) );
		$jae->setCountry( 'US' );
		$jae->setProvince( 'WA' );
		$jae->setPostalCode( rand( 98000, 99499 ) );
		$jae->setTitle( 'Carpenter' );
		$jae->setEmploymentStatus( array_rand( $jae->getOptions( 'employment_status' ) ) );
		$jae->setWageType( array_rand( $jae->getOptions( 'wage_type' ) ) );
		$jae->setWage( '' );
		$jae->setStartDate( ( $this->getDate() - ( 86400 * rand( 365, 730 ) ) ) );
		$jae->setEndDate( ( $this->getDate() - ( 86400 * rand( 1, 365 ) ) ) );

		$jae->setContactFirstName( $this->getRandomFirstName() );
		$jae->setContactLastName( $this->getRandomLastName() );
		$jae->setContactTitle( 'Engineer' );
		$jae->setContactWorkPhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$jae->setContactMobilePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$jae->setContactWorkEmail( $this->getRandomFirstName() . '.' . $this->getRandomLastName() . '@abc-company.com' );
		$jae->setIsContactAvailable( false );
		$jae->setLeaveReason( '' );

		if ( $jae->isValid() ) {
			$insert_id = $jae->Save( false );

			$jae->setIsCurrentEmployer( false );
			if ( $jae->isValid() ) {
				$jae->Save();

				Debug::Text( 'Job Applicant Employment ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $insert_id;
			}
		}

		Debug::Text( 'Failed Creating Job Applicant Employment!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @return bool
	 */
	function createJobApplicantReference( $job_applicant_id ) {
		$jar = TTnew( 'JobApplicantReferenceFactory' ); /** @var JobApplicantReferenceFactory $jar */
		$jar->setJobApplicant( $job_applicant_id );
		$jar->setType( array_rand( $jar->getOptions( 'type' ) ) );
		$first_name = $this->getRandomFirstName();
		$last_name = $this->getRandomLastName();
		if ( $first_name != '' && $last_name != '' ) {
			$jar->setFirstName( $first_name );
			$jar->setLastName( $last_name );
		}
		$jar->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
		$jar->setAddress2( 'Unit #' . rand( 10, 999 ) );
		$jar->setCity( $this->getRandomArrayValue( $this->city_names ) );
		$jar->setCountry( 'US' );
		$jar->setProvince( 'WA' );
		$jar->setPostalCode( rand( 98000, 99499 ) );
		$jar->setWorkPhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$jar->setHomePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$jar->setMobilePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
		$jar->setWorkEmail( $this->getRandomFirstName() . '.' . $this->getRandomLastName() . '@abc-company.com' );
		$jar->setHomeEmail( '' );
		$jar->setStartDate( ( $this->getDate() - ( 86400 * rand( 1, 14 ) ) ) );
		if ( $jar->isValid() ) {
			$insert_id = $jar->Save();
			Debug::Text( 'Job Applicant Reference ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant Reference!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function createJobApplicant( $company_id ) {
		$jaf = TTnew( 'JobApplicantFactory' ); /** @var JobApplicantFactory $jaf */
		$jaf->setCompany( $company_id );
		$jaf->setStatus( 10 ); //Enabled.
		$jaf->setIdentificationType( array_rand( $jaf->getOptions( 'identification_type' ) ) );
		$jaf->setIdentificationNumber( rand( 100, 999 ) . '-' . rand( 100, 999 ) . '-' . rand( 100, 999 ) );
		$jaf->setIdentificationExpireDate( ( $this->getDate() + ( 86400 * rand( 100, 200 ) ) ) );
		$jaf->setAvailableDaysOfWeek( (array)array_rand( $jaf->getOptions( 'available_days_of_week' ), rand( 1, 7 ) ) );
		$jaf->setAvailableHoursOfDay( (array)array_rand( $jaf->getOptions( 'available_hours_of_day' ), rand( 1, 10 ) ) );
		$jaf->setIdentificationCountry( 'US' );
		$jaf->setIdentificationProvince( 'WA' );
		$first_name = $this->getRandomFirstName();
		$last_name = $this->getRandomLastName();
		if ( $first_name != '' && $last_name != '' ) {
			$jaf->setFirstName( $first_name );
			$jaf->setLastName( $last_name );
			$jaf->setUserName( $first_name . '.' . $last_name . $this->getUserNamePostfix() );
			$jaf->setMaidenName( $first_name . '.' . $this->getRandomLastName() );
			$jaf->setSex( ( rand( 1, 3 ) * 5 ) );
			$jaf->setAddress1( rand( 100, 9999 ) . ' ' . $this->getRandomLastName() . ' St' );
			$jaf->setAddress2( 'Unit #' . rand( 10, 999 ) );
			$jaf->setCity( $this->getRandomArrayValue( $this->city_names ) );

			$jaf->setCountry( 'US' );
			$jaf->setProvince( 'WA' );
			$jaf->setPostalCode( rand( 98000, 99499 ) );
			$jaf->setEmail( $first_name . '.' . $last_name . '@abc-company.com' );
			$jaf->setHomePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
			$jaf->setMobilePhone( rand( 403, 600 ) . '-' . rand( 250, 600 ) . '-' . rand( 1000, 9999 ) );
			$jaf->setBirthDate( strtotime( rand( 1970, 1990 ) . '-' . rand( 1, 12 ) . '-' . rand( 1, 28 ) ) );
			$jaf->setSIN( $this->generateSIN( 'US' ) );
			$jaf->setAvailableMaximumHoursPerWeek( 50 );
			$jaf->setAvailableMinimumHoursPerWeek( 20 );
			$jaf->setPassword( 'demo' );
			$jaf->setPasswordResetKey( '1234' );
			$jaf->setPasswordResetDate( ( $this->getDate() - ( 86400 * rand( 1, 30 ) ) ) );
			$jaf->setAvailableStartDate( ( $this->getDate() - ( 86400 * rand( 1, 30 ) ) ) );
			$jaf->setMinimumWage( '' );
			$jaf->setCriminalRecordDescription( '' );
			$jaf->setCurrentlyEmployed( false );
			$jaf->setImmediateDrugTest( false );
			$jaf->setCriminalRecord( false );
			$jaf->setCreatedDate( ( $this->getDate() - ( 86400 * rand( 31, 365 ) ) ) );
		}

		$jaf->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
		$jaf->setLongitude( $this->getRandomCoordinates( 'longitude' ) );

		if ( $jaf->isValid() ) {
			$insert_id = $jaf->Save();
			Debug::Text( 'Job Applicant ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @return bool
	 */
	function createUserPreference( $user_id ) {
		$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
		$uplf->getByUserId( $user_id );
		if ( $uplf->getRecordCount() > 0 ) {
			$upf = $uplf->getCurrent();
		} else {
			$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
		}

		$upf->setUser( $user_id );
		$upf->setLanguage( 'en' );
		$upf->setDateFormat( 'd-M-y' );
		$upf->setTimeFormat( 'g:i A' );
		$upf->setTimeUnitFormat( 10 );
		$upf->setTimeZone( 'America/Vancouver' );
		$upf->setStartWeekDay( 0 );
		$upf->setItemsPerPage( 50 );

		$upf->setEnableEmailNotificationException( false );
		$upf->setEnableEmailNotificationMessage( false );
		$upf->setEnableEmailNotificationPayStub( false );
		$upf->setEnableEmailNotificationHome( false );

		if ( $upf->isValid() ) {
			$insert_id = $upf->Save();
			Debug::Text( 'User Preference ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Preference!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id      UUID
	 * @param string $legal_entity_id UUID
	 * @return bool
	 */
	function createUserDefaults( $company_id, $legal_entity_id ) {
		//User Default settings, always do this last.
		$udf = TTnew( 'UserDefaultFactory' ); /** @var UserDefaultFactory $udf */

		$clf = TTNew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getById( $company_id );
		if ( $clf->getRecordCount() > 0 ) {
			$udf->setCompany( $company_id );
			$udf->setLegalEntity( $legal_entity_id );
			$udf->setCity( $clf->getCurrent()->getCity() );
			$udf->setCountry( $clf->getCurrent()->getCountry() );
			$udf->setProvince( $clf->getCurrent()->getProvince() );
			$udf->setWorkPhone( $clf->getCurrent()->getWorkPhone() );
		}

		$udf->setLanguage( 'en' );
		$udf->setItemsPerPage( 50 );

		$udf->setDateFormat( 'd-M-y' );
		$udf->setTimeFormat( 'g:i A' );
		$udf->setTimeUnitFormat( 10 );
		$udf->setStartWeekDay( 0 );
		$udf->setTimeZone( 'America/Vancouver' );
		$udf->setDistanceFormat( 10 );

		//Get Pay Period Schedule
		$ppslf = TTNew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
		$ppslf->getByCompanyId( $company_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$udf->setPayPeriodSchedule( $ppslf->getCurrent()->getID() );
		}

		//Get Policy Group
		$pglf = TTNew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
		$pglf->getByCompanyId( $company_id );
		if ( $pglf->getRecordCount() > 0 ) {
			$udf->setPolicyGroup( $pglf->getCurrent()->getID() );
		}

		//Permissions
		$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
		$pclf->getByCompanyIdAndLevel( $company_id, 10, 1, null, null, [ 'level' => 'desc' ] );
		if ( $pclf->getRecordCount() > 0 ) {
			$udf->setPermissionControl( $pclf->getCurrent()->getID() );
		}

		//Terminated Permissions
		$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
		$pclf->getByCompanyIdAndLevel( $company_id, 5, 1, null, null, [ 'level' => 'desc' ] );
		if ( $pclf->getRecordCount() > 0 ) {
			$udf->setTerminatedPermissionControl( $pclf->getCurrent()->getID() );
		}

		//Currency
		$clf = TTNew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $clf */
		$clf->getByCompanyIdAndDefault( $company_id, true );
		if ( $clf->getRecordCount() > 0 ) {
			$udf->setCurrency( $clf->getCurrent()->getID() );
		}

		$udf->setEnableEmailNotificationException( true );
		$udf->setEnableEmailNotificationMessage( true );
		$udf->setEnableEmailNotificationPayStub( true );
		$udf->setEnableEmailNotificationHome( true );

		if ( $udf->isValid() ) {
			Debug::text( 'Adding User Default settings...', __FILE__, __LINE__, __METHOD__, 9 );

			return $udf->Save();
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $legal_entity_id
	 * @param string $user_id    UUID
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 */
	function createUserDeduction( $company_id, $legal_entity_id, $user_id ) {
		$fail_transaction = false;

		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndLegalEntityId( $company_id, $legal_entity_id );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $cd_obj ) {
				Debug::Text( 'Creating User Deduction: User Id:' . $user_id . ' Company Deduction: ' . $cd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				$udf = TTnew( 'UserDeductionFactory' ); /** @var UserDeductionFactory $udf */
				$udf->setUser( $user_id );
				$udf->setCompanyDeduction( $cd_obj->getId() );
				if ( $udf->isValid() ) {
					if ( $udf->Save() === false ) {
						Debug::Text( 'User Deductions... Save Failed!', __FILE__, __LINE__, __METHOD__, 10 );
						$fail_transaction = true;
					}
				} else {
					Debug::Text( 'User Deductions... isValid Failed!', __FILE__, __LINE__, __METHOD__, 10 );
					$fail_transaction = true;
				}
			}

			if ( $fail_transaction == false ) {
				Debug::Text( 'User Deductions Created!', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		} else {
			Debug::Text( 'No Company Deductions Found!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		Debug::Text( 'Failed Creating User Deductions!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function createUserWageGroups( $company_id ) {
		$wgf = TTnew( 'WageGroupFactory' ); /** @var WageGroupFactory $wgf */
		$wgf->setCompany( $company_id );
		$wgf->setName( 'Alternate Wage #1' );

		if ( $wgf->isValid() ) {
			$this->user_wage_groups[0] = $wgf->Save();
			Debug::Text( 'aUser Wage Group ID: ' . $this->user_wage_groups[0], __FILE__, __LINE__, __METHOD__, 10 );
		}

		$wgf = TTnew( 'WageGroupFactory' ); /** @var WageGroupFactory $wgf */
		$wgf->setCompany( $company_id );
		$wgf->setName( 'Alternate Wage #2' );

		if ( $wgf->isValid() ) {
			$this->user_wage_groups[1] = $wgf->Save();

			Debug::Text( 'bUser Wage Group ID: ' . $this->user_wage_groups[1], __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @param string $user_id     UUID
	 * @param $rate
	 * @param int $effective_date EPOCH
	 * @param int $wage_group_id
	 * @return bool
	 */
	function createUserWage( $user_id, $rate, $effective_date, $wage_group_id = 0 ) {
		if ( $wage_group_id === 0 ) {
			$wage_group_id = TTUUID::getZeroID();
		}

		$uwf = TTnew( 'UserWageFactory' ); /** @var UserWageFactory $uwf */

		$uwf->setUser( $user_id );
		$uwf->setWageGroup( $wage_group_id );
		$uwf->setType( 10 );
		$uwf->setWage( $rate );
		$uwf->setLaborBurdenPercent( 13.5 );
		$uwf->setEffectiveDate( $effective_date );

		if ( $uwf->isValid() ) {
			$insert_id = $uwf->Save();
			Debug::Text( 'User Wage ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Wage!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param null $filter_preset_options
	 * @return bool
	 */
	function createPermissionGroups( $company_id, $filter_preset_options = null ) {
		Debug::text( 'Adding Preset Permission Groups: ' . $company_id, __FILE__, __LINE__, __METHOD__, 9 );

		$pf = TTnew( 'PermissionFactory' ); /** @var PermissionFactory $pf */
		$pf->StartTransaction();

		$preset_flags = array_keys( $pf->getOptions( 'preset_flags' ) );
		$default_preset_options = $pf->getOptions( 'preset' );
		if ( $filter_preset_options != '' ) {
			$filter_preset_options = (array)$filter_preset_options;
			$preset_options = [];
			foreach ( $filter_preset_options as $filter_preset_id ) {
				if ( isset( $default_preset_options[$filter_preset_id] ) ) {
					$preset_options[$filter_preset_id] = $default_preset_options[$filter_preset_id];
				}
			}
		} else {
			$preset_options = $default_preset_options;
		}

		//Debug::Arr($preset_options, 'Preset Options: ', __FILE__, __LINE__, __METHOD__, 9);
		$preset_levels = $pf->getOptions( 'preset_level' );
		foreach ( $preset_options as $preset_id => $preset_name ) {
			$pcf = TTnew( 'PermissionControlFactory' ); /** @var PermissionControlFactory $pcf */
			$pcf->setCompany( $company_id );
			$pcf->setName( $preset_name );
			$pcf->setDescription( '' );
			$pcf->setLevel( $preset_levels[$preset_id] );
			if ( $pcf->isValid() ) {
				$pcf_id = $pcf->Save( false );

				$this->permission_presets[$preset_id] = $pcf_id;

				$pf->applyPreset( $pcf_id, $preset_id, $preset_flags );
			}
		}
		//$pf->FailTransaction(); //Only for testing.
		$pf->CommitTransaction();

		return true;
	}

	/**
	 * @param string $user_id   UUID
	 * @param string $preset_id UUID
	 * @return bool
	 */
	function createUserPermission( $user_id, $preset_id ) {
		if ( isset( $this->permission_presets[$preset_id] ) ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$pclf->getById( $this->permission_presets[$preset_id] );
			if ( $pclf->getRecordCount() > 0 ) {
				$pc_obj = $pclf->getCurrent();

				$puf = TTnew( 'PermissionUserFactory' ); /** @var PermissionUserFactory $puf */
				$puf->setPermissionControl( $pc_obj->getId() );
				$puf->setUser( $user_id );
				if ( $puf->isValid() ) {
					Debug::Text( 'Assigning User ID: ' . $user_id . ' To Permission Control: ' . $this->permission_presets[$preset_id] . ' Preset: ' . $preset_id, __FILE__, __LINE__, __METHOD__, 10 );

					$puf->Save();

					return true;
				}
			}
		}

		Debug::Text( 'Failed Assigning User to Permission Control! User ID: ' . $user_id . ' Preset: ' . $preset_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id     UUID
	 * @param string $child_user_ids UUID
	 * @return bool
	 */
	function createAuthorizationHierarchyControl( $company_id, $child_user_ids ) {
		$hcf = TTnew( 'HierarchyControlFactory' ); /** @var HierarchyControlFactory $hcf */
		$hcf->setCompany( $company_id );
		$hcf->setName( 'Default' );

		if ( $hcf->isValid() ) {
			$insert_id = $hcf->Save( false );
			Debug::Text( 'Hierarchy Control ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$hcf->setObjectType( [ 1010, 1020, 1030, 1040, 1100, 80, 90, 200, 100 ] ); //Exclude permissions.
			$hcf->setUser( $child_user_ids );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Hierarchy Control!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id   UUID
	 * @param string $hierarchy_id UUID
	 * @param string $root_user_id UUID
	 * @param $level
	 * @return bool
	 */
	function createAuthorizationHierarchyLevel( $company_id, $hierarchy_id, $root_user_id, $level ) {
		if ( $hierarchy_id != '' ) {
			//Add level
			$hlf = TTnew( 'HierarchyLevelFactory' ); /** @var HierarchyLevelFactory $hlf */
			$hlf->setHierarchyControl( $hierarchy_id );
			$hlf->setLevel( $level );
			$hlf->setUser( $root_user_id );

			if ( $hlf->isValid() ) {
				$insert_id = $hlf->Save();
				Debug::Text( 'Hierarchy Level ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		//Debug::Text('Failed Creating Hierarchy!', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param $type
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @param int $absence_policy_id
	 * @return bool
	 */
	function createRequest( $type, $user_id, $date_stamp, $absence_policy_id = null ) {
		if ( $absence_policy_id == '' ) {
			$absence_policy_id = TTUUID::getZeroID();
		}

		$date_stamp = TTDate::parseDateTime( $date_stamp ); //Make sure date_stamp is always an integer.

		$rf = TTnew( 'RequestFactory' ); /** @var RequestFactory $rf */
		$rf->setId( $rf->getNextInsertId() );
		$rf->setUser( $user_id );
		$rf->setDateStamp( $date_stamp );

		$u_obj = $rf->getUserObject();

		switch ( $type ) {
			case 10: //Try to match setType()
				$rf->setType( 10 ); //Schedule Request
				$rf->setStatus( 30 );
				$rf->setMessage( 'Sorry, I forgot to punch out today. I left at 5:00PM' );
				$rf->setCreatedBy( $user_id );

				break;
			case 30: //Try to match setType()
				$rf->setType( 30 ); //Vacation Request
				$rf->setStatus( 30 );
				$rf->setMessage( 'I would like to request vacation.' );
				$rf->setCreatedBy( $user_id );

				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rsf = TTnew( 'RequestScheduleFactory' ); /** @var RequestScheduleFactory $rsf */
					$rsf->setRequest( $rf->getId() );
					$rsf->setUser( $rf->getUser() );
					$rsf->setStatus( 20 ); //Absent
					//$rsf->setStartDate( ( TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( $date_stamp ) ) + 86400 ) );
					$rsf->setStartDate( $date_stamp );
					$rsf->setEndDate( ( TTDate::getMiddleDayEpoch( $date_stamp ) + ( 86400 * 2 ) ) );
					$rsf->setStartTime( strtotime( '8:00AM' ) );
					$rsf->setEndTime( strtotime( '5:00PM' ) );
					$rsf->setSun( false );
					$rsf->setMon( true );
					$rsf->setTue( true );
					$rsf->setWed( true );
					$rsf->setThu( true );
					$rsf->setFri( true );
					$rsf->setSat( false );
					$rsf->setBranch( $u_obj->getDefaultBranch() );
					$rsf->setDepartment( $u_obj->getDefaultDepartment() );
					$rsf->setJob( $u_obj->getDefaultJob() );
					$rsf->setJobItem( $u_obj->getDefaultJobItem() );
					$rsf->setAbsencePolicy( $absence_policy_id );
					if ( $rsf->isValid() ) {
						$rsf_insert_id = $rsf->Save();
						Debug::Text( 'RequestSchedule ID: ' . $rsf_insert_id, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Text( 'Failed Creating Request Schedule!', __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $rsf );
				}

				break;
			case 32: //Try to match setType()
				$rf->setType( 30 ); //Sick Request
				$rf->setStatus( 30 );
				$rf->setMessage( 'I would like to request a sick day.' );
				$rf->setCreatedBy( $user_id );

				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rsf = TTnew( 'RequestScheduleFactory' ); /** @var RequestScheduleFactory $rsf */
					$rsf->setRequest( $rf->getId() );
					$rsf->setUser( $rf->getUser() );
					$rsf->setStatus( 20 ); //Absent
					$rsf->setStartDate( $date_stamp );
					$rsf->setEndDate( $date_stamp );
					$rsf->setStartTime( strtotime( '8:00AM' ) );
					$rsf->setEndTime( strtotime( '5:00PM' ) );
					$rsf->setSun( false );
					$rsf->setMon( true );
					$rsf->setTue( true );
					$rsf->setWed( true );
					$rsf->setThu( true );
					$rsf->setFri( true );
					$rsf->setSat( false );
					$rsf->setBranch( $u_obj->getDefaultBranch() );
					$rsf->setDepartment( $u_obj->getDefaultDepartment() );
					$rsf->setJob( $u_obj->getDefaultJob() );
					$rsf->setJobItem( $u_obj->getDefaultJobItem() );
					$rsf->setAbsencePolicy( $absence_policy_id );
					if ( $rsf->isValid() ) {
						$rsf_insert_id = $rsf->Save();
						Debug::Text( 'RequestSchedule ID: ' . $rsf_insert_id, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Text( 'Failed Creating Request Schedule!', __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $rsf );
				}

				break;
			case 40: //Try to match setType()
				$rf->setType( 40 ); //Schedule Request
				$rf->setStatus( 30 );
				$rf->setMessage( 'I would like to leave at 1pm this friday.' );
				$rf->setCreatedBy( $user_id );

				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rsf = TTnew( 'RequestScheduleFactory' ); /** @var RequestScheduleFactory $rsf */
					$rsf->setRequest( $rf->getId() );
					$rsf->setUser( $rf->getUser() );
					$rsf->setStatus( 10 ); //Working
					$rsf->setStartDate( $date_stamp );
					$rsf->setEndDate( $date_stamp );
					$rsf->setStartTime( strtotime( '8:00AM' ) );
					$rsf->setEndTime( strtotime( '1:00PM' ) );
					$rsf->setSun( true );
					$rsf->setMon( true );
					$rsf->setTue( true );
					$rsf->setWed( true );
					$rsf->setThu( true );
					$rsf->setFri( true );
					$rsf->setSat( true );
					$rsf->setBranch( $u_obj->getDefaultBranch() );
					$rsf->setDepartment( $u_obj->getDefaultDepartment() );
					$rsf->setJob( $u_obj->getDefaultJob() );
					$rsf->setJobItem( $u_obj->getDefaultJobItem() );

					if ( $rsf->isValid() ) {
						$rsf_insert_id = $rsf->Save();
						Debug::Text( 'RequestSchedule ID: ' . $rsf_insert_id, __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						Debug::Text( 'Failed Creating Request Schedule!', __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $rsf );
				}

				break;
		}

		if ( $rf->isValid() ) {
			$insert_id = $rf->Save( true, true );
			Debug::Text( 'Request ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Request!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param int $object_type_id
	 * @param string $object_id UUID
	 * @param string $user_id   UUID
	 * @param bool $authorize
	 * @return bool
	 */
	function createAuthorization( $object_type_id, $object_id, $user_id, $authorize = true ) {
		$af = TTnew( 'AuthorizationFactory' ); /** @var AuthorizationFactory $af */
		$af->setObjectType( $object_type_id );
		$af->setObject( $object_id );
		$af->setAuthorized( $authorize );
		$af->setCurrentUser( $user_id );
		$af->setCreatedBy( $user_id );
		$af->setUpdatedBy( $user_id );

		if ( $af->isValid() ) {
			$insert_id = $af->Save();
			Debug::Text( 'Authorization ID: ' . $insert_id . ' Object ID: ' . $object_id . ' Object Type: ' . $object_type_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Authorization!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id         UUID
	 * @param string $pay_period_id   UUID
	 * @param string $current_user_id UUID
	 * @return bool
	 */
	function createTimeSheetVerification( $user_id, $pay_period_id, $current_user_id ) {
		$pptsvf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvf */
		$pptsvf->setCurrentUser( $current_user_id );
		$pptsvf->setUser( $user_id );
		$pptsvf->setPayPeriod( $pay_period_id );

		$pptsvf->setCreatedBy( $current_user_id );
		$pptsvf->setUpdatedBy( $current_user_id );

		if ( $pptsvf->isValid() ) {
			$insert_id = $pptsvf->Save();
			Debug::Text( 'TimeSheet Verification ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating TimeSheet Verification!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createTaskGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$jigf = TTnew( 'JobItemGroupFactory' ); /** @var JobItemGroupFactory $jigf */
		$jigf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$jigf->setParent( $parent_id );
				$jigf->setName( 'Construction' );
				break;
			case 20:
				$jigf->setParent( $parent_id );
				$jigf->setName( 'Inside' );
				break;
			case 30:
				$jigf->setParent( $parent_id );
				$jigf->setName( 'Outside' );
				break;
			case 40:
				$jigf->setParent( $parent_id );
				$jigf->setName( 'Projects' );
				break;
			case 50:
				$jigf->setParent( $parent_id );
				$jigf->setName( 'Accounting' );
				break;
			case 60:
				$jigf->setParent( $parent_id );
				$jigf->setName( 'Estimating' );
				break;
		}

		if ( $jigf->isValid() ) {
			$insert_id = $jigf->Save();
			Debug::Text( 'Job Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param string $group_id   UUID
	 * @param string $product_id UUID
	 * @return bool
	 */
	function createTask( $company_id, $type, $group_id, $product_id = null ) {
		$jif = TTnew( 'JobItemFactory' ); /** @var JobItemFactory $jif */
		$jif->setCompany( $company_id );
		//$jif->setProduct( $data['product_id'] );
		$jif->setStatus( 10 );
		$jif->setType( 10 );
		//$jif->setGroup( $data['group_id'] );

		switch ( $type ) {
			case 10: //Framing
				$jif->setManualID( 1 );
				$jif->setName( 'Framing' );
				$jif->setDescription( 'Framing' );

				$jif->setEstimateTime( ( 3600 * 50 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '80.00' );
				$jif->setMinimumTime( 3600 );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
			case 20: //Sanding
				$jif->setManualID( 2 );
				$jif->setName( 'Sanding' );
				$jif->setDescription( 'Sanding' );

				$jif->setEstimateTime( ( 3600 * 30 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '15.25' );
				$jif->setMinimumTime( ( 3600 * 2 ) );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
			case 30: //Painting
				$jif->setManualID( 3 );
				$jif->setName( 'Painting' );
				$jif->setDescription( 'Painting' );

				$jif->setEstimateTime( ( 3600 * 40 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '25.50' );
				$jif->setMinimumTime( ( 3600 * 1 ) );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
			case 40: //Landscaping
				$jif->setManualID( 4 );
				$jif->setName( 'Land Scaping' );
				$jif->setDescription( 'Land Scaping' );

				$jif->setEstimateTime( ( 3600 * 35 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '33' );
				$jif->setMinimumTime( ( 3600 * 1 ) );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
			case 50:
				$jif->setManualID( 5 );
				$jif->setName( 'Data Entry' );
				$jif->setDescription( '' );

				$jif->setEstimateTime( ( 3600 * 45 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '15' );
				$jif->setMinimumTime( ( 3600 * 1 ) );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
			case 60:
				$jif->setManualID( 6 );
				$jif->setName( 'Accounting' );
				$jif->setDescription( '' );

				$jif->setEstimateTime( ( 3600 * 55 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '45' );
				$jif->setMinimumTime( ( 3600 * 1 ) );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
			case 70:
				$jif->setManualID( 7 );
				$jif->setName( 'Appraisals' );
				$jif->setDescription( '' );

				$jif->setEstimateTime( ( 3600 * 25 ) );
				$jif->setEstimateQuantity( 0 );
				$jif->setEstimateBadQuantity( 0 );
				$jif->setBadQuantityRate( 0 );
				$jif->setBillableRate( '50' );
				$jif->setMinimumTime( ( 3600 * 1 ) );
				$jif->setGroup( $group_id );
				$jif->setProduct( $product_id );

				break;
		}

		if ( $jif->isValid() ) {
			$insert_id = $jif->Save();
			Debug::Text( 'Task ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Task!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createJobGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$jgf = TTnew( 'JobGroupFactory' ); /** @var JobGroupFactory $jgf */
		$jgf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$jgf->setParent( $parent_id );
				$jgf->setName( 'Houses' );
				break;
			case 20:
				$jgf->setParent( $parent_id );
				$jgf->setName( 'Duplexes' );
				break;
			case 30:
				$jgf->setParent( $parent_id );
				$jgf->setName( 'Townhomes' );
				break;
			case 40:
				$jgf->setParent( $parent_id );
				$jgf->setName( 'Projects' );
				break;
			case 50:
				$jgf->setParent( $parent_id );
				$jgf->setName( 'Internal' );
				break;
			case 60:
				$jgf->setParent( $parent_id );
				$jgf->setName( 'External' );
				break;
		}

		if ( $jgf->isValid() ) {
			$insert_id = $jgf->Save();
			Debug::Text( 'Job Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $qualification_id
	 * @return bool
	 */
	function createUserEducation( $user_id, $qualification_id = 0 ) {
		if ( $qualification_id === 0 ) {
			$qualification_id = TTUUID::getZeroID();
		}

		$uef = TTnew( 'UserEducationFactory' ); /** @var UserEducationFactory $uef */
		$uef->setUser( $user_id );
		$uef->setQualification( $qualification_id );
		$uef->setInstitute( $this->getRandomArrayValue( $this->institute ) );
		$uef->setMajor( $this->getRandomArrayValue( $this->major ) );
		$uef->setMinor( $this->getRandomArrayValue( $this->minor ) );
		$uef->setGraduateDate( ( $this->getDate() - ( 86400 * rand( 21, 30 ) ) ) );
		$uef->setGradeScore( rand( 60, 100 ) );
		$uef->setStartDate( ( $this->getDate() - ( 86400 * rand( 11, 20 ) ) ) );
		$uef->setEndDate( ( $this->getDate() - ( 86400 * rand( 1, 10 ) ) ) );

		if ( $uef->isValid() ) {
			$insert_id = $uef->Save();
			Debug::Text( 'User Education ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Education!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @param string $qualification_id UUID
	 * @return bool
	 */
	function createJobApplicantEducation( $job_applicant_id, $qualification_id ) {
		$jaef = TTnew( 'JobApplicantEducationFactory' ); /** @var JobApplicantEducationFactory $jaef */
		$jaef->setJobApplicant( $job_applicant_id );
		$jaef->setQualification( $qualification_id );
		$jaef->setInstitute( $this->getRandomArrayValue( $this->institute ) );
		$jaef->setMajor( $this->getRandomArrayValue( $this->major ) );
		$jaef->setMinor( $this->getRandomArrayValue( $this->minor ) );
		$jaef->setGraduateDate( $this->getDate() - ( 86400 * rand( 21, 30 ) ) );
		$jaef->setGradeScore( rand( 60, 100 ) );
		$jaef->setStartDate( ( $this->getDate() - ( 86400 * rand( 11, 20 ) ) ) );
		$jaef->setEndDate( ( $this->getDate() - ( 86400 * rand( 1, 10 ) ) ) );

		if ( $jaef->isValid() ) {
			$insert_id = $jaef->Save();
			Debug::Text( 'Job Applicant Education ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant Education!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $qualification_id
	 * @return bool
	 */
	function createUserLicense( $user_id, $qualification_id = 0 ) {
		if ( $qualification_id === 0 ) {
			$qualification_id = TTUUID::getZeroID();
		}

		$lf = TTnew( 'UserLicenseFactory' ); /** @var UserLicenseFactory $lf */
		$lf->setUser( $user_id );
		$lf->setQualification( $qualification_id );
		$lf->setLicenseNumber( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
		$lf->setLicenseIssuedDate( ( $this->getDate() - ( 86400 * rand( 21, 30 ) ) ) );
		$lf->setLicenseExpiryDate( ( $this->getDate() - ( 86400 * rand( 1, 10 ) ) ) );

		if ( $lf->isValid() ) {
			$insert_id = $lf->Save();
			Debug::Text( 'User License ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User License!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @param string $qualification_id UUID
	 * @return bool
	 */
	function createJobApplicantLicense( $job_applicant_id, $qualification_id ) {
		$jalf = TTnew( 'JobApplicantLicenseFactory' ); /** @var JobApplicantLicenseFactory $jalf */
		$jalf->setJobApplicant( $job_applicant_id );
		$jalf->setQualification( $qualification_id );
		$jalf->setLicenseNumber( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
		$jalf->setLicenseIssuedDate( ( $this->getDate() - ( 86400 * rand( 21, 30 ) ) ) );
		$jalf->setLicenseExpiryDate( ( $this->getDate() - ( 86400 * rand( 1, 10 ) ) ) );

		if ( $jalf->isValid() ) {
			$insert_id = $jalf->Save();
			Debug::Text( 'Job Applicant License ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant License!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param $type
	 * @param int $qualification_id
	 * @return bool
	 */
	function createUserLanguage( $user_id, $type, $qualification_id = 0 ) {
		if ( $qualification_id === 0 ) {
			$qualification_id = TTUUID::getZeroID();
		}

		$ulf = TTnew( 'UserLanguageFactory' ); /** @var UserLanguageFactory $ulf */
		$ulf->setUser( $user_id );
		$ulf->setQualification( $qualification_id );
		$ulf->setDescription( '' );
		switch ( $type ) {
			case 10:
			case 20:
			case 30:
				$ulf->setFluency( $type );
				$ulf->setCompetency( $type );
				break;
			case 40:
				$ulf->setFluency( 30 );
				$ulf->setCompetency( $type );
				break;
			default:
				$ulf->setFluency( rand( 1, 3 ) * 10 );
				$ulf->setCompetency( rand( 1, 4 ) * 10 );
				break;
		}

		if ( $ulf->isValid() ) {
			$insert_id = $ulf->Save();
			Debug::Text( 'User Language ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Language!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @param string $qualification_id UUID
	 * @return bool
	 */
	function createJobApplicantLanguage( $job_applicant_id, $qualification_id ) {
		$jalf = TTnew( 'JobApplicantLanguageFactory' ); /** @var JobApplicantLanguageFactory $jalf */
		$jalf->setJobApplicant( $job_applicant_id );
		$jalf->setQualification( $qualification_id );
		$jalf->setDescription( '' );
		$jalf->setFluency( array_rand( $jalf->getOptions( 'fluency' ) ) );
		$jalf->setCompetency( array_rand( $jalf->getOptions( 'competency' ) ) );

		if ( $jalf->isValid() ) {
			$insert_id = $jalf->Save();
			Debug::Text( 'Job Applicant Language ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant Language!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id             UUID
	 * @param $type
	 * @param string $qualification_id    UUID
	 * @param string $default_currency_id UUID
	 * @return bool
	 */
	function createUserMembership( $user_id, $type, $qualification_id, $default_currency_id ) {
		$umf = TTnew( 'UserMembershipFactory' ); /** @var UserMembershipFactory $umf */
		$umf->setUser( $user_id );
		$umf->setQualification( $qualification_id );
		$umf->setAmount( rand( 10, 100 ) );
		$umf->setCurrency( $default_currency_id );
		$umf->setStartDate( $this->getDate() - ( 86400 * rand( 21, 30 ) ) );
		$umf->setRenewalDate( $this->getDate() - ( 86400 * rand( 10, 20 ) ) );
		switch ( $type ) {
			case 10:
			case 20:
				$umf->setOwnership( $type );
				break;
			default:
				$umf->setOwnership( rand( 1, 2 ) * 10 );
				break;
		}

		if ( $umf->isValid() ) {
			$insert_id = $umf->Save();
			Debug::Text( 'User Membership ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Membership!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id    UUID
	 * @param string $qualification_id    UUID
	 * @param string $default_currency_id UUID
	 * @return bool
	 */
	function createJobApplicantMembership( $job_applicant_id, $qualification_id, $default_currency_id ) {
		$jamf = TTnew( 'JobApplicantMembershipFactory' ); /** @var JobApplicantMembershipFactory $jamf */
		$jamf->setJobApplicant( $job_applicant_id );
		$jamf->setQualification( $qualification_id );
		$jamf->setAmount( rand( 10, 100 ) );
		$jamf->setCurrency( $default_currency_id );
		$jamf->setStartDate( $this->getDate() - ( 86400 * rand( 21, 30 ) ) );
		$jamf->setRenewalDate( $this->getDate() - ( 86400 * rand( 10, 20 ) ) );
		$jamf->setOwnership( array_rand( $jamf->getOptions( 'ownership' ) ) );

		if ( $jamf->isValid() ) {
			$insert_id = $jamf->Save();
			Debug::Text( 'Job Applicant Membership ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant Membership!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id          UUID
	 * @param string $reviewer_user_id UUID
	 * @return bool
	 */
	function createUserReviewControl( $user_id, $reviewer_user_id ) {
		$urcf = TTnew( 'UserReviewControlFactory' ); /** @var UserReviewControlFactory $urcf */
		$urcf->setUser( $user_id );
		$urcf->setReviewerUser( $reviewer_user_id );
		$urcf->setStartDate( $this->getDate() - ( 86400 * rand( 21, 30 ) ) );
		$urcf->setEndDate( $this->getDate() - ( 86400 * rand( 11, 20 ) ) );
		$urcf->setDueDate( $this->getDate() - ( 86400 * rand( 1, 10 ) ) );
		$urcf->setType( rand( 2, 9 ) * 5 );
		$urcf->setSeverity( rand( 1, 5 ) * 10 );
		$urcf->setTerm( rand( 1, 3 ) * 10 );
		$urcf->setStatus( rand( 1, 3 ) * 10 );
		if ( $urcf->isValid() ) {
			$insert_id = $urcf->Save();
			Debug::Text( 'User Review Control ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Review Control!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $user_id UUID
	 * @param $type
	 * @param int $qualification_id
	 * @return bool
	 */
	function createUserSkill( $user_id, $type, $qualification_id = 0 ) {
		if ( $qualification_id === 0 ) {
			$qualification_id = TTUUID::getZeroID();
		}

		$usf = TTnew( 'UserSkillFactory' ); /** @var UserSkillFactory $usf */
		$usf->setUser( $user_id );
		$usf->setQualification( $qualification_id );
		$usf->setFirstUsedDate( $this->getDate() - ( 86400 * rand( 200, 3000 ) ) );
		$usf->setLastUsedDate( $this->getDate() - ( 86400 * rand( 11, 20 ) ) );
		$usf->setExpiryDate( $this->getDate() - ( 86400 * rand( 1, 10 ) ) );
		$usf->setEnableCalcExperience( 1 );
		$usf->setDescription( '' );
		switch ( $type ) {
			case 10:
			case 20:
			case 30:
			case 40:
			case 50:
			case 60:
			case 70:
			case 80:
			case 90:
				$usf->setProficiency( $type );
				break;
			default:
				$usf->setProficiency( rand( 1, 9 ) * 10 );
				break;
		}

		if ( $usf->isValid() ) {
			$insert_id = $usf->Save();
			Debug::Text( 'User Skill ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating User Skill!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $job_applicant_id UUID
	 * @param string $qualification_id UUID
	 * @return bool
	 */
	function createJobApplicantSkill( $job_applicant_id, $qualification_id ) {
		$jasf = TTnew( 'JobApplicantSkillFactory' ); /** @var JobApplicantSkillFactory $jasf */
		$jasf->setJobApplicant( $job_applicant_id );
		$jasf->setQualification( $qualification_id );
		$jasf->setFirstUsedDate( $this->getDate() - ( 86400 * rand( 200, 3000 ) ) );
		$jasf->setLastUsedDate( $this->getDate() - ( 86400 * rand( 11, 20 ) ) );
		$jasf->setExpiryDate( $this->getDate() - ( 86400 * rand( 1, 10 ) ) );
		$jasf->setEnableCalcExperience( true );
		$jasf->setDescription( '' );
		$jasf->setProficiency( array_rand( $jasf->getOptions( 'proficiency' ) ) );

		if ( $jasf->isValid() ) {
			$insert_id = $jasf->Save();
			Debug::Text( 'Job Applicant Skill ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Job Applicant Skill!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createClientGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$cgf = TTnew( 'ClientGroupFactory' ); /** @var ClientGroupFactory $cgf */
		$cgf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$cgf->setParent( $parent_id );
				$cgf->setName( 'Construction Company' );
				break;
			case 20:
				$cgf->setParent( $parent_id );
				$cgf->setName( 'Engineering Company' );
				break;
			case 30:
				$cgf->setParent( $parent_id );
				$cgf->setName( 'Construction Technology' );
				break;
			case 40:
				$cgf->setParent( $parent_id );
				$cgf->setName( 'Construction And Installation' );
				break;
			case 50:
				$cgf->setParent( $parent_id );
				$cgf->setName( 'Other' );
				break;
		}

		if ( $cgf->isValid() ) {
			$insert_id = $cgf->Save();
			Debug::Text( 'Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Client Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $parent_id
	 * @return bool
	 */
	function createProductGroup( $company_id, $type, $parent_id = 0 ) {
		if ( $parent_id === 0 ) {
			$parent_id = TTUUID::getZeroID();
		}

		$pgf = TTnew( 'ProductGroupFactory' ); /** @var ProductGroupFactory $pgf */
		$pgf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$pgf->setParent( $parent_id );
				$pgf->setName( 'Paints and Coatings' );
				break;
			case 20:
				$pgf->setParent( $parent_id );
				$pgf->setName( 'Basic Materials' );
				break;
			case 30:
				$pgf->setParent( $parent_id );
				$pgf->setName( 'Doors and Windows and Curtain wall' );
				break;
			case 40:
				$pgf->setParent( $parent_id );
				$pgf->setName( 'Metal doors and Frames' );
				break;
			case 50:
				$pgf->setParent( $parent_id );
				$pgf->setName( 'Stone' );
				break;
		}

		if ( $pgf->isValid() ) {
			$insert_id = $pgf->Save();
			Debug::Text( 'Group ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Product Group!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id  UUID
	 * @param string $group_ids   UUID
	 * @param $type
	 * @param string $currency_id UUID
	 * @return bool
	 */
	function createProduct( $company_id, $group_ids, $type, $currency_id ) {
		$pf = TTnew( 'ProductFactory' ); /** @var ProductFactory $pf */
		$pf->setCompany( $company_id );
		$pf->setStatus( 10 );
		$pf->setCurrency( $currency_id );
		switch ( $type ) {
			// Product
			case 10:
				$pf->setType( 10 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'nails100' );
				$pf->setName( 'Nails' );
				$pf->setDescription( 'Nails' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( '2.0' );
				$pf->setQuantity( 6000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '5.0' );
				$pf->setWeightUnit( 5 );
				$pf->setWeight( '2.0' );
				$pf->setDimensionUnit( 5 );
				$pf->setLength( '25.40' );
				$pf->setWidth( '10.65' );
				$pf->SetHeight( '10' );
				$pf->setOriginCountry( 'US' );
				$pf->setTariffCode( '1.1.1.1' );
				break;
			case 20:
				$pf->setType( 10 );
				$pf->setGroup( $group_ids[0] );
				$pf->setPartNumber( 'paint100' );
				$pf->setName( 'Paint' );
				$pf->setDescription( 'Paint' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( '3.0' );
				$pf->setQuantity( 3500 );
				$pf->setMinimumPurchaseQuantity( 100 );
				$pf->setMaximumPurchaseQuantity( 500 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '10.0' );
				$pf->setWeightUnit( 20 );
				$pf->setWeight( '20' );
				$pf->setDimensionUnit( 5 );
				$pf->setLength( '10' );
				$pf->setWidth( '29.5' );
				$pf->SetHeight( '32' );
				$pf->setOriginCountry( 'US' );
				$pf->setTariffCode( '1.1.1.1' );
				break;
			case 30:
				$pf->setType( 10 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'wood100' );
				$pf->setName( 'Wood' );
				$pf->setDescription( 'Wood' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( '12.00' );
				$pf->setQuantity( 5000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '30.00' );
				$pf->setWeightUnit( 20 );
				$pf->setWeight( '4' );
				$pf->setDimensionUnit( 5 );
				$pf->setLength( '910' );
				$pf->setWidth( '122' );
				$pf->setHeight( '50' );
				$pf->setOriginCountry( 'US' );
				$pf->setTariffCode( '1.1.1.1' );
				break;
			case 40:
				$pf->setType( 10 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'screws100' );
				$pf->setName( 'Screws' );
				$pf->setDescription( 'Screws' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( '0.5' );
				$pf->setQuantity( 4500 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '1.5' );
				$pf->setWeightUnit( 20 );
				$pf->setWeight( '0.04' );
				$pf->setDimensionUnit( 5 );
				$pf->setLength( '30' );
				$pf->setWidth( '10' );
				$pf->setHeight( '10' );
				$pf->setOriginCountry( 'US' );
				$pf->setTariffCode( '1.1.1.1' );
				break;
			case 50:
				$pf->setType( 10 );
				$pf->setGroup( $group_ids[2] );
				$pf->setPartNumber( 'doors100' );
				$pf->setName( 'Doors' );
				$pf->setDescription( 'Doors' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( '17.89' );
				$pf->setQuantity( 4000 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '34.78' );
				$pf->setWeightUnit( 20 );
				$pf->setWeight( '13.34' );
				$pf->setDimensionUnit( 5 );
				$pf->setLength( '2100' );
				$pf->setWidth( '900' );
				$pf->setHeight( '240' );
				$pf->setOriginCountry( 'US' );
				$pf->setTariffCode( '1.1.1.1' );
				break;
			// Product Service
			case 60:
				$pf->setType( 20 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'framing' );
				$pf->setName( 'Framing' );
				$pf->setDescription( 'Framing' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 7000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( 19.75 );
				break;
			case 62:
				$pf->setType( 20 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'sanding' );
				$pf->setName( 'Sanding' );
				$pf->setDescription( 'Sanding' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 7000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( 20.00 );
				break;
			case 64:
				$pf->setType( 20 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'painting' );
				$pf->setName( 'Painting' );
				$pf->setDescription( 'Painting' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 7000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( 22.00 );
				break;
			case 66:
				$pf->setType( 20 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'landscaping' );
				$pf->setName( 'Landscaping' );
				$pf->setDescription( 'Landscaping' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 7000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( 25.00 );
				break;
			case 69:
				$pf->setType( 20 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'misc' );
				$pf->setName( 'Miscellaneous' );
				$pf->setDescription( 'Miscellaneous' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 7000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( 10.00 );
				break;
			// Product Tax
			case 70:
				$pf->setType( 50 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'vat' );
				$pf->setName( 'VAT' );
				$pf->setDescription( 'VAT' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 7000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '0.5' );
				break;
			// Product Shipping Service
			case 80:
				$pf->setType( 60 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'shipping' );
				$pf->setName( 'Shipping' );
				$pf->setDescription( 'Shipping' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 4000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '5' );
				break;
			// Product Shipping Box
			case 90:
				$pf->setType( 62 );
				$pf->setGroup( $group_ids[1] );
				$pf->setPartNumber( 'shipping_box' );
				$pf->setName( 'Standard Shipping Box' );
				$pf->setDescription( 'Standard Shipping Box' );
				$pf->setDescriptionLocked( false );
				$pf->setUPC( '' );
				$pf->setUnitCost( 0 );
				$pf->setQuantity( 4000 );
				$pf->setMinimumPurchaseQuantity( 0 );
				$pf->setMaximumPurchaseQuantity( 0 );
				$pf->setPriceLocked( false );
				$pf->setUnitPriceType( 10 );
				$pf->setUnitPrice( '3.00' );
				$pf->setWeightUnit( 20 );
				$pf->setWeight( '0.01' );
				$pf->setDimensionUnit( 5 );
				$pf->setLength( '5000' );
				$pf->setWidth( '2000' );
				$pf->setHeight( '1000' );
				break;
		}

		if ( $pf->isValid() ) {
			$insert_id = $pf->Save();
			Debug::Text( 'Product ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Product!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id  UUID
	 * @param string $product_id  UUID
	 * @param $type
	 * @param string $currency_id UUID
	 * @param bool $include_area_policy_ids
	 * @param bool $exclude_area_policy_ids
	 * @return bool
	 */
	function createShippingPolicy( $company_id, $product_id, $type, $currency_id, $include_area_policy_ids = false, $exclude_area_policy_ids = false ) {
		$spf = TTnew( 'ShippingPolicyFactory' ); /** @var ShippingPolicyFactory $spf */
		$spf->setCompany( $company_id );
		$spf->setProduct( $product_id );
		$spf->setCurrency( $currency_id );
		//$spf->setType( array_rand($spf->getOptions('type')) );
		switch ( $type ) {
			case 10:
				$spf->setName( 'UPS' );
				$spf->setType( 10 );
				$spf->setWeightUnit( 10 );
				$spf->setBasePrice( '12.2' );
				$spf->setPrice( '23.45' );
				$spf->setMinimumPrice( '12' );
				$spf->setMaximumPrice( '30' );
				$spf->setHandlingFee( '0.5' );
				break;
			case 20;
				$spf->setName( 'Fedex' );
				$spf->setType( 40 );
				$spf->setWeightUnit( 10 );
				$spf->setBasePrice( '14' );
				$spf->setPrice( '30' );
				$spf->setMinimumPrice( '16' );
				$spf->setMaximumPrice( '36' );
				$spf->setHandlingFee( '0.5' );
				break;
		}

		if ( $spf->isValid() ) {
			$insert_id = $spf->Save( false );

			$spf->setIncludeAreaPolicy( $include_area_policy_ids );
			$spf->setExcludeAreaPolicy( $exclude_area_policy_ids );

			Debug::Text( 'Shipping Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Shipping Policy !', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @return bool
	 */
	public function createGEOFence( $company_id, $type ) {
		$gf = TTnew( 'GEOFenceFactory' ); /** @var GEOFenceFactory $gf */
		$gf->setCompany( $company_id );
		switch ( $type ) {
			case 10:
				$gf->setName( 'Yonkers' );
				$gf->setDescription( '' );
				$gf->setGEOCircle( [
										   'center' => [ '40.939452352446', '-73.82194519043' ],
										   'radius' => '6500',
								   ] );
				$gf->setGEOType( 20 );
				$gf->setMapLevel( 12 );
				$gf->setGEOColor( '0902C8' );
				break;
			case 20:
				$gf->setName( 'New York (Polygon) Staten Island/Brooklyn' );
				$gf->setDescription( '' );
				$gf->setGEOPolygon(
						[
								[ '40.953325951597', '-73.887090682983' ],
								[ '40.732884883744', '-74.023776054382' ],
								[ '40.649582816057', '-74.077334403992' ],
								[ '40.637078524605', '-74.152865409851' ],
								[ '40.642288930731', '-74.195437431335' ],
								[ '40.606850149638', '-74.202303886414' ],
								[ '40.552613294287', '-74.214663505554' ],
								[ '40.551569846909', '-74.248995780945' ],
								[ '40.49311084579', '-74.247622489929' ],
								[ '40.543221682538', '-74.104800224304' ],
								[ '40.598508880309', '-74.053988456726' ],
								[ '40.564090142273', '-74.003176689148' ],
								[ '40.574521933048', '-73.882327079773' ],
								[ '40.644372979307', '-73.830142021179' ],
								[ '40.641246882041', '-73.743624687195' ],
								[ '40.764096306846', '-73.753237724304' ],
								[ '40.80776768595', '-73.819155693054' ],
								[ '40.911631546484', '-73.783450126648' ],
								[ '40.953325951597', '-73.887090682983' ],
						]
				);
				$gf->setGEOType( 10 );
				$gf->setMapLevel( 12 );
				$gf->setGEOColor( 'FF7C04' );
				break;
			case 30:
				$gf->setName( 'Jersey City (Polygon)' );
				$gf->setDescription( '' );
				$gf->setGEOPolygon(
						[
								[ '40.717078515798', '-74.107933044434' ],
								[ '40.792498966133', '-74.050254821777' ],
								[ '40.778461640904', '-74.009056091309' ],
								[ '40.70484714530', '-74.031372070313' ],
								[ '40.64886648762', '-74.07154083252' ],
								[ '40.644698606019', '-74.155654907227' ],
								[ '40.717078515798', '-74.107933044434' ],
						]
				);
				$gf->setGEOType( 10 );
				$gf->setMapLevel( 12 );
				$gf->setGEOColor( 'C61B11' );
				break;

			case 40:
				$gf->setName( 'West Seattle (Circle)' );
				$gf->setDescription( '' );
				$gf->setGEOCircle( [
										   'center' => [ '47.631156', '-122.883453' ],
										   'radius' => '50000',
								   ] );
				$gf->setGEOType( 20 );
				$gf->setMapLevel( 10 );
				$gf->setGEOColor( '316400' );
				break;
			case 50:
				$gf->setName( 'Greater Seattle (Polygon)' );
				$gf->setDescription( '' );
				$gf->setGEOPolygon(
						[
								[ '47.941187', '-122.111664' ],
								[ '47.931066', '-121.740875' ],
								[ '47.102849', '-121.831512' ],
								[ '47.096305', '-122.512665' ],
								[ '47.294134', '-122.530518' ],
								[ '47.282024', '-122.100677' ],
								[ '47.941187', '-122.111664' ],
						]
				);
				$gf->setGEOType( 10 );
				$gf->setMapLevel( 9 );
				$gf->setGEOColor( 'FF0CF9' );
				break;
			case 60:
				$gf->setName( 'Seattle Proper (Polygon)' );
				$gf->setDescription( '' );
				$gf->setGEOPolygon(
						[
								[ '47.734792', '-122.374971' ],
								[ '47.733868', '-122.278154' ],
								[ '47.689515', '-122.261674' ],
								[ '47.48876', '-122.220476' ],
								[ '47.485338', '-122.393253' ],
								[ '47.556111', '-122.414968' ],
								[ '47.654837', '-122.436426' ],
								[ '47.734792', '-122.374971' ],
						]
				);
				$gf->setGEOType( 10 );
				$gf->setMapLevel( 12 );
				$gf->setGEOColor( 'BE0000' );
				break;
		}

		if ( $gf->isValid() ) {
			$insert_id = $gf->Save();
			Debug::Text( 'GEO Fence ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating GEO Fence!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id    UUID
	 * @param $type
	 * @param string $item_id       UUID
	 * @param int $job_group_id
	 * @param int $branch_id
	 * @param int $department_id
	 * @param string $client_id     UUID
	 * @param string $geo_fence_ids UUID
	 * @return bool
	 */
	function createJob( $company_id, $type, $item_id, $job_group_id = 0, $branch_id = 0, $department_id = 0, $client_id = null, $geo_fence_ids = null ) {
		if ( $job_group_id === 0 ) {
			$job_group_id = TTUUID::getZeroID();
		}

		if ( $branch_id === 0 ) {
			$branch_id = TTUUID::getZeroID();
		}

		if ( $department_id === 0 ) {
			$department_id = TTUUID::getZeroID();
		}

		$jf = TTnew( 'JobFactory' ); /** @var JobFactory $jf */

		$jf->setCompany( $company_id );
		//$jf->setClient( $data['client_id'] );
		$jf->setStatus( 10 );
		//$jf->setGroup( $data['group_id'] );
		//$jf->setBranch( $data['branch_id'] );
		//$jf->setDepartment( $data['department_id'] );

		$jf->setDefaultItem( $item_id );

		switch ( $type ) {
			case 10:
				$jf->setManualID( 10 );
				$jf->setName( 'House 1' );
				$jf->setDescription( rand( 100, 9999 ) . ' Main St' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 28 ) );
				$jf->setEndDate( '' );

				$jf->setEstimateTime( ( 3600 * 500 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '20.00' );
				$jf->setMinimumTime( ( 3600 * 30 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( '123 Main St' );
				$jf->setAddress2( 'Unit #123' );
				$jf->setCity( 'New York' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'NY' );
				$jf->setPostalCode( '12345' );

				//$jf->setNote( $data['note'] );

				break;
			case 11:
				$jf->setManualID( 11 );
				$jf->setName( 'House 2' );
				$jf->setDescription( rand( 100, 9999 ) . ' Springfield Rd' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 13 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( '789 Main St' );
				$jf->setAddress2( 'Unit #789' );
				$jf->setCity( 'Seattle' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'WA' );
				$jf->setPostalCode( '98105' );
				break;
			case 12:
				$jf->setManualID( 12 );
				$jf->setName( 'House 3' );
				$jf->setDescription( rand( 100, 9999 ) . ' Spall Ave' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 12 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Springfield St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'New York' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'NY' );
				$jf->setPostalCode( str_pad( rand( 400, 599 ), 5, 0, STR_PAD_LEFT ) );
				break;
			case 13:
				$jf->setManualID( 13 );
				$jf->setName( 'House 4' );
				$jf->setDescription( rand( 100, 9999 ) . ' Dobbin St' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 11 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );

				$jf->setAddress1( rand( 100, 9999 ) . ' Ethel St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'New York' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'NY' );
				$jf->setPostalCode( str_pad( rand( 400, 599 ), 5, 0, STR_PAD_LEFT ) );
				break;
			case 14:
				$jf->setManualID( 14 );
				$jf->setName( 'House 5' );
				$jf->setDescription( rand( 100, 9999 ) . ' Sussex Court' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 10 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Spall St' );
				$jf->setAddress2( 'Unit #123' );
				$jf->setCity( 'Seattle' );

				$jf->setCountry( 'US' );
				$jf->setProvince( 'WA' );

				$jf->setPostalCode( rand( 98000, 99499 ) );
				break;
			case 15:
				$jf->setManualID( 15 );
				$jf->setName( 'House 6' );
				$jf->setDescription( rand( 100, 9999 ) . ' Georgia St' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 9 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Dobbin St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'Seattle' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'WA' );
				$jf->setPostalCode( rand( 98000, 99499 ) );
				break;
			case 16:
				$jf->setManualID( 16 );
				$jf->setName( 'House 7' );
				$jf->setDescription( rand( 100, 9999 ) . ' Gates Rd' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 8 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Lakeshore St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'Seattle' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'WA' );
				$jf->setPostalCode( rand( 98000, 99499 ) );
				break;
			case 17:
				$jf->setManualID( 17 );
				$jf->setName( 'House 8' );
				$jf->setDescription( rand( 100, 9999 ) . ' Lakeshore Rd' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 7 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Pandosy St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'Seattle' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'WA' );
				$jf->setPostalCode( rand( 98000, 99499 ) );
				break;
			case 18:
				$jf->setManualID( 18 );
				$jf->setName( 'House 9' );
				$jf->setDescription( rand( 100, 9999 ) . ' Main St' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 6 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Ontario St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'Seattle' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'WA' );
				$jf->setPostalCode( rand( 98000, 99499 ) );
				break;
			case 19:
				$jf->setManualID( 19 );
				$jf->setName( 'House 10' );
				$jf->setDescription( rand( 100, 9999 ) . ' Ontario St' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 5 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 750 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '45.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );

				$jf->setClient( $client_id );
				$jf->setAddress1( rand( 100, 9999 ) . ' Georgia St' );
				$jf->setAddress2( 'Unit #' . rand( 10, 999 ) );
				$jf->setCity( 'New York' );
				$jf->setCountry( 'US' );
				$jf->setProvince( 'NY' );
				$jf->setPostalCode( str_pad( rand( 400, 599 ), 5, 0, STR_PAD_LEFT ) );
				break;
			case 20:
				$jf->setManualID( 20 );
				$jf->setName( 'Project A' );
				$jf->setDescription( '' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 4 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 760 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '55.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );
				//$jf->setNote( $data['note'] );

				$jf->setClient( $client_id );
				break;
			case 21:
				$jf->setManualID( 21 );
				$jf->setName( 'Project B' );
				$jf->setDescription( '' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 3 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 760 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '55.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );
				//$jf->setNote( $data['note'] );

				$jf->setClient( $client_id );
				break;
			case 22:
				$jf->setManualID( 22 );
				$jf->setName( 'Project C' );
				$jf->setDescription( '' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 2 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 760 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '55.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );
				//$jf->setNote( $data['note'] );

				$jf->setClient( $client_id );
				break;
			case 23:
				$jf->setManualID( 23 );
				$jf->setName( 'Project D' );
				$jf->setDescription( '' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 1 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 7 ) );

				$jf->setEstimateTime( ( 3600 * 760 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '55.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );
				//$jf->setNote( $data['note'] );

				$jf->setClient( $client_id );
				break;
			case 24:
				$jf->setManualID( 24 );
				$jf->setName( 'Project E' );
				$jf->setDescription( '' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 15 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 2 ) );

				$jf->setEstimateTime( ( 3600 * 760 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '55.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );
				//$jf->setNote( $data['note'] );

				$jf->setClient( $client_id );
				break;
			case 25:
				$jf->setManualID( 25 );
				$jf->setName( 'Project F' );
				$jf->setDescription( '' );

				$jf->setStartDate( $this->getDate() - ( 86400 * 28 ) );
				$jf->setEndDate( $this->getDate() + ( 86400 * 1 ) );

				$jf->setEstimateTime( ( 3600 * 760 ) );
				$jf->setEstimateQuantity( 0 );
				$jf->setEstimateBadQuantity( 0 );
				$jf->setBadQuantityRate( 0 );
				$jf->setBillableRate( '55.00' );
				$jf->setMinimumTime( ( 3600 * 100 ) );
				$jf->setGroup( $job_group_id );
				$jf->setBranch( $branch_id );
				$jf->setDepartment( $department_id );
				//$jf->setNote( $data['note'] );

				$jf->setClient( $client_id );
				break;
		}

		$jf->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
		$jf->setLongitude( $this->getRandomCoordinates( 'longitude' ) );

		if ( $jf->isValid() ) {
			$insert_id = $jf->Save( false );
			Debug::Text( 'Job ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			if ( is_array( $geo_fence_ids ) ) {
				$jf->setGEOFenceIds( $geo_fence_ids );
			} else {
				$jf->setGEOFenceIds( [] );
			}

			if ( $jf->isValid() ) {
				$jf->Save();

				Debug::Text( 'Job ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

				return $insert_id;
			}
		}

		Debug::Text( 'Failed Creating Job!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id  UUID
	 * @param string $template_id UUID
	 * @param int $start_date     EPOCH
	 * @param int $end_date       EPOCH
	 * @param string|string[] $user_ids    UUID
	 * @return bool
	 */
	function createRecurringSchedule( $company_id, $template_id, $start_date, $end_date, $user_ids, $display_weeks = 4 ) {
		$rscf = TTnew( 'RecurringScheduleControlFactory' ); /** @var RecurringScheduleControlFactory $rscf */
		$rscf->setCompany( $company_id );
		$rscf->setRecurringScheduleTemplateControl( $template_id );
		$rscf->setStartWeek( 1 );
		$rscf->setDisplayWeeks( $display_weeks );
		$rscf->setStartDate( $start_date );
		$rscf->setEndDate( $end_date );
		$rscf->setAutoFill( false );

		if ( $rscf->isValid() ) {
			$rscf->Save( false );

			if ( isset( $user_ids ) ) {
				$rscf->setUser( $user_ids );
			}

			if ( $rscf->isValid() ) {
				$rscf->Save();
				Debug::Text( 'Saving Recurring Schedule...', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $company_id         UUID
	 * @param $type
	 * @param string $schedule_policy_id UUID
	 * @param int $open_shift_multiplier
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	function createRecurringScheduleTemplate( $company_id, $type, $schedule_policy_id = null, $open_shift_multiplier = 1 ) {
		$rstcf = TTnew( 'RecurringScheduleTemplateControlFactory' ); /** @var RecurringScheduleTemplateControlFactory $rstcf */
		$rstcf->setCompany( $company_id );

		switch ( $type ) {
			case 10: //Morning Shift
				$rstcf->setName( 'Morning Shift' );
				$rstcf->setDescription( '6:00AM - 3:00PM' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '06:00 AM' ) );
					$rstf->setEndTime( strtotime( '03:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setJob( TTUUID::getNotExistID() );        //Default
					$rstf->setJobItem( TTUUID::getNotExistID() );    //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
			case 20: //Afternoon Shift
				$rstcf->setName( 'Afternoon Shift' );
				$rstcf->setDescription( '10:00AM - 7:00PM' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '10:00 AM' ) );
					$rstf->setEndTime( strtotime( '07:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setJob( TTUUID::getNotExistID() );        //Default
					$rstf->setJobItem( TTUUID::getNotExistID() );    //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
			case 30: //Evening Shift
				$rstcf->setName( 'Evening Shift' );
				$rstcf->setDescription( '2:00PM - 11:00PM' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '02:00 PM' ) );
					$rstf->setEndTime( strtotime( '11:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setJob( TTUUID::getNotExistID() );        //Default
					$rstf->setJobItem( TTUUID::getNotExistID() );    //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
			case 40: //Split Shift
				$rstcf->setName( 'Split Shift' );
				$rstcf->setDescription( '8:00AM-12:00PM, 5:00PM-9:00PM ' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '08:00 AM' ) );
					$rstf->setEndTime( strtotime( '12:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setJob( TTUUID::getNotExistID() );        //Default
					$rstf->setJobItem( TTUUID::getNotExistID() );    //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}
					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '05:00 PM' ) );
					$rstf->setEndTime( strtotime( '9:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
			case 50: //Full Rotation
				$rstcf->setName( 'Full Rotation' );
				$rstcf->setDescription( '1wk-Morning, 1wk-Afternoon, 1wk-Evening' );

				if ( $rstcf->isValid() ) {
					$rstc_id = $rstcf->Save();
					Debug::Text( 'bRecurring Schedule Template Control ID: ' . $rstc_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Week 1
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 1 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '06:00 AM' ) );
					$rstf->setEndTime( strtotime( '03:00 PM' ) );
					$rstf->setJob( TTUUID::getNotExistID() );     //Default
					$rstf->setJobItem( TTUUID::getNotExistID() ); //Default

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					//Week 2
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 2 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '10:00 AM' ) );
					$rstf->setEndTime( strtotime( '07:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}
					//Week 3
					$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
					$rstf->setRecurringScheduleTemplateControl( $rstc_id );
					$rstf->setWeek( 3 );
					$rstf->setSun( false );
					$rstf->setMon( true );
					$rstf->setTue( true );
					$rstf->setWed( true );
					$rstf->setThu( true );
					$rstf->setFri( true );
					$rstf->setSat( false );

					$rstf->setStartTime( strtotime( '02:00 PM' ) );
					$rstf->setEndTime( strtotime( '11:00 PM' ) );

					if ( TTUUID::isUUID( $schedule_policy_id ) && $schedule_policy_id != TTUUID::getZeroID() && $schedule_policy_id != TTUUID::getNotExistID() ) {
						$rstf->setSchedulePolicyID( $schedule_policy_id );
					}
					$rstf->setBranch( TTUUID::getNotExistID() );     //Default
					$rstf->setDepartment( TTUUID::getNotExistID() ); //Default
					$rstf->setJob( TTUUID::getNotExistID() );        //Default
					$rstf->setJobItem( TTUUID::getNotExistID() );    //Default
					$rstf->setOpenShiftMultiplier( $open_shift_multiplier );

					if ( $rstf->isValid() ) {
						Debug::Text( 'Saving Recurring Schedule Week...', __FILE__, __LINE__, __METHOD__, 10 );
						$rstf->Save();
					}

					return $rstc_id;
				}

				break;
		}

		Debug::Text( 'ERROR Saving schedule template!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param int $date_stamp    EPOCH
	 * @param null $data
	 * @return bool
	 */
	function createSchedule( $company_id, $user_id, $date_stamp, $data = null ) {
		$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$sf->setCompany( $company_id );
		$sf->setUser( $user_id );
		//$sf->setUserDateId( UserDateFactory::findOrInsertUserDate( $user_id, $date_stamp) );

		if ( isset( $data['status_id'] ) ) {
			$sf->setStatus( $data['status_id'] );
		} else {
			$sf->setStatus( 10 );
		}

		if ( isset( $data['schedule_policy_id'] ) ) {
			$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
		}

		if ( isset( $data['absence_policy_id'] ) ) {
			$sf->setAbsencePolicyID( $data['absence_policy_id'] );
		}
		if ( isset( $data['branch_id'] ) ) {
			$sf->setBranch( $data['branch_id'] );
		}
		if ( isset( $data['department_id'] ) ) {
			$sf->setDepartment( $data['department_id'] );
		}

		if ( isset( $data['job_id'] ) ) {
			$sf->setJob( $data['job_id'] );
		}

		if ( isset( $data['job_item_id'] ) ) {
			$sf->setJobItem( $data['job_item_id'] );
		}

		if ( $data['start_time'] != '' ) {
			$start_time = strtotime( $data['start_time'], $date_stamp );
		}
		if ( $data['end_time'] != '' ) {
			Debug::Text( 'End Time: ' . $data['end_time'] . ' Date Stamp: ' . $date_stamp, __FILE__, __LINE__, __METHOD__, 10 );
			$end_time = strtotime( $data['end_time'], $date_stamp );
			Debug::Text( 'bEnd Time: ' . $data['end_time'] . ' - ' . TTDate::getDate( 'DATE+TIME', $data['end_time'] ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		$sf->setStartTime( $start_time );
		$sf->setEndTime( $end_time );

		if ( $sf->isValid() ) {
			$sf->setEnableReCalculateDay( false );
			$insert_id = $sf->Save();
			Debug::Text( 'Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $id UUID
	 * @param null $data
	 * @return bool
	 */
	function editSchedule( $id, $data = null ) {
		if ( $id == '' ) {
			return false;
		}

		$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->StartTransaction();
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			//var_dump($data);
			$s_obj = $slf->getCurrent();

			if ( isset( $data['status_id'] ) ) {
				$s_obj->setStatus( $data['status_id'] );
			}

			if ( isset( $data['schedule_policy_id'] ) ) {
				$s_obj->setSchedulePolicyID( $data['schedule_policy_id'] );
			}

			if ( isset( $data['absence_policy_id'] ) ) {
				$s_obj->setAbsencePolicyID( $data['absence_policy_id'] );
			}

			if ( isset( $data['branch_id'] ) ) {
				$s_obj->setBranch( $data['branch_id'] );
			}
			if ( isset( $data['department_id'] ) ) {
				$s_obj->setDepartment( $data['department_id'] );
			}

			if ( isset( $data['job_id'] ) ) {
				$s_obj->setJob( $data['job_id'] );
			}
			if ( isset( $data['job_item_id'] ) ) {
				$s_obj->setJobItem( $data['job_item_id'] );
			}

			if ( isset( $data['start_time'] ) ) {
				$s_obj->setStartTime( $data['start_time'] );
			}
			if ( isset( $data['end_time'] ) ) {
				$s_obj->setEndTime( $data['end_time'] );
			}

			if ( isset( $data['note'] ) ) {
				$s_obj->setNote( $data['note'] );
			}

			if ( $s_obj->isValid() ) {
				$s_obj->setEnableReCalculateDay( false );
				$s_obj->Save();

				return true;
			}
		}

		Debug::Text( 'Failed Editing Schedule!', __FILE__, __LINE__, __METHOD__, 10 );
		$slf->FailTransaction();
		$slf->CommitTransaction();

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function deleteSchedule( $id ) {
		$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getById( $id );
		if ( $slf->getRecordCount() > 0 ) {
			foreach ( $slf as $s_obj ) {
				$s_obj->setDeleted( true );
				$s_obj->setEnableReCalculateDay( true );
				if ( $s_obj->isValid() ) {
					Debug::Text( 'Schedule ID deleted: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
					$s_obj->Save();
				}
			}

			return true;
		}

		Debug::Text( 'No Schedule to Delete: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function deletePunch( $id ) {
		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		$plf->getById( $id );
		if ( $plf->getRecordCount() > 0 ) {
			Debug::Text( 'Deleting Punch ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $plf as $p_obj ) {
				$p_obj->setUser( $p_obj->getPunchControlObject()->getUser() );
				$p_obj->setDeleted( true );
				$p_obj->setEnableCalcTotalTime( true );
				$p_obj->setEnableCalcSystemTotalTime( true );
				$p_obj->setEnableCalcWeeklySystemTotalTime( true );
				$p_obj->setEnableCalcUserDateTotal( true );
				$p_obj->setEnableCalcException( true );
				$p_obj->Save();
			}
			Debug::Text( 'Deleting Punch ID: ' . $id . ' Done...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @param null $data
	 * @return bool
	 */
	function editPunch( $id, $data = null ) {
		if ( $id == '' ) {
			return false;
		}

		//Edit out punch so its on the next day.
		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		$plf->StartTransaction();
		$plf->getById( $id );
		if ( $plf->getRecordCount() == 1 ) {
			//var_dump($data);
			$p_obj = $plf->getCurrent();

			//$p_obj->setUser( $this->user_id );

			if ( isset( $data['type_id'] ) ) {
				$p_obj->setType( $data['type_id'] );
			}

			if ( isset( $data['status_id'] ) ) {
				$p_obj->setStatus( $data['status_id'] );
			}

			if ( isset( $data['time_stamp'] ) ) {
				$p_obj->setTimeStamp( $data['time_stamp'] );
			}

			if ( $p_obj->isValid() == true ) {
				$p_obj->Save( false );

				$p_obj->getPunchControlObject()->setPunchObject( $p_obj );
				$p_obj->getPunchControlObject()->setEnableCalcUserDateID( true );
				$p_obj->getPunchControlObject()->setEnableCalcSystemTotalTime( true );
				$p_obj->getPunchControlObject()->setEnableCalcWeeklySystemTotalTime( true );
				$p_obj->getPunchControlObject()->setEnableCalcException( true );
				$p_obj->getPunchControlObject()->setEnablePreMatureException( true );
				$p_obj->getPunchControlObject()->setEnableCalcUserDateTotal( true );
				$p_obj->getPunchControlObject()->setEnableCalcTotalTime( true );

				if ( $p_obj->getPunchControlObject()->isValid() == true ) {
					$p_obj->getPunchControlObject()->Save();

					$plf->CommitTransaction();

					return true;
				}
			}
		}

		$plf->FailTransaction();
		$plf->CommitTransaction();

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $type_id
	 * @param int $status_id
	 * @param $time_stamp
	 * @param $data
	 * @param null $coordinates
	 * @param bool $calc_total_time
	 * @return bool
	 */
	function createPunch( $user_id, $type_id, $status_id, $time_stamp, $data, $coordinates = null, $calc_total_time = true ) {
		$fail_transaction = false;

		Debug::Text( 'User ID: ' . $user_id . ' Time Stamp: ' . TTDate::getDate( 'DATE+TIME', $time_stamp ), __FILE__, __LINE__, __METHOD__, 10 );

		$pf = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf */

		$pf->StartTransaction();

		$pf->setTransfer( false );
		$pf->setEnableAutoTransfer( false );
		$pf->setUser( $user_id );
		$pf->setType( $type_id );
		$pf->setStatus( $status_id );
		$pf->setTimeStamp( $time_stamp );

		if ( is_array( $coordinates ) ) {
			$pf->setLatitude( $coordinates[0] );
			$pf->setLongitude( $coordinates[1] );
		}

		if ( $pf->isNew() ) {
			$pf->setActualTimeStamp( $time_stamp );
			$pf->setOriginalTimeStamp( $pf->getTimeStamp() );
		}

		$pf->setPunchControlID( $pf->findPunchControlID() );
		if ( $pf->isValid() ) {
			if ( $pf->Save( false ) === false ) {
				Debug::Text( ' aFail Transaction: ', __FILE__, __LINE__, __METHOD__, 10 );
				$fail_transaction = true;
			}
		} else {
			$fail_transaction = true;
		}

		if ( $fail_transaction == false ) {
			$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */
			$pcf->setId( $pf->getPunchControlID() );
			$pcf->setPunchObject( $pf );
			$pcf->setBranch( $data['branch_id'] );
			$pcf->setDepartment( $data['department_id'] );
			if ( isset( $data['job_id'] ) ) {
				$pcf->setJob( $data['job_id'] );
			}
			if ( isset( $data['job_item_id'] ) ) {
				$pcf->setJobItem( $data['job_item_id'] );
			}
			if ( isset( $data['quantity'] ) ) {
				$pcf->setQuantity( $data['quantity'] );
			}
			if ( isset( $data['bad_quantity'] ) ) {
				$pcf->setBadQuantity( $data['bad_quantity'] );
			}

			$pcf->setEnableCalcUserDateID( true );
			$pcf->setEnableCalcTotalTime( $calc_total_time );
			$pcf->setEnableCalcSystemTotalTime( $calc_total_time );
			$pcf->setEnableCalcWeeklySystemTotalTime( $calc_total_time );
			$pcf->setEnableCalcUserDateTotal( $calc_total_time );
			$pcf->setEnableCalcException( $calc_total_time );

			if ( $pcf->isValid() == true ) {
				$punch_control_id = $pcf->Save( true, true ); //Force lookup

				if ( $fail_transaction == false ) {
					Debug::Text( 'Punch Control ID: ' . $punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
					$pf->CommitTransaction();

					return true;
				}
			}
		}

		Debug::Text( 'Failed Creating Punch!', __FILE__, __LINE__, __METHOD__, 10 );
		$pf->FailTransaction();
		$pf->CommitTransaction();

		return false;
	}

	/**
	 * @param string $type
	 * @return float
	 */
	function getRandomCoordinates( $type = 'longitude' ) {
		if ( $type == 'longitude' ) {
			$retval = ( round( ( mt_rand( 80, 110 ) + mt_rand( 0, 32767 ) / 32767 ), 5 ) * -1 );
		} else {
			$retval = round( ( mt_rand( 30, 50 ) + mt_rand( 0, 32767 ) / 32767 ), 5 );
		}

		return $retval;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $date_stamp EPOCH
	 * @param $total_time
	 * @param int $branch_id
	 * @param int $department_id
	 * @return bool
	 */
	function createUserDateTotal( $user_id, $date_stamp, $total_time, $branch_id = 0, $department_id = 0 ) {
		if ( $branch_id === 0 ) {
			$branch_id = TTUUID::getZeroID();
		}

		if ( $department_id === 0 ) {
			$department_id = TTUUID::getZeroID();
		}

		$udtf = TTnew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $udtf */

		$udtf->StartTransaction();

		$udtf->setUser( $user_id );
		$udtf->setDateStamp( $date_stamp );
		$udtf->setObjectType( 10 ); //Regular time
		//$udtf->setSourceObject( TTUUID::castUUID($policy_id) );
		//$udtf->setPayCode( $pay_code_id );

		$udtf->setBranch( TTUUID::castUUID( $branch_id ) );
		$udtf->setDepartment( TTUUID::castUUID( $department_id ) );
		$udtf->setJob( (int)0 );
		$udtf->setJobItem( (int)0 );

		$udtf->setQuantity( 0 );
		$udtf->setBadQuantity( 0 );
		$udtf->setTotalTime( $total_time );

		$udtf->setOverride( true );

		$udtf->setEnableTimeSheetVerificationCheck( true ); //Unverify timesheet if its already verified.
		$udtf->setEnableCalcSystemTotalTime( true );
		$udtf->setEnableCalcWeeklySystemTotalTime( true );
		$udtf->setEnableCalcException( true );

		if ( $udtf->isValid() ) {
			if ( $udtf->isNew() ) {
				$retval = $udtf->Save();
			} else {
				$retval = $udtf->getID();
				$udtf->Save();
			}

			$udtf->CommitTransaction();

			return $retval;
		}

		Debug::Text( ' Failed creating Absence...', __FILE__, __LINE__, __METHOD__, 10 );
		$udtf->FailTransaction();
		$udtf->CommitTransaction();

		return false;
	}

	/**
	 * @param string $user_id           UUID
	 * @param int $date_stamp           EPOCH
	 * @param $total_time
	 * @param string $absence_policy_id UUID
	 * @param bool $override
	 * @return bool
	 */
	function createAbsence( $user_id, $date_stamp, $total_time, $absence_policy_id, $override = false ) {
		$udtf = TTnew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $udtf */

		$udtf->StartTransaction();

		$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
		$aplf->getById( $absence_policy_id );
		if ( $aplf->getRecordCount() == 1 ) {
			$pay_code_id = $aplf->getCurrent()->getPayCode();

			if ( $override == true ) {
				$filter_data = [
						'user_id'        => TTUUID::castUUID( $user_id ),
						'date_stamp'     => $date_stamp,
						'object_type_id' => (int)50,

						//Restrict based on src_object_id when entering absences as well.
						//This allows multiple absence policies to point to the same pay code
						//and still have multiple entries on the same day with the same branch/department/job/task.
						//Some customers have 5-10 UNPAID absence policies all going to the same UNPAID pay code.
						//This is required to allow more than one to be used on the same day.
						'src_object_id'  => TTUUID::castUUID( $absence_policy_id ),
						'pay_code_id'    => TTUUID::castUUID( $pay_code_id ),
				];

				$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
				$udtlf->getAPISearchByCompanyIdAndArrayCriteria( $aplf->getCurrent()->getCompany(), $filter_data );
				if ( $udtlf->getRecordCount() > 0 ) {
					$udtf = $udtlf->getCurrent();
					Debug::Text( ' Found existing Absence, UDT ID: ' . $udtf->getID(), __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Text( ' No existing Absence...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			$udtf->setUser( $user_id );
			$udtf->setDateStamp( $date_stamp );
			$udtf->setObjectType( 50 ); //Absence Time (Taken)
			$udtf->setSourceObject( TTUUID::castUUID( $absence_policy_id ) );
			$udtf->setPayCode( $pay_code_id );

			$udtf->setBranch( (int)0 );
			$udtf->setDepartment( (int)0 );
			$udtf->setJob( (int)0 );
			$udtf->setJobItem( (int)0 );

			$udtf->setQuantity( 0 );
			$udtf->setBadQuantity( 0 );
			$udtf->setTotalTime( $total_time );

			$udtf->setOverride( true );

			$udtf->setEnableTimeSheetVerificationCheck( true ); //Unverify timesheet if its already verified.
			$udtf->setEnableCalcSystemTotalTime( true );
			$udtf->setEnableCalcWeeklySystemTotalTime( true );
			$udtf->setEnableCalcException( true );

			if ( $udtf->isValid() ) {
				if ( $udtf->isNew() ) {
					$retval = $udtf->Save();
				} else {
					$retval = $udtf->getID();
					$udtf->Save();
				}

				$udtf->CommitTransaction();

				return $retval;
			}

			Debug::Text( ' Failed creating Absence...', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::Text( ' Failed creating Absence, Absence policy does not exist...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$udtf->FailTransaction();
		$udtf->CommitTransaction();

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function deleteAbsence( $id ) {
		$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
		$udtlf->getById( $id );
		if ( $udtlf->getRecordCount() > 0 ) {
			Debug::Text( 'Deleting UDT ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $udtlf as $udt_obj ) {
				$udt_obj->setDeleted( true );
				$udt_obj->setEnableTimeSheetVerificationCheck( true ); //Unverify timesheet if its already verified.
				$udt_obj->setEnableCalcSystemTotalTime( true );
				$udt_obj->setEnableCalcWeeklySystemTotalTime( true );
				$udt_obj->setEnableCalcException( true );
				if ( $udt_obj->isValid() ) {
					$udt_obj->Save();
				}
			}
			Debug::Text( 'Deleting UDT ID: ' . $id . ' Done...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::Text( 'No record to delete UDT ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param $in_time_stamp
	 * @param $out_time_stamp
	 * @param null $data
	 * @param bool $calc_total_time
	 * @param bool $disable_rounding
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 */
	function createPunchPair( $user_id, $in_time_stamp, $out_time_stamp, $data = null, $calc_total_time = true, $disable_rounding = false ) {
		$fail_transaction = false;

		Debug::Text( 'Punch Full In Time Stamp: (' . $in_time_stamp . ') ' . TTDate::getDate( 'DATE+TIME', $in_time_stamp ) . ' Out: (' . $out_time_stamp . ') ' . TTDate::getDate( 'DATE+TIME', $out_time_stamp ), __FILE__, __LINE__, __METHOD__, 10 );

		$pf = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf */
		$pf->StartTransaction();

		//Out Punch
		if ( $out_time_stamp !== null ) {
			$pf_in = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf_in */
			$pf_in->setTransfer( $disable_rounding );
			$pf_in->setEnableAutoTransfer( false );
			$pf_in->setUser( $user_id );
			$pf_in->setType( $data['out_type_id'] );
			$pf_in->setStatus( 20 );
			$pf_in->setTimeStamp( $out_time_stamp, !$disable_rounding );
			$pf_in->setPositionAccuracy( mt_rand( 10, 100 ) );
			if ( isset( $data['in_coordinates'] ) && is_array( $data['in_coordinates'] ) ) {
				$pf_in->setLatitude( $data['in_coordinates'][0] );
				$pf_in->setLongitude( $data['in_coordinates'][1] );
			} else {
				$pf_in->setLongitude( $this->getRandomCoordinates( 'longitude' ) );
				$pf_in->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
			}
			if ( $pf_in->isNew() ) {
				$pf_in->setActualTimeStamp( $out_time_stamp );
				$pf_in->setOriginalTimeStamp( $pf_in->getTimeStamp() );
			}

			$pf_in->setPunchControlID( $pf_in->findPunchControlID() );

			$pf_in->setUpdatedBy( $user_id ); //Prevent punches being flagged as tainted.
			$pf_in->setCreatedBy( $user_id ); //Prevent punches being flagged as tainted.

			if ( $pf_in->isValid() ) {
				if ( $pf_in->Save( false ) === false ) {
					Debug::Text( ' aFail Transaction: ', __FILE__, __LINE__, __METHOD__, 10 );
					$fail_transaction = true;
				}
			} else {
				$fail_transaction = true;
			}
		}

		//In Punch
		if ( $in_time_stamp !== null ) {
			$pf_out = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf_out */
			$pf_out->setTransfer( $disable_rounding );
			$pf_out->setEnableAutoTransfer( false );
			$pf_out->setUser( $user_id );
			$pf_out->setType( $data['in_type_id'] );
			$pf_out->setStatus( 10 );
			$pf_out->setTimeStamp( $in_time_stamp, !$disable_rounding );
			$pf_out->setPositionAccuracy( mt_rand( 10, 100 ) );
			if ( isset( $data['out_coordinates'] ) && is_array( $data['out_coordinates'] ) ) {
				$pf_out->setLatitude( $data['out_coordinates'][0] );
				$pf_out->setLongitude( $data['out_coordinates'][1] );
			} else {
				$pf_out->setLongitude( $this->getRandomCoordinates( 'longitude' ) );
				$pf_out->setLatitude( $this->getRandomCoordinates( 'latitude' ) );
			}
			if ( $pf_out->isNew() ) {
				$pf_out->setActualTimeStamp( $in_time_stamp );
				$pf_out->setOriginalTimeStamp( $pf_out->getTimeStamp() );
			}

			if ( isset( $pf_in ) && $pf_in->getPunchControlID() != false ) { //Get Punch Control ID from above Out punch.
				$pf_out->setPunchControlID( $pf_in->getPunchControlID() );
			} else {
				$pf_out->setPunchControlID( $pf_out->findPunchControlID() );
			}

			$pf_out->setUpdatedBy( $user_id ); //Prevent punches being flagged as tainted.
			$pf_out->setCreatedBy( $user_id ); //Prevent punches being flagged as tainted.

			if ( $pf_out->isValid() ) {
				if ( $pf_out->Save( false ) === false ) {
					Debug::Text( ' aFail Transaction: ', __FILE__, __LINE__, __METHOD__, 10 );
					$fail_transaction = true;
				}
			} else {
				$fail_transaction = true;
			}
		}

		if ( $fail_transaction == false ) {
			if ( isset( $pf_in ) && is_object( $pf_in ) ) {
				Debug::Text( ' Using In Punch Object... TimeStamp: ' . $pf_in->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10 );
				$pf = $pf_in;
			} else if ( isset( $pf_out ) && is_object( $pf_out ) ) {
				Debug::Text( ' Using Out Punch Object... TimeStamp: ' . $pf_out->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10 );
				$pf = $pf_out;
			}

			$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */
			$pcf->setId( $pf->getPunchControlID() );
			$pcf->setPunchObject( $pf );
			$pcf->setBranch( $data['branch_id'] );
			$pcf->setDepartment( $data['department_id'] );
			if ( isset( $data['job_id'] ) ) {
				$pcf->setJob( $data['job_id'] );
			}
			if ( isset( $data['job_item_id'] ) ) {
				$pcf->setJobItem( $data['job_item_id'] );
			}
			if ( isset( $data['quantity'] ) ) {
				$pcf->setQuantity( $data['quantity'] );
			}
			if ( isset( $data['bad_quantity'] ) ) {
				$pcf->setBadQuantity( $data['bad_quantity'] );
			}

			$pcf->setUpdatedBy( $user_id ); //Prevent punches being flagged as tainted.
			$pcf->setCreatedBy( $user_id ); //Prevent punches being flagged as tainted.

			$pcf->setEnableCalcUserDateID( true );
			$pcf->setEnableCalcTotalTime( true );                   //This always needs to be called.
			$pcf->setEnableCalcSystemTotalTime( $calc_total_time ); //This is optional
			//$pcf->setEnableCalcWeeklySystemTotalTime( $calc_total_time );
			$pcf->setEnableCalcUserDateTotal( true );               //This always needs to be called
			$pcf->setEnableCalcException( $calc_total_time );       //This is optional

			if ( $pcf->isValid() == true ) {
				$punch_control_id = $pcf->Save( false, true ); //Force lookup

				if ( isset( $pf_in ) && is_object( $pf_in ) && isset( $pf_out ) && is_object( $pf_out ) ) {
					Debug::Text( ' Using Out Punch Object to save PunchControl for the 2nd time. TimeStamp: ' . $pf_out->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10 );
					$pcf->setPunchObject( $pf_out );
					if ( $pcf->isValid() == true ) {
						$punch_control_id = $pcf->Save( false, true ); //Force lookup
					}
				}

				if ( $fail_transaction == false ) {
					Debug::Text( 'Punch Control ID: ' . $punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
					$pf->CommitTransaction();

					return true;
				}
			}
		}

		Debug::Text( 'Failed Creating Punch Pair!', __FILE__, __LINE__, __METHOD__, 10 );
		$pf->FailTransaction();
		$pf->CommitTransaction();

		return false;
	}

	/**
	 * @param string $user_id                   UUID
	 * @param string $accrual_policy_account_id UUID
	 * @param int $type
	 * @return bool
	 */
	function createAccrualBalance( $user_id, $accrual_policy_account_id, $type = 30 ) {
		$af = TTnew( 'AccrualFactory' ); /** @var AccrualFactory $af */

		$af->setUser( $user_id );
		$af->setType( $type ); //Awarded
		$af->setAccrualPolicyAccount( $accrual_policy_account_id );
		$af->setAmount( rand( ( 3600 * 8 ), ( 3600 * 24 ) ) );
		$af->setTimeStamp( $this->getDate() - ( 86400 * 3 ) );
		$af->setEnableCalcBalance( true );

		if ( $af->isValid() ) {
			$insert_id = $af->Save();
			Debug::Text( 'Accrual ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Accrual Balance!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $report
	 * @param $type
	 * @return bool
	 */
	function createReportCustomColumn( $company_id, $report, $type ) {
		$rcf = TTnew( 'ReportCustomColumnFactory' ); /** @var ReportCustomColumnFactory $rcf */
		$rcf->setCompany( $company_id );
		$rcf->setScript( $report );

		switch ( $report ) {
			case 'UserSummaryReport':
				switch ( $type ) {
					case 10:
						$rcf->setName( 'Years Employed' );
						$rcf->setDescription( 'The number of years the employee has been with the company' );
						$rcf->setType( 20 );
						$rcf->setFormat( 100 );
						$rcf->setFormula( '#hire-time_stamp#' );
						break;
					case 20:
						$rcf->setName( 'Hire Date (<5yrs)' );
						$rcf->setDescription( 'Filter that only displays employees that have been with the company less than 5 years' );
						$rcf->setType( 30 );
						$rcf->setFormat( 100 );
						$rcf->setFormula( 'if((time-#hire-time_stamp#)/31536000 < 5, 1, 0)' );
						break;
					case 100:
						$rcf->setName( 'Employees Age' );
						$rcf->setDescription( 'Employees age' );
						$rcf->setType( 20 );
						$rcf->setFormat( 100 );
						$rcf->setFormula( '#birth-time_stamp#' );
						break;
					case 200:
						$rcf->setName( 'Employees Age (>30 yrs)' );
						$rcf->setDescription( 'Filter that only displays employees that are older than 30 years' );
						$rcf->setType( 30 );
						$rcf->setFormat( 100 );
						$rcf->setFormula( 'if((time-#birth-time_stamp#)/31536000 > 30, 1, 0)' );
						break;
					case 270:
						$rcf->setName( 'New Hires (<30 days)' );
						$rcf->setDescription( 'Only shows the employees hired in the last 30 days' );
						$rcf->setType( 31 );
						$rcf->SetFormat( 100 );
						$rcf->setFormula( 'if( #hire-date_time_stamp# > (time - 86400*30), 1, 0 )' );
						break;
					case 400:
						$rcf->setName( 'Hierarchy (Sales Department)' );
						$rcf->setDescription( 'Only shows employees assigned to a specific hierarchy' );
						$rcf->setType( 31 );
						$rcf->SetFormat( 100 ); // The format of the filter column is insignificant.
						$rcf->setFormula( 'if( string_contains( #hierarchy_control_display#, #Sales Department# ), 1, 0 )' );
						break;
					case 405:
						$rcf->setName( 'Hierarchy (None)' );
						$rcf->setDescription( 'Only shows employees NOT assigned to any hierarchy' );
						$rcf->setType( 31 );
						$rcf->SetFormat( 100 ); // The format of the filter column is insignificant.
						$rcf->setFormula( 'if( string_match( #hierarchy_control_display#, ## ), 1, 0 )' );
						break;
				}
				break;
			case 'TimesheetSummaryReport':
			case 'TimesheetDetailReport':
				switch ( $type ) {
					case 10:
						$rcf->setName( 'Non-Regular Time' );
						$rcf->setDescription( 'Worked Time Subtract Regular Time' );
						$rcf->setType( 20 );
						$rcf->setFormat( 20 );
						$rcf->setFormula( '#worked_time# - #regular_time#' );
						break;
					case 20:
						$rcf->setName( 'Worked Time (>78hrs)' );
						$rcf->setDescription( 'Only show employees who have worked time greater than 78hrs' );
						$rcf->setType( 31 );
						$rcf->setFormat( 20 );
						$rcf->setFormula( 'if(#worked_time# > 78*3600, 1, 0)' );
						break;
					case 200:
						$rcf->setName( 'Scheduled Time Diff. (>2hrs)' );
						$rcf->setDescription( 'Only show employees who scheduled time diff greater than 2hrs' );
						$rcf->setType( 31 );
						$rcf->setFormat( 20 );
						$rcf->setFormula( 'if(#schedule_working_diff# > 2*3600, 1, 0)' );
						break;
				}
				break;
		}

		if ( $rcf->isValid() ) {
			$insert_id = $rcf->Save();
			Debug::Text( 'Report Custom Column ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Report Custom Column!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param string $company_id UUID
	 * @param $type
	 * @param int $status_id
	 * @return bool
	 */
	function createLegalEntity( $company_id, $type, $status_id = 10 ) {
		$lef = TTnew( 'LegalEntityFactory' ); /** @var LegalEntityFactory $lef */
		$lef->setCompany( $company_id );
		$lef->setStatus( $status_id );
		switch ( $type ) {
			case 10: //US
				$lef->setType( 10 );
				$lef->setLegalName( 'ACME USA East Inc.' );
				$lef->setTradeName( 'ACME USA East' );
				$lef->setAddress1( '123 Main St' );
				$lef->setAddress2( 'Unit #123' );
				$lef->setCity( 'New York' );
				$lef->setCountry( 'US' );
				$lef->setProvince( 'NY' );

				$lef->setPostalCode( '12345' );
				$lef->setWorkPhone( '555-555-5555' );
				$lef->setFaxPhone( '' );
				$lef->setStartDate( $this->getDate() - ( 86400 * 365 ) );
				//$lef->setEndDate( $this->getDate() + (86400 * 7) );
				break;
			case 15: //US
				$lef->setType( 10 );
				$lef->setLegalName( 'ACME USA West Inc.' );
				$lef->setTradeName( 'ACME USA West' );
				$lef->setAddress1( '321 Main St' );
				$lef->setAddress2( 'Unit #321' );
				$lef->setCity( 'Seattle' );
				$lef->setCountry( 'US' );
				$lef->setProvince( 'WA' );

				$lef->setPostalCode( '98101' );
				$lef->setWorkPhone( '555-555-5555' );
				$lef->setFaxPhone( '' );
				$lef->setStartDate( $this->getDate() - ( 86400 * 365 ) );
				//$lef->setEndDate( $this->getDate() + (86400 * 7) );
				break;

			case 20: //Canada
				$lef->setType( 10 );
				$lef->setLegalName( 'ACME Canada East Inc.' );
				$lef->setTradeName( 'ACME Canada East' );
				$lef->setAddress1( '456 Main St' );
				$lef->setAddress2( 'Unit #456' );
				$lef->setCity( 'Toronto' );
				$lef->setCountry( 'CA' );
				$lef->setProvince( 'ON' );

				$lef->setPostalCode( 'M5E 1A1' );
				$lef->setWorkPhone( '555-555-5555' );
				$lef->setFaxPhone( '' );
				$lef->setStartDate( $this->getDate() - ( 86400 * 365 ) );
				//$lef->setEndDate( $this->getDate() - (86400 * 7) );
				break;
			case 25: //Canada
				$lef->setType( 10 );
				$lef->setLegalName( 'ACME Canada West Inc.' );
				$lef->setTradeName( 'ACME Canada West' );
				$lef->setAddress1( '654 Main St' );
				$lef->setAddress2( 'Unit #654' );
				$lef->setCity( 'Vancouver' );
				$lef->setCountry( 'CA' );
				$lef->setProvince( 'BC' );

				$lef->setPostalCode( 'V6G 1A1' );
				$lef->setWorkPhone( '555-555-5555' );
				$lef->setFaxPhone( '' );
				$lef->setStartDate( $this->getDate() - ( 86400 * 365 ) );
				//$lef->setEndDate( $this->getDate() - (86400 * 7) );
				break;
		}

		if ( $lef->isValid() ) {
			$insert_id = $lef->Save();
			Debug::Text( 'Legal ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Legal Entity!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param $company_id
	 * @param string $legal_entity_id UUID
	 * @param string $currency_id     UUID
	 * @param $type
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 */
	function createRemittanceSourceAccount( $company_id, $legal_entity_id, $currency_id, $type ) {
		$rsaf = TTnew( 'RemittanceSourceAccountFactory' ); /** @var RemittanceSourceAccountFactory $rsaf */
		$rsaf->setLegalEntity( $legal_entity_id );
		$rsaf->setCompany( $company_id );
		$rsaf->setStatus( 10 ); //Enabled
		$rsaf->setCurrency( $currency_id );
		$rsaf->setLastTransactionNumber( rand( 100, 999 ) );
		switch ( $type ) {
			// First Legal Entity
			case 10:
				$rsaf->setType( 2000 ); //Check
				$rsaf->setCountry( 'US' );
				$rsaf->setName( 'Checks (USD)' . ' - ' . $rsaf->getLegalEntityObject()->getLegalName() );
				$rsaf->setDescription( '' );
				$rsaf->setDataFormat( 10 );
				$rsaf->setValue1( rand( 100, 999 ) );
				$rsaf->setValue2( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue3( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue4( '' );
				$rsaf->setValue5( '' );
				break;
			case 20:
				$rsaf->setType( 3000 ); //US ACH
				$rsaf->setCountry( 'US' );
				$rsaf->setName( 'ACH (USD)' . ' - ' . $rsaf->getLegalEntityObject()->getLegalName() );
				$rsaf->setDescription( '' );
				$rsaf->setDataFormat( 10 );
				$rsaf->setValue1( rand( 100, 999 ) );
				$rsaf->setValue2( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue3( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue4( rand( 100, 999 ) );
				$rsaf->setValue5( rand( 100, 999 ) );
				$rsaf->setValue7( rand( 100, 999 ) );
				break;
			case 30:
				$rsaf->setType( 3000 );
				$rsaf->setCountry( 'CA' );
				$rsaf->setName( 'EFT (CAD)' . ' - ' . $rsaf->getLegalEntityObject()->getLegalName() );
				$rsaf->setDescription( '' );
				$rsaf->setDataFormat( 20 );
				$rsaf->setValue1( rand( 100, 999 ) );
				$rsaf->setValue2( rand( 10000, 99999 ) );
				$rsaf->setValue3( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue4( rand( 100, 999 ) );
				$rsaf->setValue5( rand( 100, 999 ) );
				$rsaf->setValue7( rand( 100, 999 ) );
				break;
			case 100:
			case 110:
			case 120:
				$rsaf->setType( 2000 ); //Check
				$rsaf->setCountry( 'US' );
				$rsaf->setName( 'Checks' . ' - ' . $rsaf->getLegalEntityObject()->getLegalName() .' ['. $type .']' );
				$rsaf->setDescription( '' );
				$rsaf->setDataFormat( 10 );
				$rsaf->setValue1( rand( 100, 999 ) );
				$rsaf->setValue2( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue3( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				$rsaf->setValue4( '' );
				$rsaf->setValue5( '' );
				break;
		}

		if ( $rsaf->isValid() ) {
			$insert_id = $rsaf->Save();
			Debug::Text( 'Remittance Source Account ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Remittance Source Account!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id                      UUID
	 * @param string $currency_id                  UUID
	 * @param string $legal_entity_id              UUID
	 * @param string $remittance_source_account_id UUID
	 * @param int $type_id
	 * @param null $percent_amount
	 * @return bool|int|string
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	function createRemittanceDestinationAccount( $user_id, $currency_id, $legal_entity_id, $remittance_source_account_id, $type_id, $percent_amount = null ) {
		$rdaf = TTnew( 'RemittanceDestinationAccountFactory' ); /** @var RemittanceDestinationAccountFactory $rdaf */

		Debug::Text( 'Creating remittance destination account.', __FILE__, __LINE__, __METHOD__, 10 );
		$rdaf->setUser( $user_id );
		$rdaf->setCurrency( $currency_id );
		$rdaf->setStatus( 10 ); //Enabled
		$rdaf->setRemittanceSourceAccount( $remittance_source_account_id );

		srand( (int)($this->getRandomSeed() + $type_id) );
		$amount_type_id = array_rand( $rdaf->getOptions( 'amount_type' ) ); //10=Percent or 20=Fixed

		$rdaf->setAmountType( $amount_type_id );
		if ( $amount_type_id == 10 ) {
			if ( isset( $percent_amount ) && is_numeric( $percent_amount ) ) {
				$rdaf->setPercentAmount( 100 - $percent_amount );
			} else {
				$percent_amount = rand( 10, 89 );
				$rdaf->setPercentAmount( $percent_amount );
			}
			$rdaf->setAmount( 0 );
			$rdaf->setPriority( rand( 6, 10 ) );
		} else {
			$rdaf->setAmount( rand( 10, 200 ) );
			$rdaf->setPercentAmount( 0 );
			$rdaf->setPriority( rand( 2, 5 ) );
		}

		Debug::Text( 'Type ID: ' . $type_id, __FILE__, __LINE__, __METHOD__, 10 );
		switch ( $type_id ) {
			case 10:
				$rdaf->setType( 2000 );
				$rdaf->setName( 'Checking Account' );
				break;
			case 20:
				$rdaf->setType( 3000 );
				$rdaf->setName( 'Saving Account' );
				$rdaf->setValue1( rand( 100, 999 ) );
				if ( is_object( $rdaf->getRemittanceSourceAccountObject() ) && $rdaf->getRemittanceSourceAccountObject()->getCountry() == 'CA' ) {
					$rdaf->setValue2( rand( 10000, 99999 ) );
				} else {
					$rdaf->setValue2( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				}
				$rdaf->setValue3( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				break;
			case 30:
				$rdaf->setType( 3000 );
				$rdaf->setName( 'Retirement Savings' );
				$rdaf->setValue1( rand( 100, 999 ) );
				if ( is_object( $rdaf->getRemittanceSourceAccountObject() ) && $rdaf->getRemittanceSourceAccountObject()->getCountry() == 'CA' ) {
					$rdaf->setValue2( rand( 10000, 99999 ) );
				} else {
					$rdaf->setValue2( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				}
				$rdaf->setValue3( rand( 100, 999 ) . rand( 100, 999 ) . rand( 100, 999 ) );
				break;
			case 100:
			case 110:
			case 120:
			case 130:
				$rdaf->setType( 2000 );
				$rdaf->setPriority( 1 );
				$rdaf->setAmountType( 10 ); //10=Percent
				$rdaf->setPercentAmount( $percent_amount );
				$rdaf->setName( 'Checking Account ['. $type_id .']' );
				break;
		}

		if ( $rdaf->isValid() ) {
			$insert_id = $rdaf->Save();
			Debug::Text( 'Remittance Source Account ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Remittance Destination Account!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool
	 */
	function generateData() {
		global $current_company, $current_user;

		TTDate::setTimeZone( 'America/Vancouver' );
		TTi18n::setLocale( 'en_US' );

		if ( $this->getRandomSeed() == '' ) {
			$this->setRandomSeed( (int)( $this->getDate() + $this->getUserNamePostfix() ) );
		}

		$current_epoch = $this->getDate();

		//$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
		//$cf->StartTransaction(); //Don't wrap the entire thing in a transaction incase one thing fails not all data is rollbacked.

		$company_id = $this->createCompany();

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getById( $company_id );
		$this->current_company = $current_company = $clf->getCurrent();

		if ( $company_id !== false ) {
			Debug::Text( 'Company Created Successfully!', __FILE__, __LINE__, __METHOD__, 10 );

			$this->createPermissionGroups( $company_id );

			//Create currency
			$currency_ids[] = $this->createCurrency( $company_id, 10 );                                                                                               //USD
			$currency_ids[] = $this->createCurrency( $company_id, 20 );                                                                                               //CAD
			//$currency_ids[] = $this->createCurrency( $company_id, 30 ); //EUR

			//Legal Entity
			$legal_entity_ids[] = $this->createLegalEntity( $company_id, 10 );
			$legal_entity_ids[] = $this->createLegalEntity( $company_id, 15 );
			$legal_entity_ids[] = $this->createLegalEntity( $company_id, 20 );
			$legal_entity_ids[] = $this->createLegalEntity( $company_id, 25 );

			//Create branch
			$branch_ids[] = $this->createBranch( $company_id, 10 );                                                                                                   //NY
			$branch_ids[] = $this->createBranch( $company_id, 20 );                                                                                                   //WA
			$branch_ids[] = $this->createBranch( $company_id, 30 );                                                                                                   //ON
			$branch_ids[] = $this->createBranch( $company_id, 40 );                                                                                                   //BC

			//Break coordinates up into different buckets based on physical location.
			$coordinates['ALL'] = $this->coordinates;
			$coordinates['NY'] = (array)array_slice( $this->coordinates, 0, 10 );
			$coordinates['WA'] = (array)array_slice( $this->coordinates, 10, 12 );
			$coordinates['00'] = (array)array_slice( $this->coordinates, 22 );

			//Create departments
			$department_ids[] = $this->createDepartment( $company_id, 10 );
			$department_ids[] = $this->createDepartment( $company_id, 20 );
			$department_ids[] = $this->createDepartment( $company_id, 30 );
			$department_ids[] = $this->createDepartment( $company_id, 40 );

			//Create stations
			$this->createStation( $company_id );

			//Create pay stub accounts.
			$this->createPayStubAccount( $company_id );

			//Link pay stub accounts.
			$this->createPayStubAccountLink( $company_id );

			//Wage Groups
			$wage_group_ids[] = $this->createUserWageGroups( $company_id );

			//User Groups
			$user_group_ids[] = $this->createUserGroup( $company_id, 10, 0 );
			$user_group_ids[] = $this->createUserGroup( $company_id, 20, $user_group_ids[0] );
			$user_group_ids[] = $this->createUserGroup( $company_id, 30, $user_group_ids[0] );
			$user_group_ids[] = $this->createUserGroup( $company_id, 40, 0 );
			$user_group_ids[] = $this->createUserGroup( $company_id, 50, $user_group_ids[3] );


			//User Title
			$user_title_ids[] = $this->createUserTitle( $company_id, 10 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 20 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 30 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 40 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 50 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 60 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 70 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 80 );
			$user_title_ids[] = $this->createUserTitle( $company_id, 90 );

			//Ethnic Group
			$ethnic_group_ids[] = $this->createEthnicGroup( $company_id, 10 );
			$ethnic_group_ids[] = $this->createEthnicGroup( $company_id, 20 );
			$ethnic_group_ids[] = $this->createEthnicGroup( $company_id, 30 );
			$ethnic_group_ids[] = $this->createEthnicGroup( $company_id, 40 );
			$ethnic_group_ids[] = $this->createEthnicGroup( $company_id, 50 );

			$this->createUserDefaults( $company_id, $legal_entity_ids[0] );

			//Remittance Source Account - US
			$remittance_source_account_ids[$legal_entity_ids[0]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[0], $currency_ids[0], 10 ); // Type=10 (Checking)
			$remittance_source_account_ids[$legal_entity_ids[0]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[0], $currency_ids[0], 20 ); // Type=20
			$remittance_source_account_ids[$legal_entity_ids[0]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[0], $currency_ids[1], 30 ); // Type=30
			$remittance_source_account_ids[$legal_entity_ids[1]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[1], $currency_ids[0], 10 ); // Type=10 (Checking)
			$remittance_source_account_ids[$legal_entity_ids[1]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[1], $currency_ids[0], 20 ); // Type=20
			$remittance_source_account_ids[$legal_entity_ids[1]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[1], $currency_ids[1], 30 ); // Type=30

			//Remittance Source Account - CA
			$remittance_source_account_ids[$legal_entity_ids[2]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[2], $currency_ids[1], 10 ); // Type=10 (Checking)
			$remittance_source_account_ids[$legal_entity_ids[2]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[2], $currency_ids[1], 20 ); // Type=20
			$remittance_source_account_ids[$legal_entity_ids[2]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[2], $currency_ids[0], 30 ); // Type=30
			$remittance_source_account_ids[$legal_entity_ids[3]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[3], $currency_ids[1], 10 ); // Type=10 (Checking)
			$remittance_source_account_ids[$legal_entity_ids[3]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[3], $currency_ids[1], 20 ); // Type=20
			$remittance_source_account_ids[$legal_entity_ids[3]][] = $this->createRemittanceSourceAccount( $company_id, $legal_entity_ids[3], $currency_ids[0], 30 ); // Type=30

			//Administrator User - Do this first so they can be used in SetupPresets
			$current_user_id = $user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 100, 0, $branch_ids[0], $department_ids[0], $currency_ids[0], $user_group_ids[4], $user_title_ids[0], $ethnic_group_ids, $remittance_source_account_ids );
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $current_user_id );
			$current_user = $ulf->getCurrent();
			if ( $current_user_id === false ) {
				Debug::Text( 'Administrator user wasn\'t created! Duplicate username perhaps? Are we appending a random number?', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
			unset( $current_user_id );

			//Create Recurring Holidays, MUST GO BEFORE createPayrollRemittanceAgency()
			$this->createRecurringHolidays( $company_id, $current_user->getId() );

			//Create payroll remittance agency. MUST GO BEFORE createCompanyDeduction()
			$this->createPayrollRemittanceAgency( $company_id, $current_user->getId(), $legal_entity_ids[0] );
			$this->createPayrollRemittanceAgency( $company_id, $current_user->getId(), $legal_entity_ids[1] );
			$this->createPayrollRemittanceAgency( $company_id, $current_user->getId(), $legal_entity_ids[2], 'CA' );
			$this->createPayrollRemittanceAgency( $company_id, $current_user->getId(), $legal_entity_ids[3], 'CA' );

			//Company Deductions. MUST GO AFTER createPayrollRemittanceAgency() and createUser()
			$this->createCompanyDeduction( $company_id, $current_user->getId(), $legal_entity_ids[0] );
			$this->createCompanyDeduction( $company_id, $current_user->getId(), $legal_entity_ids[1] );
			$this->createCompanyDeduction( $company_id, $current_user->getId(), $legal_entity_ids[2] );
			$this->createCompanyDeduction( $company_id, $current_user->getId(), $legal_entity_ids[3] );

			//Users
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 10, 0, $branch_ids[0], $department_ids[0], $currency_ids[0], $user_group_ids[0], $user_title_ids[0], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 11, 0, $branch_ids[0], $department_ids[1], $currency_ids[0], $user_group_ids[0], $user_title_ids[0], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 12, 0, $branch_ids[0], $department_ids[1], $currency_ids[0], $user_group_ids[0], $user_title_ids[0], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 13, 0, $branch_ids[0], $department_ids[1], $currency_ids[0], $user_group_ids[0], $user_title_ids[0], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 14, 0, $branch_ids[0], $department_ids[1], $currency_ids[0], $user_group_ids[1], $user_title_ids[1], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 15, 0, $branch_ids[0], $department_ids[0], $currency_ids[0], $user_group_ids[1], $user_title_ids[1], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 16, 0, $branch_ids[0], $department_ids[1], $currency_ids[0], $user_group_ids[1], $user_title_ids[1], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 17, 0, $branch_ids[0], $department_ids[1], $currency_ids[0], $user_group_ids[1], $user_title_ids[1], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 18, 0, $branch_ids[0], $department_ids[0], $currency_ids[0], $user_group_ids[2], $user_title_ids[2], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[0], 19, 0, $branch_ids[0], $department_ids[1], $currency_ids[1], $user_group_ids[2], $user_title_ids[2], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['NY'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[1], 20, 0, $branch_ids[1], $department_ids[1], $currency_ids[1], $user_group_ids[2], $user_title_ids[2], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['WA'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[1], 21, 0, $branch_ids[1], $department_ids[1], $currency_ids[0], $user_group_ids[3], $user_title_ids[3], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['WA'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[1], 22, 0, $branch_ids[1], $department_ids[1], $currency_ids[0], $user_group_ids[3], $user_title_ids[3], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['WA'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[1], 23, 0, $branch_ids[1], $department_ids[2], $currency_ids[0], $user_group_ids[3], $user_title_ids[3], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['WA'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[2], 24, 0, $branch_ids[2], $department_ids[2], $currency_ids[1], $user_group_ids[3], $user_title_ids[3], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[2], 25, 0, $branch_ids[2], $department_ids[2], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[2], 26, 0, $branch_ids[2], $department_ids[1], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[2], 27, 0, $branch_ids[2], $department_ids[3], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[3], 28, 0, $branch_ids[3], $department_ids[3], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[3], 29, 0, $branch_ids[3], $department_ids[3], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[3], 30, 0, $branch_ids[3], $department_ids[0], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );
			$user_ids[] = $this->createUser( $company_id, $legal_entity_ids[3], 40, 0, $branch_ids[3], $department_ids[0], $currency_ids[1], $user_group_ids[4], $user_title_ids[4], $ethnic_group_ids, $remittance_source_account_ids, $coordinates['00'] );

			//Create random users.
			Debug::Text( 'Creating random users: ' . $this->getMaxRandomUsers(), __FILE__, __LINE__, __METHOD__, 10 );
			for ( $i = 0; $i <= $this->getMaxRandomUsers(); $i++ ) {
				$tmp_user_id = $this->createUser( $company_id, $legal_entity_ids[0], 999, 0, $branch_ids[( $i % 2 )], $department_ids[( $i % 4 )], $currency_ids[0], $user_group_ids[( $i % 5 )], $user_title_ids[( $i % 9 )], $ethnic_group_ids, $remittance_source_account_ids );
				if ( $tmp_user_id != false ) {
					$user_ids[] = $tmp_user_id;
					$tmp_user_ids[] = $tmp_user_id;
				}
			}
			unset( $i, $tmp_user_id );


			//Create policies
			$policy_ids['round'][] = $this->createRoundingPolicy( $company_id, 10 );                                                                                  //In
			$policy_ids['round'][] = $this->createRoundingPolicy( $company_id, 20 );                                                                                  //Out

			$policy_ids['accrual_account'][] = $this->createAccrualPolicyAccount( $company_id, 10 ); //Bank Time
			$policy_ids['accrual_account'][] = $this->createAccrualPolicyAccount( $company_id, 20 ); //Vacaction
			$policy_ids['accrual_account'][] = $this->createAccrualPolicyAccount( $company_id, 30 ); //Sick

			$policy_ids['accrual'][] = $this->createAccrualPolicy( $company_id, 20, $policy_ids['accrual_account'][1] ); //Vacaction
			$policy_ids['accrual'][] = $this->createAccrualPolicy( $company_id, 30, $policy_ids['accrual_account'][2] ); //Sick

			$policy_ids['pay_formula_policy'][100] = $this->createPayFormulaPolicy( $company_id, 100 );                                    //Regular
			$policy_ids['pay_formula_policy'][110] = $this->createPayFormulaPolicy( $company_id, 110, $policy_ids['accrual_account'][0] ); //Bank
			$policy_ids['pay_formula_policy'][120] = $this->createPayFormulaPolicy( $company_id, 120, $policy_ids['accrual_account'][1] ); //Vacation
			$policy_ids['pay_formula_policy'][130] = $this->createPayFormulaPolicy( $company_id, 130, $policy_ids['accrual_account'][2] ); //Sick
			$policy_ids['pay_formula_policy'][200] = $this->createPayFormulaPolicy( $company_id, 200 );                                    //OT1.5
			$policy_ids['pay_formula_policy'][210] = $this->createPayFormulaPolicy( $company_id, 210, $policy_ids['accrual_account'][0] ); //OT2.0
			$policy_ids['pay_formula_policy'][300] = $this->createPayFormulaPolicy( $company_id, 300 );                                    //Prem1
			$policy_ids['pay_formula_policy'][310] = $this->createPayFormulaPolicy( $company_id, 310 );                                    //Prem2

			$policy_ids['pay_code'][100] = $this->createPayCode( $company_id, 100, $policy_ids['pay_formula_policy'][100] ); //Regular
			$policy_ids['pay_code'][190] = $this->createPayCode( $company_id, 190, $policy_ids['pay_formula_policy'][100] ); //Lunch
			$policy_ids['pay_code'][192] = $this->createPayCode( $company_id, 192, $policy_ids['pay_formula_policy'][100] ); //Break
			$policy_ids['pay_code'][200] = $this->createPayCode( $company_id, 200, $policy_ids['pay_formula_policy'][200] ); //OT1
			$policy_ids['pay_code'][210] = $this->createPayCode( $company_id, 210, $policy_ids['pay_formula_policy'][210] ); //OT2
			$policy_ids['pay_code'][300] = $this->createPayCode( $company_id, 300, $policy_ids['pay_formula_policy'][300] ); //Prem1
			$policy_ids['pay_code'][310] = $this->createPayCode( $company_id, 310, $policy_ids['pay_formula_policy'][310] ); //Prem2
			$policy_ids['pay_code'][910] = $this->createPayCode( $company_id, 910, $policy_ids['pay_formula_policy'][110] ); //Bank
			$policy_ids['pay_code'][900] = $this->createPayCode( $company_id, 900, $policy_ids['pay_formula_policy'][120] ); //Vacation
			$policy_ids['pay_code'][920] = $this->createPayCode( $company_id, 920, $policy_ids['pay_formula_policy'][130] ); //Sick

			$policy_ids['contributing_pay_code_policy'][10] = $this->createContributingPayCodePolicy( $company_id, 10, [ $policy_ids['pay_code'][100] ] );                                                                                                                         //Regular
			$policy_ids['contributing_pay_code_policy'][12] = $this->createContributingPayCodePolicy( $company_id, 12, [ $policy_ids['pay_code'][100], $policy_ids['pay_code'][190], $policy_ids['pay_code'][192] ] );                                                             //Regular+Meal/Break
			$policy_ids['contributing_pay_code_policy'][14] = $this->createContributingPayCodePolicy( $company_id, 14, [ $policy_ids['pay_code'][100], $policy_ids['pay_code'][190], $policy_ids['pay_code'][192], $policy_ids['pay_code'][900] ] );                               //Regular+Meal/Break+Absence
			$policy_ids['contributing_pay_code_policy'][20] = $this->createContributingPayCodePolicy( $company_id, 20, [ $policy_ids['pay_code'][100], $policy_ids['pay_code'][200], $policy_ids['pay_code'][210], $policy_ids['pay_code'][190], $policy_ids['pay_code'][192] ] ); //Regular+OT+Meal/Break
			$policy_ids['contributing_pay_code_policy'][90] = $this->createContributingPayCodePolicy( $company_id, 90, [ $policy_ids['pay_code'][900] ] );                                                                                                                         //Absence
			$policy_ids['contributing_pay_code_policy'][99] = $this->createContributingPayCodePolicy( $company_id, 99, $policy_ids['pay_code'] );                                                                                                                                  //All Time

			$policy_ids['contributing_shift_policy'][10] = $this->createContributingShiftPolicy( $company_id, 10, $policy_ids['contributing_pay_code_policy'][10] ); //Regular
			$policy_ids['contributing_shift_policy'][20] = $this->createContributingShiftPolicy( $company_id, 20, $policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
			$policy_ids['contributing_shift_policy'][30] = $this->createContributingShiftPolicy( $company_id, 30, $policy_ids['contributing_pay_code_policy'][20] ); //Regular+OT+Meal/Break

			$policy_ids['regular'][] = $this->createRegularTimePolicy( $company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][100] );

			$policy_ids['overtime'][] = $this->createOverTimePolicy( $company_id, 10, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][200] );
			$policy_ids['overtime'][] = $this->createOverTimePolicy( $company_id, 20, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][210] );
			$policy_ids['overtime'][] = $this->createOverTimePolicy( $company_id, 30, $policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][200] );

			if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
				$policy_ids['expense_tax'][] = $this->createExpensePolicy( $company_id, 100 ); // Tax(Percent) - HST
				$policy_ids['expense_tax'][] = $this->createExpensePolicy( $company_id, 110 ); // Tax(Percent) - VAT
				$policy_ids['expense_tax'][] = $this->createExpensePolicy( $company_id, 120 ); // Tax(Flat Amount) - Improvement Fee

				$policy_ids['expense'][] = $this->createExpensePolicy( $company_id, 10, [ $policy_ids['expense_tax'][1] ] );                                // Flat Amount
				$policy_ids['expense'][] = $this->createExpensePolicy( $company_id, 20, [ $policy_ids['expense_tax'][0], $policy_ids['expense_tax'][1] ] ); // Percent
				$policy_ids['expense'][] = $this->createExpensePolicy( $company_id, 30 );                                                                   // Per Unit - No Tax
				$policy_ids['expense'][] = $this->createExpensePolicy( $company_id, 40, [ $policy_ids['expense_tax'][0], $policy_ids['expense_tax'][2] ] ); // Flat Amount
			} else {
				$policy_ids['expense'] = [];
			}

			$policy_ids['premium'][] = $this->createPremiumPolicy( $company_id, 10, $policy_ids['contributing_shift_policy'][30], $policy_ids['pay_code'][300] );
			$policy_ids['premium'][] = $this->createPremiumPolicy( $company_id, 20, $policy_ids['contributing_shift_policy'][30], $policy_ids['pay_code'][310] );

			$policy_ids['absence'][] = $this->createAbsencePolicy( $company_id, 10, $policy_ids['pay_code'][900] ); //Vacation
			$policy_ids['absence'][] = $this->createAbsencePolicy( $company_id, 20, $policy_ids['pay_code'][910] ); //Bank
			$policy_ids['absence'][] = $this->createAbsencePolicy( $company_id, 30, $policy_ids['pay_code'][920] ); //Sick

			$policy_ids['meal_1'] = $this->createMealPolicy( $company_id, $policy_ids['pay_code'][190] );

			$policy_ids['schedule_1'] = $this->createSchedulePolicy( $company_id, $policy_ids['meal_1'] );

			$policy_ids['exception_1'] = $this->createExceptionPolicy( $company_id );

			//Create tax forms after absence policies and pay stub accounts
			$this->createTaxForms( $company_id, $current_user->getId() );

			$hierarchy_user_ids = $user_ids;
			$root_user_id = array_pop( $hierarchy_user_ids );
			//unset($hierarchy_user_ids[0], $hierarchy_user_ids[1] );  //Have supervisors be subordinates for themselves too.

			//Create authorization hierarchy
			$hierarchy_control_id = $this->createAuthorizationHierarchyControl( $company_id, $hierarchy_user_ids );

			if ( $root_user_id == false ) {
				Debug::Text( 'Administrator wasn\'t created! Duplicate username perhaps? Are we appending a random number?', __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}

			//Admin user at the top
			$this->createAuthorizationHierarchyLevel( $company_id, $hierarchy_control_id, $current_user->getID(), 1 );
			$this->createAuthorizationHierarchyLevel( $company_id, $hierarchy_control_id, $user_ids[0], 2 );
			$this->createAuthorizationHierarchyLevel( $company_id, $hierarchy_control_id, $user_ids[1], 3 );
			$superior_user_ids = [ $root_user_id, $user_ids[0], $user_ids[1] ]; //Create an array of supervisor user_ids in level order so we can use them in createAuthorizations() lower down.
			//unset($hierarchy_user_ids, $root_user_id); //Keep $hierarchy_user_ids so they can be used further down to help with createRequests()

			//Pay Period Schedule
			$this->createPayPeriodSchedule( $company_id, $user_ids );

			//Create Policy Group
			$this->createPolicyGroup( $company_id,
									  $policy_ids['meal_1'],
									  $policy_ids['exception_1'],
									  null,
									  $policy_ids['overtime'],
									  $policy_ids['premium'],
									  $policy_ids['round'],
									  $user_ids,
									  null,
									  $policy_ids['accrual'],
									  $policy_ids['expense'],
									  $policy_ids['absence'],
									  $policy_ids['regular']
			);
			Debug::Text( ' a.Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

			//Create Accrual balances
			foreach ( $user_ids as $user_id ) {
				foreach ( $policy_ids['accrual_account'] as $accrual_policy_account_id ) {
					$this->createAccrualBalance( $user_id, $accrual_policy_account_id );
				}
				unset( $accrual_policy_account_id );
			}

			if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 10 );
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 20 );
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 100 );
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 200 );
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 270 );
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 400 );
				$this->createReportCustomColumn( $company_id, 'UserSummaryReport', 405 );

				$this->createReportCustomColumn( $company_id, 'TimesheetSummaryReport', 10 );
				$this->createReportCustomColumn( $company_id, 'TimesheetSummaryReport', 20 );
				$this->createReportCustomColumn( $company_id, 'TimesheetSummaryReport', 200 );

				$this->createReportCustomColumn( $company_id, 'TimesheetDetailReport', 10 );
				$this->createReportCustomColumn( $company_id, 'TimesheetDetailReport', 20 );
				$this->createReportCustomColumn( $company_id, 'TimesheetDetailReport', 200 );
			}


			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE && isset( $this->create_data['invoice'] ) && $this->create_data['invoice'] == true ) {
				//Client Groups
				$client_group_ids[] = $this->createClientGroup( $company_id, 10, 0 );
				$client_group_ids[] = $this->createClientGroup( $company_id, 20, $client_group_ids[0] );
				$client_group_ids[] = $this->createClientGroup( $company_id, 30, $client_group_ids[0] );
				$client_group_ids[] = $this->createClientGroup( $company_id, 40, 0 );
				$client_group_ids[] = $this->createClientGroup( $company_id, 50, $client_group_ids[3] );


				$product_group_ids[] = $this->createProductGroup( $company_id, 10, 0 );
				$product_group_ids[] = $this->createProductGroup( $company_id, 20, 0 );
				$product_group_ids[] = $this->createProductGroup( $company_id, 30, $product_group_ids[1] );
				$product_group_ids[] = $this->createProductGroup( $company_id, 40, $product_group_ids[2] );
				$product_group_ids[] = $this->createProductGroup( $company_id, 50, $product_group_ids[1] );


				//Create at least 10 Clients
				$client_ids[] = $this->createClient( $company_id, 10, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 20, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 30, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 40, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 50, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 60, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 70, $user_ids, $client_group_ids );
				$client_ids[] = $this->createClient( $company_id, 80, $user_ids, $client_group_ids );


				//Create Invoice District
				$invoice_district_ids[] = $this->createInvoiceDistrict( $company_id, 10 );//US
				$invoice_district_ids[] = $this->createInvoiceDistrict( $company_id, 20 );//US
				$invoice_district_ids[] = $this->createInvoiceDistrict( $company_id, 30 );//CA
				$invoice_district_ids[] = $this->createInvoiceDistrict( $company_id, 40 );//CA


				//Create Client Contact, Each client should have at least two client contacts.
				foreach ( $client_ids as $client_id ) {
					$client_contact_ids[] = $this->createClientContact( $client_id, 10, $invoice_district_ids, $currency_ids[0] );
					$client_contact_ids[] = $this->createClientContact( $client_id, 20, $invoice_district_ids, $currency_ids[0] );
				}

				$product_ids[10][] = $this->createProduct( $company_id, $product_group_ids, 10, $currency_ids[0] );
				$product_ids[10][] = $this->createProduct( $company_id, $product_group_ids, 20, $currency_ids[0] );
				$product_ids[10][] = $this->createProduct( $company_id, $product_group_ids, 30, $currency_ids[0] );
				$product_ids[10][] = $this->createProduct( $company_id, $product_group_ids, 40, $currency_ids[0] );
				$product_ids[10][] = $this->createProduct( $company_id, $product_group_ids, 50, $currency_ids[0] );
				$product_ids[20][] = $this->createProduct( $company_id, $product_group_ids, 60, $currency_ids[0] );
				$product_ids[20][] = $this->createProduct( $company_id, $product_group_ids, 62, $currency_ids[0] );
				$product_ids[20][] = $this->createProduct( $company_id, $product_group_ids, 64, $currency_ids[0] );
				$product_ids[20][] = $this->createProduct( $company_id, $product_group_ids, 66, $currency_ids[0] );
				$product_ids[20][] = $this->createProduct( $company_id, $product_group_ids, 69, $currency_ids[0] );
				$product_ids[30][] = $this->createProduct( $company_id, $product_group_ids, 70, $currency_ids[0] );
				$product_ids[40][] = $this->createProduct( $company_id, $product_group_ids, 80, $currency_ids[0] );
				$product_ids[50][] = $this->createProduct( $company_id, $product_group_ids, 90, $currency_ids[0] );

				$area_policy_ids[] = $this->createAreaPolicy( $company_id, 20, [ $invoice_district_ids[0], $invoice_district_ids[1] ] );
				$area_policy_ids[] = $this->createAreaPolicy( $company_id, 10, [ $invoice_district_ids[2], $invoice_district_ids[3] ] );

				$tax_policy_ids[] = $this->createTaxPolicy( $company_id, $product_ids[30][0], $area_policy_ids );

				//Create Shipping Policy
				$shipping_policy_ids[] = $this->createShippingPolicy( $company_id, $product_ids[40][0], 10, $currency_ids[0], $area_policy_ids );
				$shipping_policy_ids[] = $this->createShippingPolicy( $company_id, $product_ids[40][0], 20, $currency_ids[0], $area_policy_ids );


				//Create Invoice
				$x = 0;
				foreach ( $client_ids as $client_id ) {
					srand( (int)($this->getRandomSeed() + $x + 0) ); //Different seed for each invoice
					$invoice_ids[] = $this->createInvoice( $company_id, $client_id, $currency_ids[0], $product_ids[20][0], 100, [], $user_ids, $shipping_policy_ids );

					srand( (int)($this->getRandomSeed() + $x + 1) ); //Different seed for each invoice
					$invoice_ids[] = $this->createInvoice( $company_id, $client_id, $currency_ids[0], [ $product_ids[10][0], $product_ids[10][1], $product_ids[10][2], $product_ids[10][3], $product_ids[10][4], $product_ids[20][0] ], 40, null, $user_ids, $shipping_policy_ids );

					srand( (int)($this->getRandomSeed() + $x + 2) ); //Different seed for each invoice
					$invoice_ids[] = $this->createInvoice( $company_id, $client_id, $currency_ids[0], [ $product_ids[10][1], $product_ids[10][2], $product_ids[10][3] ], 40, null, $user_ids, $shipping_policy_ids );

					srand( (int)($this->getRandomSeed() + $x + 3) ); //Different seed for each invoice
					$invoice_ids[] = $this->createInvoice( $company_id, $client_id, $currency_ids[0], [ $product_ids[10][0], $product_ids[10][4], $product_ids[20][0] ], 100, [], $user_ids, $shipping_policy_ids );

					srand( (int)($this->getRandomSeed() + $x + 4) ); //Different seed for each invoice
					$invoice_ids[] = $this->createInvoice( $company_id, $client_id, $currency_ids[0], [ $product_ids[10][3], $product_ids[10][4] ], 100, [], $user_ids, $shipping_policy_ids );

					$x++;
				}
			} else {
				Debug::Text( 'NOTICE: Skipping invoices...', __FILE__, __LINE__, __METHOD__, 10 );
				$client_group_ids[] = TTUUID::getZeroID();
				$product_group_ids[] = TTUUID::getZeroID();
				$client_ids = array_fill( 0, 10, TTUUID::getZeroID() );
				$invoice_district_ids[] = TTUUID::getZeroID();
				$client_contact_ids[] = TTUUID::getZeroID();
				$product_ids[10] = array_fill( 0, 5, TTUUID::getZeroID() );
				$product_ids[20] = array_fill( 0, 5, TTUUID::getZeroID() );
				$product_ids[30] = array_fill( 0, 5, TTUUID::getZeroID() );
				$product_ids[40] = array_fill( 0, 5, TTUUID::getZeroID() );
				$product_ids[50] = array_fill( 0, 5, TTUUID::getZeroID() );
				$area_policy_ids[] = TTUUID::getZeroID();
				$tax_policy_ids[] = TTUUID::getZeroID();
				$shipping_policy_ids[] = TTUUID::getZeroID();
				$invoice_ids[] = TTUUID::getZeroID();
			}

			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				//Task Groups
				$task_group_ids[] = $this->createTaskGroup( $company_id, 10, 0 );
				$task_group_ids[] = $this->createTaskGroup( $company_id, 20, $task_group_ids[0] );
				$task_group_ids[] = $this->createTaskGroup( $company_id, 30, $task_group_ids[0] );
				$task_group_ids[] = $this->createTaskGroup( $company_id, 40, 0 );
				$task_group_ids[] = $this->createTaskGroup( $company_id, 50, $task_group_ids[3] );
				$task_group_ids[] = $this->createTaskGroup( $company_id, 60, $task_group_ids[3] );

				//Job Tasks
				$default_task_id = $this->createTask( $company_id, 10, $task_group_ids[2], $product_ids[20][0] );
				$task_ids[] = $this->createTask( $company_id, 20, $task_group_ids[1], $product_ids[20][1] );
				$task_ids[] = $this->createTask( $company_id, 30, $task_group_ids[1], $product_ids[20][2] );
				$task_ids[] = $this->createTask( $company_id, 40, $task_group_ids[2], $product_ids[20][3] );

				$task_ids[] = $this->createTask( $company_id, 50, $task_group_ids[4], $product_ids[20][4] );
				$task_ids[] = $this->createTask( $company_id, 60, $task_group_ids[4], $product_ids[20][4] );
				$task_ids[] = $this->createTask( $company_id, 70, $task_group_ids[5], $product_ids[20][4] );

				//Job Groups
				$job_group_ids[] = $this->createJobGroup( $company_id, 10, 0 );
				$job_group_ids[] = $this->createJobGroup( $company_id, 20, $job_group_ids[0] );
				$job_group_ids[] = $this->createJobGroup( $company_id, 30, $job_group_ids[0] );
				$job_group_ids[] = $this->createJobGroup( $company_id, 40, 0 );
				$job_group_ids[] = $this->createJobGroup( $company_id, 50, $job_group_ids[3] );
				$job_group_ids[] = $this->createJobGroup( $company_id, 60, $job_group_ids[3] );

				// GEO FENCE
				$geo_fence_ids[] = $this->createGEOFence( $company_id, 10 ); // New York
				$geo_fence_ids[] = $this->createGEOFence( $company_id, 20 );
				$geo_fence_ids[] = $this->createGEOFence( $company_id, 30 );

				$geo_fence_ids[] = $this->createGEOFence( $company_id, 40 );                                                                                                                                            // Seattle
				$geo_fence_ids[] = $this->createGEOFence( $company_id, 50 );
				$geo_fence_ids[] = $this->createGEOFence( $company_id, 60 );

				//Jobs
				$job_ids[] = $this->createJob( $company_id, 10, $default_task_id, $job_group_ids[1], $branch_ids[0], $department_ids[0], $client_ids[0], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] ); // new york
				$job_ids[] = $this->createJob( $company_id, 11, $default_task_id, $job_group_ids[1], $branch_ids[0], $department_ids[0], $client_ids[1], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );
				$job_ids[] = $this->createJob( $company_id, 12, $default_task_id, $job_group_ids[1], $branch_ids[0], $department_ids[0], $client_ids[2], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );
				$job_ids[] = $this->createJob( $company_id, 13, $default_task_id, $job_group_ids[1], $branch_ids[0], $department_ids[0], $client_ids[3], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );
				$job_ids[] = $this->createJob( $company_id, 14, $default_task_id, $job_group_ids[1], $branch_ids[0], $department_ids[0], $client_ids[4], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );
				$job_ids[] = $this->createJob( $company_id, 20, $default_task_id, $job_group_ids[4], $branch_ids[0], $department_ids[0], $client_ids[2], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );
				$job_ids[] = $this->createJob( $company_id, 21, $default_task_id, $job_group_ids[4], $branch_ids[0], $department_ids[0], $client_ids[3], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );
				$job_ids[] = $this->createJob( $company_id, 22, $default_task_id, $job_group_ids[4], $branch_ids[0], $department_ids[0], $client_ids[4], [ $geo_fence_ids[0], $geo_fence_ids[1], $geo_fence_ids[2] ] );

				$job_ids[] = $this->createJob( $company_id, 15, $default_task_id, $job_group_ids[2], $branch_ids[1], $department_ids[1], $client_ids[5], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] ); // seattle
				$job_ids[] = $this->createJob( $company_id, 16, $default_task_id, $job_group_ids[2], $branch_ids[1], $department_ids[1], $client_ids[6], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
				$job_ids[] = $this->createJob( $company_id, 17, $default_task_id, $job_group_ids[2], $branch_ids[1], $department_ids[1], $client_ids[7], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
				$job_ids[] = $this->createJob( $company_id, 18, $default_task_id, $job_group_ids[2], $branch_ids[1], $department_ids[1], $client_ids[0], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
				$job_ids[] = $this->createJob( $company_id, 19, $default_task_id, $job_group_ids[2], $branch_ids[1], $department_ids[1], $client_ids[1], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
				$job_ids[] = $this->createJob( $company_id, 23, $default_task_id, $job_group_ids[5], $branch_ids[1], $department_ids[1], $client_ids[5], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
				$job_ids[] = $this->createJob( $company_id, 24, $default_task_id, $job_group_ids[5], $branch_ids[1], $department_ids[1], $client_ids[6], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
				$job_ids[] = $this->createJob( $company_id, 25, $default_task_id, $job_group_ids[5], $branch_ids[1], $department_ids[1], $client_ids[7], [ $geo_fence_ids[3], $geo_fence_ids[4], $geo_fence_ids[5] ] );
			} else {
				$task_ids[] = TTUUID::getZeroID();
				$job_ids[] = TTUUID::getZeroID();
			}

			if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
				if ( isset( $this->create_data['expense'] ) && $this->create_data['expense'] == true ) {
					$user_expense_ids[] = $this->createUserExpense( $user_ids[2], $policy_ids['expense'][0], $branch_ids[0], $department_ids[1], $currency_ids[0], $job_ids[1], $task_ids[1] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[3], $policy_ids['expense'][0], $branch_ids[0], $department_ids[2], $currency_ids[1], $job_ids[1], $task_ids[0] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[4], $policy_ids['expense'][0], $branch_ids[0], $department_ids[0], $currency_ids[0], $job_ids[1], $task_ids[2] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[5], $policy_ids['expense'][0], $branch_ids[0], $department_ids[0], $currency_ids[0], $job_ids[2], $task_ids[2] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[6], $policy_ids['expense'][1], $branch_ids[0], $department_ids[0], $currency_ids[0], $job_ids[2], $task_ids[2] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[7], $policy_ids['expense'][1], $branch_ids[0], $department_ids[0], $currency_ids[0], $job_ids[2], $task_ids[2] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[8], $policy_ids['expense'][1], $branch_ids[0], $department_ids[0], $currency_ids[0], $job_ids[3], $task_ids[1] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[9], $policy_ids['expense'][1], $branch_ids[1], $department_ids[0], $currency_ids[0], $job_ids[4], $task_ids[1] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[10], $policy_ids['expense'][2], $branch_ids[1], $department_ids[1], $currency_ids[0], $job_ids[4], $task_ids[1] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[11], $policy_ids['expense'][2], $branch_ids[1], $department_ids[1], $currency_ids[0], $job_ids[5], $task_ids[0] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[12], $policy_ids['expense'][2], $branch_ids[1], $department_ids[1], $currency_ids[0], $job_ids[6], $task_ids[0] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[13], $policy_ids['expense'][2], $branch_ids[1], $department_ids[2], $currency_ids[0], $job_ids[7], $task_ids[0] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[14], $policy_ids['expense'][2], $branch_ids[1], $department_ids[2], $currency_ids[0], $job_ids[7], $task_ids[4] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[15], $policy_ids['expense'][3], $branch_ids[1], $department_ids[2], $currency_ids[0], $job_ids[8], $task_ids[4] );

					$user_expense_ids[] = $this->createUserExpense( $user_ids[16], $policy_ids['expense'][3], $branch_ids[1], $department_ids[3], $currency_ids[0], $job_ids[8], $task_ids[4] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[17], $policy_ids['expense'][3], $branch_ids[1], $department_ids[3], $currency_ids[0], $job_ids[9], $task_ids[1] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[18], $policy_ids['expense'][3], $branch_ids[1], $department_ids[3], $currency_ids[0], $job_ids[10], $task_ids[3] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[19], $policy_ids['expense'][3], $branch_ids[1], $department_ids[3], $currency_ids[0], $job_ids[0], $task_ids[3] );
					$user_expense_ids[] = $this->createUserExpense( $user_ids[20], $policy_ids['expense'][3], $branch_ids[1], $department_ids[3], $currency_ids[0], $job_ids[0], $task_ids[3] );

					//Authorize random expenses.
					foreach ( $user_expense_ids as $user_expense_id ) {
						if ( rand( 0, 99 ) < 85 ) { //85% chance
							$this->createAuthorization( 200, $user_expense_id, $superior_user_ids[2], true );
							if ( rand( 0, 99 ) < 85 ) { //85% chance
								$this->createAuthorization( 200, $user_expense_id, $superior_user_ids[1], true );
								if ( rand( 0, 99 ) < 50 ) { //50% chance
									$this->createAuthorization( 200, $user_expense_id, $superior_user_ids[0], true );
								}
							}
						}
					}
				} else {
					Debug::Text( 'NOTICE: Skipping expense...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				$user_expense_ids[] = 0;
			}


			if ( isset( $this->create_data['hr'] ) && $this->create_data['hr'] == true ) {
				//Create Qualification
				$qualification_group_ids[] = $this->createQualificationGroup( $company_id, 10, 0 );
				$qualification_group_ids[] = $this->createQualificationGroup( $company_id, 20, 0 );
				$qualification_group_ids[] = $this->createQualificationGroup( $company_id, 30, 0 );
				$qualification_group_ids[] = $this->createQualificationGroup( $company_id, 40, 0 );
				$qualification_group_ids[] = $this->createQualificationGroup( $company_id, 50, 0 );

				// Create Qualification
				$qualification_ids['skill'][] = $this->createQualification( $company_id, 10, $qualification_group_ids[0] );
				$qualification_ids['skill'][] = $this->createQualification( $company_id, 20, $qualification_group_ids[1] );
				$qualification_ids['skill'][] = $this->createQualification( $company_id, 40, $qualification_group_ids[2] );
				$qualification_ids['skill'][] = $this->createQualification( $company_id, 50, $qualification_group_ids[3] );
				$qualification_ids['skill'][] = $this->createQualification( $company_id, 60, $qualification_group_ids[0] );
				$qualification_ids['license'][] = $this->createQualification( $company_id, 200, $qualification_group_ids[0] );
				$qualification_ids['license'][] = $this->createQualification( $company_id, 210, $qualification_group_ids[1] );
				$qualification_ids['license'][] = $this->createQualification( $company_id, 220, $qualification_group_ids[1] );
				$qualification_ids['license'][] = $this->createQualification( $company_id, 230, $qualification_group_ids[2] );
				$qualification_ids['license'][] = $this->createQualification( $company_id, 240, $qualification_group_ids[4] );
				$qualification_ids['education'][] = $this->createQualification( $company_id, 310, $qualification_group_ids[4] );
				$qualification_ids['education'][] = $this->createQualification( $company_id, 320, $qualification_group_ids[2] );
				$qualification_ids['education'][] = $this->createQualification( $company_id, 330, $qualification_group_ids[3] );
				$qualification_ids['education'][] = $this->createQualification( $company_id, 340, $qualification_group_ids[2] );
				$qualification_ids['education'][] = $this->createQualification( $company_id, 350, $qualification_group_ids[1] );
				$qualification_ids['language'][] = $this->createQualification( $company_id, 400, $qualification_group_ids[0] );
				$qualification_ids['language'][] = $this->createQualification( $company_id, 410, $qualification_group_ids[1] );
				$qualification_ids['language'][] = $this->createQualification( $company_id, 420, $qualification_group_ids[3] );
				$qualification_ids['membership'][] = $this->createQualification( $company_id, 500, $qualification_group_ids[0] );
				$qualification_ids['membership'][] = $this->createQualification( $company_id, 510, $qualification_group_ids[1] );
				$qualification_ids['membership'][] = $this->createQualification( $company_id, 520, $qualification_group_ids[2] );
				$qualification_ids['membership'][] = $this->createQualification( $company_id, 530, $qualification_group_ids[3] );

				$kpi_group_ids[] = $this->createKPIGroup( $company_id, 10, 0 );
				$kpi_group_ids[] = $this->createKPIGroup( $company_id, 20, 0 );
				$kpi_group_ids[] = $this->createKPIGroup( $company_id, 30, 0 );
				$kpi_group_ids[] = $this->createKPIGroup( $company_id, 40, 0 );
				$kpi_group_ids[] = $this->createKPIGroup( $company_id, 50, 0 );

				$kpi_all_ids[]['10'] = $this->createKPI( $company_id, 10, 10, [ -1 ] );
				$kpi_all_ids[]['10'] = $this->createKPI( $company_id, 20, 10, [ -1 ] );
				$kpi_all_ids[]['20'] = $this->createKPI( $company_id, 30, 20, [ -1 ] );
				$kpi_group1_ids[]['20'] = $this->createKPI( $company_id, 40, 20, [ $kpi_group_ids[0] ] );
				$kpi_group1_ids[]['10'] = $this->createKPI( $company_id, 50, 10, [ $kpi_group_ids[0] ] );
				$kpi_group2_ids[]['30'] = $this->createKPI( $company_id, 60, 30, [ $kpi_group_ids[1] ] );
				$kpi_group2_ids[]['30'] = $this->createKPI( $company_id, 70, 30, [ $kpi_group_ids[1] ] );

				foreach ( $user_ids as $code => $user_id ) {
					$reviewer_user_ids = $user_ids;
					unset( $reviewer_user_ids[$code] );
					$reviewer_user_ids = array_values( $reviewer_user_ids );
					$reviewer_user_random_ids = array_rand( $reviewer_user_ids, 3 );
					$user_review_control_id = $this->createUserReviewControl( $user_id, $reviewer_user_ids[array_rand( $reviewer_user_random_ids )] );
					if ( $user_review_control_id != '' ) {
						foreach ( $kpi_all_ids as $kpi_all_id ) {
							foreach ( $kpi_all_id as $code => $kpi_id ) {
								$this->createUserReview( $user_review_control_id, $code, $kpi_id );
							}
						}
						$group_id = rand( 1, 2 );
						switch ( $group_id ) {
							case 1:
								foreach ( $kpi_group1_ids as $kpi_group1_id ) {
									foreach ( $kpi_group1_id as $code => $kpi_id ) {
										$this->createUserReview( $user_review_control_id, $code, $kpi_id );
									}
								}
								break;
							case 2:
								foreach ( $kpi_group2_ids as $kpi_group2_id ) {
									foreach ( $kpi_group2_id as $code => $kpi_id ) {
										$this->createUserReview( $user_review_control_id, $code, $kpi_id );
									}
								}
								break;
						}
					}
				}

				//Create Qualification, Skills, Education, Language, Lencense, Membership

				$x = 1;
				foreach ( $user_ids as $user_id ) {
					$type = ( $x * 10 );
					$rand_arr_ids = [ 1, 2, 3, 4, 5 ];
					$rand_ids = array_rand( $rand_arr_ids, rand( 3, 5 ) );
					foreach ( $rand_ids as $rand_id ) {
						switch ( $rand_arr_ids[$rand_id] ) {
							case 1:
								$this->createUserSkill( $user_id, $type, $qualification_ids['skill'][array_rand( $qualification_ids['skill'] )] );
								break;
							case 2:
								$this->createUserEducation( $user_id, $qualification_ids['education'][array_rand( $qualification_ids['education'] )] );
								break;
							case 3:
								$this->createUserLicense( $user_id, $qualification_ids['license'][array_rand( $qualification_ids['license'] )] );
								break;
							case 4:
								$this->createUserLanguage( $user_id, $type, $qualification_ids['language'][array_rand( $qualification_ids['language'] )] );
								break;
							case 5:
								$this->createUserMembership( $user_id, $type, $qualification_ids['membership'][array_rand( $qualification_ids['membership'] )], $currency_ids[0] );
								break;
						}
					}
					$x++;
				}

				if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
					$this->createRecruitmentPortalConfig( $company_id );

					$x = 1;
					while ( $x <= 9 ) {
						$interviewer_random_user_ids = array_rand( $user_ids, 3 );
						$user_id = $user_ids[array_rand( $interviewer_random_user_ids )];
						$job_vacancy_id = $this->createJobVacancy( $company_id, $user_id, $user_title_ids[array_rand( $user_title_ids )], $branch_ids[array_rand( $branch_ids )], $department_ids[array_rand( $department_ids )] );
						if ( $job_vacancy_id != '' ) {

							$job_applicant_id = $this->createJobApplicant( $company_id );

							$y = 1;
							while ( $y <= rand( 75, 150 ) ) {
								$this->createJobApplication( $job_applicant_id, $job_vacancy_id, $user_id );
								$y++;
							}
							$n = 1;
							while ( $n <= rand( 2, 5 ) ) {
								$this->createJobApplicantLocation( $job_applicant_id );
								$this->createJobApplicantEmployment( $job_applicant_id, ( $n * 10 ) );
								$this->createJobApplicantReference( $job_applicant_id );
								$this->createJobApplicantSkill( $job_applicant_id, $qualification_ids['skill'][array_rand( $qualification_ids['skill'] )] );
								$this->createJobApplicantEducation( $job_applicant_id, $qualification_ids['education'][array_rand( $qualification_ids['education'] )] );
								$this->createJobApplicantLicense( $job_applicant_id, $qualification_ids['license'][array_rand( $qualification_ids['license'] )] );
								$this->createJobApplicantLanguage( $job_applicant_id, $qualification_ids['language'][array_rand( $qualification_ids['language'] )] );
								$this->createJobApplicantMembership( $job_applicant_id, $qualification_ids['membership'][array_rand( $qualification_ids['membership'] )], $currency_ids[0] );
								$n++;
							}
						}
						$x++;
					}
				}
			} else {
				Debug::Text( 'NOTICE: Skipping HR (Qualifications/Recruitment)...', __FILE__, __LINE__, __METHOD__, 10 );
			}


			if ( isset( $this->create_data['document'] ) && $this->create_data['document'] == true ) {
				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					// Attach  document to employee
					foreach ( $user_ids as $user_id ) {
						$x = 1;
						while ( $x <= rand( 2, 4 ) ) {
							$type = ( $x * 10 );
							$document_id = $this->createDocument( $company_id, 100, $type );

							for ( $n = 1; $n <= rand( 1, 6 ); $n++ ) {
								$this->createDocumentRevision( $company_id, $document_id, 100, $type, $n );
							}

							$this->createDocumentAttachment( $document_id, 100, $user_id );

							$x++;
						}
					}

					// Attach document to jobs
					foreach ( $job_ids as $job_id ) {
						$x = 1;
						while ( $x <= rand( 2, 3 ) ) {
							$type = ( $x * 10 );
							$document_id = $this->createDocument( $company_id, 60, $type );

							for ( $n = 1; $n <= rand( 1, 6 ); $n++ ) {
								$this->createDocumentRevision( $company_id, $document_id, 60, $type, $n );
							}

							$this->createDocumentAttachment( $document_id, 60, $job_id );

							$x++;
						}
					}

					// Attach document to clients
					foreach ( $client_ids as $client_id ) {
						$x = 1;
						while ( $x <= rand( 2, 4 ) ) {
							$type = ( $x * 10 );
							$document_id = $this->createDocument( $company_id, 80, $type );

							for ( $n = 1; $n <= rand( 1, 6 ); $n++ ) {
								$this->createDocumentRevision( $company_id, $document_id, 80, $type, $n );
							}

							$this->createDocumentAttachment( $document_id, 80, $client_id );

							$x++;
						}
					}

					// Attach document to client contacts
					foreach ( $client_contact_ids as $client_contact_id ) {
						$x = 1;
						while ( $x <= 2 ) {
							$type = ( $x * 10 );
							$document_id = $this->createDocument( $company_id, 85, $type );

							for ( $n = 1; $n <= rand( 1, 6 ); $n++ ) {
								$this->createDocumentRevision( $company_id, $document_id, 85, $type, $n );
							}

							$this->createDocumentAttachment( $document_id, 85, $client_contact_id );

							$x++;
						}
					}
				}
			} else {
				Debug::Text( 'NOTICE: Skipping documents...', __FILE__, __LINE__, __METHOD__, 10 );
			}
			Debug::Text( ' b.Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( isset( $this->create_data['schedule'] ) && $this->create_data['schedule'] == true ) {
				//Create recurring schedule templates
				$recurring_schedule_ids[] = $this->createRecurringScheduleTemplate( $company_id, 10, $policy_ids['schedule_1'] );    //Morning shift
				$recurring_schedule_ids[] = $this->createRecurringScheduleTemplate( $company_id, 20, $policy_ids['schedule_1'] );    //Afternoon shift
				$recurring_schedule_ids[] = $this->createRecurringScheduleTemplate( $company_id, 30, $policy_ids['schedule_1'] );    //Evening shift
				$recurring_schedule_ids[] = $this->createRecurringScheduleTemplate( $company_id, 40, null, 3 );                      //Split Shift - Open Shifts with 3x multiplier
				$recurring_schedule_ids[] = $this->createRecurringScheduleTemplate( $company_id, 50, $policy_ids['schedule_1'], 3 ); //Full rotation

				$recurring_schedule_start_date = TTDate::getBeginWeekEpoch( ( $current_epoch + ( 86400 * 7.5 ) ) );
				$this->createRecurringSchedule( $company_id, $recurring_schedule_ids[0], $recurring_schedule_start_date, '', [ $user_ids[0], $user_ids[1], $user_ids[2], $user_ids[3], $user_ids[4] ] );
				$this->createRecurringSchedule( $company_id, $recurring_schedule_ids[1], $recurring_schedule_start_date, '', [ $user_ids[5], $user_ids[6], $user_ids[7], $user_ids[8], $user_ids[9] ] );
				$this->createRecurringSchedule( $company_id, $recurring_schedule_ids[2], $recurring_schedule_start_date, '', [ $user_ids[10], $user_ids[11], $user_ids[12], $user_ids[13], $user_ids[14] ] );
				$this->createRecurringSchedule( $company_id, $recurring_schedule_ids[3], TTDate::getBeginWeekEpoch( ( $current_epoch - ( 86400 * 28 ) ) ), '', [ TTUUID::getZeroId() ], 6 ); //Open Shift - Start as soon as possible so they can see open shifts combined with committed.


				//Create different schedule shifts.
				$schedule_options_arr = [
						[ //Morning Shift
						  'status_id'          => 10,
						  'start_time'         => '06:00AM',
						  'end_time'           => '03:00PM',
						  'schedule_policy_id' => $policy_ids['schedule_1'],
						],
						[ //Afternoon Shift
						  'status_id'          => 10,
						  'start_time'         => '10:00AM',
						  'end_time'           => '07:00PM',
						  'schedule_policy_id' => $policy_ids['schedule_1'],
						],
						[ //Evening Shift
						  'status_id'          => 10,
						  'start_time'         => '2:00PM',
						  'end_time'           => '11:00PM',
						  'schedule_policy_id' => $policy_ids['schedule_1'],
						],
						[ //Common shift.
						  'status_id'          => 10,
						  'start_time'         => '08:00AM',
						  'end_time'           => '05:00PM',
						  'schedule_policy_id' => $policy_ids['schedule_1'],
						],
				];

				//Create schedule for each employee.
				$x = 1;
				foreach ( array_slice( $user_ids, 0, ( count( $user_ids ) - 3 ) ) as $user_id ) {
					//Always skip the last 3 employees so there are employees available to fill shifts.
					//Create schedule starting 6 weeks ago, up to the end of the week.
					Debug::Text( 'Creating schedule for User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

					$schedule_date = TTDate::getBeginWeekEpoch( ( $current_epoch - ( 86400 * 28 ) ) );
					$schedule_end_date = TTDate::getEndWeekEpoch( $current_epoch );

					$y = 1;
					while ( $schedule_date <= $schedule_end_date ) {
						srand( (int)($this->getRandomSeed() + $x + $y ) ); //Different seed for each user.

						if ( rand( 0, 99 ) < 90 ) { //90% chance of creating the schedule
							//Schedule just weekdays for users 1-4, then weekends and not mon/tue for user 5.
							if ( ( ( $x % 5 ) != 0 && date( 'w', $schedule_date ) != 0 && date( 'w', $schedule_date ) != 6 )
									|| ( ( $x % 5 ) == 0 && date( 'w', $schedule_date ) != 1 && date( 'w', $schedule_date ) != 2 )
							) {
								if ( ( $x % 5 ) == 0 ) {
									$schedule_options_key = 3; //Common shift
								} else {
									$schedule_options_key = array_rand( $schedule_options_arr );
								}

								Debug::Text( '  Schedule Date: ' . $schedule_date . ' Schedule Options Key: ' . $schedule_options_key, __FILE__, __LINE__, __METHOD__, 10 );

								//Random departments/branches
								$schedule_options_arr[$schedule_options_key]['branch_id'] = TTUUID::getNotExistID();     //Default
								$schedule_options_arr[$schedule_options_key]['department_id'] = TTUUID::getNotExistID(); //Default

								if ( ( $x % 3 ) == 0 && ( $y % 5 ) == 0 ) { //Every 3rd employee, only every 5th day.
									$schedule_options_arr[$schedule_options_key]['branch_id'] = $branch_ids[array_rand( $branch_ids )];
									$schedule_options_arr[$schedule_options_key]['department_id'] = $department_ids[array_rand( $department_ids )];
								}

								$this->createSchedule( $company_id, $user_id, $schedule_date, $schedule_options_arr[$schedule_options_key] );
							}
						}

						$schedule_date += 86400;
						$y++;
					}
					//break;

					unset( $schedule_date, $schedule_end_date, $user_id );
					$x++;
				}
				unset( $schedule_options_arr, $schedule_options_key );
			} else {
				Debug::Text( 'NOTICE: Skipping schedules...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( isset( $this->create_data['punch'] ) && $this->create_data['punch'] == true ) {
				//Punch users in/out randomly.
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

				$x = 1;
				foreach ( $user_ids as $user_id ) {
					srand( (int)($this->getRandomSeed() + $x) ); //Different seed for each user.

					//Pick random jobs/tasks that are used for the entire date range. So one employee isn't punching into 15 jobs.
					$user_random_branch_ids = (array)array_flip( (array)array_rand( $branch_ids, 2 ) );
					$user_random_department_ids = (array)array_flip( (array)array_rand( $department_ids, 2 ) );

					$user_random_new_york_job_ids = (array)array_flip( (array)array_rand( (array)array_slice( $job_ids, 0, 7 ), 2 ) ); // New York
					$user_random_seattle_job_ids = (array)array_flip( (array)array_rand( (array)array_slice( $job_ids, 8 ), 2 ) );     // Seattle
					$user_random_other_job_ids = (array)array_flip( (array)array_rand( $job_ids, 2 ) );

					$user_random_task_ids = (array)array_flip( (array)array_rand( $task_ids, 3 ) );

					//Create punches starting 6 weeks ago, up to the end of the week.
					$start_date = $punch_date = TTDate::getBeginWeekEpoch( ( $current_epoch - ( 86400 * 28 ) ) );
					$end_date = TTDate::getEndWeekEpoch( $current_epoch );
					$exception_cutoff_date = TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $start_date ) + ( 86400 * 14 ) ) );

					$i = 1; //Start at 1 so $i % 5 == 0 doesn't match on the first iteration every time.
					while ( $punch_date <= $end_date ) {
						srand( (int)($this->getRandomSeed() + $punch_date + $x + $i) ); //Different seed for each user/date

						$date_stamp = TTDate::getDate( 'DATE', $punch_date );

						if ( ( $i % 25 ) == 0 ) {
							$user_random_job_ids = $user_random_other_job_ids;
							$user_random_coordinates = $coordinates['00']; // outside New York, Seattle
						} else {
							if ( ( $i % 2 ) == 0 ) {
								$user_random_job_ids = $user_random_new_york_job_ids; // New York
								$user_random_coordinates = $coordinates['NY'];        // inside New York
							} else {
								$user_random_job_ids = $user_random_seattle_job_ids; // Seattle
								$user_random_coordinates = $coordinates['WA'];       // inside Seattle
							}
						}

						if ( date( 'w', $punch_date ) != 0 && date( 'w', $punch_date ) != 6 ) {
							$first_punch_in = '8:00AM';
							if ( date( 'w', $punch_date ) == 5 ) { //5=Friday - Don't adjust punch after this on fridays as we want to show Evening premium.
								$last_punch_out = strtotime( $date_stamp . ' ' . rand( 5, 7 ) . ':' . str_pad( rand( 0, 59 ), 2, '0', STR_PAD_LEFT ) . 'PM' );
							} else {
								$last_punch_out = strtotime( $date_stamp . ' 5:00PM' );
							}

							if ( $punch_date >= $exception_cutoff_date && in_array( $user_id, $hierarchy_user_ids ) && date( 'w', $punch_date ) != 5 ) { //Make sure requests are only created when supervisors exist.
								if ( rand( 0, 99 ) < 30 ) { //30% chance
									$first_punch_in = rand( 7, 8 ) . ':' . str_pad( rand( 0, 30 ), 2, '0', STR_PAD_LEFT ) . 'AM';
									$last_punch_out = strtotime( $date_stamp . ' ' . rand( 4, 5 ) . ':' . str_pad( rand( 0, 59 ), 2, '0', STR_PAD_LEFT ) . 'PM' );

									if ( rand( 0, 99 ) < 25 ) {                                          //25% chance
										//Create request
										$request_id = $this->createRequest( 40, $user_id, $date_stamp ); //40=Leave Early. These change the schedule and add a lot of absences, so keep them to a minimum.
										if ( rand( 0, 99 ) < 40 ) { //40% chance
											$this->createAuthorization( 1020, $request_id, $superior_user_ids[2], true );
											$this->createAuthorization( 1020, $request_id, $superior_user_ids[1], true );
											$this->createAuthorization( 1020, $request_id, $superior_user_ids[0], true );
										}
									}
								} else if ( rand( 0, 99 ) < 10 ) { //10% chance
									//Don't punch out to generate exception.
									$last_punch_out = null;

									$request_id = $this->createRequest( 10, $user_id, $date_stamp ); //10=Forgot to punch out request
									if ( rand( 0, 99 ) < 40 ) { //40% chance
										$this->createAuthorization( 1010, $request_id, $superior_user_ids[2], true );
									}
								} else if ( rand( 0, 99 ) < 25 ) { //25% chance
									//Create request
									if ( rand( 0, 99 ) < 50 ) {                                                                     //50% chance
										$request_id = $this->createRequest( 32, $user_id, $date_stamp, $policy_ids['absence'][2] ); //30=Multi-day Sick. These change the schedule and add a lot of absences, so keep them to a minimum.
									} else {
										$request_id = $this->createRequest( 30, $user_id, $date_stamp, $policy_ids['absence'][0] ); //30=Multi-day Vacation. These change the schedule and add a lot of absences, so keep them to a minimum.
									}

									if ( rand( 0, 99 ) < 75 ) { //75% chance
										$this->createAuthorization( 1020, $request_id, $superior_user_ids[2], true );
										$this->createAuthorization( 1020, $request_id, $superior_user_ids[1], true );
									}
								}
							}

							//Weekdays
							if ( rand( 0, 99 ) < 60 ) { //60% chance (at least 3 out of 5 days)
								//Transfer punches.

								//Choose random transfer punch hour/minutes.
								$transfer_punch_time = str_pad( rand( 10, 11 ), 2, '0', STR_PAD_LEFT ) . ':' . str_pad( rand( 0, 59 ), 2, '0', STR_PAD_LEFT ) . 'AM';

								$this->createPunchPair( $user_id,
														strtotime( $date_stamp . ' ' . $first_punch_in ),
														strtotime( $date_stamp . ' ' . $transfer_punch_time ),
														[
																'in_type_id'      => 10,
																'out_type_id'     => 10,
																'branch_id'       => $branch_ids[array_rand( $user_random_branch_ids )],
																'department_id'   => $department_ids[array_rand( $user_random_department_ids )],
																'job_id'          => $job_ids[array_rand( $user_random_job_ids )],
																'job_item_id'     => $task_ids[array_rand( $user_random_task_ids )],
																'in_coordinates'  => $user_random_coordinates[array_rand( $user_random_coordinates )],
																'out_coordinates' => $user_random_coordinates[array_rand( $user_random_coordinates )],
														],
														false, //Calculate at end of loop.
														true   //Disable rounding due to transfer punch.
								);
								$this->createPunchPair( $user_id,
														strtotime( $date_stamp . ' ' . $transfer_punch_time ),
														strtotime( $date_stamp . ' 1:00PM' ),
														[
																'in_type_id'      => 10,
																'out_type_id'     => 20,
																'branch_id'       => $branch_ids[array_rand( $user_random_branch_ids )],
																'department_id'   => $department_ids[array_rand( $user_random_department_ids )],
																'job_id'          => $job_ids[array_rand( $user_random_job_ids )],
																'job_item_id'     => $task_ids[array_rand( $user_random_task_ids )],
																'in_coordinates'  => $user_random_coordinates[array_rand( $user_random_coordinates )],
																'out_coordinates' => $user_random_coordinates[array_rand( $user_random_coordinates )],
														],
														false, //Calculate at end of loop.
														true   //Disable rounding due to transfer punch.
								);
								//Calc total time on last punch pair only.
								$this->createPunchPair( $user_id,
														strtotime( $date_stamp . ' 2:00PM' ),
														$last_punch_out,
														[
																'in_type_id'      => 20,
																'out_type_id'     => 10,
																'branch_id'       => $branch_ids[array_rand( $user_random_branch_ids )],
																'department_id'   => $department_ids[array_rand( $user_random_department_ids )],
																'job_id'          => $job_ids[array_rand( $user_random_job_ids )],
																'job_item_id'     => $task_ids[array_rand( $user_random_task_ids )],
																'in_coordinates'  => $user_random_coordinates[array_rand( $user_random_coordinates )],
																'out_coordinates' => $user_random_coordinates[array_rand( $user_random_coordinates )],
														],
														false //Calculate at end of loop.
								);
							} else {
								//No transfer punches.
								$this->createPunchPair( $user_id,
														strtotime( $date_stamp . ' ' . $first_punch_in ),
														strtotime( $date_stamp . ' 12:00PM' ),
														[
																'in_type_id'      => 10,
																'out_type_id'     => 20,
																'branch_id'       => $branch_ids[array_rand( $user_random_branch_ids )],
																'department_id'   => $department_ids[array_rand( $user_random_department_ids )],
																'job_id'          => $job_ids[array_rand( $user_random_job_ids )],
																'job_item_id'     => $task_ids[array_rand( $user_random_task_ids )],
																'in_coordinates'  => $user_random_coordinates[array_rand( $user_random_coordinates )],
																'out_coordinates' => $user_random_coordinates[array_rand( $user_random_coordinates )],
														],
														false //Calculate at end of loop.
								);
								//Calc total time on last punch pair only.
								$this->createPunchPair( $user_id,
														strtotime( $date_stamp . ' 1:00PM' ),
														$last_punch_out,
														[
																'in_type_id'      => 20,
																'out_type_id'     => 10,
																'branch_id'       => $branch_ids[array_rand( $user_random_branch_ids )],
																'department_id'   => $department_ids[array_rand( $user_random_department_ids )],
																'job_id'          => $job_ids[array_rand( $user_random_job_ids )],
																'job_item_id'     => $task_ids[array_rand( $user_random_task_ids )],
																'in_coordinates'  => $user_random_coordinates[array_rand( $user_random_coordinates )],
																'out_coordinates' => $user_random_coordinates[array_rand( $user_random_coordinates )],
														],
														false //Calculate at end of loop.
								);
							}
						} else if ( date( 'w', $punch_date ) == 6 && rand( 0, 99 ) < 40 ) { //40% chance.
							//Sat.
							$this->createPunchPair( $user_id,
													strtotime( $date_stamp . ' 10:00AM' ),
													strtotime( $date_stamp . ' 2:30PM' ),
													[
															'in_type_id'      => 10,
															'out_type_id'     => 10,
															'branch_id'       => $branch_ids[array_rand( $user_random_branch_ids )],
															'department_id'   => $department_ids[array_rand( $user_random_department_ids )],
															'job_id'          => $job_ids[array_rand( $user_random_job_ids )],
															'job_item_id'     => $task_ids[array_rand( $user_random_task_ids )],
															'in_coordinates'  => $user_random_coordinates[array_rand( $user_random_coordinates )],
															'out_coordinates' => $user_random_coordinates[array_rand( $user_random_coordinates )],
													],
													false
							);
						}

						//Recalculate entire day. Performance optimization.
						//UserDateTotalFactory::reCalculateRange( $user_id, $start_date, $end_date );

						$punch_date += 86400;
						$i++;
					}

					//Calculate entire user timesheet here, as an optimization. Reduced time in half.
					$ulf->getById( $user_id );
					$user_obj = $ulf->getCurrent();

					$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
					$cp->setUserObject( $user_obj );
					$cp->addPendingCalculationDate( $start_date, $end_date );
					$cp->calculate(); //This sets timezone itself.
					$cp->Save();

					unset( $punch_options_arr, $punch_date, $user_id, $user_obj, $cp );

					$x++;
				}
				Debug::Text( ' c.Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

				//Punches must be created for pay stubs to be created.
				if ( isset( $this->create_data['pay_stub'] ) && $this->create_data['pay_stub'] == true ) {
					//Generate pay stubs for each pay period
					//Can't do this unless punches are created too.
					$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
					$pplf->getByCompanyId( $company_id, null, null, null, [ 'start_date' => 'asc' ] );
					if ( $pplf->getRecordCount() > 0 ) {
						$n = 0;
						foreach ( $pplf as $pp_obj ) {
							foreach ( $user_ids as $user_id ) {
								if ( !in_array( $user_id, $superior_user_ids ) ) {
									//Verify timesheets at random for each regular user/pay period.
									if ( rand( 0, 99 ) < 85 ) { //85% chance
										$timesheet_verification_id = $this->createTimeSheetVerification( $user_id, $pp_obj->getId(), $user_id );
										if ( rand( 0, 99 ) < 85 ) { //85% chance
											$this->createAuthorization( 90, $timesheet_verification_id, $superior_user_ids[2], true );
											if ( rand( 0, 99 ) < 85 ) { //85% chance
												$this->createAuthorization( 90, $timesheet_verification_id, $superior_user_ids[1], true );
												if ( rand( 0, 99 ) < 25 ) { //25% chance
													$this->createAuthorization( 90, $timesheet_verification_id, $superior_user_ids[0], true );
												}
											}
										}
									}
								}

								$cps = new CalculatePayStub();
								$cps->setUser( $user_id );
								$cps->setPayPeriod( $pp_obj->getId() );
								$cps->calculate();
							}

							if ( $n <= 1 ) {                                                 //Pay and close the first two pay periods.
								Debug::Text( '  Processing PayStub Transactions and closing the pay period... Pay Period: ' . $pp_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								//Process Payments for all transactions.
								$data['filter_data']['transaction_status_id'] = [ 10, 200 ]; //10=Pending, 200=ReIssue
								$data['filter_data']['transaction_type_id'] = 10;            //10=Valid (Enabled)
								$data['filter_data']['pay_period_id'] = $pp_obj->getId();

								$pslf = TTnew( 'PayStubTransactionListFactory' ); /** @var PayStubTransactionListFactory $pslf */
								$pslf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $data['filter_data'] );
								$pslf->exportPayStubTransaction( $pslf );
								unset( $pslf, $data );

								$pp_obj->setStatus( 20 );
								if ( $pp_obj->isValid() ) {
									$pp_obj->Save();
								} else {
									Debug::Text( '  ERROR: Unable to close pay period: ' . $pp_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								Debug::Text( '  NOT Processing PayStub Transactions and closing the pay period... Pay Period: ' . $pp_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							}

							$n++;
						}
					}
					unset( $pplf, $pp_obj, $user_id );
				} else {
					Debug::Text( 'NOTICE: Skipping pay stubs...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( 'NOTICE: Skipping punches and pay stubs...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			//
			//Government Documents for W2s.
			//
			$report_obj = TTnew( 'FormW2Report' ); /** @var FormW2Report $report_obj */
			$report_obj->setUserObject( $current_user );
			$report_obj->setPermissionObject( new Permission() );

			$report_data['config'] = $report_obj->getTemplate( 'by_employee' );
			$report_data['config']['filter']['time_period']['time_period'] = 'this_year';                       //Change time period to this year.
			$report_data['config']['form'] = Misc::convertObjectToArray( $report_obj->getCompanyFormConfig() ); //This is included in the rest of the config set below.
			$report_obj->setConfig( (array)$report_data['config'] );

			$output_format = 'pdf_form_publish_employee';
			$validation_obj = $report_obj->validateConfig( $output_format );
			if ( $validation_obj->isValid() == true ) {
				$report_obj->getOutput( $output_format );
			}
		}

		////$cf->FailTransaction();
		//$cf->CommitTransaction(); //See StartTransaction() call at the top for details.

		Debug::Text( ' z.Memory Usage: Current: ' . memory_get_usage() . ' Peak: ' . memory_get_peak_usage(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}
}

?>
