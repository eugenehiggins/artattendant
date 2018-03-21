<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exporter for the CSV format
 *
 * @since 1.0
 */
class ACP_Export_Exporter_CSV extends ACP_Export_Exporter {

	/**
	 * @see   BulkPress_Exporter::export()
	 * @since 1.0
	 */
	public function export( $fh, $encrypt = false ) {
		if ( $encrypt ) {
			// Write the CSV to memory
			$fh_memory = fopen( 'php://memory', 'w' );
			$this->export( $fh_memory );

			// Read the CSV from memory
			fseek( $fh_memory, 0 );
			$csv = stream_get_contents( $fh_memory );

			// Encrypt the file contents
			try {
				$cryptor = new ACP_Export_Cryptor();
				$key = ACP_Export_Utility_Users::get_user_encryption_key();
				$result = $cryptor->encrypt( $csv, $key );
				$csv_encrypted = $result['result'];

				// Write the encrypted contents to the file
				fwrite( $fh, $csv_encrypted );
			} catch ( Exception $e ) {
				wp_send_json_error( __( 'The requested file could not be downloaded.', 'codepress-admin-columns' ) );
			}

			return;
		}

		/**
		 * Filters the delimiter to use in exporting to the CSV file format
		 *
		 * @since 1.0
		 *
		 * @param string                  $delimiter Delimiter to use
		 * @param ACP_Export_Exporter_CSV $exporter  Exporter class instance
		 */
		$delimiter = apply_filters( 'ac/export/exporter_csv/delimiter', ',', $this );

		// Column headers
		$column_labels = $this->get_column_labels();

		if ( $column_labels ) {
			fputcsv( $fh, $this->get_column_labels(), $delimiter );
		}

		// Get data to export
		$data = $this->get_data();

		// Output all items
		foreach ( $data as $item ) {
			fputcsv( $fh, array_map( array( $this, 'format_output' ), $item ), $delimiter );
		}
	}

	/**
	 * Format the output to a string. For scalars (integers, strings, etc.), it returns the input
	 * value cast to a string. For arrays, it (deeply) applies this function to the array values
	 * and returns them in a comma-separated string
	 *
	 * @since 1.0
	 *
	 * @param mixed $value Input value
	 *
	 * @return string Formatted value
	 */
	private function format_output( $value ) {
		if ( is_scalar( $value ) ) {

			// convert HTML entities to symbols
			$value = html_entity_decode( $value, ENT_QUOTES, 'utf-8' );

			// Remove newlines from value
			return str_replace( PHP_EOL, ' ', strval( $value ) );
		}

		if ( is_array( $value ) ) {
			return implode( ', ', array_map( array( $this, 'format_output' ), $value ) );
		}

		return '';
	}

}
