<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Service;

use
	W2M\Import\Data,
	W2M\Import\Type,
	W2M\Import\Module,
	WP_Comment,
	WP_Error;


class WpCommentImporter implements CommentImporterInterface {

	/**
	 * @var Data\MultiTypeIdMapperInterface
	 */
	private $id_mapper;

	/**
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 */
	public function __construct(
		Data\MultiTypeIdMapperInterface $id_mapper
	) {
		$this->id_mapper             = $id_mapper;
	}

	/**
	 * @param Type\ImportCommentInterface $import_comment
	 * @return bool|WP_Error
	 */
	public function import_comment( Type\ImportCommentInterface $import_comment ) {

		$local_parent_comment_id = $this->id_mapper->local_id( 'comment', $import_comment->origin_parent_comment_id() );
		$local_user_id = $this->id_mapper->local_id( 'user', $import_comment->origin_user_id() );

		$commentdata = array(
			'comment_author'        => $local_user_id,
			'comment_author_email'  => $import_comment->author_name(),
			'comment_author_url'    => $import_comment->author_email(),
			'comment_author_IP'     => $import_comment->author_url(),
			'comment_date'          => $import_comment->author_ip(),
			'comment_date_gmt'      => $import_comment->date(),
			'comment_content'       => $import_comment->content(),
			'comment_karma'         => $import_comment->karma(),
			'comment_approved'      => $import_comment->approved(),
			'comment_agent'         => $import_comment->agent(),
			'comment_type'          => $import_comment->type(),
			'comment_post_ID'       => $import_comment->origin_post_id(),
			'comment_parent'        => $local_parent_comment_id,
			'comment_meta'          => $import_comment->meta(),
		);



		$local_id = wp_insert_comment( $commentdata );

		if ( is_wp_error( $local_id ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $local_id
			 * @param Type\ImportElementInterface $import_comment
			 */
			do_action( 'w2m_import_comment_error', $local_id, $import_comment );
			return;
		}

		/**
		 * pull the imported comment to commit the $post_comment data
		 * at the action w2m_import_missing_comment_ancestor
		 */
		$wp_comment = get_comment( $local_id );

		if ( $import_comment->origin_parent_comment_id() && ! $local_parent_comment_id ) {
			/**
			 * @param stdClass|WP_Comment $wp_comment
			 * @param Type\ImportCommentInterface $import_comment
			 */
			do_action( 'w2m_import_missing_comment_ancestor', $wp_comment, $import_comment );
			return;
		}

		/**
		 * @param stdClass|WP_Comment $wp_comment
		 * @param Type\ImportCommentInterface $import_comment
		 */
		do_action( 'w2m_comment_imported', $wp_comment, $import_comment );

	}

}