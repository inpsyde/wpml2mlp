<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Integration\Import;

use
	W2M\Import\Iterator,
	W2M\Import\Service,
	W2M\Test\Helper,
	SimpleXMLElement;

class IteratorsParsersTest extends Helper\MonkeyTestCase {

	/**
	 * @var Helper\FileSystem
	 */
	private $file_system;

	/**
	 * @var array
	 */
	private $test_files = [ ];

	public function setUp() {

		parent::setUp();
		$this->file_system = new Helper\FileSystem;
	}

	public function tearDown() {

		foreach ( $this->test_files as $key => $file ) {
			$this->file_system->delete_file( $file );
			unset( $this->test_files[ $key ] );
		}
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_post_while_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\PostIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'item'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpPostParser( $wp_factory_mock )
		);

		$index = 0;
		while ( $iterator->valid() ) {
			$import_post = $iterator->current();
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportPostInterface',
				$import_post,
				"Test failed at index {$index}"
			);

			$this->assertSame(
				$expected[ 'posts' ][ 'origin_ids' ][ $index ],
				$import_post->origin_id(),
				"Test failed at index {$index}"
			);
			$iterator->next();
			$index ++;
		}
		$this->assertSame(
			$expected[ 'posts'][ 'expected_posts' ],
			$index
		);
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_post_foreach_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\PostIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'item'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpPostParser( $wp_factory_mock )
		);

		$index = 0;
		foreach ( $iterator as $import_post ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportPostInterface',
				$import_post,
				"Test failed at index {$index}"
			);

			$this->assertSame(
				$expected[ 'posts' ][ 'origin_ids' ][ $index ],
				$import_post->origin_id(),
				"Test failed at index {$index}"
			);
			$index ++;
		}
		$this->assertSame(
			$expected[ 'posts'][ 'expected_posts' ],
			$index
		);
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_term_while_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\TermIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'wp:category'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpTermParser( $wp_factory_mock )
		);

		$index = 0;
		while ( $iterator->valid() ) {
			$import_term = $iterator->current();
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportTermInterface',
				$import_term,
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'terms' ][ 'origin_ids' ][ $index ],
				$import_term->origin_id(),
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'terms' ][ 'taxonomies' ][ $index ],
				$import_term->taxonomy(),
				"Test failed at index {$index}"
			);

			$iterator->next();
			$index ++;
		}

		$this->assertSame(
			$expected[ 'terms' ][ 'expected_terms' ],
			$index
		);
	}


	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_term_foreach_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\TermIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'wp:category'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpTermParser( $wp_factory_mock )
		);

		$index = 0;
		foreach ( $iterator as $import_term ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportTermInterface',
				$import_term,
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'terms' ][ 'origin_ids' ][ $index ],
				$import_term->origin_id(),
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'terms' ][ 'taxonomies' ][ $index ],
				$import_term->taxonomy(),
				"Test failed at index {$index}"
			);

			$index ++;
		}

		$this->assertSame(
			$expected[ 'terms' ][ 'expected_terms' ],
			$index
		);
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_user_while_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\UserIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'wp:author'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpUserParser( $wp_factory_mock )
		);

		$index = 0;
		while ( $iterator->valid() ) {
			$import_user = $iterator->current();
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportUserInterface',
				$import_user,
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'users' ][ 'origin_ids' ][ $index ],
				$import_user->origin_id()
			);
			$this->assertSame(
				$expected[ 'users' ][ 'logins' ][ $index ],
				$import_user->login()
			);
			$this->assertSame(
				$expected[ 'users' ][ 'emails' ][ $index ],
				$import_user->email()
			);
			$iterator->next();
			$index ++;
		}

		$this->assertSame(
			$expected[ 'users' ][ 'expected_users' ],
			$index
		);
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_user_foreach_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\UserIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'wp:author'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpUserParser( $wp_factory_mock )
		);

		$index = 0;
		foreach ( $iterator as $import_user ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportUserInterface',
				$import_user,
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'users' ][ 'origin_ids' ][ $index ],
				$import_user->origin_id()
			);
			$this->assertSame(
				$expected[ 'users' ][ 'logins' ][ $index ],
				$import_user->login()
			);
			$this->assertSame(
				$expected[ 'users' ][ 'emails' ][ $index ],
				$import_user->email()
			);
			$index ++;
		}

		$this->assertSame(
			$expected[ 'users' ][ 'expected_users' ],
			$index
		);
	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_comment_while_iteration( SimpleXMLElement $document, Array $expected )  {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\CommentIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'wp:comment'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpCommentParser( $wp_factory_mock )
		);

		$index = 0;
		while ( $iterator->valid() ) {
			$import_comment = $iterator->current();
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportCommentInterface',
				$import_comment,
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'comments' ][ 'origin_ids' ][ $index ],
				$import_comment->origin_id()
			);
			$this->assertSame(
				$expected[ 'comments' ][ 'origin_post_ids' ][ $index ],
				$import_comment->origin_post_id()
			);

			$index ++;
		}

		$this->assertSame(
			$expected[ 'comments' ][ 'expected_comments' ],
			$index
		);

	}

	/**
	 * @dataProvider default_test_data
	 *
	 * @param SimpleXMLElement $document
	 * @param array $expected
	 */
	public function test_comment_foreach_iteration( SimpleXMLElement $document, Array $expected ) {

		$test_file = implode( '-', [ __CLASS__, __FUNCTION__, time() ] ) . '.xml';
		$this->file_system->file_put_contents( $test_file, $document->asXML() );
		$this->test_files[] = $test_file;

		$wp_factory_mock = $this->mock_builder->common_wp_factory();
		$wp_factory_mock->expects( $this->never() )
			->method( 'wp_error' );

		$iterator = new Iterator\CommentIterator(
			new Iterator\SimpleXmlItemWrapper(
				new Iterator\XmlNodeIterator(
					$this->file_system->abs_path( $test_file ),
					'wp:comment'
				),
				'root',
				[],
				$wp_factory_mock
			),
			new Service\Parser\WpCommentParser( $wp_factory_mock )
		);

		$index = 0;
		foreach ( $iterator as $import_user ) {
			$this->assertInstanceOf(
				'W2M\Import\Type\ImportCommentInterface',
				$import_user,
				"Test failed at index {$index}"
			);
			$this->assertSame(
				$expected[ 'comments' ][ 'origin_ids' ][ $index ],
				$import_user->origin_id()
			);
			$this->assertSame(
				$expected[ 'comments' ][ 'origin_post_ids' ][ $index ],
				$import_user->origin_post_id()
			);

			$index ++;
		}

		$this->assertSame(
			$expected[ 'comments' ][ 'expected_comments' ],
			$index
		);

	}

	/**
	 * @see test_post_while_iteration
	 * @see test_post_foreach_iteration
	 * @see test_term_while_iteration
	 * @see test_term_foreach_iteration
	 * @see test_user_while_iteration
	 * @see test_user_foreach_iteration
	 * @see test_comment_foreach_iteration
	 * @see test_commnet_foreach_iteration
	 * @return array
	 */
	public function default_test_data() {

		$data = [];

		/**
		 * data_1
		 */
		$xml = <<<XML
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.2/"
	         >
	<channel>
		<title>WPML to MLP Integration tests</title>
		<link>https://github.com/inpsyde/wpml2mlp</link>
		<description>Convert posts from an existing WPML multilingual site via WXR Export/Import for MultilingualPress</description>
		<pubDate>Mon, 18 Jan 2016 13:40:37 +0000</pubDate>
		<language></language>
		<wp:wxr_version>1.2</wp:wxr_version>
		<wp:base_site_url>http://wpml.to.mlp/</wp:base_site_url>
		<wp:base_blog_url>http://wpml.to.mlp</wp:base_blog_url>
		<wp:author>
			<wp:author_id>2</wp:author_id>
			<wp:author_login><![CDATA[john]]></wp:author_login>
			<wp:author_email><![CDATA[john@doe.tld]]></wp:author_email>
			<wp:author_display_name><![CDATA[John D.]]></wp:author_display_name>
			<wp:author_first_name><![CDATA[John]]></wp:author_first_name>
			<wp:author_last_name><![CDATA[Doe]]></wp:author_last_name>
		</wp:author>
		<wp:category>
			<wp:term_id>61</wp:term_id>
			<wp:category_nicename><![CDATA[cat-pics]]></wp:category_nicename>
			<wp:category_parent><![CDATA[]]></wp:category_parent>
			<wp:cat_name><![CDATA[Cat Pictures]]></wp:cat_name>
			<wp:category_description><![CDATA[My funniest cat pictures.]]></wp:category_description>
			<wp:taxonomy><![CDATA[category]]></wp:taxonomy>
		</wp:category>
		<wp:author>
			<wp:author_id>1</wp:author_id>
			<wp:author_login><![CDATA[jane]]></wp:author_login>
			<wp:author_email><![CDATA[jane@wpml.to.mlp]]></wp:author_email>
			<wp:author_display_name><![CDATA[Jane Doe]]></wp:author_display_name>
			<wp:author_first_name><![CDATA[Jane]]></wp:author_first_name>
			<wp:author_last_name><![CDATA[Doe]]></wp:author_last_name>
		</wp:author>
		<wp:category>
			<wp:term_id>144</wp:term_id>
			<wp:category_nicename><![CDATA[php]]></wp:category_nicename>
			<wp:category_parent><![CDATA[0]]></wp:category_parent>
			<wp:cat_name><![CDATA[PHP]]></wp:cat_name>
			<wp:category_description><![CDATA[Posts around PHP.]]></wp:category_description>
			<wp:taxonomy><![CDATA[post_tag]]></wp:taxonomy>
		</wp:category>
		<item>
			<title>My First Test Post</title>
			<link>https://wpml.to.mlp//?p=44330</link>
			<pubDate><![CDATA[2015-04-03 12:24:55]]></pubDate>
			<dc:creator><![CDATA[]]></dc:creator>
			<guid isPermaLink="false">https://wpml.to.mlp//?p=44330</guid>
			<excerpt:encoded><![CDATA[]]></excerpt:encoded>
			<content:encoded><![CDATA[]]></content:encoded>
			<wp:post_id>44330</wp:post_id>
			<wp:post_date><![CDATA[2015-04-03 12:24:55]]></wp:post_date>
			<wp:post_author><![CDATA[2]]></wp:post_author>
			<wp:post_date_gmt><![CDATA[2015-04-03 12:24:55]]></wp:post_date_gmt>
			<wp:comment_status><![CDATA[closed]]></wp:comment_status>
			<wp:ping_status><![CDATA[closed]]></wp:ping_status>
			<wp:post_name><![CDATA[my-first-test-post]]></wp:post_name>
			<wp:status><![CDATA[publish]]></wp:status>
			<wp:post_parent>0</wp:post_parent>
			<wp:menu_order>0</wp:menu_order>
			<wp:post_type><![CDATA[post]]></wp:post_type>
			<wp:post_password><![CDATA[]]></wp:post_password>
			<wp:is_sticky>0</wp:is_sticky>
			<category domain="category" nicename="cat-pics" term_id="63"><![CDATA[My funniest cat pictures.]]></category>
			<wp:postmeta>
				<wp:meta_key><![CDATA[_edit_lock]]></wp:meta_key>
				<wp:meta_value><![CDATA[1453111692:688]]></wp:meta_value>
			</wp:postmeta>
			<wp:postmeta>
				<wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
				<wp:meta_value><![CDATA[9]]></wp:meta_value>
			</wp:postmeta>
			<wp:translation>
				<wp:locale><![CDATA[en_US]]></wp:locale>
				<wp:element_id>44330</wp:element_id>
			</wp:translation>
		</item>
		<item>
			<title>My first test page</title>
			<link>https://wpml.to.mlp//?p=44512</link>
			<pubDate><![CDATA[2015-10-09 14:02:22]]></pubDate>
			<dc:creator><![CDATA[]]></dc:creator>
			<guid isPermaLink="false">https://wpml.to.mlp//?p=44512</guid>
			<excerpt:encoded><![CDATA[]]></excerpt:encoded>
			<content:encoded><![CDATA[]]></content:encoded>
			<wp:post_id>44512</wp:post_id>
			<wp:post_date><![CDATA[2015-10-09 14:02:22]]></wp:post_date>
			<wp:post_author><![CDATA[4]]></wp:post_author>
			<wp:post_date_gmt><![CDATA[2015-10-09 14:02:22]]></wp:post_date_gmt>
			<wp:comment_status><![CDATA[open]]></wp:comment_status>
			<wp:ping_status><![CDATA[open]]></wp:ping_status>
			<wp:post_name><![CDATA[my-first-test-page]]></wp:post_name>
			<wp:status><![CDATA[publish]]></wp:status>
			<wp:post_parent>0</wp:post_parent>
			<wp:menu_order>0</wp:menu_order>
			<wp:post_type><![CDATA[page]]></wp:post_type>
			<wp:post_password><![CDATA[]]></wp:post_password>
			<wp:is_sticky>0</wp:is_sticky>
			<wp:postmeta>
				<wp:meta_key><![CDATA[_edit_lock]]></wp:meta_key>
				<wp:meta_value><![CDATA[1453111692:688]]></wp:meta_value>
			</wp:postmeta>
			<wp:postmeta>
				<wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
				<wp:meta_value><![CDATA[9]]></wp:meta_value>
			</wp:postmeta>
			<wp:translation>
				<wp:locale><![CDATA[en_US]]></wp:locale>
				<wp:element_id>44512</wp:element_id>
			</wp:translation>
		</item>
		<item>
			<title>Some other post</title>
			<link>https://wpml.to.mlp//?p=56790</link>
			<pubDate><![CDATA[2016-01-03 08:33:12]]></pubDate>
			<dc:creator><![CDATA[]]></dc:creator>
			<guid isPermaLink="false">https://wpml.to.mlp//?p=56790</guid>
			<excerpt:encoded><![CDATA[]]></excerpt:encoded>
			<content:encoded><![CDATA[]]></content:encoded>
			<wp:post_id>56790</wp:post_id>
			<wp:post_date><![CDATA[2016-01-03 08:33:12]]></wp:post_date>
			<wp:post_author><![CDATA[1]]></wp:post_author>
			<wp:post_date_gmt><![CDATA[2016-01-03 08:33:12]]></wp:post_date_gmt>
			<wp:comment_status><![CDATA[closed]]></wp:comment_status>
			<wp:ping_status><![CDATA[closed]]></wp:ping_status>
			<wp:post_name><![CDATA[some-other-post]]></wp:post_name>
			<wp:status><![CDATA[publish]]></wp:status>
			<wp:post_parent>0</wp:post_parent>
			<wp:menu_order>0</wp:menu_order>
			<wp:post_type><![CDATA[post]]></wp:post_type>
			<wp:post_password><![CDATA[]]></wp:post_password>
			<wp:is_sticky>0</wp:is_sticky>
			<category domain="category" nicename="cat-pics" term_id="63"><![CDATA[My funniest cat pictures.]]></category>
			<category domain="post_tag" nicename="photos" term_id="112"><![CDATA[Photos]]></category>
			<wp:postmeta>
				<wp:meta_key><![CDATA[_edit_lock]]></wp:meta_key>
				<wp:meta_value><![CDATA[1453111692:688]]></wp:meta_value>
			</wp:postmeta>
			<wp:postmeta>
				<wp:meta_key><![CDATA[_edit_last]]></wp:meta_key>
				<wp:meta_value><![CDATA[9]]></wp:meta_value>
			</wp:postmeta>
			<wp:translation>
				<wp:locale><![CDATA[en_US]]></wp:locale>
				<wp:element_id>56790</wp:element_id>
			</wp:translation>
			<wp:comment>
				<wp:comment_ID><![CDATA[12]]></wp:comment_ID>
				<wp:comment_post_ID><![CDATA[56790]]></wp:comment_post_ID>
				<wp:comment_author><![CDATA[Migration wpml2mlp Comment]]></wp:comment_author>
				<wp:comment_author_email><![CDATA[info@inpsyde.com]]></wp:comment_author_email>
				<wp:comment_author_url><![CDATA[http://www.inpsyde.com]]></wp:comment_author_url>
				<wp:comment_author_IP><![CDATA[172.245.136.143]]></wp:comment_author_IP>
				<wp:comment_date><![CDATA[2015-11-26 00:38:18]]></wp:comment_date>
				<wp:comment_date_gmt><![CDATA[2015-11-25 23:38:18]]></wp:comment_date_gmt>
				<wp:comment_content><![CDATA[Yes! Hello World.]]></wp:comment_content>
				<wp:comment_karma><![CDATA[0]]></wp:comment_karma>
				<wp:comment_approved><![CDATA[1]]></wp:comment_approved>
				<wp:comment_agent><![CDATA[Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.116 Safari/537.36]]></wp:comment_agent>
				<wp:comment_type><![CDATA[]]></wp:comment_type>
				<wp:comment_parent>0</wp:comment_parent>
				<wp:user_id><![CDATA[0]]></wp:user_id>
			</wp:comment>
		</item>
	</channel>
</rss>
XML;

		$data[ 'data_1' ] = [
			# 1. Parameter $xml
			new SimpleXMLElement( $xml ),
			# 2. Parameter $expected
			[
				'posts' => [
					'expected_posts' => 3,
					'origin_ids'     => [ 44330, 44512, 56790 ]
				],
				'terms' => [
					'expected_terms' => 2,
					'origin_ids'     => [ 61, 144 ],
					'taxonomies'     => [ 'category', 'post_tag' ]
				],
				'users' => [
					'expected_users' => 2,
					'origin_ids'     => [ 2, 1 ],
					'logins'         => [ 'john', 'jane' ],
					'emails'         => [ 'john@doe.tld', 'jane@wpml.to.mlp' ]
				],
				'comments' => [
					'expected_comments' => 1,
					'origin_ids'        => [ 12 ],
					'origin_post_ids'   => [ 56790 ]
				]
			]
		];

		return $data;
	}
}
