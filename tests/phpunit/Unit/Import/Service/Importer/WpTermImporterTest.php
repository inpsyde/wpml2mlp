<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service\Importer;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain;

class WpTermImporterTest extends Helper\MonkeyTestCase {

	/**
	 * @group import_term
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
			'origin_parent_term_id' => 42,
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

		$new_parent_id = 15;
		$id_mapper_mock->expects( $this->atLeast( 1 ) )
		               ->method( 'local_id' )
		               ->with( 'term', $test_data['origin_parent_term_id'] )
					   ->willReturn( $new_parent_id );

		$wp_term_data = array(
			'term_id' => 45,
			'term_taxonomy_id' => 45
		);

		Brain\Monkey\Functions::expect( 'wp_insert_term' )
			->atLeast()->once()
			->with(
				$test_data[ 'name' ],
				$test_data[ 'taxonomy' ],
				array(
					'slug'        => $test_data[ 'slug' ],
					'description' => $test_data[ 'description' ],
					'parent'      => $new_parent_id
				)
			)
			->andReturn( $wp_term_data );

		Brain\Monkey\Functions::when( 'is_wp_error' )->justReturn( FALSE );

		$wp_term_mock = $this->getMock( 'WP_Term' );

		Brain\Monkey\Functions::expect( 'get_term_by' )
			->atLeast()->once()
			->with(
				'id',
				$wp_term_data[ 'term_id' ],
				$test_data[ 'taxonomy' ]
			)
			->andReturn( $wp_term_mock );


		// wp_insert_term() is now configured and expects and return concrete values.
		// the testee has to pass the ID of the newly created term to the import element ...
		$term_mock->expects( $this->atLeast( 1 ) )
			->method( 'id' )
			->with( $wp_term_data[ 'term_id' ] )
			->willReturn( $wp_term_data[ 'term_id' ] );

		/**
		 * Finally start the tests.
		 * All assertions will be made by the mocks that
		 * gets invoked by the testee
		 */
		$testee->import_term( $term_mock );
	}

}
