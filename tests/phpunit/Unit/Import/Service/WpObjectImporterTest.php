<?php  # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper;

class WpObjectImporterTest extends \PHPUnit_Framework_TestCase {

	private $fs_helper;

	/**
	 * runs before each test
	 */
	protected function setUp(){

		if( ! $this->fs_helper ){

			$this->fs_helper = new Helper\FileSystem;

		}

	}

	/**
	 * runs after each test
	 */
	protected function tearDown() {

	}

	/**
	 * @group rene
	 */
	public function test_import_term(){

		$translation_connector_mock = $this->getMockBuilder( 'W2M\Import\Service\TranslationConnectorInterface' )->getMock();
		$id_mapper_mock = $this->getMockBuilder( 'W2M\Import\Data\IdMapperInterface' )->getMock();

		$testee = new Service\WpObjectImporter( $translation_connector_mock, $id_mapper_mock );


	}

}
