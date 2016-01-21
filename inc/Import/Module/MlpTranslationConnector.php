<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Type,
	W2M\Import\Data,
	W2M\Import\Common,
	Mlp_Content_Relations_Interface,
	WP_Error,
	WP_Query,
	WP_Post;

class MlpTranslationConnector implements TranslationConnectorInterface {

	/**
	 * @var string
	 */
	private $origin_id_post_meta_key = '_w2m_origin_post_id';

	/**
	 * @var Mlp_Content_Relations_Interface
	 */
	private $mlp_content_relations;

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @var Common\WpFactoryInterface
	 */
	private $wp_factory;

	/**
	 * @param Mlp_Content_Relations_Interface $mpl_content_relations
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 * @param Common\WpFactoryInterface $wp_factory (Optional)
	 */
	public function __construct(
		Mlp_Content_Relations_Interface $mpl_content_relations,
		Data\MultiTypeIdMapperInterface $id_mapper,
		Common\WpFactoryInterface $wp_factory = NULL
	) {

		$this->mlp_content_relations = $mpl_content_relations;
		$this->id_mapper             = $id_mapper;
		$this->wp_factory            = $wp_factory
			? $wp_factory
			: new Common\WpFactory;
	}

	/**
	 * @wp-hook w2m_term_imported
	 *
	 * @param object $wp_term (stdClass, since WP 4.4 WP_Term)
	 * @param Type\ImportTermInterface $import_term
	 *
	 * @return void
	 */
	public function link_term( $wp_term, Type\ImportTermInterface $import_term ) {
		// TODO: Implement link_term() method.
	}

	/**
	 * @wp-hook w2m_post_imported
	 *
	 * @param WP_Post $wp_post
	 * @param Type\ImportPostInterface $import_post
	 *
	 * @return void
	 */
	public function link_post( WP_Post $wp_post, Type\ImportPostInterface $import_post ) {

		$this->set_origin_post_id_meta( $import_post );

		/* @var Type\LocaleRelationInterface $relation */
		foreach ( $import_post->locale_relations() as $relation ) {
			$blog_id = $this->get_blog_id_by_locale( $relation->locale() );
			if ( ! $blog_id ) {
				$this->trigger_missing_blog_error( $import_post, $relation );
				continue;
			}
			$remote_post_id = $this->get_remote_post_id( $blog_id, $relation );
			if ( ! $remote_post_id ) {
				$this->trigger_missing_remote_post( $import_post, $relation );
				continue;
			}
			$this->mlp_content_relations->set_relation(
				get_current_blog_id(),
				$blog_id,
				$import_post->id(),
				$remote_post_id
			);
		}
	}

	/**
	 * Gets the blog id for a locale (two or four letter)
	 *
	 * @param $locale
	 *
	 * @return int|mixed|string
	 */
	public function get_blog_id_by_locale( $locale ) {

		$locales = mlp_get_available_languages( TRUE );
		$blog_id = array_search( $locale, $locales );
		if ( $blog_id )
			return $blog_id;

		// try to match two-letter language code like en,de,fr
		foreach ( $locales as $blog_id => $l ) {
			if ( 0 === strpos( $l, $locale ) )
				return $blog_id;
		}

		return 0;
	}

	/**
	 * Gets the remote post id by the origin id
	 *
	 * @param $blog_id
	 * @param Type\LocaleRelationInterface $relation
	 *
	 * @return int
	 */
	public function get_remote_post_id( $blog_id, Type\LocaleRelationInterface $relation ) {

		switch_to_blog( $blog_id );
		$query = $this->wp_factory->wp_query(
			[
				'posts_per_page'         => 1,
				'post_status'            => 'any',
				'post_type'              => 'any',
				'meta_key'               => $this->origin_id_post_meta_key,
				'meta_value'             => $relation->origin_id(),
				'fields'                 => 'ids',
				'update_post_meta_cache' => FALSE,
				'update_post_term_cache' => FALSE
			]
		);
		restore_current_blog();

		if ( empty( $query->posts ) )
			return 0;

		return (int) current( $query->posts );
	}

	/**
	 * @param Type\ImportPostInterface $import_post
	 */
	private function set_origin_post_id_meta( Type\ImportPostInterface $import_post ) {

		update_post_meta(
			$import_post->id(),
			$this->origin_id_post_meta_key,
			$import_post->origin_id()
		);
	}

	/**
	 * Propagate an error that there was no blog for a given locale
	 *
	 * @param Type\ImportPostInterface $import_post
	 * @param Type\LocaleRelationInterface $locale_relation
	 */
	private function trigger_missing_blog_error(
		Type\ImportPostInterface $import_post,
		Type\LocaleRelationInterface $locale_relation
	) {

		$error = $this->wp_factory->wp_error(
			'locale',
			"Cannot find blog for locale {$locale_relation->locale()}"
		);
		$error->add_data(
			array (
				'data' => array(
					'locale_relation' => $locale_relation,
					'import_post' => $import_post
				)
			),
			'locale'
		);
		$this->propagate_error( $error );
	}

	/**
	 * Propagate an error that there was no blog for a given locale
	 *
	 * @param Type\ImportPostInterface $import_post
	 * @param Type\LocaleRelationInterface $locale_relation
	 */
	private function trigger_missing_remote_post(
		Type\ImportPostInterface $import_post,
		Type\LocaleRelationInterface $locale_relation
	) {

		$error = $this->wp_factory->wp_error(
			'post',
			"Cannot find remote post for locale {$locale_relation->locale()}"
		);
		$error->add_data(
			array (
				'data' => array(
					'locale_relation' => $locale_relation,
					'import_post' => $import_post
				)
			),
			'post'
		);
		$this->propagate_error( $error );
	}

	/**
	 * @param WP_Error $error
	 */
	private function propagate_error( WP_Error $error ) {

		/**
		 * @param WP_Error $error
		 */
		do_action( 'w2m_import_mlp_link_error', $error );
	}
}