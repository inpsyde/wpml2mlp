<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service\Parser;

use
	W2M\Import\Service,
	W2M\Import\Type,
	W2M\Test\Helper,
	Brain,
	SimpleXMLElement,
	DateTimeZone;
use W2M\Test\Helper\MonkeyTestCase;

class WpPostParserTest extends Helper\MonkeyTestCase {

	public function setUp() {

		parent::setUp();

		Brain\Monkey::functions()
			->when( 'maybe_unserialize' )
			->returnArg( 1 );
	}

	/**
	 * Test the parsing of a XML that is considered valid.
	 *
	 * @dataProvider parse_post_test_data
	 *
	 * @param SimpleXMLElement $item
	 * @param array $expected
	 */
	public function test_parse_post_valid_item( SimpleXMLElement $item, Array $expected ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_post_error' )
			->never();

		$factory_mock = $this->mock_builder->common_wp_factory();
		$factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$testee = new Service\Parser\WpPostParser(
			$factory_mock
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

		// terms
		$this->assertInternalType(
			'array',
			$result->terms()
		);
		foreach ( $result->terms() as $term_reference ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\TermReferenceInterface',
				$term_reference
			);
		}

		// meta
		$actual_meta = $result->meta();
		$this->assertInternalType(
			'array',
			$actual_meta
		);
		$this->assertCount(
			count( $expected[ 'meta' ] ),
			$actual_meta
		);
		foreach ( $actual_meta as $meta ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportMetaInterface',
				$meta
			);
		}

		// locale relations
		$this->assertInternalType(
			'array',
			$result->locale_relations()
		);

		foreach ( $result->locale_relations() as $relation ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\LocaleRelationInterface',
				$relation
			);
		}
	}

	/**
	 * @see test_parse_post_valid_item
	 * @see test_parse_post_terms
	 * @see test_parse_post_meta
	 * @see test_parse_locale_relations
	 * @return array
	 */
	public function parse_post_test_data() {

		$data = array();

		/**
		 * Valid post
		 */
		$post = array(
			'origin_id'             => 4736,
			'title'                 => 'This is the post title',
			'guid'                  => 'http://wpml.to.mlp/?p=4736',
			'date'                  => '2014-04-23 09:45:30',
			'origin_author_id'      => 4,
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
			'password'              => '',

		);

		$xml = <<<XML
<root
	xmlns:wp="urn:wp"
	xmlns:excerpt="urn:excerpt"
	xmlns:content="urn:content"
	xmlns:dc="urn:dc"
	>
	<item>
		<title>{$post[ 'title' ]}</title>
		<link>{$post['origin_link']}</link>
		<pubDate><![CDATA[{$post[ 'date' ]}]]></pubDate>
		<dc:creator><![CDATA[]]></dc:creator>
		<guid isPermaLink="false">{$post[ 'guid' ]}</guid>
		<excerpt:encoded><![CDATA[{$post[ 'excerpt' ]}]]></excerpt:encoded>
		<content:encoded><![CDATA[{$post[ 'content' ]}]]></content:encoded>
		<wp:post_id>{$post[ 'origin_id' ]}</wp:post_id>
		<wp:post_date><![CDATA[{$post[ 'date' ]}]]></wp:post_date>
		<wp:post_date_gmt><![CDATA[{$post[ 'date' ]}]]></wp:post_date_gmt>
		<wp:post_author><![CDATA[{$post[ 'origin_author_id' ]}]]></wp:post_author>
		<wp:comment_status><![CDATA[{$post[ 'comment_status' ]}]]></wp:comment_status>
		<wp:ping_status><![CDATA[{$post[ 'ping_status' ]}]]></wp:ping_status>
		<wp:post_name><![CDATA[{$post[ 'name' ]}]]></wp:post_name>
		<wp:status><![CDATA[{$post[ 'status' ]}]]></wp:status>
		<wp:post_parent>{$post[ 'origin_parent_post_id' ]}</wp:post_parent>
		<wp:menu_order>{$post[ 'menu_order' ]}</wp:menu_order>
		<wp:post_type><![CDATA[{$post[ 'type' ]}]]></wp:post_type>
		<wp:post_password><![CDATA[{$post[ 'password' ]}]]></wp:post_password>
		<wp:is_sticky>{$post[ 'is_sticky' ]}</wp:is_sticky>
		<category domain="category" nicename="news" term_id="112"><![CDATA[News]]></category>
		<category domain="post_tag" nicename="some-tag" term_id="98"><![CDATA[Some tag]]></category>

		<wp:postmeta>
			<wp:meta_key><![CDATA[_edit_lock]]></wp:meta_key>
			<wp:meta_value><![CDATA[1414579147:9]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta>
			<wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
			<wp:meta_value><![CDATA[9]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta>
			<wp:meta_key><![CDATA[multiple_values]]></wp:meta_key>
			<wp:meta_value><![CDATA[foo]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta>
			<wp:meta_key><![CDATA[multiple_values]]></wp:meta_key>
			<wp:meta_value><![CDATA[bar]]></wp:meta_value>
		</wp:postmeta>

		<wp:translation>
			<wp:locale><![CDATA[en_US]]></wp:locale>
			<wp:element_id>44330</wp:element_id>
		</wp:translation>

		<wp:translation>
			<wp:locale><![CDATA[nl_NL]]></wp:locale>
			<wp:element_id>57664</wp:element_id>
		</wp:translation>
	</item>
</root>
XML;

		$post[ 'is_sticky' ] = (bool) $post[ 'is_sticky' ];

		$data[ 'valid_post' ] = array(
			# 1. Parameter $item
			new SimpleXMLElement( $xml ),
			# 2. Parameter
			array(
				'post' => $post,
				'terms' => array(
					array( 'origin_id' => 112, 'taxonomy' => 'category' ),
					array( 'origin_id' => 98, 'taxonomy' => 'post_tag' )
				),
				'meta' => array(
					array( 'key' => '_edit_lock', 'value' => '1414579147:9', 'is_single' => TRUE ),
					array( 'key' => '_edit_last', 'value' => '9', 'is_single' => TRUE ),
					array( 'key' => 'multiple_values', 'value' => array( 'foo', 'bar' ), 'is_single' => FALSE )
				),
				'locale_relations' => array(
					array( 'locale' => 'en_US', 'origin_id' => 44330 ),
					array( 'locale' => 'nl_NL', 'origin_id' => 57664 )
				)
			)
		);


		/**
		 * valid attachment
		 */
		$post = array(
			'origin_id'             => 4096,
			'title'                 => 'hello-world-2.jpeg',
			'guid'                  => 'http://wpml.to.mlp/wp-content/uploads/2013/10/hello-world-2.jpeg',
			'date'                  => '2013-10-27 20:13:05',
			'origin_author_id'      => 1,
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'type'                  => 'attachment',
			'is_sticky'             => 0,
			'origin_link'           => 'http://wpml.to.mlp/hello-world/',
			'excerpt'               => '',
			'content'               => 'http://wpml.to.mlp/wp-content/uploads/2013/10/hello-world-2.jpeg',
			'name'                  => 'hello-world-2',
			'status'                => 'inherit',
			'origin_parent_post_id' => 0,
			'menu_order'            => 0,
			'password'              => '',
			'origin_attachment_url' => 'http://wpml.to.mlp/wp-content/uploads/2015/03/hello-world.jpeg'
		);

		$xml = <<<XML
<root
	xmlns:wp="urn:wp"
	xmlns:excerpt="urn:excerpt"
	xmlns:content="urn:content"
	xmlns:dc="urn:dc"
	>
	<item>
			<title>{$post['title']}</title>
			<link>{$post['origin_link']}</link>
			<pubDate><![CDATA[{$post['date']}]]></pubDate>
			<dc:creator><![CDATA[]]></dc:creator>
			<guid isPermaLink="false">{$post['guid']}</guid>
			<excerpt:encoded><![CDATA[{$post['excerpt']}]]></excerpt:encoded>
			<content:encoded><![CDATA[{$post['content']}]]></content:encoded>
			<wp:post_id>{$post['origin_id']}</wp:post_id>
			<wp:post_date><![CDATA[]]></wp:post_date>
			<wp:post_date_gmt><![CDATA[{$post['date']}]]></wp:post_date_gmt>
			<wp:post_author><![CDATA[{$post[ 'origin_author_id' ]}]]></wp:post_author>
			<wp:comment_status><![CDATA[{$post['comment_status']}]]></wp:comment_status>
			<wp:ping_status><![CDATA[{$post['ping_status']}]]></wp:ping_status>
			<wp:post_name><![CDATA[{$post['name']}]]></wp:post_name>
			<wp:status><![CDATA[{$post['status']}]]></wp:status>
			<wp:post_parent>{$post['origin_parent_post_id']}</wp:post_parent>
			<wp:menu_order>{$post['menu_order']}</wp:menu_order>
			<wp:post_type><![CDATA[{$post['type']}]]></wp:post_type>
			<wp:post_password><![CDATA[{$post['password']}]]></wp:post_password>
			<wp:is_sticky>{$post['is_sticky']}</wp:is_sticky>
			<wp:attachment_url><![CDATA[{$post['origin_attachment_url']}]]></wp:attachment_url>

			<wp:postmeta>
				<wp:meta_key><![CDATA[wpml_media_processed]]></wp:meta_key>
				<wp:meta_value><![CDATA[1]]></wp:meta_value>
			</wp:postmeta>

			<wp:postmeta>
				<wp:meta_key><![CDATA[_wp_attached_file]]></wp:meta_key>
				<wp:meta_value><![CDATA[2013/10/hello-world-2.jpeg]]></wp:meta_value>
			</wp:postmeta>

			<wp:translation>
				<wp:locale><![CDATA[en_US]]></wp:locale>
				<wp:element_id>4096</wp:element_id>
			</wp:translation>

		</item>
</root>
XML;

		$post[ 'is_sticky' ] = (bool) $post[ 'is_sticky' ];

		$data[ 'valid_attachment' ] = array(
			# 1. Parameter $item
			new SimpleXMLElement( $xml ),
			# 2. Parameter
			array(
				'post' => $post,
				'terms' => [],
				'meta' => [
					[ 'key' => 'wpml_media_processed', 'value' => '1', 'is_single' => TRUE ],
					[ 'key' => '_wp_attached_file', 'value' => '2013/10/hello-world-2.jpeg', 'is_single' => TRUE ]
				],
				'locale_relations' => [
					[ 'locale' => 'en_US', 'origin_id' => 4096 ],
				]
			)
		);


		/**
		 * valid post with local namespace attributes
		 *
		 */
		$post = array(
			'origin_id'             => 1252,
			'title'                 => 'This is the post title',
			'guid'                  => 'http://wpml.to.mlp/?p=4736',
			'date'                  => '2014-04-23 09:45:30',
			'origin_author_id'      => 655,
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
			'password'              => '',

		);

		$xml = <<<XML
<root>
	<item>
		<title>{$post[ 'title' ]}</title>
		<link>{$post['origin_link']}</link>
		<pubDate><![CDATA[{$post[ 'date' ]}]]></pubDate>
		<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/"><![CDATA[]]></dc:creator>
		<guid isPermaLink="false">{$post[ 'guid' ]}</guid>
		<excerpt:encoded xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"><![CDATA[{$post[ 'excerpt' ]}]]></excerpt:encoded>
		<content:encoded xmlns:content="http://purl.org/rss/1.0/modules/content/"><![CDATA[{$post[ 'content' ]}]]></content:encoded>
		<wp:post_id xmlns:wp="http://wordpress.org/export/1.2/">{$post[ 'origin_id' ]}</wp:post_id>
		<wp:post_date xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'date' ]}]]></wp:post_date>
		<wp:post_date_gmt xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'date' ]}]]></wp:post_date_gmt>
		<wp:post_author xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'origin_author_id' ]}]]></wp:post_author>
		<wp:comment_status xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'comment_status' ]}]]></wp:comment_status>
		<wp:ping_status xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'ping_status' ]}]]></wp:ping_status>
		<wp:post_name xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'name' ]}]]></wp:post_name>
		<wp:status xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'status' ]}]]></wp:status>
		<wp:post_parent xmlns:wp="http://wordpress.org/export/1.2/">{$post[ 'origin_parent_post_id' ]}</wp:post_parent>
		<wp:menu_order xmlns:wp="http://wordpress.org/export/1.2/">{$post[ 'menu_order' ]}</wp:menu_order>
		<wp:post_type xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'type' ]}]]></wp:post_type>
		<wp:post_password xmlns:wp="http://wordpress.org/export/1.2/"><![CDATA[{$post[ 'password' ]}]]></wp:post_password>
		<wp:is_sticky xmlns:wp="http://wordpress.org/export/1.2/">{$post[ 'is_sticky' ]}</wp:is_sticky>
		<category domain="category" nicename="news" term_id="112"><![CDATA[News]]></category>
		<category domain="post_tag" nicename="some-tag" term_id="98"><![CDATA[Some tag]]></category>

		<wp:postmeta xmlns:wp="http://wordpress.org/export/1.2/">
			<wp:meta_key><![CDATA[_edit_lock]]></wp:meta_key>
			<wp:meta_value><![CDATA[1414579147:9]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta xmlns:wp="http://wordpress.org/export/1.2/">
			<wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
			<wp:meta_value><![CDATA[9]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta xmlns:wp="http://wordpress.org/export/1.2/">
			<wp:meta_key><![CDATA[multiple_values]]></wp:meta_key>
			<wp:meta_value><![CDATA[foo]]></wp:meta_value>
		</wp:postmeta>

		<wp:postmeta xmlns:wp="http://wordpress.org/export/1.2/">
			<wp:meta_key><![CDATA[multiple_values]]></wp:meta_key>
			<wp:meta_value><![CDATA[bar]]></wp:meta_value>
		</wp:postmeta>

		<wp:translation xmlns:wp="http://wordpress.org/export/1.2/">
			<wp:locale><![CDATA[en_US]]></wp:locale>
			<wp:element_id>44330</wp:element_id>
		</wp:translation>

		<wp:translation xmlns:wp="http://wordpress.org/export/1.2/">
			<wp:locale><![CDATA[nl_NL]]></wp:locale>
			<wp:element_id>57664</wp:element_id>
		</wp:translation>
	</item>
</root>
XML;

		$post[ 'is_sticky' ] = (bool) $post[ 'is_sticky' ];

		$data[ 'valid_post_local_namespace_references' ] = array(
			# 1. Parameter $item
			new SimpleXMLElement( $xml ),
			# 2. Parameter
			array(
				'post' => $post,
				'terms' => array(
					array( 'origin_id' => 112, 'taxonomy' => 'category' ),
					array( 'origin_id' => 98, 'taxonomy' => 'post_tag' )
				),
				'meta' => array(
					array( 'key' => '_edit_lock', 'value' => '1414579147:9', 'is_single' => TRUE ),
					array( 'key' => '_edit_last', 'value' => '9', 'is_single' => TRUE ),
					array( 'key' => 'multiple_values', 'value' => array( 'foo', 'bar' ), 'is_single' => FALSE )
				),
				'locale_relations' => array(
					array( 'locale' => 'en_US', 'origin_id' => 44330 ),
					array( 'locale' => 'nl_NL', 'origin_id' => 57664 )
				)
			)
		);
		return $data;
	}

	/**
	 * Todo: improve test
	 */
	public function test_parse_post_missing_item() {

		$document = new SimpleXMLElement( '<root><not_item/></root>' );

		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );
		$wp_error_mock->expects( $this->once() )
			->method( 'add_data' )
			->with( $this->callback( 'is_array' ), 'item' );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_post_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpPostParser( $wp_factory_mock );

		$result = $testee->parse_post( $document );
		$this->assertNull(
			$result
		);
		$this->markTestIncomplete( 'Improve test, check the data passed to WP_Error' );
	}

	/**
	 * Todo: improve test
	 */
	public function test_parse_post_missing_wp_namespace() {

		$xml = <<<XML
<root
	xmlns:excerpt="urn:excerpt"
	xmlns:content="urn:content"
	xmlns:dc="urn:dc"
	>
	<item>
		<title>Title</title>
		<link>http://wpml.to.mlp/this-is-the-post-title/</link>
		<pubDate><![CDATA[2016-01-09 20:22:53]]></pubDate>
		<dc:creator><![CDATA[]]></dc:creator>
		<guid isPermaLink="false">http://wpml.to.mlp/?p=1234</guid>
		<excerpt:encoded><![CDATA[The excerpt]]></excerpt:encoded>
		<content:encoded><![CDATA[The content]]></content:encoded>
	</item>
</root>
XML;
		$document = new SimpleXMLElement( $xml );

		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );
		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with( $this->callback( 'is_array' ), 'namespace' );

		Brain\Monkey::actions()
			->expectAdded( 'w2m_import_parse_post_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpPostParser( $wp_factory_mock );
		$result = $testee->parse_post( $document );
		$this->assertNull(
			$result
		);

		$this->markTestIncomplete( 'Improve test, check the data passed to WP_Error' );
	}

	/**
	 * Todo: improve test
	 *
	 * @dataProvider parse_post_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_post_missing_title_attribute( SimpleXMLElement $document, Array $expected ) {

		//remove <title/> from the document
		list( $title ) = $document->xpath( '//item/title' );
		unset( $title[ 0 ] );

		$wp_error_mock   = $this->mock_builder->wp_error( array( 'add_data' ) );
		$wp_factory_mock = $this->mock_builder->common_wp_factory();

		$wp_factory_mock->expects( $this->atLeast( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );
		$wp_error_mock->expects( $this->atLeast( 1 ) )
			->method( 'add_data' )
			->with( $this->callback( 'is_array' ), 'attribute' );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_post_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpPostParser( $wp_factory_mock );
		$result = $testee->parse_post( $document );

		$this->assertInstanceOf(
			'W2M\Import\Type\ImportPostInterface',
			$result
		);

		$this->markTestIncomplete( 'Improve test, check the data passed to WP_Error' );
	}

	/**
	 * @dataProvider parse_post_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_post_terms( SimpleXMLElement $document, Array $expected ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_post_error' )
			->never();

		$testee = new Service\Parser\WpPostParser;
		$result = $testee->parse_post_terms( $document );

		$this->assertInternalType(
			'array',
			$result
		);
		// don't care about array indices
		$result = array_values( $result );

		foreach ( $result as $index => $term_reference ) {
			/* @type Type\TermReferenceInterface $term_reference */
			$this->assertInstanceOf(
				'W2M\Import\Type\TermReferenceInterface',
				$term_reference
			);
			$this->assertSame(
				$expected[ 'terms' ][ $index ][ 'origin_id' ],
				$term_reference->origin_id()
			);
			$this->assertSame(
				$expected[ 'terms' ][ $index ][ 'taxonomy' ],
				$term_reference->taxonomy()
			);
		}
	}

	/**
	 * @dataProvider parse_post_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_post_meta( SimpleXMLElement $document, Array $expected ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_post_error' )
			->never();

		$testee = new Service\Parser\WpPostParser;
		$result = $testee->parse_post_meta( $document );

		$this->assertInternalType(
			'array',
			$result
		);
		$this->assertCount(
			count( $expected[ 'meta' ] ),
			$result
		);

		foreach ( $result as $index => $meta ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportMetaInterface',
				$meta
			);
			$this->assertSame(
				$expected[ 'meta' ][ $index ][ 'key' ],
				$meta->key()
			);
			$this->assertSame(
				$expected[ 'meta' ][ $index ][ 'value' ],
				$meta->value()
			);
			$this->assertSame(
				$expected[ 'meta' ][ $index ][ 'is_single' ],
				$meta->is_single()
			);

		}
	}

	/**
	 * @dataProvider parse_post_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_locale_relations( SimpleXMLElement $document, Array $expected ) {

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_post_error' )
			->never();

		$testee = new Service\Parser\WpPostParser;
		$result = $testee->parse_locale_relations( $document );

		$this->assertInternalType(
			'array',
			$result
		);

		foreach ( $result as $index => $relation ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\LocaleRelationInterface',
				$relation
			);
			$this->assertSame(
				$expected[ 'locale_relations' ][ $index ][ 'origin_id' ],
				$relation->origin_id()
			);
			$this->assertSame(
				$expected[ 'locale_relations' ][ $index ][ 'locale' ],
				$relation->locale()
			);
		}
	}
}
