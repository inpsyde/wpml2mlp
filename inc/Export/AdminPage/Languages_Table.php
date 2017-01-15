<?php # -*- coding: utf-8 -*-

namespace W2M\Export\AdminPage;

class Languages_Table extends \WP_List_Table {


	public function no_items() {
		_e( 'No Languages avaliable.', 'wpml2mlp' );
	}

	public function get_columns(){

		$columns = array(
			'cb'        => '<input type="checkbox" />',
			'languages' => 'Languages',
			'langcode'  => 'Code',
			'filesize'  => 'Filesize',
			'date'      => 'Date'
		);

		return $columns;

	}

	public function get_bulk_actions() {
		$actions = array(
			'export'    => 'Export'
		);
		return $actions;
	}

	public function get_sortable_columns() {

		return array(
			'date'  => array( 'date', true ),
			'langcode' => array( 'langcode', true ),
			'filesize' => array( 'filesize', true ),
		);

	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="lang[]" value="%s" />', $item['langcode']
		);
	}

	public function column_languages( $item ){

		$export_action = json_encode([
			'wpml2mlp'  => 1,
			'language' => $item['langcode']
		]);

		if( $item[ 'filesize' ] == '-' ){
			$actions['create']  = '<a class="submit" id="lang_' . esc_html( $item['langcode'] ) . '" href="" data-export=' . $export_action . '>' . __( 'Export now', 'wpml2mlp' ) . '</a>';
		}else {
			$actions[ 'download' ] = '<a href="' .   esc_html( $item[ 'file']['url'] ) . '">' . __( 'Download', 'wpml2mlp' ) . '</a>';
			$actions[ 'update' ]   = '<a class="submit" id="lang_' . esc_html( $item['langcode'] ). '" href="" data-export=' . $export_action . '>' . __( 'Update', 'wpml2mlp' ) . '</a>';
		}


		return sprintf('%1$s %2$s', $item['languages'], $this->row_actions( $actions ) );
	}

	public function prepare_items() {

		$languages = wpml_get_active_languages_filter( FALSE );

		$exiting_exports = $this->get_exports();

		foreach( $languages as $i => $lang ){

			$this->items[$i]['languages']   = '<strong><img src="' .  esc_html( $lang[ 'country_flag_url' ] ) . '"/> ' .  esc_html( $lang[ 'native_name' ] ) . '</strong>';
			$this->items[$i]['langcode']    = $lang[ 'language_code' ];
			$this->items[$i]['filesize']    = '-';
			$this->items[$i]['date']        = '-';
			$this->items[$i]['file']        = '-';


			if( array_key_exists( $lang['default_locale'], $exiting_exports ) ) {

				$export_file = $exiting_exports[ $lang[ 'default_locale' ] ][ 'path' ];

				$this->items[$i]['filesize']    = size_format( filesize( $export_file ), 2 );
				$this->items[$i]['date']        = date( 'm.d.y H:i', filemtime( $export_file ) );
				$this->items[$i]['file']        = $exiting_exports[ $lang[ 'default_locale' ] ];

			}

		}


		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);


	}

	public function column_default( $item, $column_name ) {

		switch( $column_name ) {
			case 'Languages':
			case 'langcode':
			case 'filesize':
			case 'date':
			default:
				return $item[ $column_name ] ; //Show the whole array for troubleshooting purposes
		}
	}

	private function get_exports(){

		$uploads = wp_upload_dir( );

		$exports = [];

		foreach( glob( $uploads['basedir'] . '/wpml2mlp/*.xml') as $export ){

			preg_match( '/_([a-z]{2}(_[A-Z]{2})?)\.xml$/', $export, $langcode );

			if( isset( $langcode[1] ) ) {
				$exports[ $langcode[ 1 ] ] = [
					'path'  => $uploads['basedir'] . '/wpml2mlp/' . basename( $export ),
					'url'   => $uploads['baseurl'] . '/wpml2mlp/' . basename( $export )
				];
			}

		}

		return $exports;

	}

}