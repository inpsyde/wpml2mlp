<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service\Parser;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	SimpleXMLElement,
	Brain;

class WpCommentParserTest extends Helper\MonkeyTestCase {

	/**
	 * @dataProvider parse_comment_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_comment( SimpleXMLElement $document, Array $expected ) {

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_comment_error' )
			->never();

		$testee = new Service\Parser\WpCommentParser( $wp_factory_mock );
		$result = $testee->parse_comment( $document );

		$this->assertInstanceOf(
			'W2M\Import\Type\ImportCommentInterface',
			$result
		);
		foreach ( $expected[ 'comment_data' ] as $method => $value ) {
			$this->assertSame(
				$value,
				$result->{$method}(),
				"Test failed for method '{$method}'"
			);
		}

		// check Date
		$this->assertSame(
			$expected[ 'date_str_gmt' ],
			$result->date()
				->format( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * @see test_parse_comment
	 * @return array
	 */
	public function parse_comment_test_data() {

		$data = [];

		/**
		 * Valid XML, global namespace
		 */
		$comment_data = [
			'origin_id'                => 171159,
			'origin_post_id'           => 4543,
			'author_name'              => 'test',
			'author_email'             => 'test@mail.tld',
			'author_url'               => 'https://test.tld',
			'author_ip'                => '127.0.0.1',
			'content'                  => 'Lorem Ipsum',
			'karma'                    => 0,
			'approved'                 => '1',
			'agent'                    => 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
			'type'                     => 'comment',
			'origin_parent_comment_id' => 0,
			'origin_user_id'           => 1
		];
		$date_str_gmt = '2016-01-13 17:07:32';
		$date_str     = '1999-05-23 03:45:36';

		$xml = <<<XML
<root
	xmlns:wp="urn:wp"
	>
	<wp:comment>
		<wp:comment_ID><![CDATA[{$comment_data['origin_id']}]]></wp:comment_ID>
		<wp:comment_post_ID><![CDATA[{$comment_data['origin_post_id']}]]></wp:comment_post_ID>
		<wp:comment_author><![CDATA[{$comment_data['author_name']}]]></wp:comment_author>
		<wp:comment_author_email><![CDATA[{$comment_data['author_email']}]]></wp:comment_author_email>
		<wp:comment_author_url><![CDATA[{$comment_data['author_url']}]]></wp:comment_author_url>
		<wp:comment_author_IP><![CDATA[{$comment_data['author_ip']}]]></wp:comment_author_IP>
		<wp:comment_date><![CDATA[{$date_str}]]></wp:comment_date>
		<wp:comment_date_gmt><![CDATA[{$date_str_gmt}]]></wp:comment_date_gmt>
		<wp:comment_content><![CDATA[{$comment_data['content']}]]></wp:comment_content>
		<wp:comment_karma><![CDATA[{$comment_data['karma']}]]></wp:comment_karma>
		<wp:comment_approved><![CDATA[{$comment_data['approved']}]]></wp:comment_approved>
		<wp:comment_agent><![CDATA[{$comment_data['agent']}]]></wp:comment_agent>
		<wp:comment_type><![CDATA[{$comment_data['type']}]]></wp:comment_type>
		<wp:comment_parent>{$comment_data['origin_parent_comment_id']}</wp:comment_parent>
		<wp:user_id><![CDATA[{$comment_data['origin_user_id']}]]></wp:user_id>
	</wp:comment>
</root>
XML;

		$data[ 'valid_xml_global_ns' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $xml ),
			# 2. Parameter $expected
			[
				'comment_data' => $comment_data,
				'date_str_gmt' => $date_str_gmt,
				'date_str'     => $date_str
			]
		];

		/**
		 * Valid xml, local namespace
		 */
		$comment_data = [
			'origin_id'                => 171160,
			'origin_post_id'           => 32,
			'author_name'              => 'another test',
			'author_email'             => 'foo@wpml.to.mlp',
			'author_url'               => 'https://wpml.to.mlp/foo',
			'author_ip'                => '192.168.0.1',
			'content'                  => 'Lorem Ipsum dolor sit',
			'karma'                    => -3,
			'approved'                 => '0',
			'agent'                    => 'Spambot/1.0',
			'type'                     => 'pint',
			'origin_parent_comment_id' => 171159,
			'origin_user_id'           => 0
		];
		$date_str_gmt = '2015-12-31 23:59:58';
		$date_str     = '1999-05-23 03:45:36';

		$xml = <<<XML
<root>
	<wp:comment xmlns:wp="http://wordpress.org/export/1.2/">
		<wp:comment_ID><![CDATA[{$comment_data['origin_id']}]]></wp:comment_ID>
		<wp:comment_post_ID><![CDATA[{$comment_data['origin_post_id']}]]></wp:comment_post_ID>
		<wp:comment_author><![CDATA[{$comment_data['author_name']}]]></wp:comment_author>
		<wp:comment_author_email><![CDATA[{$comment_data['author_email']}]]></wp:comment_author_email>
		<wp:comment_author_url><![CDATA[{$comment_data['author_url']}]]></wp:comment_author_url>
		<wp:comment_author_IP><![CDATA[{$comment_data['author_ip']}]]></wp:comment_author_IP>
		<wp:comment_date><![CDATA[{$date_str}]]></wp:comment_date>
		<wp:comment_date_gmt><![CDATA[{$date_str_gmt}]]></wp:comment_date_gmt>
		<wp:comment_content><![CDATA[{$comment_data['content']}]]></wp:comment_content>
		<wp:comment_karma><![CDATA[{$comment_data['karma']}]]></wp:comment_karma>
		<wp:comment_approved><![CDATA[{$comment_data['approved']}]]></wp:comment_approved>
		<wp:comment_agent><![CDATA[{$comment_data['agent']}]]></wp:comment_agent>
		<wp:comment_type><![CDATA[{$comment_data['type']}]]></wp:comment_type>
		<wp:comment_parent>{$comment_data['origin_parent_comment_id']}</wp:comment_parent>
		<wp:user_id><![CDATA[{$comment_data['origin_user_id']}]]></wp:user_id>
	</wp:comment>
</root>
XML;

		$data[ 'valid_xml_local_ns' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $xml ),
			# 2. Parameter $expected
			[
				'comment_data' => $comment_data,
				'date_str_gmt' => $date_str_gmt,
				'date_str'     => $date_str
			]
		];

		return $data;
	}
	public function test_parse_comment_missing_namespace_error() {

		$xml = <<<XML
<root
	xmlns:notwp="urn:not-wp"
	>
	<notwp:comment />
</root>
XML;

		$document = new SimpleXMLElement( $xml );

		$wp_error_mock = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_error_mock->expects( $this->exactly( 1 ) )
			->method( 'add_data' )
			->with(
				$this->callback(
					function ( $parameter ) use ( $document ) {

						return 'wp' === $parameter[ 'data' ][ 'namespace' ]
						&& $document === $parameter[ 'data' ][ 'document' ];
					}
				),
				'namespace'
			);

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->exactly( 1 ) )
			->method( 'wp_error' )
			->with(
				'namespace',
				$this->callback( 'is_string' )
			)
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_comment_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpCommentParser( $wp_factory_mock );
		$this->assertNull(
			$testee->parse_comment( $document )
		);
	}

	public function test_parse_comment_missing_item_error() {

		$xml = <<<XML
<root
	xmlns:wp="urn:wp"
	>
	<wp:notComment />
</root>
XML;

		$document = new SimpleXMLElement( $xml );

		$wp_error_mock = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_error_mock->expects( $this->exactly( 1 ) )
			->method( 'add_data' )
			->with(
				$this->callback(
					function ( $parameter ) use ( $document ) {

						return 'comment' === $parameter[ 'data' ][ 'item' ]
						&& $document === $parameter[ 'data' ][ 'document' ];
					}
				),
				'item'
			);

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->exactly( 1 ) )
			->method( 'wp_error' )
			->with(
				'item',
				$this->callback( 'is_string' )
			)
			->willReturn( $wp_error_mock );

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_parse_comment_error' )
			->once()
			->with( $wp_error_mock );

		$testee = new Service\Parser\WpCommentParser( $wp_factory_mock );
		$this->assertNull(
			$testee->parse_comment( $document )
		);

	}

	/**
	 * @dataProvider missing_attribute_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_parse_comment_missing_attribute( SimpleXMLElement $document, Array $expected ) {

		$wp_error_mock = $this->mock_builder->wp_error( [ 'add_data' ] );
		$wp_error_mock->expects( $this->exactly( 1 ) )
			->method( 'add_data' )
			->with(
				$this->callback(
					function ( $parameter ) use ( $expected, $document ) {

						return $expected[ 'missing_attribute' ] === $parameter[ 'data' ][ 'attribute' ]
						&& $document === $parameter[ 'data' ][ 'document' ];
					}
				),
				'attribute'
			);

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->exactly( 1 ) )
			->method( 'wp_error' )
			->willReturn( $wp_error_mock );

		$testee = new Service\Parser\WpCommentParser( $wp_factory_mock );
		$result = $testee->parse_comment( $document );

		$this->assertInstanceOf(
			'W2M\Import\Type\ImportCommentInterface',
			$result
		);
		foreach ( $expected[ 'comment_data' ] as $method => $value ) {
			if ( $method === $expected[ 'missing_method' ] ) {
				//skip this for date()
				if ( 'date_str_gmt' === $method ) {
					continue;
				}

				$this->assertSame(
					$expected[ 'default_value' ],
					$result->{$method}()
				);
				continue;
			}
			if ( 'date_str_gmt' == $method ) {
				$this->assertSame(
					$value,
					$result->date()
						->format( 'Y-m-d H:i:s' )
				);
				continue;
			}
			$this->assertSame(
				$value,
				$result->{$method}(),
				"Test failed for method '{$method}'"
			);
		}

	}

	/**
	 * @see test_parse_comment_missing_attribute
	 */
	public function missing_attribute_test_data() {

		$data         = [ ];
		$comment_data = [
			'origin_id'                => 171159,
			'origin_post_id'           => 4543,
			'author_name'              => 'test',
			'author_email'             => 'test@mail.tld',
			'author_url'               => 'https://test.tld',
			'author_ip'                => '127.0.0.1',
			'content'                  => 'Lorem Ipsum',
			'date_str_gmt'             => '2016-01-13 17:07:32',
			'karma'                    => 0,
			'approved'                 => '1',
			'agent'                    => 'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
			'type'                     => 'comment',
			'origin_parent_comment_id' => 0,
			'origin_user_id'           => 0
		];
		$xml          = <<<XML
<root
	xmlns:wp="urn:wp"
	>
	<wp:comment>
		<wp:comment_ID><![CDATA[{$comment_data['origin_id']}]]></wp:comment_ID>
		<wp:comment_post_ID><![CDATA[{$comment_data['origin_post_id']}]]></wp:comment_post_ID>
		<wp:comment_author><![CDATA[{$comment_data['author_name']}]]></wp:comment_author>
		<wp:comment_author_email><![CDATA[{$comment_data['author_email']}]]></wp:comment_author_email>
		<wp:comment_author_url><![CDATA[{$comment_data['author_url']}]]></wp:comment_author_url>
		<wp:comment_author_IP><![CDATA[{$comment_data['author_ip']}]]></wp:comment_author_IP>
		<wp:comment_date><![CDATA[1999-05-23 03:45:36]]></wp:comment_date>
		<wp:comment_date_gmt><![CDATA[{$comment_data['date_str_gmt']}]]></wp:comment_date_gmt>
		<wp:comment_content><![CDATA[{$comment_data['content']}]]></wp:comment_content>
		<wp:comment_karma><![CDATA[{$comment_data['karma']}]]></wp:comment_karma>
		<wp:comment_approved><![CDATA[{$comment_data['approved']}]]></wp:comment_approved>
		<wp:comment_agent><![CDATA[{$comment_data['agent']}]]></wp:comment_agent>
		<wp:comment_type><![CDATA[{$comment_data['type']}]]></wp:comment_type>
		<wp:comment_parent>{$comment_data['origin_parent_comment_id']}</wp:comment_parent>
		<wp:user_id><![CDATA[{$comment_data['origin_user_id']}]]></wp:user_id>
	</wp:comment>
</root>
XML;

		//<wp:comment_ID/>
		$tmp_xml                      = str_replace(
			"<wp:comment_ID><![CDATA[{$comment_data['origin_id']}]]></wp:comment_ID>",
			'',
			$xml
		);
		$data[ 'missing_comment_id' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_ID',
				'missing_method'    => 'origin_id',
				'comment_data'      => $comment_data,
				'default_value'     => 0
			]
		];

		// <wp:comment_post_ID />
		$tmp_xml                   = str_replace(
			"<wp:comment_post_ID><![CDATA[{$comment_data['origin_post_id']}]]></wp:comment_post_ID>",
			'',
			$xml
		);
		$data[ 'missing_post_id' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_post_ID',
				'missing_method'    => 'origin_post_id',
				'comment_data'      => $comment_data,
				'default_value'     => 0
			]
		];

		// <wp:comment_author />
		$tmp_xml                               = str_replace(
			"<wp:comment_author><![CDATA[{$comment_data['author_name']}]]></wp:comment_author>",
			'',
			$xml
		);
		$data[ 'missing_comment_author_name' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_author',
				'missing_method'    => 'author_name',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_author_email />
		$tmp_xml                                = str_replace(
			"<wp:comment_author_email><![CDATA[{$comment_data['author_email']}]]></wp:comment_author_email>",
			'',
			$xml
		);
		$data[ 'missing_comment_author_email' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_author_email',
				'missing_method'    => 'author_email',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_author_url />
		$tmp_xml                              = str_replace(
			"<wp:comment_author_url><![CDATA[{$comment_data['author_url']}]]></wp:comment_author_url>",
			'',
			$xml
		);
		$data[ 'missing_comment_author_url' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_author_url',
				'missing_method'    => 'author_url',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_author_ip />
		$tmp_xml                             = str_replace(
			"<wp:comment_author_IP><![CDATA[{$comment_data['author_ip']}]]></wp:comment_author_IP>",
			'',
			$xml
		);
		$data[ 'missing_comment_author_ip' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_author_IP',
				'missing_method'    => 'author_ip',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_date />
		$tmp_xml                        = str_replace(
			"<wp:comment_date_gmt><![CDATA[{$comment_data['date_str_gmt']}]]></wp:comment_date_gmt>",
			'',
			$xml
		);
		$data[ 'missing_comment_date' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_date_gmt',
				'missing_method'    => 'date_str_gmt',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_content />
		$tmp_xml                           = str_replace(
			"<wp:comment_content><![CDATA[{$comment_data['content']}]]></wp:comment_content>",
			'',
			$xml
		);
		$data[ 'missing_comment_content' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_content',
				'missing_method'    => 'content',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_karma />
		$tmp_xml                         = str_replace(
			"<wp:comment_karma><![CDATA[{$comment_data['karma']}]]></wp:comment_karma>",
			'',
			$xml
		);
		$data[ 'missing_comment_karma' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_karma',
				'missing_method'    => 'karma',
				'comment_data'      => $comment_data,
				'default_value'     => 0
			]
		];

		// <wp:comment_approved />
		$tmp_xml                            = str_replace(
			"<wp:comment_approved><![CDATA[{$comment_data['approved']}]]></wp:comment_approved>",
			'',
			$xml
		);
		$data[ 'missing_comment_approved' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_approved',
				'missing_method'    => 'approved',
				'comment_data'      => $comment_data,
				'default_value'     => '1'
			]
		];

		// <wp:comment_agent />
		$tmp_xml = str_replace(
			"<wp:comment_agent><![CDATA[{$comment_data['agent']}]]></wp:comment_agent>",
			'',
			$xml
		);
		$data[ 'missing_comment_agent' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_agent',
				'missing_method'    => 'agent',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_type />
		$tmp_xml = str_replace(
			"<wp:comment_type><![CDATA[{$comment_data['type']}]]></wp:comment_type>",
			'',
			$xml
		);
		$data[ 'missing_comment_type' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_type',
				'missing_method'    => 'type',
				'comment_data'      => $comment_data,
				'default_value'     => ''
			]
		];

		// <wp:comment_parent />
		$tmp_xml = str_replace(
			"<wp:comment_parent>{$comment_data['origin_parent_comment_id']}</wp:comment_parent>",
			'',
			$xml
		);
		$data[ 'missing_comment_parent' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'comment_parent',
				'missing_method'    => 'origin_parent_comment_id',
				'comment_data'      => $comment_data,
				'default_value'     => 0
			]
		];

		// <wp:user_id />
		$tmp_xml = str_replace(
			"<wp:user_id><![CDATA[{$comment_data['origin_user_id']}]]></wp:user_id>",
			'',
			$xml
		);
		$data[ 'missing_user_id' ] = [
			# 1. Parameter $document
			new SimpleXMLElement( $tmp_xml ),
			# 2. Parameter $expected
			[
				'missing_attribute' => 'user_id',
				'missing_method'    => 'origin_user_id',
				'comment_data'      => $comment_data,
				'default_value'     => 0
			]
		];

		return $data;
	}
}
