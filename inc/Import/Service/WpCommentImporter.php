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
	 * @param Module\TranslationConnectorInterface $translation_connector
	 * @param Data\MultiTypeIdMapperInterface $id_mapper
	 * @param $ancestor_resolver (Not specified yet)
	 */
	public function __construct(
		Data\MultiTypeIdMapperInterface $id_mapper
	) {

		#$this->translation_connector = $translation_connector;
		$this->id_mapper             = $id_mapper;
	}

	/**
	 * @param Type\ImportCommentInterface $Comment
	 * @return bool|\WP_Error
	 */
	public function import_Comment( Type\ImportCommentInterface $comment ) {

		$local_parent_comment_id = $this->id_mapper->local_id( 'comment', $comment->origin_parent_comment_id() );
		$local_user_id = $this->id_mapper->local_id( 'user', $comment->origin_user_id() );

		$commentdata = array(
			'comment_author'        => $local_user_id,
			'comment_author_email'  => $comment->author_name(),
			'comment_author_url'    => $comment->author_email(),
			'comment_author_IP'     => $comment->author_url(),
			'comment_date'          => $comment->author_ip(),
			'comment_date_gmt'      => $comment->date(),
			'comment_content'       => $comment->content(),
			'comment_karma'         => $comment->karma(),
			'comment_approved'      => $comment->approved(),
			'comment_agent'         => $comment->agent(),
			'comment_type'          => $comment->type(),
			'comment_post_ID'       => $comment->origin_post_id(),
			'comment_parent'        => $local_parent_comment_id,
			'comment_meta'          => $comment->meta(),
		);

		$comment_id = wp_insert_Comment( $commentdata, TRUE );

		if ( is_wp_error( $comment_id ) ) {
			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $comment_id
			 * @param Type\ImportElementInterface $commentdata
			 */
			do_action( 'w2m_import_comment_error', $comment_id, $commentdata );
			return;
		}

		/**
		 * TODO: implement & test this pull
		 *
		 * pull the imported comment, compare the parent ids
		 */
		#$post_comment = get_Comment( $comment_id );

		#if ( $comment->origin_parent_comment_id() && ! $local_parent_id ) {
			/**
			 * @param stdClass|WP_Comment $post_comment
			 * @param Type\ImportCommentInterface $Comment
			 */
		#	do_action( 'w2m_import_missing_comment_ancestor', $comment_id, $post_comment );
		#	return;
		#}

		/**
		 * TODO: check if we have to store origin data as meta
		 */
		#update_Comment_meta( $comment_id, '_w2m_origin_...', $Comment->... );

		#TODO: Import the comment meta

		/**
		 * @param WP_Comment $post_comment
		 * @param Type\ImportCommentInterface $comment
		 */
		#do_action( 'w2m_comment_imported', $post_comment, $commentdata );

	}

	private function meta_result( $meta_result, $attribute ){

		if ( $meta_result !== TRUE ) {

			$meta_result = new WP_Error( 'broken', "Cant add or update Commentmeta." );

			/**
			 * Attach error handler/logger here
			 *
			 * @param WP_Error $meta_result
			 * @param int     $comment_id
			 * @param array   $term_ids
			 * @param string  $taxonomy
			 */
			do_action( 'w2m_import_update_comment_meta_error', $meta_result, $attribute['comment_id'], $attribute['meta']['key'], $attribute['meta']['value'] );
		}

	}

}