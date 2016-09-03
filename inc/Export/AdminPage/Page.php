<?php # -*- coding: utf-8 -*-

namespace W2M\Export\AdminPage;

/**
 * wpml2mlp export admin page here we add two tabs to the export admin page.
 * Tab 1. is the origin export screen. Tab 2. is a new wpml2mlp tab for the export.
 *
 * @package W2M\Export\AdminPage
 */
class Export_Admin_Page {

	/**
	 * class instance.
	 *
	 * @see   get_instance()
	 * @type  object
	 */
	protected static $instance = NULL;

	/**
	 * Access this plugin’s working instance
	 *
	 * @since   02/09/2016
	 * @return  object of this class
	 */
	public static function get_instance(){

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Construct the admin page
	 * To add tabs at the export admin page we Hook into all_admin_notices
	 * and add javascriptsto call exports via ajax and css to handle
	 * visible things.
	 *
	 * @wp-hook admin_init
	 *
	 */
	public function __construct() {

		$this->tabs = [
			'default_export' => __( 'Export', 'wpml2mlp' ),
			'export_wpml' => __( 'WPML Export', 'wpml2mlp' ),
		];

		$this->current_tab();


		add_action( 'admin_head', function () {

			$instance = self::get_instance();

			add_action( 'all_admin_notices', function () use ( $instance ) {

				$current_screen = get_current_screen()->get();

				if ( $current_screen->id == 'export' ) {
					$instance->display();
				}

			} );

			if ( $this->current_tab() == 'export_wpml' ) {
				$instance->attache_js();

			}

			$instance->attache_css();

		} );


	}

	/**
	 * Strip the active tab and return this as string
	 *
	 * @return string
	 */
	private function current_tab() {

		$active_tab = 'default_export';

		if ( isset( $_GET[ 'tab' ] ) ) {
			$active_tab = $_GET[ 'tab' ];
		}

		return $active_tab;

	}

	/**
	 * create the markup for the tabs
	 *
	 * @return string
	 */
	private function get_tabs(){

		$tabs = false;

		foreach( $this->tabs as $tab_key => $tab_value ){

			$active = $this->current_tab() ==  $tab_key  ? 'nav-tab-active' : '';
			$tabs .= '<a href="?tab=' . $tab_key . '" class="nav-tab ' . $active . '">' . $tab_value . '</a>';

		}

		return '<h2 class="nav-tab-wrapper">' . $tabs . '</h2>';

	}

	/**
	 * Attache JavaScript to the admin page head
	 *
	 */
	private function attache_js() { ?>

	<script type="text/javascript">

		jQuery(document).ready(function($){
			$( ".wrap.wpml2ml_export" ).show();

			$('.submit').click(function(e){
				e.preventDefault();
				var self = $( this );

				var loaderContainer = $( '<span/>', {
					'class': 'loader-image-container'
				}).insertAfter( self );

				var loader = $( '<img/>', {
					src: '<?php /*echo admin_url() */?>images/loading.gif',
					'class': 'loader-image'
				}).appendTo( loaderContainer );

				console.log( 'foo' );
				var searchval = $('#s').val(); // get search term

				$.post(
					ajaxurl,
					{
						action : 'add_foobar',
						searchval : searchval
					},
					function( response ) {
						$('#results').empty().append( response );
						loaderContainer.remove();
					}
				);
			});

		});

	</script>

	<?php
	}

	/**
	 * Attache CSS to the admin page head
	 */
	private function attache_css() {

		$style = FALSE;

		if( $this->current_tab() != 'default_export' ) {
			$style .= '.wrap{display:none}';
		}

		$style .= '.wrap h1{display:none}';
		$style .= '.wrap.wpml2ml_export{display:block}';

		echo '<style>' . $style . '</style>';

	}

	/**
	 * Attache the wpml2mlp tab content
	 */
	private function display() {
	?>

		<div class="wrap wpml2ml_export">

			<?php echo $this->get_tabs(); ?>

			<div>

				<?php

				if ( $this->current_tab() == 'export_wpml' ) {;

					$table = new Languages_Table();

					$table->prepare_items();
					$table->display();

				}

				?>
			</div>

		</div>

		<?php
	}

}