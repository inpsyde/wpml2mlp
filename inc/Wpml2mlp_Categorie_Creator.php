<?php

/**
 * Class Wpml2mlp_Categorie_Creator
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
	 */
	public function __construct(
		wpdb $wpdb,
		Mlp_Content_Relations_Interface $content_relations
	) {

		if ( NULL == $wpdb ) {
			return;
		}

		$this->wpdb              = $wpdb;
		$this->content_relations = $content_relations;
	}

	/**
	 * Creates the categories from the language
	 *
	 * @param $blog
	 *
	 */
	public function create_categories_from_lng( $blog ) {

		global $sitepress;

		$current_language = $sitepress->get_current_language();
		$blog_language = get_blog_language( $blog[ 'blog_id' ], TRUE );

		$sitepress->switch_lang($blog_language);

		$get_cat_args = array(
			"hide_empty" => 0
		);
		$categories = get_categories($get_cat_args);

		$sitepress->switch_lang($current_language);

		switch_to_blog( (int) $blog[ 'blog_id' ] );

		foreach ( $categories as $current_categorie ) {

			if(!term_exists($current_categorie->name, 'category')){
				$mlp_categorie_id = self::add_categorie( $current_categorie );
			}
		}

		restore_current_blog();

	}

	/**
	 * Adds the categorie to the relevant site
	 *
	 * @param $categorie
	 *
	 * @return int
	 */
	public function add_categorie( $categorie ) {

		$categorie_args = array(
			'cat_name' => $categorie->name,
			'category_description' => $categorie->description,
			'category_nicename' => $categorie->slug,
			'category_parent' => $categorie->parent
		);

		return wp_insert_category($categorie_args);

	}

}