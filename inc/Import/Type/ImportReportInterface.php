<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	DateTime;

/**
 * Interface ImportReportInterface
 *
 * Describes a basic import report
 *
 * @package W2M\Import\Type
 */
interface ImportReportInterface {

	/**
	 * @return string
	 */
	public function name();

	/**
	 * @return DateTime
	 */
	public function date();

	/**
	 * @param DateTime $end (Optional, defines the end of the runtime relative to date() )
	 *
	 * @return int (Runtime in seconds)
	 */
	public function runtime( DateTime $end = NULL );

	/**
	 * @param int $memory_usage (Optional, sets the memory usage if provided)
	 *
	 * @return int (peak memory usage in byte)
	 */
	public function memory_usage( $memory_usage = NULL );

	/**
	 * @param string $type (comment, post, term, user)
	 *
	 * @return array Associative list [ old_id => new_id ]
	 */
	public function id_map( $type );
}