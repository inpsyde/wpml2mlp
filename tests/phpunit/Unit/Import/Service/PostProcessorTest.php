<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain;

class PostProcessorTest extends Helper\MonkeyTestCase {

	public function test_process_elements() {

		$iterator_mock = $this->mock_builder->iterator_post_iterator();
		$importer_mock = $this->mock_builder->service_post_importer_interface();

		$posts = [
			$this->mock_builder->type_wp_import_post(),
			NULL, // test skipping some invalid posts
			$this->mock_builder->type_wp_import_post(),
			NULL,
			$this->mock_builder->type_wp_import_post(),
		];

		$iterator_mock->expects( $this->exactly( 6 ) )
			->method( 'valid' )
			->will(
				$this->onConsecutiveCalls(
					TRUE,
					TRUE,
					TRUE,
					TRUE,
					TRUE,
					FALSE
				)
			);
		$iterator_mock->expects( $this->exactly( 5 ) )
			->method( 'current' )
			->will(
				$this->onConsecutiveCalls(
					$posts[ 0 ],
					$posts[ 1 ],
					$posts[ 2 ],
					$posts[ 3 ],
					$posts[ 4 ]
				)
			);
		$iterator_mock->expects( $this->exactly( 5 ) )
			->method( 'next' );

		$importer_mock->expects( $this->exactly( 3 ) )
			->method( 'import_post' )
			->withConsecutive(
				$posts[ 0 ],
				$posts[ 2 ],
				$posts[ 4 ]
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_posts_done' )
			->once();

		$testee = new Service\PostProcessor(
			$iterator_mock,
			$importer_mock
		);
		$testee->process_elements();
	}
}
