<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Module;

use
	W2M\Import\Module,
	W2M\Test\Helper,
	DateTime;

class JsonXmlImportReportTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider create_report_test_data
	 *
	 * @param array $data
	 */
	public function test_create_report( Array $data ) {

		$id_map_mock = $this->mock_builder->data_multi_type_id_list();
		$file_mock   = $this->mock_builder->common_file();
		$import_mock = $this->mock_builder->data_xml_import_interface();

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
		$import_mock->method( 'import_file' )
			->willReturn( $data[ 'import_file' ] );
		$import_mock->method( 'map_file' )
			->willReturn( $data[ 'map_file' ] );
		$import_mock->method( 'start_date' )
			->willReturn( $data[ 'start_date' ] );

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

		$testee = new Module\JsonXmlImportReport( $id_map_mock, $file_mock );
		$testee->create_report( $import_mock );
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
			$data[ 'start_date' ]->format( DateTime::W3C ),
			$result->date
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

		$data = [];

		$data[ 'test_1' ] = [
			# 1. parameter $data
			[
				'import_file' => '/path/to/what.ever',
				'map_file'    => '/path/to/something.else',
				'start_date'  => new DateTime( '-10 seconds' ),
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
