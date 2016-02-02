<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Module;

use
	W2M\Import\Common,
	W2M\Import\Data,
	DateTime;

class JsonXmlImportReport implements XmlImportReporterInterface {

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
	 * @param Data\XmlImportInterface $import
	 */
	public function create_report( Data\XmlImportInterface $import ) {

		$runtime = $import->start_date()->diff( new DateTime );
		$report  = (object) [
			'name'        => 'WPML to MLP XML import report',
			'date'        => $import->start_date()->format( DateTime::W3C ),
			'runtime'     => $runtime->format( 's \s'),
			'import_file' => $import->import_file(),
			'map_file'    => $import->map_file(),
			'maps'        => (object) [
				'comments'    => (object) $this->list->id_map( 'comment' ),
				'posts'       => (object) $this->list->id_map( 'post' ),
				'terms'       => (object) $this->list->id_map( 'term' ),
				'users'       => (object) $this->list->id_map( 'user' )
			]
		];

		$report = json_encode( $report, JSON_PRETTY_PRINT );
		$this->file->set_content( $report );
	}

}