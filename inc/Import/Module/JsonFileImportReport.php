<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Common,
	W2M\Import\Data,
	DateTime;

/**
 * Class JsonFileImportReport
 *
 * Writes file import report in JSON format
 *
 * @package W2M\Import\Module
 */
class JsonFileImportReport implements FileImportReporterInterface {

	/**
	 * @var Data\MultiTypeIdListInterface
	 */
	private $list;

	/**
	 * @var Common\FileInterface
	 */
	private $file;

	/**
	 * @param Data\MultiTypeIdListInterface $list
	 * @param Common\FileInterface $file
	 */
	public function __construct(
		Data\MultiTypeIdListInterface $list,
		Common\FileInterface $file
	) {

		$this->list = $list;
		$this->file = $file;
	}

	/**
	 * @param Data\FileImportInterface $import
	 */
	public function create_report( Data\FileImportInterface $import ) {

		$runtime = time() - $import->start_date()->getTimestamp();
		/* @link https://secure.php.net/manual/en/function.memory-get-usage.php */
		$convert = function( $size ) {
			$unit = [ 'B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' ];
			return round( $size / pow( 1024, ( $i = (int) floor( log( $size, 1024 ) ) ) ), 2 ) . ' ' . $unit[ $i ];
		};
		$memory_usage = memory_get_peak_usage( TRUE );

		$report  = (object) [
			'name'         => 'WPML to MLP XML import report',
			'date'         => $import->start_date()->format( DateTime::W3C ),
			'runtime'      => "{$runtime} s",
			'memory_usage' => $convert( $memory_usage ),
			'import_file'  => $import->import_file(),
			'map_file'     => $import->map_file(),
			'maps'         => (object) [
				'comments'    => (object) $this->list->id_map( 'comment' ),
				'posts'       => (object) $this->list->id_map( 'post' ),
				'terms'       => (object) $this->list->id_map( 'term' ),
				'users'       => (object) $this->list->id_map( 'user' )
			]
		];

		$report = json_encode( $report, JSON_PRETTY_PRINT );
		$this->file->set_content( $report );

		/**
		 * @param Common\FileInterface $this->file
		 */
		do_action( 'w2m_import_json_report_created', $this->file );
	}

}