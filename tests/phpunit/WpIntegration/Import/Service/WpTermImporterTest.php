<?php # -*- coding: utf-8 -*-

namespace W2M\Test\WpIntegration\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper;

class WpTermImporterTest extends Helper\WpIntegrationTestCase {

	/**
	 * @group import
	 */
	public function test_import_term() {

		$id_mapper_mock = $this->getMockBuilder( 'W2M\Import\Data\MultiTypeIdMapperInterface' )
		                       ->getMock();

		$testee = new Service\Importer\WpTermImporter( $id_mapper_mock );

		$term_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportTermInterface' )
		                  ->getMock();

		$test_data = array(
			'taxonomy'              => 'category',
			'name'                  => 'My cat pics',
			'slug'                  => 'my-cat-pics',
			'description'           => "Collection of my funniest cat photos.\n\n You should have a look at them.",
			'origin_parent_term_id' => FALSE,
			'locale_relations'      => array(
				'en_US' => 13,
				'fr_CH' => 32
			)
		);

		foreach ( $test_data as $method => $return_value ) {
			if ( 'locale_relations' === $method )
				continue; // we already have this one

			$term_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}

		$new_parent_id = 0;

		$id_mapper_mock->expects( $this->atLeast( 1 ) )
		               ->method( 'local_id' )
		               ->with( 'term', $test_data['origin_parent_term_id'] )
					   ->willReturn( $new_parent_id );

		/**
		 * Finally start the tests.
		 * All assertions will be made by the mocks that
		 * gets invoked by the testee
		 */
		$testee->import_term( $term_mock );
	}

}
