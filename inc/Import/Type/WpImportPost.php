<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	W2M\Import\Common,
	DateTime;

/**
 * Class WpImportPost
 *
 * @package W2M\Import\Type
 */
class WpImportPost implements ImportPostInterface {

	/**
	 * @var int
	 */
	private $origin_id = 0;

	/**
	 * @var null
	 */
	private $id = NULL;

	/**
	 * @var string
	 */
	private $title = '';

	/**
	 * @var string
	 */
	private $guid = '';

	/**
	 * @var \DateTime
	 */
	private $date = NULL;

	/**
	 * @var string
	 */
	private $comment_status = '';

	/**
	 * @var string
	 */
	private $ping_status = '';

	/**
	 * @var int
	 */
	private $origin_author_id = 0;

	/**
	 * @var string
	 */
	private $type = '';

	/**
	 * @var bool
	 */
	private $is_sticky = FALSE;

	/**
	 * @var string
	 */
	private $origin_link = '';

	/**
	 * @var string
	 */
	private $excerpt = '';

	/**
	 * @var string
	 */
	private $content = '';

	/**
	 * @var string
	 */
	private $name = '';

	/**
	 * @var string
	 */
	private $status = '';

	/**
	 * @var int
	 */
	private $origin_parent_post_id = 0;

	/**
	 * @var int
	 */
	private $menu_order = 0;

	/**
	 * @var string
	 */
	private $password = '';

	/**
	 * @var array
	 */
	private $terms = array();

	/**
	 * @var array
	 */
	private $meta = array();

	/**
	 * @var array
	 */
	private $locale_relations = array();

	/**
	 * @var string
	 */
	private $origin_attachment_url = '';

	/**
	 * @var Common\ParameterSanitizerInterface
	 */
	private $param_sanitizer;

	/**
	 * @param array $attributes {
	 *      int      $origin_id,
	 *      string   $title,
	 *      string   $guid,
	 *      DateTime $date,
	 *      string   $comment_status,
	 *      string   $ping_status,
	 *      int      $origin_author_id
	 *      string   $type,
	 *      bool     $is_sticky,
	 *      string   $origin_link,
	 *      string   $excerpt,
	 *      string   $content
	 *      string   $name,
	 *      string   $status,
	 *      int      $origin_parent_post_id,
	 *      int      $menu_order,
	 *      string   $password,
	 *      array    $terms,
	 *      array    $meta,
	 *      array    $locale_relations,
	 * }
	 * @param Common\ParameterSanitizerInterface $param_sanitizer (Optional)
	 */
	public function __construct(
		Array $attributes,
		Common\ParameterSanitizerInterface $param_sanitizer = NULL
	) {

		$this->param_sanitizer = $param_sanitizer
			? $param_sanitizer
			: new Common\TypeCastParameterSanitizer();
		$this->set_attributes( $attributes );
	}

	/**
	 * @param array $attributes
	 */
	private function set_attributes( Array $attributes ) {

		$type_map = array(
			'origin_id'             => 'int',
			'title'                 => 'string',
			'guid'                  => 'string',
			'comment_status'        => 'string',
			'ping_status'           => 'string',
			'origin_author_id'      => 'int',
			'type'                  => 'string',
			'is_sticky'             => 'bool',
			'origin_link'           => 'string',
			'excerpt'               => 'string',
			'content'               => 'string',
			'name'                  => 'string',
			'status'                => 'string',
			'origin_parent_post_id' => 'int',
			'menu_order'            => 'int',
			'password'              => 'string',
			'terms'                 => 'array',
			'meta'                  => 'array',
			'locale_relations'      => 'array',
			'origin_attachment_url' => 'string'
		);

		$valid_attributes = $this->param_sanitizer
			->sanitize_parameter( $type_map, $attributes );

		foreach ( $type_map as $key => $type ) {
			if ( ! isset( $valid_attributes[ $key ] ) ) {
				continue;
			}
			if ( 'terms' === $key ) {
				$valid_attributes[ $key ] = $this->param_sanitizer
					->sanitize_object_list(
						$valid_attributes[ $key ],
						'W2M\Import\Type\TermReferenceInterface'
					);
			}
			if ( 'meta' === $key ) {
				$valid_attributes[ $key ] = $this->param_sanitizer
					->sanitize_object_list(
						$valid_attributes[ $key ],
						'W2M\Import\Type\ImportMetaInterface'
					);
			}
			if ( 'locale_relations' === $key ) {
				$valid_attributes[ $key ] = $this->param_sanitizer
					->sanitize_object_list(
						$valid_attributes[ $key ],
						'W2M\Import\Type\LocaleRelationInterface'
					);
			}

			$this->{$key} = $valid_attributes[ $key ];
		}

		// Date
		$this->date = isset( $attributes[ 'date' ] )
			&& is_a( $attributes[ 'date' ], 'DateTime' )
			? $attributes[ 'date' ]
			: new DateTime;
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
		 * Todo: This hook is redundant to w2m_post_imported and should be removed
		 *
		 * @deprecated
		 * @param ImportPostInterface $this
		 */
		do_action( 'w2m_import_set_post_id', $this );
	}

	/**
	 * @return string
	 */
	public function title() {

		return $this->title;
	}

	/**
	 * @return string
	 */
	public function guid() {

		return $this->guid;
	}

	/**
	 * @return \DateTime
	 */
	public function date() {

		return $this->date;
	}

	/**
	 * @return string
	 */
	public function comment_status() {

		return $this->comment_status;
	}

	/**
	 * @return string
	 */
	public function ping_status() {

		return $this->ping_status;
	}

	/**
	 * @return string
	 */
	public function origin_author_id() {

		return $this->origin_author_id;
	}

	/**
	 * @return string
	 */
	public function type() {

		return $this->type;
	}

	/**
	 * @return bool
	 */
	public function is_sticky() {

		return $this->is_sticky;
	}

	/**
	 * @return string
	 */
	public function origin_link() {

		return $this->origin_link;
	}

	/**
	 * @return string
	 */
	public function excerpt() {

		return $this->excerpt;
	}

	/**
	 * @return string
	 */
	public function content() {

		return $this->content;
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
	public function status() {

		return $this->status;
	}

	/**
	 * @return int
	 */
	public function origin_parent_post_id() {

		return $this->origin_parent_post_id;
	}

	/**
	 * @return int
	 */
	public function menu_order() {

		return $this->menu_order;
	}

	/**
	 * @return string
	 */
	public function password() {

		return $this->password;
	}

	/**
	 * @return TermReferenceInterface[]
	 */
	public function terms() {

		return $this->terms;
	}

	/**
	 * @return ImportMetaInterface[]
	 */
	public function meta() {

		return $this->meta;
	}

	/**
	 * @return LocaleRelationInterface[]
	 */
	public function locale_relations() {

		return $this->locale_relations;
	}


	/**
	 * @return string
	 */
	public function origin_attachment_url() {

		return $this->origin_attachment_url;
	}
}