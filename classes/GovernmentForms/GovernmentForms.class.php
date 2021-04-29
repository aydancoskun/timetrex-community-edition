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
 * @package GovernmentForms
 */
class GovernmentForms {
	public $objs = null;

	public $tcpdf_dir = '../tcpdf/'; //TCPDF class directory.
	public $fpdi_dir = '../fpdi/';   //FPDI class directory.

	function __construct() {
		return true;
	}

	function getFormObject( $form, $country = null, $province = null, $district = null ) {
		$class_name = 'GovernmentForms';
		$class_directory = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'country';
		if ( $country != '' ) {
			$class_name .= '_' . strtoupper( $country );
			$class_directory .= DIRECTORY_SEPARATOR . strtolower( $country );
		}
		if ( $province != '' ) {
			$class_name .= '_' . strtoupper( $province );
			$class_directory .= DIRECTORY_SEPARATOR . strtolower( $province );
		}
		if ( $district != '' ) {
			$class_name .= '_' . strtoupper( $district );
			$class_directory .= DIRECTORY_SEPARATOR . strtolower( $district );
		}
		$class_name .= '_' . $form;

		$class_file_name = $class_directory . DIRECTORY_SEPARATOR . strtolower( $form ) . '.class.php';
		Debug::text( 'Class Directory: ' . $class_directory, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Class File Name: ' . $class_file_name, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Class Name: ' . $class_name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( file_exists( $class_file_name ) ) {
			include_once( $class_file_name );

			$obj = new $class_name;
			$obj->setClassDirectory( $class_directory );

			//Need to capture the form object parameters in this object, so when its serialized they are stored, and can easily be used during unserialize.
			$obj->metadata = [ 'form' => $form, 'country' => $country, 'province' => $province, 'district' => $district ];

			return $obj;
		} else {
			Debug::text( 'Class File does not exist!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	function addForm( $obj ) {
		if ( is_object( $obj ) ) {
			$this->objs[] = $obj;

			return true;
		}

		return false;
	}

	function getForms() {
		return $this->objs;
	}

	function clearForms() {
		$this->objs = null;

		return true;
	}

	function validateXML( $xml, $schema_file ) {
		Debug::text( 'Schema File: ' . $schema_file, __FILE__, __LINE__, __METHOD__, 10 );
		if ( class_exists( 'DomDocument' ) && file_exists( $schema_file ) ) {
			libxml_use_internal_errors( true );

			$dom = new DomDocument;
			$dom->loadXML( $xml );

			if ( $dom->schemaValidate( $schema_file ) ) {
				Debug::Text( 'Schema is valid!', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			} else {
				Debug::Text( 'Schema is NOT valid!', __FILE__, __LINE__, __METHOD__, 10 );
				//Debug::Arr( $xml, 'Full XML file: ', __FILE__, __LINE__, __METHOD__, 10 );

				$error_msg = '';
				$errors = libxml_get_errors();
				$i = 1;
				foreach ( $errors as $error ) {
					Debug::Text( 'XML Error (Line: ' . $error->line . '): ' . $error->message, __FILE__, __LINE__, __METHOD__, 10 );
					$error_msg .= $i . ': ' . $error->message . "<br>\n";
					$i++;
				}
				unset( $errors, $error );

				return [
						'api_retval'  => false,
						//'api_request'
						//'api_pager'
						'api_details' => [
								'code'        => 'VALIDATION',
								'description' => $error_msg,
						],
				];
			}
		} else {
			Debug::Text( 'DomDocument not available!', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}
	}

	/**
	 * Serializes the object to JSON for storing in the DB and later retrieval. Especially important for handling correction reports like W2C.
	 * @return false|string
	 */
	function serialize( $clear_records = true ) {
		if ( is_array( $this->objs ) && count( $this->objs ) > 0 ) {
			$retval = [ 'parent_metadata' => [ 'class' => get_class( $this ), 'tt_version' => APPLICATION_VERSION ], 'objs' => [] ];

			foreach ( $this->objs as $obj ) {
				$retval['objs'][] = $obj->serialize( $clear_records );
			}

			return $retval;
		}

		return false;
	}

	/**
	 * Unserializes a JSON object into the form itself.
	 * @return false|string
	 */
	function unserialize( $data ) {
		if ( is_array( $data ) && isset( $data['parent_metadata'] ) ) {
			if ( isset( $data['objs'] ) && is_array( $data['objs'] ) ) {
				foreach( $data['objs'] as $tmp_arr ) {
					$form_obj = $this->getFormObject( $tmp_arr['metadata']['object_data']['form'], $tmp_arr['metadata']['object_data']['country'], $tmp_arr['metadata']['object_data']['province'], $tmp_arr['metadata']['object_data']['district'] );
					if ( is_object( $form_obj ) ) {
						$form_obj->unserialize( $tmp_arr );
						$this->addForm( $form_obj );
					} else {
						Debug::Arr( $tmp_arr, 'ERROR: Unable to recreate form object from serialized data!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}

			return true;
		}

		return false;
	}

	function Output( $type, $clear_records = true ) {
		if ( !is_array( $this->objs ) ) {
			Debug::Text( 'ERROR! No objects to output!', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$output = false;

		$type = strtolower( $type );

		//Initialize PDF object so all subclasses can access it.
		//Loop through all objects and combine the output from each into a single document.
		if ( $type == 'pdf' ) {
			if ( !class_exists( 'tcpdf' ) ) {
				require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'tecnickcom' . DIRECTORY_SEPARATOR . 'tcpdf' . DIRECTORY_SEPARATOR . 'tcpdf.php' );
			}

			if ( !class_exists( 'fpdi' ) ) {
				require_once ( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'setasign' . DIRECTORY_SEPARATOR . 'fpdi' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'autoload.php' );
			}

			/*
			 *  setasign\Fpdi\Tcpdf\Fpdi instead of setasign\Fpdi\FPDI to make use of tcpdf methods fontsubsetting() and others
			 */
			$pdf = new setasign\Fpdi\Tcpdf\Fpdi( 'P', 'pt', 'LETTER', false, 'ISO-8859-1', false ); //Does not support unicode/UTF-8 by default.
			$pdf->setCreator( APPLICATION_NAME . ' ' . getTTProductEditionName() . ' v' . APPLICATION_VERSION );
			$pdf->setTitle( '' ); //Always clear out the title incase an incorrect one comes through on the template or something.
			$pdf->setSubject( '' );
			$pdf->setKeywords( '' );
			$pdf->setMargins( 0, 0 ); //Margins are ignored because we use setXY() to force the coordinates before each drawing and therefore ignores margins.
			$pdf->SetAutoPageBreak( false );
			$pdf->setFontSubsetting( false );
			$pdf->setPrintHeader( false ); //Removes the thin horizontal line from top of each page.
			$pdf->setPrintFooter( false );

			foreach ( $this->objs as $obj ) {
				$obj->setPDFObject( $pdf );
				$obj->Output( $type, $clear_records );
			}

			$output = $pdf->Output( '', 'S' );
		} else if ( $type == 'efile' ) {
			foreach ( $this->objs as $obj ) {
				$output = $obj->Output( $type, $clear_records );
				break; //Only support eFiling the first form object.
			}
		} else if ( $type == 'xml' ) {
			//Since multiple XML sections may need to be joined together,
			//We must pass the XML object between each form and  build the entire XML object completely
			//then output it all at once at the end.
			$xml = null;
			$xml_schema = null;
			foreach ( $this->objs as $obj ) {
				if ( is_object( $xml ) ) {
					$obj->setXMLObject( $xml );
				}

				$obj->Output( $type, $clear_records );
				if ( isset( $obj->xml_schema ) ) {
					$xml_schema = $obj->getClassDirectory() . DIRECTORY_SEPARATOR . 'schema' . DIRECTORY_SEPARATOR . $obj->xml_schema;
				}

				if ( $xml == null && is_object( $obj->getXMLObject() ) ) {
					$xml = $obj->getXMLObject();
				}
			}

			if ( is_object( $xml ) ) {
				$output = $xml->asXML();

				$xml_validation_retval = $this->validateXML( $output, $xml_schema );
				if ( $xml_validation_retval !== true ) {
					Debug::text( 'XML Schema is invalid! Malformed XML!', __FILE__, __LINE__, __METHOD__, 10 );
					//$output = FALSE;
					$output = $xml_validation_retval;
				}
			} else {
				Debug::text( 'No XML object!', __FILE__, __LINE__, __METHOD__, 10 );
				$output = false;
			}
		}

		return $output;
	}
}

?>
