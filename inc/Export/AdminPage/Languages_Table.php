<?php # -*- coding: utf-8 -*-

namespace W2M\Export\AdminPage;


class Languages_Table extends \WP_List_Table {

	var $example_data = array(
		array('ID' => 1,'booktitle' => '<a href="#" class="submit">Quarter Share</a>', 'author' => 'Nathan Lowell',
		      'isbn' => '978-0982514542'),
		array('ID' => 2, 'booktitle' => '7th Son: Descent','author' => 'J. C. Hutchins',
		      'isbn' => '0312384378'),
		array('ID' => 3, 'booktitle' => 'Shadowmagic', 'author' => 'John Lenahan',
		      'isbn' => '978-1905548927'),
		array('ID' => 4, 'booktitle' => 'The Crown Conspiracy', 'author' => 'Michael J. Sullivan',
		      'isbn' => '978-0979621130'),
		array('ID' => 5, 'booktitle'     => 'Max Quick: The Pocket and the Pendant', 'author'    => 'Mark Jeffrey',
		      'isbn' => '978-0061988929'),
		array('ID' => 6, 'booktitle' => 'Jack Wakes Up: A Novel', 'author' => 'Seth Harwood',
		      'isbn' => '978-0307454355')
	);


	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			                     'singular' => __( 'Customer', 'sp' ), //singular name of the listed records
			                     'plural'   => __( 'Customers', 'sp' ), //plural name of the listed records
			                     'ajax'     => FALSE //should this table support ajax?

		                     ] );

	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No customers avaliable.', 'sp' );
	}

	public function get_columns(){
		$columns = array(
			'booktitle' => 'Title',
			'author'    => 'Author',
			'isbn'      => 'ISBN'
		);
		return $columns;
	}

	public function prepare_items() {
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = $this->example_data;;
	}

	public function column_default( $item, $column_name ) {
		switch( $column_name ) {
			case 'booktitle':
			case 'author':
			case 'isbn':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ) ; //Show the whole array for troubleshooting purposes
		}
	}
}