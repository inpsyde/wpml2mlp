<?php
/**
 * Class Wpml2mlp_Categorie_Creator
 *
 * @version 2015-08-24
 */
class Wpml2mlp_Categorie_Creator {

	/**
	 *
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 *
	 * @var Mlp_Content_Relations_Interface
	 */
	private $content_relations;

	/**
	 * Constructs the WPML2MLP_Categorie_Creator
	 *
	 * @param wpdb                            $wpdb
	 * @param Mlp_Content_Relations_Interface $content_relations
	 */
	public function __construct(
		wpdb $wpdb,
		Mlp_Content_Relations_Interface $content_relations
	) {

		if ( NULL === $wpdb ) {
			return;
		}

		$this->wpdb              = $wpdb;
		$this->content_relations = $content_relations;
	}

	/**
	 * Creates the categories from the language
	 *
	 * @param $blog
	 */
	public function create_categories_from_lng( $blog ) {

		global $sitepress;

		$current_language = $sitepress->get_current_language();
		$blog_language    = get_blog_language( $blog[ 'blog_id' ], TRUE );

		$sitepress->switch_lang( $blog_language );

		$get_cat_args = array(
			'hide_empty' => 0
		);
		$categories   = get_categories( $get_cat_args );

		$sitepress->switch_lang( $current_language );

		switch_to_blog( (int) $blog[ 'blog_id' ] );

		foreach ( $categories as $current_category ) {

			if ( ! term_exists( $current_category->name, 'category' ) ) {
				$mlp_category_id = self::add_category( $current_category );
			}
		}

		restore_current_blog();
	}

	/**
	 * Adds the category to the relevant site
	 *
	 * @param array $category
	 *
	 * @return int
	 */
	public function add_category( $category ) {

		$category_args = array(
			'cat_name'             => $category->name,
			'category_description' => $category->description,
			'category_nicename'    => $category->slug,
			'category_parent'      => $category->parent
		);

		return wp_insert_category( $category_args );
	}

}