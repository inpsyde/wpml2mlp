<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain;

class TermProcessorTest extends Helper\MonkeyTestCase {

	public function test_process_elements() {

		$iterator_mock = $this->mock_builder->iterator_term_iterator();
		$importer_mock = $this->mock_builder->service_term_importer_interface();

		$terms = [
			NULL, // test skipping some invalid terms
			NULL,
			NULL,
			$this->mock_builder->type_wp_import_term(),
			$this->mock_builder->type_wp_import_term(),
			$this->mock_builder->type_wp_import_term(),
			NULL
		];

		$iterator_mock->expects( $this->exactly( 8 ) )
			->method( 'valid' )
			->will(
				$this->onConsecutiveCalls(
					TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, FALSE
				)
			);
		$iterator_mock->expects( $this->exactly( 7 ) )
			->method( 'current' )
			->will(
				$this->onConsecutiveCalls(
					$terms[ 0 ],
					$terms[ 1 ],
					$terms[ 2 ],
					$terms[ 3 ],
					$terms[ 4 ],
					$terms[ 5 ],
					$terms[ 6 ]
				)
			);
		$iterator_mock->expects( $this->exactly( 7 ) )
			->method( 'next' );

		$importer_mock->expects( $this->exactly( 3 ) )
			->method( 'import_term' )
			->withConsecutive(
				$terms[ 3 ],
				$terms[ 4 ],
				$terms[ 5 ]
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_terms_done' )
			->once();

		$testee = new Service\TermProcessor(
			$iterator_mock,
			$importer_mock
		);
		$testee->process_elements();

	}
}
