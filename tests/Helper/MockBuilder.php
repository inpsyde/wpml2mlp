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
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_meta( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpImportMeta', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_locale_relation( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\LocaleRelation', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_term_reference( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpTermReference', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_post( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpImportPost', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_term( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpImportTerm', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function type_wp_import_user( Array $methods = array() ) {

		return $this->mock_without_constructor( 'W2M\Import\Type\WpImportUser', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_error( Array $methods = array() ) {

		return $this->mock_without_constructor( 'WP_Error', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_post( Array $methods = array() ) {

		return $this->mock_without_constructor( 'WP_Post', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_user( Array $methods = array() ) {

		return $this->mock_without_constructor( 'WP_User', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_term( Array $methods = array() ) {

		return $this->mock_without_constructor( 'WP_Term', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function wp_query( Array $methods = array() ) {

		return $this->mock_without_constructor( 'WP_Query', $methods );
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function common_wp_factory( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'W2M\Import\Common\WpFactory',
			$methods
		);
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function module_translation_connector( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'W2M\Import\Module\TranslationConnectorInterface',
			$methods
		);
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_post_importer_interface( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\PostImporterInterface',
			$methods
		);
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function service_post_parser_interface( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'W2M\Import\Service\PostParserInterface',
			$methods
		);
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function data_multi_type_id_mapper( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'W2M\Import\Data\MultiTypeIdMapperInterface',
			$methods
		);
	}

	/**
	 * @param array $methods
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	public function mlp_content_relations_interface( Array $methods = array() ) {

		return $this->mock_without_constructor(
			'Mlp_Content_Relations_Interface',
			$methods
		);
	}

	/**
	 * @param $class
	 * @param array $methods
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	public function mock_without_constructor( $class, Array $methods = array() ) {

		$mockBuilder = $this->test_case->getMockBuilder( $class )
			->disableOriginalConstructor();
		if ( $methods )
			$mockBuilder->setMethods( $methods );

		return $mockBuilder->getMock();
	}
}