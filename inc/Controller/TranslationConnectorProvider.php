<?php # -*- coding: utf-8 -*-

namespace W2M\Controller;

use
	W2M\Import;

class TranslationConnectorProvider {

	/**
	 * @var Import\Module\TranslationConnectorInterface
	 */
	private $connector;

	/**
	 * @param Import\Module\TranslationConnectorInterface $connector
	 */
	public function __construct( Import\Module\TranslationConnectorInterface $connector ) {

		$this->connector = $connector;
	}

	/**
	 * Register the translation connector to the actions
	 * 'w2m_term_imported' and 'w2m_post_imported'
	 */
	public function register_connector() {

		add_action( 'w2m_term_imported', [ $this->connector, 'link_term' ], 10, 2 );
		add_action( 'w2m_post_imported', [ $this->connector, 'link_post' ], 10, 2 );
	}
}