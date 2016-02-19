<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Class BlacklistPostTypeFilter
 *
 * Basically for development purposes
 *
 * @package W2M\Import\Filter
 */
class BlacklistPostTypeFilter implements PostImportFilterInterface {

	/**
	 * @var array
	 */
	private $blacklist;

	/**
	 * @var PostImportFilterInterface
	 */
	private $filter;

	/**
	 * @param array $blacklist
	 * @param PostImportFilterInterface $filter (Optional)
	 */
	public function __construct( array $blacklist, PostImportFilterInterface $filter = NULL ) {

		$this->blacklist = $blacklist;
		$this->filter    = $filter
			? $filter
			: new PostPassThroughFilter;
	}
	/**
	 * Checks if a post should be imported or not
	 *
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return bool
	 */
	public function post_to_import( Type\ImportPostInterface $import_post ) {

		if ( in_array( $import_post->type(), $this->blacklist ) )
			return FALSE;

		return $this->filter->post_to_import( $import_post );
	}

}