<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Common;

use
	W2M\Import\Common,
	W2M\Test\Helper;

class FileTest extends Helper\MonkeyTestCase {

	/**
	 * @var Helper\FileSystem
	 */
	private $file_system;

	public function setUp() {

		parent::setUp();
		if ( ! $this->file_system )
			$this->file_system = new Helper\FileSystem;
	}

	public function test_get_content() {

		$content = <<<EOT
Hello World!
This is some file content.
EOT;
		$test_file = implode(
			'-',
			[
				__CLASS__,
				__FUNCTION__,
				time()
			]
		) . '.txt';
		$this->file_system->file_put_contents( $test_file, $content );

		$testee = new Common\File( $this->file_system->abs_path( $test_file ) );

		$this->assertSame(
			$content,
			$testee->get_content()
		);

		$this->file_system->delete_file( $test_file );
	}

	public function test_set_content() {

		$content = <<<EOT
Hello World!
This is some file content.
EOT;
		$test_file = implode(
			'-',
			[
				__CLASS__,
				__FUNCTION__,
				time()
			]
		) . '.txt';

		$testee = new Common\File( $this->file_system->abs_path( $test_file ) );
		$testee->set_content( $content );

		$this->assertSame(
			$content,
			$this->file_system->file_get_contents( $test_file )
		);

		$this->file_system->delete_file( $test_file );
	}

}