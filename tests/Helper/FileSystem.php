<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Helper;

use
	W2M\Test;

class FileSystem {

	/**
	 * @var string
	 */
	private $test_dir;

	/**
	 * @var string
	 */
	private $tmp_dir;

	/**
	 * @param string $test_dir Main test directory
	 */
	public function __construct( $test_dir = '' ) {

		if ( ! $test_dir )
			$test_dir = self::get_test_dir();

		$this->test_dir = $this->sanitize_path( $test_dir );
		$this->tmp_dir = sys_get_temp_dir() . '/w2mtest';
		// defined in phpunit.xml(.dist)
		if ( Test\TMP_DIR ) {
			$this->tmp_dir = Test\TMP_DIR;
		}
		$this->create_tmp_dir();
	}

	/**
	 * @param $rel_path
	 *
	 * @return string
	 */
	public function abs_path( $rel_path ) {

		$rel_path = $this->sanitize_rel_path( $rel_path );

		return $this->tmp_dir . "/{$rel_path}";
	}
	/**
	 * create test_dir/tmp
	 */
	public function create_tmp_dir() {

		if ( is_dir( $this->tmp_dir ) )
			return;

		mkdir( $this->tmp_dir, 0755 );
	}

	/**
	 * @param $rel_file
	 * @param $data
	 * @param null $flags
	 * @param null $context
	 *
	 * @return int
	 */
	public function file_put_contents( $rel_file, $data, $flags = NULL, $context = NULL ) {

		$rel_file = $this->sanitize_rel_path( $rel_file );
		$file = $this->tmp_dir . "/$rel_file";

		return file_put_contents( $file, $data, $flags, $context );
	}

	/**
	 * @param $rel_file
	 */
	public function delete_file( $rel_file ) {

		$rel_file = $this->sanitize_rel_path( $rel_file );
		$file = $this->tmp_dir . "/{$rel_file}";
		if ( is_file( $file ) )
			unlink( $file );
	}

	/**
	 * @param $path
	 *
	 * @return string
	 */
	public function sanitize_path( $path ) {

		$path = trim( $path );
		$path = rtrim( $path, '\\/' );

		return $path;
	}

	/**
	 * @param $rel_path
	 *
	 * @return string
	 */
	public function sanitize_rel_path( $rel_path ) {

		$rel_path = trim( $rel_path );
		$rel_path = ltrim( $rel_path, '\\/' );

		return $rel_path;
	}

	/**
	 * @return string
	 */
	public static function get_test_dir() {

		return
			dirname( // tests/
				__DIR__ // Helper/
			);
	}
}