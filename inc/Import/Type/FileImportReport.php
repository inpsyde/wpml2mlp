<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Type;

use
	W2M\Import\Common,
	W2M\Import\Data,
	DateTime;

/**
 * Class FileImportReport
 *
 * @todo Write Tests for
 *
 * @package W2M\Import\Type
 */
class FileImportReport implements FileImportReportInterface {

	/**
	 * @var Common\FileInterface
	 */
	private $import_file;

	/**
	 * @var Data\MultiTypeIdListInterface
	 */
	private $map;

	/**
	 * @var DateTime
	 */
	private $date;

	/**
	 * @var int
	 */
	private $blog_id;

	/**
	 * @var Common\File
	 */
	private $map_file;

	/**
	 * @var string
	 */
	private $name = '';

	/**
	 * @var int
	 */
	private $runtime = 0;

	/**
	 * @var int
	 */
	private $memory_usage = 0;

	/**
	 * @param Common\FileInterface $import_file
	 * @param Data\MultiTypeIdListInterface $map
	 * @param $blog_id
	 * @param array $data {
	 *      Common\File $map_file,
	 *      DateTime $date,
	 *      int $runtime,
	 *      int $memory_usage,
	 *      string $name
	 * }
	 */
	public function __construct(
		Common\FileInterface $import_file,
		Data\MultiTypeIdListInterface $map,
		$blog_id,
		array $data = []
	) {

		$this->import_file = $import_file;
		$this->map         = $map;
		$this->blog_id     = (int) $blog_id;

		$this->map_file = isset( $data[ 'map_file' ] )
			&& is_a( $data[ 'map_file' ], 'W2M\Import\Common\File' )
				? $data[ 'map_file' ]
				: NULL;

		$this->date = isset( $data[ 'date' ] )
		&& is_a( $data[ 'date' ], 'DateTime' )
			? $data[ 'date' ]
			: new DateTime;

		$this->runtime = isset( $data[ 'runtime' ] )
			? (int) $data[ 'runtime' ]
			: 0;

		$this->memory_usage = isset( $data[ 'memory_usage' ] )
			? (int) $data[ 'memory_usage' ]
			: 0;

		$this->name = isset( $data[ 'name' ] )
			? (string) $data[ 'name' ]
			: '';

	}

	/**
	 * @return Common\FileInterface The file with the import data
	 */
	public function import_file() {

		return $this->import_file;
	}

	/**
	 * @return Common\FileInterface|NULL The file that was used as id-map
	 */
	public function map_file() {

		return $this->map_file;
	}

	/**
	 * @return string
	 */
	public function name() {

		return $this->name;
	}

	/**
	 * @return DateTime
	 */
	public function date() {

		return $this->date;
	}

	/**
	 * @param DateTime $end (Optional, defines the end of the runtime relative to date() )
	 *
	 * @return int (Runtime in seconds)
	 */
	public function runtime( DateTime $end = NULL ) {

		if ( $end ) {
			$this->runtime = $end->getTimestamp() - $this->date->getTimestamp();
		}

		return $this->runtime;
	}

	/**
	 * @param int $memory_usage (Optional, sets the memory usage if provided)
	 *
	 * @return int (peak memory usage in byte)
	 */
	public function memory_usage( $memory_usage = NULL ) {

		if ( ! is_null( $memory_usage ) ) {
			$this->memory_usage = (int) $memory_usage;
		}

		return $this->memory_usage;
	}

	/**
	 * @param string $type (comment, post, term, user)
	 *
	 * @return array Associative list [ old_id => new_id ]
	 */
	public function id_map( $type ) {

		return $this->map->id_map( $type );
	}

	/**
	 * @return object
	 */
	public function jsonSerialize() {

		/* @link https://secure.php.net/manual/en/function.memory-get-usage.php */
		$convert = function( $size ) {
			$unit = [ 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' ];
			return round( $size / pow( 1024, ( $i = (int) floor( log( $size, 1024 ) ) ) ), 2 ) . ' ' . $unit[ $i ];
		};

		return (object) [
			'name'         => $this->name(),
			'date'         => $this->date()->format( DateTime::W3C ),
			'runtime'      => "{$this->runtime()} s",
			'memory_usage' => $convert( $this->memory_usage() ),
			'import_file'  => $this->import_file()->name(),
			'map_file'     => $this->map_file() ? $this->map_file()->name() : '',
			'maps'         => (object) [
				'comments'    => (object) $this->id_map( 'comment' ),
				'posts'       => (object) $this->id_map( 'post' ),
				'terms'       => (object) $this->id_map( 'term' ),
				'users'       => (object) $this->id_map( 'user' )
			]
		];
	}
}