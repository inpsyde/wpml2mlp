<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Helper;

use
	PHPUnit_Framework_TestCase,
	PHPUnit_Framework_MockObject_MockObject;

class MockBuilder {

	/**
	 * @var PHPUnit_Framework_TestCase
	 */
	private $test_case;

	/**
	 * @param PHPUnit_Framework_TestCase $test_case
	 */
	public function __construct( PHPUnit_Framework_TestCase $test_case ) {

		$this->test_case = $test_case;
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function common_file( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Common\File',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function common_wp_factory( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Common\WpFactory',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function data_multi_type_id_mapper( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Data\MultiTypeIdMapperInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function data_multi_type_id_list( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Data\MultiTypeIdListInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function iterator_simple_xml_item_wrapper( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Iterator\SimpleXmlItemWrapper',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function iterator_comment_iterator( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Iterator\CommentIterator',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function iterator_post_iterator( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Iterator\PostIterator',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function iterator_term_iterator( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Iterator\TermIterator',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function iterator_user_iterator( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Iterator\UserIterator',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function module_translation_connector( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Module\TranslationConnectorInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_comment_importer_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Importer\CommentImporterInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_post_importer_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Importer\PostImporterInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_term_importer_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Importer\TermImporterInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_user_importer_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Importer\UserImporterInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_comment_parser_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Parser\CommentParserInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_post_parser_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Parser\PostParserInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_term_parser_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Parser\TermParserInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_user_parser_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\Parser\UserParserInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_locale_relation( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\LocaleRelation',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_file_import_report_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\FileImportReportInterface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_comment( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\WpImportComment',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_meta( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\WpImportMeta',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_post( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\WpImportPost',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_term_reference( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\WpTermReference',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_term( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\WpImportTerm',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_user( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'W2M\Import\Type\WpImportUser',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function mlp_content_relations_interface( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'Mlp_Content_Relations_Interface',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_comment( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'WP_Comment',
			$methods
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_error( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'WP_Error',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_http( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'WP_Http',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_query( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'WP_Query',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_post( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'WP_Post',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_term( Array $methods = [], Array $data = []) {

		return $this->mock_without_constructor(
			'WP_Term',
			$methods,
			$data
		);
	}

	/**
	 * @param array $methods (Optional)
	 * @param array $data (Optional)
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_user( Array $methods = [], Array $data = [] ) {

		return $this->mock_without_constructor(
			'WP_User',
			$methods,
			$data
		);
	}

	/**
	 * @param $class
	 * @param array $methods (optional)
	 * @param array $data (optional)
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function mock_without_constructor( $class, Array $methods = array(), $data = array() ) {

		$mockBuilder = $this->test_case->getMockBuilder( $class )
			->disableOriginalConstructor();
		if ( $methods ) {
			$mockBuilder->setMethods( $methods );
		}

		$mock = $mockBuilder->getMock();
		if ( ! $data )
			return $mock;

		foreach ( $data as $method => $value ) {
			$mock->method( $method )->willReturn( $value );
		}

		return $mock;
	}
}