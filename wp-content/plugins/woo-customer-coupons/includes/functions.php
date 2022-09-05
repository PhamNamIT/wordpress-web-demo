<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Function include all files in folder
 *
 * @param $path   Directory address
 * @param $ext    array file extension what will include
 * @param $prefix string Class prefix
 */
if ( ! function_exists( 'vi_include_folder' ) ) {
	function vi_include_folder( $path, $prefix = '', $ext = array( 'php' ) ) {

		/*Include all files in payment folder*/
		if ( ! is_array( $ext ) ) {
			$ext = explode( ',', $ext );
			$ext = array_map( 'trim', $ext );
		}
		$sfiles = scandir( $path );
		foreach ( $sfiles as $sfile ) {
			if ( $sfile != '.' && $sfile != '..' ) {
				if ( is_file( $path . "/" . $sfile ) ) {
					$ext_file  = pathinfo( $path . "/" . $sfile );
					$file_name = $ext_file['filename'];
					if ( $ext_file['extension'] ) {
						if ( in_array( $ext_file['extension'], $ext ) ) {
							$class = preg_replace( '/\W/i', '_', $prefix . ucfirst( $file_name ) );

							if ( ! class_exists( $class ) ) {
								require_once $path . $sfile;
								if ( class_exists( $class ) ) {
									new $class;
								}
							}
						}
					}
				}
			}
		}
	}
}

if ( ! function_exists( 'vi_stripslashes_deep' ) ) {
	function vi_stripslashes_deep( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'vi_stripslashes_deep', $value );
		} else {
			$value = sanitize_text_field( wp_unslash( $value ) );
		}
		return $value;
	}
}
if ( ! function_exists( 'vi_stripslashes_deep_kses' ) ) {
	function vi_stripslashes_deep_kses( $value ) {
		if ( is_array( $value ) ) {
			$value = array_map( 'vi_stripslashes_deep_kses', $value );
		} else {
			$value = wp_kses_post( wp_unslash( $value ) );
		}
		return $value;
	}
}



