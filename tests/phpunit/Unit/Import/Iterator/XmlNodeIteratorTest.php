<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Iterator;

use
	W2M\Import\Iterator,
	W2M\Test\Helper;

class XmlNodeIteratorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Helper\FileSystem
	 */
	private $fs_helper;

	/**
	 * @var array
	 */
	private $test_files = array();

	public function setUp() {

		if ( ! $this->fs_helper )
			$this->fs_helper = new Helper\FileSystem;
	}

	public function tearDown() {

		foreach ( $this->test_files as $k => $file ) {
			$this->fs_helper->delete_file( $file );
			unset( $this->test_files[ $k ] );
		}
	}

	/**
	 * @dataProvider iteration_test_data
	 *
	 * @param $xml
	 * @param $node_name
	 * @param $expected
	 */
	public function test_iteration( $xml, $node_name, $expected ) {

		$test_file = __FUNCTION__ . '-' . time() . '.xml';
		$abs_test_file = $this->fs_helper->abs_path( $test_file );

		$this->fs_helper->file_put_contents(
			$test_file,
			$xml
		);
		$this->test_files[] = $test_file;

		$testee = new Iterator\XmlNodeIterator( $abs_test_file, $node_name );
		$index = 0;
		while ( $testee->valid() ) {

			$this->assertXmlStringEqualsXmlString(
				$expected[ $index ],
				$testee->current()
			);
			$index++;
		}
	}

	public function iteration_test_data() {

		$data = array();

		/**
		 * single_level_nodes
		 */
		$xml = <<<XML
<root>
	<item>
		<id>1</id>
		<title>Hello World!</title>
	</item>
	<item>
		<id>2</id>
		<title>Lorem Ipsum</title>
	</item>
</root>
XML;

		$data[ 'single_level_nodes' ] = array(
			#1. parameter $xml
			$xml,
			#2. parameter $node_name
			'item',
			#3. parameter $expected output
			array(
				'<item><id>1</id><title>Hello World!</title></item>',
				'<item><id>2</id><title>Lorem Ipsum</title></item>'
			)
		);

		/**
		 * nested_div_elements
		 */
		$xml = <<<XML
<root>
	<div>
		<p>Test nested nodes</p>
		<div>
			<p>Am I here?</p>
		</div>
	</div>
	<div>
		<p>Lorem Ipsum</p>
	</div>
</root>
XML;
		$data[ 'nested_div_elements' ] = array(
			#1. parameter $xml
			$xml,
			#2. parameter $node_name
			'div',
			#3. parameter $expected output
			array(
				'<div><p>Test nested nodes</p><div><p>Am I here?</p></div></div>',
				'<div><p>Am I here?</p></div>',
				'<div><p>Lorem Ipsum</p></div>'
			)
		);

		/**
		 * single_level_nodes_with_local_ns
		 */
		$xml = <<<XML
<root
	xmlns:wp="urn:wp"
>
	<node>
		<wp:title>The first title</wp:title>
		<wp:excerpt>Some excerpt</wp:excerpt>
	</node>
	<node>
		<wp:title>Another title</wp:title>
		<wp:excerpt>another excerpt</wp:excerpt>
	</node>
</root>
XML;

		$data[ 'single_level_nodes_with_local_ns' ] = array(
			#1. parameter $xml
			$xml,
			#2. parameter $node_name
			'node',
			#3. parameter $expected output
			array(
				'<node xmlns:wp="urn:wp"><wp:title>The first title</wp:title>'
				. '<wp:excerpt>Some excerpt</wp:excerpt></node>',

				'<node xmlns:wp="urn:wp"><wp:title>Another title</wp:title>'
				. '<wp:excerpt>another excerpt</wp:excerpt></node>'
			)
		);

		/**
		 * two_level_nodes_with_ns
		 */
		$xml = <<<XML
<root
	xmlns:wp="urn:wp"
>
	<wp:node>
		<wp:title>The first title</wp:title>
		<wp:excerpt>Some excerpt</wp:excerpt>
	</wp:node>
	<wp:node>
		<wp:title>Another title</wp:title>
		<wp:excerpt>another excerpt</wp:excerpt>
	</wp:node>
</root>
XML;

		$data[ 'two_level_nodes_with_ns' ] = array(
			#1. parameter $xml
			$xml,
			#2. parameter $node_name
			'wp:node',
			#3. parameter $expected output
			array(
				'<wp:node xmlns:wp="urn:wp"><wp:title>The first title</wp:title>'
				. '<wp:excerpt>Some excerpt</wp:excerpt></wp:node>',

				'<wp:node xmlns:wp="urn:wp"><wp:title>Another title</wp:title>'
				. '<wp:excerpt>another excerpt</wp:excerpt></wp:node>'
			)
		);

		return $data;
	}
}
