<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	W2M\Import\Common,
	DateTime;

/**
 * Class WpImportComment
 *
 * @package W2M\Import\Type
 */
class WpImportComment implements ImportCommentInterface {

	/**
	 * @var int
	 */
	private $id = NULL;

	/**
	 * @var int
	 */
	private $origin_id = 0;

	/**
	 * @var int
	 */
	private $origin_post_id = 0;

	/**
	 * @var string
	 */
	private $author_name = '';

	/**
	 * @var string
	 */
	private $author_email = '';

	/**
	 * @var string
	 */
	private $author_url = '';

	/**
	 * @var string
	 */
	private $author_ip = '';

	/**
	 * @var
	 */
	private $date;

	/**
	 * @var string
	 */
	private $content = '';

	/**
	 * @var int
	 */
	private $karma = 0;

	/**
	 * @var string
	 */
	private $approved = '1';

	/**
	 * @var string
	 */
	private $agent = '';

	/**
	 * @var string
	 */
	private $type = '';

	/**
	 * @var int
	 */
	private $origin_user_id = 0;

	/**
	 * @var int
	 */
	private $origin_parent_comment_id = 0;

	/**
	 * @var array
	 */
	private $meta = array();

	/**
	 * @var Common\ParameterSanitizerInterface
	 */
	private $param_sanitizer;

	/**
	 * @param array $attributes {
	 *      int      $origin_id,
	 *      int      $origin_post_id,
	 *      string   $author_name,
	 *      string   $author_email,
	 *      string   $author_url,
	 *      string   $author_ip,
	 *      DateTime $date
	 *      string   $content,
	 *      string   $karma,
	 *      string   $approved,
	 *      string   $agent,
	 *      string   $type,
	 *      int      $origin_user_id,
	 *      int      $origin_parent_comment_id,
	 *      array    $meta
	 * }
	 * @param Common\ParameterSanitizerInterface $param_sanitizer (Optional)
	 */
	public function __construct( Array $attributes, Common\ParameterSanitizerInterface $param_sanitizer = NULL ) {

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
			'origin_id'                => 'int',
			'origin_post_id'           => 'int',
			'author_name'              => 'string',
			'author_email'             => 'string',
			'author_url'               => 'string',
			'author_ip'                => 'string',
			'content'                  => 'string',
			'karma'                    => 'int',
			'approved'                 => 'string',
			'agent'                    => 'string',
			'type'                     => 'string',
			'origin_user_id'           => 'int',
			'origin_parent_comment_id' => 'int',
			'meta'                     => 'array'
		);

		$valid_attributes = $this->param_sanitizer->sanitize_parameter( $type_map, $attributes );
		foreach ( $type_map as $key => $type ) {
			if ( ! isset( $valid_attributes[ $key ] ) )
				continue;

			if ( 'meta' === $key ) {
				$valid_attributes[ $key ] = $this->param_sanitizer
					->sanitize_object_list(
						$valid_attributes[ $key ],
						'W2M\Import\Type\ImportMetaInterface'
					);
			}

			$this->{$key} = $valid_attributes[ $key ];
		}

		// date
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
		 * Todo: This hook is redundant to w2m_comment_imported and should be removed
		 *
		 * @deprecated
		 * @param ImportPostInterface $this
		 */
		do_action( 'w2m_import_set_comment_id', $this );
	}

	/**
	 * @return int
	 */
	public function origin_post_id() {

		return $this->origin_post_id;
	}

	/**
	 * @return string
	 */
	public function author_name() {

		return $this->author_name;
	}

	/**
	 * @return string
	 */
	public function author_email() {

		return $this->author_email;
	}

	/**
	 * @return string
	 */
	public function author_url() {

		return $this->author_url;
	}

	/**
	 * @return string
	 */
	public function author_ip() {

		return $this->author_ip;
	}

	/**
	 * @return DateTime
	 */
	public function date() {

		return $this->date;
	}

	/**
	 * @return string
	 */
	public function content() {

		return $this->content;
	}

	/**
	 * @return int
	 */
	public function karma() {

		return $this->karma;
	}

	/**
	 * @return string
	 */
	public function approved() {

		return $this->approved;
	}

	/**
	 * @return string
	 */
	public function agent() {

		return $this->agent;
	}

	/**
	 * @return string
	 */
	public function type() {

		return $this->type;
	}

	/**
	 * @return int
	 */
	public function origin_user_id() {

		return $this->origin_user_id;
	}

	/**
	 * @return int
	 */
	public function origin_parent_comment_id() {

		return $this->origin_parent_comment_id;
	}

	/**
	 * @return ImportMetaInterface[]
	 */
	public function meta() {

		return $this->meta;
	}

}