<?php
/* forge_fdf, by Sid Steward
   version 1.1
   visit: www.pdfhacks.com/forge_fdf/

  For text fields, combo boxes and list boxes, add
  field values as a name => value pair to $fdf_data_strings.

  For check boxes and radio buttons, add field values
  as a name => value pair to $fdf_data_names.  Typically,
  true and false correspond to the (case sensitive)
  names "Yes" and "Off".

  Any field added to the $fields_hidden or $fields_readonly
  array must also be a key in $fdf_data_strings or
  $fdf_data_names; this might be changed in the future

  Any field listed in $fdf_data_strings or $fdf_data_names
  that you want hidden or read-only must have its field
  name added to $fields_hidden or $fields_readonly; do this
  even if your form has these bits set already

  PDF can be particular about CR and LF characters, so I
  spelled them out in hex: CR == \x0d : LF == \x0a
*/

class ForgeFDF {
	function escape_pdf_string( $ss ) {
		$backslash = chr( 0x5c );
		$ss_esc = '';
		$ss_len = strlen( $ss );
		for ( $ii = 0; $ii < $ss_len; ++$ii ) {
			if ( ord( $ss{$ii} ) == 0x28 ||  // open paren
					ord( $ss{$ii} ) == 0x29 ||  // close paren
					ord( $ss{$ii} ) == 0x5c
			)   // backslash
			{
				$ss_esc .= $backslash . $ss{$ii}; // escape the character w/ backslash
			} else {
				if ( ord( $ss{$ii} ) < 32 || 126 < ord( $ss{$ii} ) ) {
					$ss_esc .= sprintf( "\\%03o", ord( $ss{$ii} ) ); // use an octal code
				} else {
					$ss_esc .= $ss{$ii};
				}
			}
		}

		return $ss_esc;
	}

	function escape_pdf_name( $ss ) {
		$ss_esc = '';
		$ss_len = strlen( $ss );
		for ( $ii = 0; $ii < $ss_len; ++$ii ) {
			if ( ord( $ss{$ii} ) < 33 || 126 < ord( $ss{$ii} ) ||
					ord( $ss{$ii} ) == 0x23
			) // hash mark
			{
				$ss_esc .= sprintf( "#%02x", ord( $ss{$ii} ) ); // use a hex code
			} else {
				$ss_esc .= $ss{$ii};
			}
		}

		return $ss_esc;
	}

	// In PDF, partial form field names are combined using periods to
	// yield the full form field name; we'll take these dot-delimited
	// names and then expand them into nested arrays, here; takes
	// an array that uses dot-delimited names and returns a tree of arrays;
	//
	function burst_dots_into_arrays( &$fdf_data_old ) {
		$fdf_data_new = array();

		foreach ( $fdf_data_old as $key => $value ) {
			$key_split = explode( '.', (string)$key, 2 );

			if ( count( $key_split ) == 2 ) { // handle dot
				if ( !array_key_exists( (string)( $key_split[0] ), $fdf_data_new ) ) {
					$fdf_data_new[ (string)( $key_split[0] ) ] = array();
				}
				if ( gettype( $fdf_data_new[ (string)( $key_split[0] ) ] ) != 'array' ) {
					// this new key collides with an existing name; this shouldn't happen;
					// associate string value with the special empty key in array, anyhow;

					$fdf_data_new[ (string)( $key_split[0] ) ] =
							array('' => $fdf_data_new[ (string)( $key_split[0] ) ]);
				}

				$fdf_data_new[ (string)( $key_split[0] ) ][ (string)( $key_split[1] ) ] = $value;
			} else { // no dot
				if ( array_key_exists( (string)( $key_split[0] ), $fdf_data_new ) &&
						gettype( $fdf_data_new[ (string)( $key_split[0] ) ] ) == 'array'
				) { // this key collides with an existing array; this shouldn't happen;
					// associate string value with the special empty key in array, anyhow;

					$fdf_data_new[ (string)$key ][''] = $value;
				} else { // simply copy
					$fdf_data_new[ (string)$key ] = $value;
				}
			}
		}

		foreach ( $fdf_data_new as $key => $value ) {
			if ( gettype( $value ) == 'array' ) {
				$fdf_data_new[ (string)$key ] = $this->burst_dots_into_arrays( $value ); // recurse
			}
		}

		return $fdf_data_new;
	}

	function forge_fdf_fields_flags( &$fdf,
							$field_name,
							&$fields_hidden,
							&$fields_readonly ) {
		if ( in_array( $field_name, $fields_hidden ) ) {
			$fdf .= "/SetF 2 ";
		} // set
		else {
			$fdf .= "/ClrF 2 ";
		} // clear

		if ( in_array( $field_name, $fields_readonly ) ) {
			$fdf .= "/SetFf 1 ";
		} // set
		else {
			$fdf .= "/ClrFf 1 ";
		} // clear
	}

	function forge_fdf_fields( &$fdf,
					  &$fdf_data,
					  &$fields_hidden,
					  &$fields_readonly,
					  $accumulated_name,
					  $strings_b ) // true <==> $fdf_data contains string data
		//
		// string data is used for text fields, combo boxes and list boxes;
		// name data is used for checkboxes and radio buttons, and
		// /Yes and /Off are commonly used for true and false
	{
		if ( 0 < strlen( $accumulated_name ) ) {
			$accumulated_name .= '.'; // append period seperator
		}

		foreach ( $fdf_data as $key => $value ) {
			// we use string casts to prevent numeric strings from being silently converted to numbers

			$fdf .= "<< "; // open dictionary

			if ( gettype( $value ) == 'array' ) { // parent; recurse
				$fdf .= "/T (" . $this->escape_pdf_string( (string)$key ) . ") "; // partial field name
				$fdf .= "/Kids [ ";                                    // open Kids array

				// recurse
				$this->forge_fdf_fields( $fdf,
								  $value,
								  $fields_hidden,
								  $fields_readonly,
								  $accumulated_name . (string)$key,
								  $strings_b );

				$fdf .= "] "; // close Kids array
			} else {

				// field name
				$fdf .= "/T (" . $this->escape_pdf_string( (string)$key ) . ") ";

				// field value
				if ( $strings_b ) { // string
					$fdf .= "/V (" . $this->escape_pdf_string( (string)$value ) . ") ";
				} else { // name
					$fdf .= "/V /" . $this->escape_pdf_name( (string)$value ) . " ";
				}

				// field flags
				$this->forge_fdf_fields_flags( $fdf,
										$accumulated_name . (string)$key,
										$fields_hidden,
										$fields_readonly );
			}

			$fdf .= ">> \x0d"; // close dictionary
		}

	}

	function forge_fdf_fields_strings( &$fdf,
							  &$fdf_data_strings,
							  &$fields_hidden,
							  &$fields_readonly ) {
		return $this->forge_fdf_fields( $fdf,
								  $fdf_data_strings,
								  $fields_hidden,
								  $fields_readonly,
								  '',
								  TRUE ); // true => strings data
	}


	function forge_fdf_fields_names( &$fdf,
							&$fdf_data_names,
							&$fields_hidden,
							&$fields_readonly ) {
		return $this->forge_fdf_fields( $fdf,
								  $fdf_data_names,
								  $fields_hidden,
								  $fields_readonly,
								  '',
								  FALSE ); // false => names data
	}

	function forge_fdf( $pdf_form_url,
			   $fdf_data_strings,
			   $fdf_data_names = array(),
			   $fields_hidden = array(),
			   $fields_readonly = array() ) {
		$fdf = "%FDF-1.2\x0d%\xe2\xe3\xcf\xd3\x0d\x0a"; // header
		$fdf .= "1 0 obj\x0d<< "; // open the Root dictionary
		$fdf .= "\x0d/FDF << "; // open the FDF dictionary
		$fdf .= "/Fields [ "; // open the form Fields array

		$fdf_data_strings = $this->burst_dots_into_arrays( $fdf_data_strings );
		$this->forge_fdf_fields_strings( $fdf,
								  $fdf_data_strings,
								  $fields_hidden,
								  $fields_readonly );

		$fdf_data_names = $this->burst_dots_into_arrays( $fdf_data_names );
		$this->forge_fdf_fields_names( $fdf,
								$fdf_data_names,
								$fields_hidden,
								$fields_readonly );

		$fdf .= "] \x0d"; // close the Fields array

		// the PDF form filename or URL, if given
		if ( $pdf_form_url ) {
			$fdf .= "/F (" . $this->escape_pdf_string( $pdf_form_url ) . ") \x0d";
		}

		$fdf .= ">> \x0d"; // close the FDF dictionary
		$fdf .= ">> \x0dendobj\x0d"; // close the Root dictionary

		// trailer; note the "1 0 R" reference to "1 0 obj" above
		$fdf .= "trailer\x0d<<\x0d/Root 1 0 R \x0d\x0d>>\x0d";
		$fdf .= "%%EOF\x0d\x0a";

		return $fdf;
	}
}
?>