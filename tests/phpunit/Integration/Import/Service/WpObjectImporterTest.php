<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Integration\Import\Service;

use
	W2M\Import\Service,
	PHPUnit_Framework_TestCase;

class WpObjectImporterTest extends PHPUnit_Framework_TestCase {

	public function test_import_term() {

		/**
		 * Here we want to test the method WpObjectImporter::import_term().
		 * The tested object is called $testee.
		 */

		/**
		 * These are mock objects of the dependencies of the testee:
		 */
		$translation_connector_mock = $this->getMockBuilder(
			'W2M\Import\Service\TranslationConnectorInterface'
		)
			->disableOriginalConstructor()
			->getMock();
		$data_mapper_mock = $this->getMockBuilder(
			'W2M\Import\Data\IdMapperInterface'
		)
			->disableOriginalConstructor()
			->getMock();

		// This is our test object
		$testee = new Service\WpObjectImporter(
			$translation_connector_mock,
			$data_mapper_mock
		);

		// now we need some import data to pass to $testee->import_term()
		// we simply mock the interface:
		$import_term_mock = $this->getMockBuilder(
			'W2M\Import\Type\ImportTermInterface'
		)
			->disableOriginalConstructor()
			->getMock();

		/**
		 * now we define the data which is actually returned
		 * by the mock object
		 *
		 * @see ImportTermInterface
		 *
		 * ImportTermInterface::name()
		 */
		$import_term_mock->expects( $this->any() )
			->method( 'name' )
			->willReturn( 'My cat photos' );

		// ImportTermInterface::taxonomy()
		$import_term_mock->expects( $this->any() )
			->method( 'taxonomy' )
			->willReturn( 'category' );

		// Specify the rest of the methods here ...

		// Now do the import:
		$testee->import_term( $import_term_mock );

		// now check if the term was actually inserted
		// remember, these are integration tests,
		// you're in a full featured WP environment

		$terms = get_terms( 'category', array( 'hide_empty' => FALSE ) );

		// search in $terms for term with the name 'My cat photos' and make assertions
		// like:
		//$this->assertSame( 'category', $terms->taxonomy );

		$this->markTestIncomplete();
	}
}
