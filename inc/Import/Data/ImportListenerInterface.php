<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Data;

/**
 * Interface ImportListenerInterface
 *
 * Will replace deprecated IdObserverInterface
 * see https://github.com/inpsyde/wpml2mlp/issues/54
 *
 * @package W2M\Import\Data
 */
interface ImportListenerInterface extends
	CommentImportListenerInterface,
	PostImportListenerInterface,
	TermImportListenerInterface,
	UserImportListenerInterface
{}