<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Common,
	W2M\Import\Data,
	W2M\Import\Type,
	WP_Post;

/**
 * Class DuplicatePostFilter
 *
 * Todo: Write tests for
 *
 * @package W2M\Import\Filter
 */
class DuplicatePostFilter implements PostImportFilterInterface, Data\PostImportListenerInterface {

	/**
	 * @var string
	 */
	private $meta_key = '_w2m_remote_guid';

	/**
	 * @var Common\WpFactoryInterface
	 */
	private $wp_factory;

	/**
	 * @param Common\WpFactoryInterface $wp_factory (Optional)
	 */
	public function __construct( Common\WpFactoryInterface $wp_factory = NULL ) {

		$this->wp_factory = $wp_factory
			? $wp_factory
			: new Common\WpFactory;
	}

	/**
	 * Checks if a post should be imported or not
	 *
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return bool
	 */
	public function post_to_import( Type\ImportPostInterface $import_post ) {

		$query = $this->wp_factory->wp_query(
			[
				'posts_per_page'         => 1,
				'post_status'            => 'any',
				'post_type'              => 'any',
				'meta_key'               => $this->meta_key,
				'meta_value'             => $import_post->guid(),
				'fields'                 => 'ids',
				'update_post_meta_cache' => FALSE,
				'update_post_term_cache' => FALSE
			]
		);

		return ! $query->have_posts();
	}

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 */
	public function record_post( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {

		update_post_meta(
			$wp_post->ID,
			$this->meta_key,
			$import_post->guid()
		);
	}
}