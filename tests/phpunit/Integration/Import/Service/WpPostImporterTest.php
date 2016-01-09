<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Integration\Import\Service;

use
	W2M\Import\Service,
	PHPUnit_Framework_TestCase;

class WpPostImporterTest extends PHPUnit_Framework_TestCase {

	/**
	 * @group import_post
	 */
	public function test_import_term() {

		$translation_connector_mock = $this->getMockBuilder( 'W2M\Import\Service\TranslationConnectorInterface' )
		                                   ->getMock();
		$id_mapper_mock = $this->getMockBuilder( 'W2M\Import\Data\IdMapperInterface' )
		                       ->getMock();

		$testee = new Service\WpPostImporter( $translation_connector_mock, $id_mapper_mock );

		$post_mock = $this->getMockBuilder( 'W2M\Import\Type\ImportPostInterface' )
		                  ->getMock();

		$test_data = array(
			'title'                 => 'Mocky test fight',
			'status'                => 'draft',
			'guid'                  => 'mocky',
			'date'                  => (new \DateTime( 'NOW' ))->format('Y-m-d H:i:s'),
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'origin_author_id'      => 42,
			'type'                  => 'post',
			'is_sticky'             => FALSE,
			'origin_link'           => 'mocky',
			'excerpt'               => 'Mocky the fighter',
			'content'               => 'Mock will go for a greate fight.',
			'name'                  => 'mocky',
			'origin_parent_post_id' => 42,
			'menu_order'            => 1,
			'password'              => 'mocky',
			'terms'                 => array( 'terms' ),
			'meta'                  => array( 'meta' ),
			'locale_relations'      => array(
				'en_US' => 13,
				'fr_CH' => 32
			)
		);

		foreach ( $test_data as $method => $return_value ) {

			$post_mock->expects( $this->atLeast( 1 ) )
			          ->method( $method )
			          ->willReturn( $return_value );

		}

		$testee->import_post( $post_mock );

		#$this->markTestIncomplete();

	}
}
