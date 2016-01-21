<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	SimpleXMLElement,
	Brain;

class WpUserParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider import_user_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_import_user( SimpleXMLElement $document, Array $expected ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_user_error' )
			->never();

		$testee = new Service\WpUserParser;
		$result = $testee->parse_user( $document );

		$this->assertInstanceOf(
			'W2M\Import\Type\ImportUserInterface',
			$result
		);
		foreach ( $expected[ 'user_data' ] as $method => $value ) {
			$this->assertSame(
				$value,
				$result->{$method}(),
				"Test failed for method '{$method}'"
			);
		}
	}

	/**
	 * @see test_import_user
	 * @return array
	 */
	public function import_user_test_data() {

		$data = [];

		/**
		 * Valid user, root namespace
		 */
		$user_data = [
			'origin_id'    => 9,
			'login'        => 'john',
			'email'        => 'john.doe@mail.tld',
			'first_name'   => 'John',
			'last_name'    => 'Doe',
			'display_name' => 'John Doe'
		];
		$xml = <<<XML
<root
	xmlns:wp="wp"
	>
	<wp:author>
		<wp:author_id>{$user_data[ 'origin_id' ]}</wp:author_id>
		<wp:author_login><![CDATA[{$user_data[ 'login' ]}]]></wp:author_login>
		<wp:author_email><![CDATA[{$user_data[ 'email' ]}]]></wp:author_email>
		<wp:author_display_name><![CDATA[{$user_data[ 'display_name' ]}]]></wp:author_display_name>
		<wp:author_first_name><![CDATA[{$user_data[ 'first_name' ]}]]></wp:author_first_name>
		<wp:author_last_name><![CDATA[{$user_data[ 'last_name' ]}]]></wp:author_last_name>
	</wp:author>
</root>
XML;

		$data[ 'valid_user_root_ns' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $xml ),
			# 2. Parameter $expected
			[
				'user_data' => $user_data
			]
		];

		return $data;
	}

	public function test_errors() {

		/**
		 * Todo: implement test for invalid xml to check correct error propagation
		 */
		$this->markTestIncomplete();
	}
}
