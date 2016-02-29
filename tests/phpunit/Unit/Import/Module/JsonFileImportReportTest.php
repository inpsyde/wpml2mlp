<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Module;

use
	W2M\Import\Common,
	W2M\Import\Module,
	W2M\Test\Helper,
	DateTime;

class JsonFileImportReportTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider create_report_test_data
	 *
	 * @param array $data
	 */
	public function test_create_report( Array $data ) {

		/**
		 * Test has to be adapted to the refactoring of Module\JsonFileReport and
		 * Type\FileImportReport …
		 */
		$this->markTestSkipped( 'Under constructuin ...' );


		$id_map_mock = $this->mock_builder->data_multi_type_id_list();
		$file_mock   = $this->mock_builder->common_file();
		$report_mock = $this->mock_builder->type_file_import_report_interface();

		$maps = $data[ 'maps' ];
		$id_map_mock->expects( $this->exactly( 4 ) )
			->method( 'id_map' )
			->withConsecutive( [ 'comment' ], [ 'post' ], [ 'term' ], [ 'user' ] )
			->will(
				$this->onConsecutiveCalls(
					$maps[ 'comments' ],
					$maps[ 'posts' ],
					$maps[ 'terms' ],
					$maps[ 'users' ]
				)
			);
		$report_mock->method( 'import_file' )
			->willReturn( $data[ 'import_file' ] );
		$report_mock->method( 'map_file' )
			->willReturn( $data[ 'map_file' ] );
		$report_mock->method( 'date' )
			->willReturn( $data[ 'date' ] );

		$test_case = $this;
		$file_mock->expects( $this->exactly( 1 ) )
			->method( 'set_content' )
			->with(
				$this->callback(
					function( $json ) use ( $test_case, $data ) {
						$test_case->report_assertions( $json, $data );

						return TRUE;
					}
				)
			);

		$testee = new Module\JsonFileImportReport( $id_map_mock, $file_mock );
		$testee->create_report();
	}

	public function report_assertions( $json, $data ) {

		$this->assertJson( $json );
		$result = json_decode( $json );

		$this->assertSame(
			$data[ 'import_file' ],
			$result->import_file
		);
		$this->assertSame(
			$data[ 'map_file' ],
			$result->map_file
		);
		$this->assertSame(
			$data[ 'date' ]->format( DateTime::W3C ),
			$result->date
		);
		$this->assertRegExp(
			'~\d+\s?s~',
			$result->runtime
		);

		$this->assertSame(
			$data[ 'maps' ][ 'comments' ],
			// It's important to use get_object_vars() here, an (array)-cast will produce string type array keys
			get_object_vars( $result->maps->comments)
		);
		$this->assertSame(
			$data[ 'maps' ][ 'posts' ],
			get_object_vars( $result->maps->posts )
		);
		$this->assertSame(
			$data[ 'maps' ][ 'terms' ],
			get_object_vars( $result->maps->terms )
		);
		$this->assertSame(
			$data[ 'maps' ][ 'users' ],
			get_object_vars( $result->maps->users )
		);
	}

	/**
	 * @see test_create_report
	 */
	public function create_report_test_data() {

		// DataProvider runs before setUp() :<
		if ( ! $this->mock_builder )
			$this->mock_builder = new Helper\MockBuilder( $this );

		$data = [];

		$data[ 'test_1' ] = [
			# 1. parameter $data
			[
				'import_file' => $this->mock_builder->common_file( [], [ 'name' => '/path/to/what.ever' ] ),
				'map_file'    => $this->mock_builder->common_file( [], [ 'name' => '/path/to/something.else' ] ),
				'date'        => new DateTime( '-10 seconds' ),
				'maps' => [
					'comments' => [
						1 => 2,
						2 => 3
					],
					'posts' => [
						3   => 9,
						54  => 194,
						145 => 1467
					],
					'terms' => [
						2   => 10,
						46  => 512,
						153 => 167
					],
					'users' => [
						22  => 100,
						42  => 52,
						123 => 231
					]
				]
			]
		];

		return $data;
	}
}
