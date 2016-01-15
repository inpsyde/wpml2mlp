<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Service;

use
	W2M\Import\Service,
	W2M\Test\Helper,
	Brain;

class UserProcessorTest extends Helper\MonkeyTestCase {

	public function test_process_elements() {

		$iterator_mock = $this->mock_builder->iterator_user_iterator();
		$importer_mock = $this->mock_builder->service_user_importer_interface();

		$users = [
			$this->mock_builder->type_wp_import_user(),
			NULL, // test skipping some invalid users
			NULL,
			$this->mock_builder->type_wp_import_user()
		];

		$iterator_mock->expects( $this->exactly( 5 ) )
			->method( 'valid' )
			->will(
				$this->onConsecutiveCalls(
					TRUE, TRUE, TRUE, TRUE, FALSE
				)
			);
		$iterator_mock->expects( $this->exactly( 4 ) )
			->method( 'current' )
			->will(
				$this->onConsecutiveCalls(
					$users[ 0 ],
					$users[ 1 ],
					$users[ 2 ],
					$users[ 3 ]
				)
			);
		$iterator_mock->expects( $this->exactly( 4 ) )
			->method( 'next' );

		$importer_mock->expects( $this->exactly( 2 ) )
			->method( 'import_user' )
			->withConsecutive(
				$users[ 0 ],
				$users[ 3 ]
			);

		Brain\Monkey::actions()
			->expectFired( 'w2m_import_users_done' )
			->once();

		$testee = new Service\UserProcessor(
			$iterator_mock,
			$importer_mock
		);
		$testee->process_elements();
	}
}
