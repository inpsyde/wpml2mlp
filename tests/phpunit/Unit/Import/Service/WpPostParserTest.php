<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	SimpleXMLElement,
	DateTimeZone;

/**
 * @group post_parser
 */
class WpPostParserTest extends Helper\MonkeyTestCase {

	/**
	 * Test the parsing of a XML that is considered valid.
	 *
	 * @dataProvider parse_post_test_data
	 *
	 * @param SimpleXMLElement $item
	 * @param array $expected
	 */
	public function test_parse_post_valid_item( SimpleXMLElement $item, Array $expected ) {

		$testee = new Service\WpPostParser(
			$this->mock_builder->common_wp_factory()
		);

		$result = $testee->parse_post( $item );

		$this->assertInstanceOf(
			'W2M\Import\Type\ImportPostInterface',
			$result
		);

		foreach ( $expected[ 'post' ] as $method => $value ) {
			if ( 'date' === $method )
				continue;
			$this->assertSame(
				$value,
				$result->{$method}(),
				"Test failed for method '{$method}'"
			);
		}

		$this->assertInstanceOf(
			'DateTime',
			$result->date()
		);
		$result->date()->setTimezone( new DateTimeZone( 'UTC' ) );
		$this->assertSame(
			$expected[ 'post' ][ 'date' ],
			$result->date()->format( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * @see test_parse_post_valid_item
	 * @return array
	 */
	public function parse_post_test_data() {

		$data = array();

		$post = array(
			'title'                 => 'This is the post title',
			'guid'                  => 'http://wpml.to.mlp/?p=4736',
			'date'                  => '2014-04-23 09:45:30',
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'type'                  => 'post',
			'is_sticky'             => 0,
			'origin_link'           => 'http://wpml.to.mlp/this-is-the-post-title/',
			'excerpt'               => 'Some Excerpt',
			'content'               => 'Some Content',
			'name'                  => 'this-is-the-post-title',
			'status'                => 'publish',
			'origin_parent_post_id' => 0,
			'menu_order'            => 0,
			'password'              => ''
		);

		$xml = <<<XML
<root
	xmlns:wp="wp"
	xmlns:excerpt="excerpt"
	xmlns:content="content"
	xmlns:dc="dc"
	>
	<item>
		<title>{$post[ 'title' ]}</title>
		<link>http://wpml.to.mlp/this-is-the-post-title/</link>
		<pubDate><![CDATA[{$post[ 'date' ]}]]></pubDate>
		<dc:creator><![CDATA[]]></dc:creator>
		<guid isPermaLink="false">{$post[ 'guid' ]}</guid>
		<excerpt:encoded><![CDATA[{$post[ 'excerpt' ]}]]></excerpt:encoded>
		<content:encoded><![CDATA[{$post[ 'content' ]}]]></content:encoded>
		<wp:post_id>4736</wp:post_id>
		<wp:post_date><![CDATA[{$post[ 'date' ]}]]></wp:post_date>
		<wp:post_date_gmt><![CDATA[{$post[ 'date' ]}]]></wp:post_date_gmt>
		<wp:comment_status><![CDATA[{$post[ 'comment_status' ]}]]></wp:comment_status>
		<wp:ping_status><![CDATA[{$post[ 'ping_status' ]}]]></wp:ping_status>
		<wp:post_name><![CDATA[{$post[ 'name' ]}]]></wp:post_name>
		<wp:status><![CDATA[{$post[ 'status' ]}]]></wp:status>
		<wp:post_parent>{$post[ 'origin_parent_post_id' ]}</wp:post_parent>
		<wp:menu_order>{$post[ 'menu_order' ]}</wp:menu_order>
		<wp:post_type><![CDATA[{$post[ 'type' ]}]]></wp:post_type>
		<wp:post_password><![CDATA[{$post[ 'password' ]}]]></wp:post_password>
		<wp:is_sticky>{$post[ 'is_sticky' ]}</wp:is_sticky>
		<category domain="category" nicename="ta-65-nachrichten" term_id="112"><![CDATA[TA-65 Nachrichten]]></category>

		<wp:postmeta>
			<wp:meta_key><![CDATA[_edit_lock]]></wp:meta_key>
			<wp:meta_value><![CDATA[1414579147:9]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta>
			<wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
			<wp:meta_value><![CDATA[9]]></wp:meta_value>
		</wp:postmeta>
	</item>
</root>
XML;

		$post[ 'is_sticky' ] = (bool) $post[ 'is_sticky' ];

		$data[ 'valid_post' ] = array(
			# 1. Parameter $item
			new SimpleXMLElement( $xml ),
			# 2. Parameter
			array(
				'post' => $post
			)
		);

		return $data;
	}
}
