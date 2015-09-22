<?php

class Wpml2mlp_Importer {

	/**
	 * @var wpdb
	 */
	private $wpdb;

	/**
	 * @var blog_cache
	 */

	private $blog_cache;

	/**
	 * @var WPML2MLP_Site_Creator
	 */
	private $site_creator;

	/**
	 * @var WPML2MLP_Post_Creator
	 */
	private $post_creator;

	/**
	 * @var WPML2MLP_Translations_Builder
	 */
	private $translation_builder;

	/**
	 * @var WPML2MLP_Language_Holder
	 */
	private $language_holder;

	/**
	 * @var string
	 */
	private $main_language;

	/**
	 * @var Wpml2mlp_Xliff_Extractor
	 */
	private $xliff_extractor;

	/**
	 * @var string
	 */
	private static $show_success_msg;

	/**
	 * @var string
	 */
	private static $xliff_message;

	/**
	 * Constructor
	 *
	 * @param wpdb $wpdb
	 */
	public function __construct( wpdb $wpdb = NULL ) {

		if ( NULL === $wpdb ) {
			return;
		}

		$this->wpdb = $wpdb;

		$link_table = $wpdb->base_prefix . 'multilingual_linked';
		$table_list = new Mlp_Db_Table_List( $this->wpdb );

		$site_relations            = new Mlp_Site_Relations( $wpdb, 'mlp_site_relations' );
		$content_relations         = new Mlp_Content_Relations(
			$this->wpdb,
			$site_relations,
			new Mlp_Db_Table_Name( $link_table, $table_list )
		);
		$this->site_creator        = new Wpml2mlp_Site_Creator( $this->wpdb );
		$this->post_creator        = new Wpml2mlp_Post_Creator( $this->wpdb, $content_relations );
		$this->categorie_creator   = new Wpml2mlp_Categorie_Creator( $this->wpdb, $content_relations );
		$this->main_language       = Wpml2mlp_Helper::get_main_language();
		$this->translation_builder = new Wpml2mlp_Translations_Builder( $this->main_language );
		$this->language_holder     = new Wpml2mlp_Language_Holder();
		$this->xliff_extractor     = new Wpml2mlp_Xliff_Extractor();

		self::$show_success_msg = FALSE;
		self::$xliff_message    = FALSE;
	}

	/**
	 * Runs import of xliff files to the mlp and creates new sites in multisite environment if doesn't exists.
	 */
	private function do_xliff2mlp() {

		$zip_file = $this->xliff_extractor->check_and_get_xliff_zip( $_FILES[ 'xliff_translations' ] );

		if ( ! $zip_file ) {
			self::$xliff_message = "Please select correct file for upload.";

			return;
		}

		self::check_sites();

		self::create_posts( $this->xliff_extractor->extract( $zip_file ) );
	}

	/**
	 * Runs import of wmpl to the mlp and creates new sites in multisite environment if doesn't exists.
	 */
	private function do_wpml2mlp() {

		self::check_sites();

		self::create_categories();

		self::create_posts( Wpml2mlp_Helper::get_all_posts() );
	}

	/**
	 * @param $posts_arr
	 *
	 * Creates posts in multisite.
	 */
	private function create_posts( $posts_arr ) {

		if ( ! $posts_arr ) {

			return;
		}

		foreach ( $posts_arr as $current_post ) {

			$relevant_blog = $this->get_relevant_blog( $current_post );

			if ( $relevant_blog != FALSE ) {
				if ( ! $this->post_creator->post_exists( $current_post, $relevant_blog ) ) {
					$mlp_post_id = $this->post_creator->add_post( $current_post, $relevant_blog );
				} else {
					$mlp_post_id = $this->post_creator->update( $current_post, $relevant_blog );
				}

				self::set_post_categories( $current_post, $relevant_blog );

			}
		}
		self::$show_success_msg = TRUE;
	}

	/**
	 * Creates categories in multisite.
	 */
	private function create_categories( ) {

		if ( ! $this->blog_cache ) {
			$this->blog_cache = wp_get_sites();
		}

		foreach ( $this->blog_cache as $blog ) {
			$this->categorie_creator->create_categories_from_lng( $blog );
		}

		self::$show_success_msg = TRUE;

	}

	/**
	 * Set post categories in multisite
	 *
	 * @param $post
	 *
	 * @param $blog
	 *
	 */
	private function set_post_categories( $post, $blog ) {

		$get_cats_args = array(
			"fields" => "slugs"
		);
		$post_cat_arr = wp_get_post_categories( $post->ID , $get_cats_args);

		if( ! empty( $post_cat_arr ) ){

			switch_to_blog( (int) $blog[ 'blog_id' ] );

			$cat_id_arr = array();

			foreach( $post_cat_arr as $post_cat_slug ){
				$cat = get_category_by_slug($post_cat_slug);
				$cat_id_arr[] = $cat->cat_ID;
			}

			$mlp_post_id = $this->post_creator->get_multisite_id( $post, $blog );
			
			wp_set_post_categories( $mlp_post_id, $cat_id_arr, FALSE );

			restore_current_blog();
		}
	}

	/**
	 * Checks if relevant sites exists.
	 */
	private function check_sites() {

		$lng_arr = icl_get_languages( 'skip_missing=1' );

		foreach ( $lng_arr as $lng ) {
			if ( ! $this->site_creator->site_exists( $lng ) ) {
				$this->site_creator->create_site( $lng );
			}
		}

		$this->blog_cache = FALSE; // reset object cache after adding new site. (it will be recreated)
	}

	/**
	 * Runs the import from WPML or Xliff to MLP
	 */
	public static function display() {

		?>
		<div class="wrap">
			<?php if ( ! is_network_admin() ) { ?>
				<div>
					To perform import from WPML or Xliff to multi site you need to be<br />
					on network admin and have "Multilingual Press" plugin enabled.
				</div>
				<hr />
			<?php } ?>
			<?php if ( self::$show_success_msg ) { ?>
				<h3>
					You have successfully import translations data to the MLP.
				</h3>
				<hr />
			<?php } ?>

			<h2><?php _e( 'WPML 2 MLP import' ); ?></h2>

			<p><?php _e( 'MLP import from WPML.' ); ?></p>
			<?php
			if ( is_network_admin() ) {
				?>

				<form method="post" action="settings.php?page=wpml2mlp">

					<input type="hidden" name="post_type" value="do_wmpl_2_mlp" />

					<?php
					submit_button( __( 'Run WPML to MLP import' ) ); ?>

				</form>
			<?php } else { ?>
				<div>
					<button disabled="disabled">Run WPML to MLP import</button>
				</div>
			<?php } ?>
			</p>
			<hr />
			<p>

			<form method="post" action="settings.php?page=wpml2mlp" enctype="multipart/form-data">
				<h2>
					Xliff 2 MLP import
				</h2>

				<p><?php _e( 'MLP import from WPML.' ); ?></p>

				<?php if ( self::$xliff_message ) { ?>
					<p>
						<?php _e( self::$xliff_message ) ?>
					</p>
				<?php } ?>

				<input type="hidden" name="post_type" value="do_xliff_import" />
				<?php
				if ( is_network_admin() ) {
					?>
					<div>
						<input type="file" name="xliff_translations" id="xliff_translations" />
					</div>
					<div>
						<?php submit_button( __( 'Upload xliff translations' ) ); ?>
					</div>
				<?php
				} else {
					?>
					<div>
						<input type="file" disabled="disabled" />
					</div>
					<div>
						<button disabled="disabled">Upload xliff translations</button>
					</div>
				<?php } ?>
			</form>
			</p>
			<hr />
		</div>
	<?php
	}

	/**
	 * Runs the import from WPML to MLP
	 *
	 * @wp-hook admin_init
	 */
	public function run_import() {

		if ( isset( $_POST[ 'submit' ] ) ) {
			if ( $_POST[ 'post_type' ] == 'do_wmpl_2_mlp' ) {
				$this->do_wpml2mlp();
			} else if ( $_POST[ 'post_type' ] == 'do_xliff_import' ) {
				$this->do_xliff2mlp();
			}
		}
	}

	/**
	 * Gets blog relevant for provided post.
	 *
	 * @param $post
	 *
	 * @return blog
	 */
	private function get_relevant_blog( $post ) {

		if ( ! $this->blog_cache ) {
			$this->blog_cache = wp_get_sites();
		}

		$pst_lng = Wpml2mlp_Helper::get_language_info( $post->ID );

		foreach ( $this->blog_cache as $ab ) {
			if ( get_blog_language( $ab[ 'blog_id' ], TRUE ) == $pst_lng ) {
				return $ab;
			}
		}

		return FALSE;
	}

	/**
	 * Setups the importer.
	 */
	public function setup() {

		/**
		 * Run import on admin_init
		 */
		add_action( 'admin_init', array( $this, 'run_import' ) );
	}
}