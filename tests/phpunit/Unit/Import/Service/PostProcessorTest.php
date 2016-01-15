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

		$import_posts = [
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
					$import_posts[ 0 ],
					$import_posts[ 1 ],
					$import_posts[ 2 ],
					$import_posts[ 3 ],
					$import_posts[ 4 ]
				)
			);
		$iterator_mock->expects( $this->exactly( 5 ) )
			->method( 'next' );

		$importer_mock->expects( $this->exactly( 3 ) )
			->method( 'import_post' )
			->withConsecutive(
				$import_posts[ 0 ],
				$import_posts[ 2 ],
				$import_posts[ 4 ]
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
