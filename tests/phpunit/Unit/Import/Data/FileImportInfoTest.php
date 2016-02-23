<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Data;

use
	W2M\Import\Data,
	W2M\Test\Helper,
	Brain,
	DateTime;

class FileImportInfoTest extends Helper\MonkeyTestCase {

	public function setUp() {

		parent::setUp();
		Brain\Monkey::functions()
			->when( 'get_current_blog_id' )
			->justReturn( 1 );

	}

	public function test_start_date() {

		$date   = new DateTime;
		$testee = new Data\FileImportInfo( '', 1, $date );
		$this->assertSame(
			$date,
			$testee->start_date()
		);
	}

	public function test_implicit_start_date() {

		$testee = new Data\FileImportInfo( '' );
		$this->assertInstanceOf(
			'DateTime',
			$testee->start_date()
		);
	}

	public function test_import_blog_id() {

		$blog_id = 5;
		$testee  = new Data\FileImportInfo( '', $blog_id );
		$this->assertSame(
			$blog_id,
			$testee->import_blog_id()
		);
	}

	public function test_implicit_blog_id() {

		$blog_id = 10;
		Brain\Monkey::functions()
			->when( 'get_current_blog_id' )
			->justReturn( $blog_id );
		$testee = new Data\FileImportInfo( '' );
		$this->assertSame(
			$blog_id,
			$testee->import_blog_id()
		);
	}

	public function test_import_file() {

		$file   = '/path/to/what.ever';
		$testee = new Data\FileImportInfo( $file );
		$this->assertSame(
			$file,
			$testee->import_file()
		);
	}

	public function test_map_file() {

		$map_file = '/path/to/what.ever';
		$testee   = new Data\FileImportInfo( '', 1, new DateTime, $map_file );

		$this->assertSame(
			$map_file,
			$testee->map_file()
		);
	}

	public function test_empty_map_file() {

		$testee   = new Data\FileImportInfo( '' );
		$this->assertSame(
			'',
			$testee->map_file()
		);
	}

	public function test_complete_set() {

		$import_file = '/path/to/what.ever';
		$blog_id     = 2;
		$date        = new DateTime;
		$map_file    = '/path/to/something.else';
		$testee      = new Data\FileImportInfo(
			$import_file,
			$blog_id,
			$date,
			$map_file
		);

		$this->assertSame(
			$import_file,
			$testee->import_file()
		);
		$this->assertSame(
			$blog_id,
			$testee->import_blog_id()
		);
		$this->assertSame(
			$date,
			$testee->start_date()
		);
		$this->assertSame(
			$map_file,
			$testee->map_file()
		);
	}
}
