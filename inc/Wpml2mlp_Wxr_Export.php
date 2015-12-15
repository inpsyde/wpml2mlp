<?php

/**
 * Class Wpml_Wxr_Export
 */
class Wpml_Wxr_Export {

	/**
	 * the current language code like en, fr ....
	 *
	 * @var string
	 */
	private $current_lng;

	/**
	 * all post of the current language
	 *
	 * @var string
	 */
	private $posts;

	const WXR_VERSION = '1.2';

	/**
	 * Constructs new Wpml_WXR_Export instance.
	 */
	public function __construct( $lng, $lng_obj ) {

		$this->current_lng = $lng;
		$this->posts       = $lng_obj[ 'posts' ];
		$this->categories  = $lng_obj[ 'category' ];
		$this->post_tags   = $lng_obj[ 'post_tag' ];

	}

	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 2.1.0
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 *
	 * @return string
	 */
	private function wxr_cdata( $str ) {

		if ( ! seems_utf8( $str ) ) {
			$str = utf8_encode( $str );
		}
		// $str = ent2ncr(esc_html($str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}

	/**
	 * Return the URL of the site
	 *
	 * @since 2.5.0
	 *
	 * @return string Site URL.
	 */
	private function wxr_site_url() {

		// Multisite: the base URL.
		if ( is_multisite() ) {
			return network_home_url();
		} // WordPress (single site): the blog URL.
		else {
			return get_bloginfo_rss( 'url' );
		}
	}

	/**
	 * Output a tag_name XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	private function wxr_tag_name( $tag ) {

		if ( empty( $tag->name ) ) {
			return;
		}

		echo '<wp:tag_name>' . $this->wxr_cdata( $tag->name ) . '</wp:tag_name>';
	}

	/**
	 * Output a tag_description XML tag from a given tag object
	 *
	 * @since 2.3.0
	 *
	 * @param object $tag Tag Object
	 */
	private function wxr_tag_description( $tag ) {

		if ( empty( $tag->description ) ) {
			return;
		}

		echo '<wp:tag_description>' . $this->wxr_cdata( $tag->description ) . '</wp:tag_description>';
	}

	/**
	 * Output a term_name XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	private function wxr_term_name( $term ) {

		if ( empty( $term->name ) ) {
			return;
		}

		echo '<wp:term_name>' . $this->wxr_cdata( $term->name ) . '</wp:term_name>';
	}

	/**
	 * Output a term_description XML tag from a given term object
	 *
	 * @since 2.9.0
	 *
	 * @param object $term Term Object
	 */
	private function wxr_term_description( $term ) {

		if ( empty( $term->description ) ) {
			return;
		}

		echo '<wp:term_description>' . $this->wxr_cdata( $term->description ) . '</wp:term_description>';
	}

	/**
	 * Output list of authors with posts
	 *
	 * @since 3.1.0
	 *
	 * @global wpdb $wpdb     WordPress database abstraction object.
	 *
	 * @param array $post_ids Array of post IDs to filter the query by. Optional.
	 */
	private function wxr_authors_list( array $post_ids = NULL ) {

		global $wpdb;

		if ( ! empty( $post_ids ) ) {
			$post_ids = array_map( 'absint', $post_ids );
			$and      = 'AND ID IN ( ' . implode( ', ', $post_ids ) . ')';
		} else {
			$and = '';
		}

		$authors = array();
		$results = $wpdb->get_results( "SELECT DISTINCT post_author FROM $wpdb->posts WHERE post_status != 'auto-draft' $and" );
		foreach ( (array) $results as $result ) {
			$authors[] = get_userdata( $result->post_author );
		}

		$authors = array_filter( $authors );

		$wxr_authors = FALSE;

		foreach ( $authors as $author ) {
			$wxr_authors .= "\n\t\t<wp:author>\n";
			$wxr_authors .= "\t\t\t<wp:author_id>" . intval( $author->ID ) . "</wp:author_id>\n";
			$wxr_authors .= "\t\t\t<wp:author_login>" . $this->wxr_cdata( $author->user_login ) . "</wp:author_login>\n";
			$wxr_authors .= "\t\t\t<wp:author_email>" . $this->wxr_cdata( $author->user_email ) . "</wp:author_email>\n";
			$wxr_authors .= "\t\t\t<wp:author_display_name>" . $this->wxr_cdata( $author->display_name ) . "</wp:author_display_name>\n";
			$wxr_authors .= "\t\t\t<wp:author_first_name>" . $this->wxr_cdata( $author->first_name ) . "</wp:author_first_name>\n";
			$wxr_authors .= "\t\t\t<wp:author_last_name>" . $this->wxr_cdata( $author->last_name ) . "</wp:author_last_name>\n";
			$wxr_authors .= "\t\t</wp:author>\n";
		}

		return $wxr_authors;
	}

	/**
	 * Ouput all navigation menu terms
	 *
	 * @since 3.1.0
	 */
	private function wxr_nav_menu_terms() {

		$nav_menus = wp_get_nav_menus();
		if ( empty( $nav_menus ) || ! is_array( $nav_menus ) ) {
			return;
		}

		foreach ( $nav_menus as $menu ) {
			echo "\t<wp:term>";
			echo '<wp:term_id>' . intval( $menu->term_id ) . '</wp:term_id>';
			echo '<wp:term_taxonomy>nav_menu</wp:term_taxonomy>';
			echo '<wp:term_slug>' . $this->wxr_cdata( $menu->slug ) . '</wp:term_slug>';
			wxr_term_name( $menu );
			echo "</wp:term>\n";
		}
	}

	/*
	* Get the requested terms ready, empty unless posts filtered by category
	* or all content.
	*/
	private function wxr_get_categories() {

		$wxr_categories = FALSE;

		foreach ( $this->categories as $category ) {
			$wxr_categories .= "\n\t\t<wp:category>\n";
			$wxr_categories .= "\t\t\t<wp:term_id>" . intval( $category->term_id ) . "</wp:term_id>\n";
			$wxr_categories .= "\t\t\t<wp:category_nicename>" . $this->wxr_cdata( $category->slug ) . "</wp:category_nicename >\n";
			$wxr_categories .= "\t\t\t<wp:category_parent>" . $this->wxr_cdata( $category->parent ? $category->parent : '' ) . " </wp:category_parent >\n";
			$wxr_categories .= "\t\t\t<wp:cat_name>" . $this->wxr_cdata( $category->name ) . "</wp:cat_name>\n";
			$wxr_categories .= "\t\t\t<wp:category_description>" . $this->wxr_cdata( $category->description ) . "</wp:category_description>\n";
			$wxr_categories .= "\t\t</wp:category>\n";
		}

		foreach ( $this->post_tags as $post_tag ) {
			$wxr_categories .= "\n\t\t<wp:tag>\n";
			$wxr_categories .= "\t\t\t<wp:term_id>" . intval( $post_tag->term_id ) . "</wp:term_id>\n";
			$wxr_categories .= "\t\t\t<wp:tag_slug>" . $this->wxr_cdata( $post_tag->slug ) . "</wp:tag_slug >\n";
			$wxr_categories .= "\t\t</wp:tag>\n";
		}

		return $wxr_categories;

	}

	/**
	 * Output list of taxonomy terms, in XML tag format, associated with a post
	 *
	 * @since 2.3.0
	 */
	private function wxr_post_taxonomy() {

		$post = get_post();

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( empty( $taxonomies ) ) {
			return;
		}
		$terms = wp_get_object_terms( $post->ID, $taxonomies );

		foreach ( (array) $terms as $term ) {
			echo "\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\">" . $this->wxr_cdata( $term->name ) . "</category>\n";
		}
	}

	/**
	 *
	 * @param bool   $return_me
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	private function wxr_filter_postmeta( $return_me, $meta_key ) {

		if ( '_edit_lock' == $meta_key ) {
			$return_me = TRUE;
		}

		return $return_me;
	}

	private function get_wxr_output( $items ) {

		$wxr_version   = Wpml_Wxr_Export::WXR_VERSION;
		$wxr_site_url  = $this->wxr_site_url();
		$wxr_generator = get_the_generator( 'export' );

		$time             = date( 'D, d M Y H:i:s +0000' );
		$blog_name        = get_bloginfo_rss( 'name' );
		$blog_url         = get_bloginfo_rss( 'url' );
		$blog_description = get_bloginfo_rss( 'description' );
		$blog_charset     = get_bloginfo( 'charset' );

		return <<<EOF
<?xml version="1.0" encoding="{$blog_charset}" ?>
{$wxr_generator}
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/{$wxr_version}/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/{$wxr_version}/"
	         >
	<channel>
		<title>{$blog_name}</title>
		<link>{$blog_url}</link>
		<description>{$blog_description}</description>
		<pubDate>{$time}</pubDate>
		<language>{$lng}</language>
		<wp:wxr_version>{$wxr_version}</wp:wxr_version>
		<wp:base_site_url>{$wxr_site_url}</wp:base_site_url>
		<wp:base_blog_url>{$blog_url}</wp:base_blog_url>
		{$items}
	</channel>
</rss>
EOF;

	}

	/**
	 * create wxr xml string
	 *
	 * @param $lng   current language of this wxr file like en it fr ...
	 * @param $posts all posts with translation relation
	 *
	 * return string xml
	 */
	public function get_wxr() {

		$wxr_items = $this->wxr_authors_list( $post_ids );
		$wxr_items .= $this->wxr_get_categories();

		$wxr = $this->get_wxr_output( $wxr_items );

		debug( $wxr );

	}

}