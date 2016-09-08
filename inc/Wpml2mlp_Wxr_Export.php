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
	 * Constructs new Wpml_WXR_Export instance.git checkout
	 */
	public function __construct( $locale, $locale_obj ) {

		$this->current_locale = $locale;
		$this->posts          = $locale_obj[ 'posts' ];
		$this->categories     = $locale_obj[ 'category' ];
		$this->post_tags      = $locale_obj[ 'post_tag' ];
		$this->custom_items   = $locale_obj[ 'custom_items' ];

		$this->wxr_cache    = new Wpml2mlp_Wxr_Cache();
		$this->wxr_filename = 'wpml_export_' . $locale . '.xml';

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

		return '<wp:tag_name>' . $this->wxr_cdata( $tag->name ) . '</wp:tag_name>';
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

		return '<wp:tag_description>' . $this->wxr_cdata( $tag->description ) . '</wp:tag_description>';
	}

	/**
	 * Output list of taxonomy terms, in XML tag format, associated with a post
	 *
	 * @since 2.3.0
	 */
	function wxr_post_categories( $post_ID ) {

		$terms = wp_get_post_terms( $post_ID, 'category', array( "fields" => "all" ) );

		foreach ( (array) $terms as $term ) {
			$categories = "\t\t\t<category domain=\"{$term->taxonomy}\" nicename=\"{$term->slug}\" term_id=\"$term->term_id\">" . $this->wxr_cdata( $term->name ) . "</category>\n";
		}

		unset( $terms );

		if ( ! empty( $categories ) ) {

			return $categories;

		}

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

			foreach( $author->roles as $role ){

			}

			$wxr_authors .= "\n\t\t<wp:author>\n";
			$wxr_authors .= "\t\t\t<wp:author_id>" . intval( $author->ID ) . "</wp:author_id>\n";
			$wxr_authors .= "\t\t\t<wp:author_login>" . $this->wxr_cdata( $author->user_login ) . "</wp:author_login>\n";
			$wxr_authors .= "\t\t\t<wp:author_email>" . $this->wxr_cdata( $author->user_email ) . "</wp:author_email>\n";
			$wxr_authors .= "\t\t\t<wp:author_display_name>" . $this->wxr_cdata( $author->display_name ) . "</wp:author_display_name>\n";
			$wxr_authors .= "\t\t\t<wp:author_first_name>" . $this->wxr_cdata( $author->first_name ) . "</wp:author_first_name>\n";
			$wxr_authors .= "\t\t\t<wp:author_last_name>" . $this->wxr_cdata( $author->last_name ) . "</wp:author_last_name>\n";
			$wxr_authors .= "\t\t\t<wp:author_role>" . $this->wxr_cdata( $role ) . "</wp:author_role>\n";
			$wxr_authors .= "\t\t</wp:author>\n";
		}

		unset( $authors );
		unset( $results );

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

		if( property_exists( $this, 'categories' ) && ! empty( $this->categories ) ) {

			foreach ( $this->categories as $category ) {

				$wxr_categories .= "\n\t\t<wp:category>\n";
				$wxr_categories .= "\t\t\t<wp:term_id>" . intval( $category->term_id ) . "</wp:term_id>\n";
				$wxr_categories .= "\t\t\t<wp:category_nicename>" . $this->wxr_cdata( $category->slug ) . "</wp:category_nicename>\n";
				$wxr_categories .= "\t\t\t<wp:category_parent>" . $this->wxr_cdata( $category->parent ? $category->parent : '' ) . " </wp:category_parent>\n";
				$wxr_categories .= "\t\t\t<wp:cat_name>" . $this->wxr_cdata( $category->name ) . "</wp:cat_name>\n";
				$wxr_categories .= "\t\t\t<wp:category_description>" . $this->wxr_cdata( $category->description ) . "</wp:category_description>\n";
				$wxr_categories .= "\t\t\t<wp:taxonomy>" . $this->wxr_cdata( $category->taxonomy ) . "</wp:taxonomy>\n";
				$wxr_categories .= "\t\t</wp:category>\n";
			}

		}

		if( property_exists( $this, 'post_tags' ) && ! empty( $this->post_tags ) ) {

			foreach ( $this->post_tags as $post_tag ) {
				$wxr_categories .= "\n\t\t<wp:tag>\n";
				$wxr_categories .= "\t\t\t<wp:term_id>" . intval( $post_tag->term_id ) . "</wp:term_id>\n";
				$wxr_categories .= "\t\t\t<wp:tag_slug>" . $this->wxr_cdata( $post_tag->slug ) . "</wp:tag_slug >\n";
				$wxr_categories .= "\t\t\t" . $this->wxr_tag_name( $post_tag ) . "\n";
				$wxr_categories .= "\t\t\t" . $this->wxr_tag_description( $post_tag ) . "\n";
				$wxr_categories .= "\t\t</wp:tag>\n";
			}

		}

		return $wxr_categories;

	}


	private function wxr_get_custom_terms(){

		$wxr_custom_type = false;

		if( property_exists( $this, 'custom_items' ) && ! empty( $this->custom_items ) ) {

			foreach ( $this->custom_items as $custom_type => $custom_items ) {

				foreach ( $custom_items as $custom_item ) {

					$wxr_custom_type .= "\n\t\t<wp:term>\n";
					$wxr_custom_type .= "\t\t\t<wp:term_id>" . intval( $custom_item->term_id ) . "</wp:term_id>\n";
					$wxr_custom_type .= "\t\t\t<wp:term_slug>" . $this->wxr_cdata( $custom_item->slug ) . "</wp:term_slug>\n";
					$wxr_custom_type .= "\t\t\t<wp:term_name>" . $this->wxr_cdata( $custom_item->name ) . "</wp:term_name>\n";
					$wxr_custom_type .= "\t\t\t<wp:term_taxonomy>" . $this->wxr_cdata( $custom_item->taxonomy ) . "</wp:term_taxonomy>\n";
					$wxr_custom_type .= "\t\t</wp:term>\n";

				}

			}

		}


		return $wxr_custom_type;

	}


	private function wxr_get_post_items() {


		global $wpdb;

		$wxr_items = FALSE;

		foreach ( $this->posts as $i => $post ) {

			$wxr_items .= "\n\t\t<item>\n";
			$wxr_items .= "\t\t\t<title>" . apply_filters( 'the_title_rss', $post->post_title ) . "</title>\n";
			$wxr_items .= "\t\t\t<link>" . $this->wxr_cdata( get_permalink( $post->ID ) ) . "</link>\n";
			$wxr_items .= "\t\t\t<pubDate>" . $this->wxr_cdata( $post->post_date ) . "</pubDate>\n";
			$wxr_items .= "\t\t\t<dc:creator>" . $this->wxr_cdata( get_the_author_meta( $post->post_author ) ) . "</dc:creator>\n";
			$wxr_items .= "\t\t\t<guid isPermaLink=\"false\">" . $this->wxr_cdata( get_the_guid( $post->ID ) ) . "</guid>\n";
			$wxr_items .= "\t\t\t<excerpt:encoded>" . $this->wxr_cdata( $post->post_excerpt ) . "</excerpt:encoded>\n";
			$wxr_items .= "\t\t\t<content:encoded>" . $this->wxr_cdata( $post->post_content ) . "</content:encoded>\n";

			$wxr_items .= "\t\t\t<wp:post_id>" . intval( $post->ID ) . "</wp:post_id>\n";
			$wxr_items .= "\t\t\t<wp:post_date>" . $this->wxr_cdata( $post->post_date ) . "</wp:post_date>\n";
			$wxr_items .= "\t\t\t<wp:post_author>" . $this->wxr_cdata( $post->post_author ) . "</wp:post_author>\n";
			#$wxr_items .= "\t\t\t<wp:post_date_gmt>" . $this->wxr_cdata( $post->post_date_gmt ) . "</wp:post_date_gmt>\n";
			$wxr_items .= "\t\t\t<wp:comment_status>" . $this->wxr_cdata( $post->comment_status ) . "</wp:comment_status>\n";
			$wxr_items .= "\t\t\t<wp:ping_status>" . $this->wxr_cdata( $post->ping_status ) . "</wp:ping_status>\n";
			$wxr_items .= "\t\t\t<wp:post_name>" . $this->wxr_cdata( $post->post_name ) . "</wp:post_name>\n";
			$wxr_items .= "\t\t\t<wp:status>" . $this->wxr_cdata( $post->post_status ) . "</wp:status>\n";
			$wxr_items .= "\t\t\t<wp:post_parent>" . intval( $post->post_parent ) . "</wp:post_parent>\n";
			$wxr_items .= "\t\t\t<wp:menu_order>" . intval( $post->menu_order ) . "</wp:menu_order>\n";
			$wxr_items .= "\t\t\t<wp:post_type>" . $this->wxr_cdata( $post->post_type ) . "</wp:post_type>\n";
			$wxr_items .= "\t\t\t<wp:post_password>" . $this->wxr_cdata( $post->post_password ) . "</wp:post_password>\n";
			$wxr_items .= "\t\t\t<wp:is_sticky>" . intval( is_sticky( $post->ID ) ) . "</wp:is_sticky>\n";

			if ( $post->post_type == 'attachment' ) {
				$wxr_items .= "\t\t\t<wp:attachment_url>" . $this->wxr_cdata( wp_get_attachment_url( $post->ID ) ) . "</wp:attachment_url>\n";
			}

			$wxr_items .= $this->wxr_post_categories( $post->ID );
			$wxr_items .= $this->wxr_get_postmeta( $post );
			$wxr_items .= $this->wxr_get_translations( $post->translations );
			$wxr_items .= $this->wxr_comments( $post->ID );

			$wxr_items .= "\n\t\t</item>\n";

/*
			#buddy take a break, its hard work i now ;)
			if ( $i >= 50 ) {

				$this->wxr_cache->write( $wxr_items, $this->wxr_filename );

				unset( $wxr_items );

				$i = 0;
			}*/


		}

		return $wxr_items;

	}

	/**
	 * Filter whether to selectively skip post meta used for WXR exports.
	 *
	 * Returning a truthy value to the filter will skip the current meta
	 * object from being exported.
	 *
	 * @since 3.3.0
	 *
	 * @param bool   $skip     Whether to skip the current post meta. Default false.
	 * @param string $meta_key Current meta key.
	 * @param object $meta     Current meta object.
	 */
	private function wxr_get_postmeta( $post ) {

		global $wpdb;

		$postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $post->ID ) );

		$wxr_postmeta = FALSE;

		foreach ( $postmeta as $meta ) {

			$wxr_postmeta .= "\n\t\t\t<wp:postmeta>\n";
			$wxr_postmeta .= "\t\t\t\t<wp:meta_key>" . $this->wxr_cdata( $meta->meta_key ) . "</wp:meta_key>\n";
			$wxr_postmeta .= "\t\t\t\t<wp:meta_value>" . $this->wxr_cdata( $meta->meta_value ) . "</wp:meta_value>\n";
			$wxr_postmeta .= "\t\t\t</wp:postmeta>\n";

		}

		return apply_filters( 'wpml2mlp_xml_postmeta', $wxr_postmeta, $post );

	}

	private function wxr_get_translations( $post_translations ) {

		$wxr_post_translations = FALSE;

		if ( ! empty( $post_translations ) ) {

			foreach ( $post_translations as $lng => $post_id ) {

				$wxr_post_translations .= "\n\t\t\t<wp:translation>\n";
				$wxr_post_translations .= "\t\t\t\t<wp:locale>" . $this->wxr_cdata( $lng ) . "</wp:locale>\n";
				$wxr_post_translations .= "\t\t\t\t<wp:element_id>" . intval( $post_id ) . "</wp:element_id>\n";
				$wxr_post_translations .= "\t\t\t</wp:translation>\n";

			}

		}

		return $wxr_post_translations;

	}

	private function wxr_comments( $post_id ) {

		global $wpdb;

		$_comments = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d AND comment_approved = 1", $post_id ) );

		$comments = array_map( 'get_comment', $_comments );

		$wxr_post_comments = FALSE;

		foreach ( $comments as $comment ) {

			$wxr_post_comments .= "\n\t\t\t<wp:comment>\n";

			foreach ( $comment as $wxr_tag => $wxr_data ) {

				if ( $wxr_tag == 'comment_id' || $wxr_tag == 'comment_parent' || $wxr_tag == 'comment_user_id' ) {
					$wxr_data = intval( $wxr_data );
				} else {
					$wxr_data = $this->wxr_cdata( $wxr_data );
				}

				$wxr_post_comments .= "\t\t\t\t<wp:" . $wxr_tag . ">" . $wxr_data . "</wp:" . $wxr_tag . ">\n";

			}

			$wxr_post_comments .= "\t\t\t</wp:comment>\n";

		}

		return $wxr_post_comments;

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

	private function get_wxr_header() {

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
		<language>{$this->current_locale}</language>
		<wp:wxr_version>{$wxr_version}</wp:wxr_version>
		<wp:base_site_url>{$wxr_site_url}</wp:base_site_url>
		<wp:base_blog_url>{$blog_url}</wp:base_blog_url>
EOF;

	}

	private function get_wxr_footer() {

		return <<<EOF

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

		$post_ids = [];
		foreach( $this->posts as $post ){
			$post_ids[] = $post->ID;
		}


		$this->wxr_cache->unlink_wxr( $this->wxr_filename );

		$this->wxr_cache->write( $this->get_wxr_header(), $this->wxr_filename );
		$this->wxr_cache->write( $this->wxr_authors_list( $post_ids ), $this->wxr_filename );
		$this->wxr_cache->write( $this->wxr_get_categories(), $this->wxr_filename );
		#$this->wxr_cache->write( $this->wxr_get_custom_terms(), $this->wxr_filename );
		$this->wxr_cache->write( $this->wxr_get_post_items(), $this->wxr_filename );

		$this->wxr_cache->write( $this->get_wxr_footer(), $this->wxr_filename, FALSE );

		unset( $wxr_items );

		$wxr = $this->wxr_cache->get_wxr_stack();

		return $wxr;

	}

}