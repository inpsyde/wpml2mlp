<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

class WpImportTerm implements ImportTermInterface {

	private $origin_id = 0;
	private $id = 0;
	private $taxonomy = 0;
	private $name = '';
	private $slug = '';
	private $description = '';
	private $origin_parent_term_id = 0;
	private $locale_relations = array();

	public function __construct( Array $data ) {

		$keys = array(
			'origin_id' => 'int',
			'taxonomy' => 'string',
			'name' => 'string',
			'slug' => 'string',
			'description' => 'string',
			'origin_parent_term_id' => 'int',
			'locale_relations' => 'array'
		);
	}

	/**
	 * The id of the element in the original system
	 *
	 * @return int
	 */
	public function origin_id() {
		// TODO: Implement origin_id() method.
	}

	/**
	 * Set the id of the imported object in the local system
	 * If any parameter of type integer is passed, the method
	 * acts like a setter, otherwise it acts like a getter.
	 * It must return the ID value (integer) in any case.
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function id( $id = 0 ) {
		// TODO: Implement id() method.
	}

	/**
	 * @return string
	 */
	public function taxonomy() {
		// TODO: Implement taxonomy() method.
	}

	/**
	 * @return string
	 */
	public function name() {
		// TODO: Implement name() method.
	}

	/**
	 * @return string
	 */
	public function slug() {
		// TODO: Implement slug() method.
	}

	/**
	 * @return string
	 */
	public function description() {
		// TODO: Implement description() method.
	}

	/**
	 * @return int
	 */
	public function origin_parent_term_id() {
		// TODO: Implement origin_parent_term_id() method.
	}

	/**
	 * @return array
	 */
	public function locale_relations() {
		// TODO: Implement locale_relations() method.
	}

}