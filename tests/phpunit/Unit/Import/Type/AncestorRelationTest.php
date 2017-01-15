<?php # -*- coding: utf-8 -*-

namespace W2M\Test\Unit\Import\Type;

use
	W2M\Import\Type,
	W2M\Test\Helper;

class AncestorRelationTest extends Helper\MonkeyTestCase {

	public function test_parent_id() {

		$parent_id = 12;
		$id        = 42;
		$testee    = new Type\AncestorRelation( $parent_id, $id );

		$this->assertSame(
			$parent_id,
			$testee->parent_id()
		);
		$this->assertNotEquals(
			$parent_id,
			$testee->id()
		);
	}

	public function test_id() {

		$parent_id = 454;
		$id        = 265;
		$testee    = new Type\AncestorRelation( $parent_id, $id );

		$this->assertSame(
			$id,
			$testee->id()
		);
		$this->assertNotEquals(
			$id,
			$testee->parent_id()
		);
	}
}
