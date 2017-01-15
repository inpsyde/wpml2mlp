<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	W2M\Import\Common;

/**
 * Class WpImportTerm
 *
 * @package W2M\Import\Type
 */
class WpImportTerm implements ImportTermInterface {

	/**
	 * @var int
	 */
	private $origin_id = 0;

	/**
	 * @var int
	 */
	private $id = NULL;

	/**
	 * @var int
	 */
	private $taxonomy = 0;

	/**
	 * @var string
	 */
	private $name = '';

	/**
	 * @var string
	 */
	private $slug = '';

	/**
	 * @var string
	 */
	private $description = '';

	/**
	 * @var int
	 */
	private $origin_parent_term_id = 0;

	/**
	 * @var array
	 */
	private $locale_relations = array();

	/**
	 * @var Common\ParameterSanitizerInterface
	 */
	private $param_sanitizer;

	/**
	 * @param array $attributes {
	 *      int    $origin_id,
	 *      string $taxonomy,
	 *      string $name,
	 *      string $slug,
	 *      string $description,
	 *      int    $origin_parent_term_id,
	 *      array  $locale_relations
	 * }
	 * @param Common\ParameterSanitizerInterface $param_sanitizer (Optional)
	 */
	public function __construct(
		Array $attributes,
		Common\ParameterSanitizerInterface $param_sanitizer = NULL
	) {

		$this->param_sanitizer = $param_sanitizer
			? $param_sanitizer
			: new Common\TypeCastParameterSanitizer;

		$this->set_attributes( $attributes );
	}

	/**
	 * @param array $attributes
	 */
	private function set_attributes( Array $attributes ) {

		$type_map = array(
			'origin_id'             => 'int',
			'taxonomy'              => 'string',
			'name'                  => 'string',
			'slug'                  => 'string',
			'description'           => 'string',
			'origin_parent_term_id' => 'int',
			'locale_relations'      => 'array'
		);

		$attributes = $this->param_sanitizer
			->sanitize_parameter( $type_map, $attributes );

		foreach ( $type_map as $key => $type ) {
			if ( ! isset( $attributes[ $key ] ) ) {
				continue;
			}
			$this->{$key} = $attributes[ $key ];
		}
	}

	/**
	 * The id of the element in the original system
	 *
	 * @return int
	 */
	public function origin_id() {

		return $this->origin_id;
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

		if ( ! is_null( $this->id ) || empty( $id ) ) {
			return (int) $this->id;
		}

		$this->id = (int) $id;

		/**
		 * This action allows an automated mapping of old/new
		 * element ids
		 *
		 * Todo: This hook is redundant to w2m_term_imported and should be removed
		 *
		 * @deprecated
		 * @param ImportTermInterface $this
		 */
		do_action( 'w2m_import_set_term_id', $this );
	}

	/**
	 * @return string
	 */
	public function taxonomy() {

		return $this->taxonomy;
	}

	/**
	 * @return string
	 */
	public function name() {

		return $this->name;
	}

	/**
	 * @return string
	 */
	public function slug() {

		return $this->slug;
	}

	/**
	 * @return string
	 */
	public function description() {

		return $this->description;
	}

	/**
	 * @return int
	 */
	public function origin_parent_term_id() {

		return $this->origin_parent_term_id;
	}

	/**
	 * @return LocaleRelationInterface[]
	 */
	public function locale_relations() {

		return $this->locale_relations;
	}
}