<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use Brain;
use W2M\Import\Service;
use W2M\Test\Helper;

class WpObjectImporterTest extends \PHPUnit_Framework_TestCase {

	private $fs_helper;

	/**
	 * runs before each test
	 */
	protected function setUp() {

		if ( !$this->fs_helper ) {
			$this->fs_helper = new Helper\FileSystem;
		}

		Brain\Monkey::setUp();
		Brain\Monkey::setUpWP();

		/**
		 * Just create some mocks of these types to avoid
		 * error messages like this
		 * https://github.com/sebastianbergmann/phpunit-mock-objects/issues/273
		 * when mocking objects that type hint WP core components
		 */
		$this->getMock( 'WP_Post' );
	}

	/**
	 * runs after each test
	 */
	protected function tearDown() {

		Brain\Monkey::tearDown();
		Brain\Monkey::tearDownWP();
	}

	/**
	 * @group import_term
	 */
	public function test_import_term() {

		/**
		 * Create mocks for the dependency of the testee (WpObjectImporter)
		 */
		$translation_connector_mock = $this->getMockBuilder( 'W2M\Import\Service\TranslationConnectorInterface' )
			->getMock();
		$id_mapper_mock = $this->getMockBuilder( 'W2M\Import\Data\IdMapperInterface' )
			->getMock();

		$testee = new Service\WpObjectImporter( $translation_connector_mock, $id_mapper_mock );

		/**
		 * Create a mock to pass it as parameter to the intended method
		 * ( WpObjectLog::import_term() )
		 */
		$term_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportTermInterface' )
			->getMock();

		/**
		 * Now define the behaviour of the mock object. Each of the specified
		 * methods ( @see ImportTermInterface ) should return a proper value!
		 *
		 *  - taxonomy()
		 *  - name()
		 *  - slug()
		 *  - description()
		 *  - origin_parent_term_id()
		 *  - locale_relations()
		 */
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

		/**
		 * Okay, now we have a real-world representation of our import data.
		 *
		 * Our test candidate (WpObjectImporter::import_term()) obviously calls
		 * wp_insert_term(). As it is not available, remember, these are unit-tests
		 * we have to mock it.
		 *
		 * @see wp_insert_term()
		 * @link https://giuseppe-mazzapica.github.io/BrainMonkey/docs/functions-expect.html
		 */
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
		 * Todo:
		 * Currently the test and implementation assuming a simple pass-through
		 * of the values coming from ImportTermInterface to wp_insert_term().
		 *
		 * both have to deal with the concept of IdMapperInterface::local_id().
		 *
		 * So the mock of IdMapperInterface::local_id should except the parent ID and should
		 * return another »local« id of the term. The mock of wp_insert_term() has to be adapted
		 * to expect not the parent ID but the local id.
		 */

		/**
		 * Finally start the tests.
		 * All assertions will be made by the mocks that
		 * gets invoked by the testee
		 */
		$testee->import_term( $term_mock );
	}
}
