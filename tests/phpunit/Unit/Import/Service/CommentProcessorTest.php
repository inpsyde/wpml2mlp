<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain;

class CommentProcessorTest extends Helper\MonkeyTestCase {

	public function test_process_elements() {

		$iterator_mock = $this->mock_builder->iterator_comment_iterator();
		$importer_mock = $this->mock_builder->service_comment_importer_interface();

		$comments = [
			$this->mock_builder->type_wp_import_comment(),
			$this->mock_builder->type_wp_import_comment(),
			NULL // test skipping some invalid posts
		];

		$iterator_mock->expects( $this->exactly( 4 ) )
			->method( 'valid' )
			->will(
				$this->onConsecutiveCalls( TRUE, TRUE, TRUE, FALSE )
			);
		$iterator_mock->expects( $this->exactly( 3 ) )
			->method( 'current' )
			->will(
				$this->onConsecutiveCalls(
					$comments[ 0 ],
					$comments[ 1 ],
					$comments[ 2 ]
				)
			);
		$iterator_mock->expects( $this->exactly( 3 ) )
			->method( 'next' );

		$importer_mock->expects( $this->exactly( 2 ) )
			->method( 'import_comment' )
			->withConsecutive(
				$comments[ 0 ],
				$comments[ 1 ]
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_comments_done' );

		$testee = new Service\CommentProcessor(
			$iterator_mock,
			$importer_mock
		);
		$testee->process_elements();
	}
}
