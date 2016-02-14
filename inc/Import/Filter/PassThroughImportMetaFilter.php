<?php # -*- coding: utf-8 -*-

namespace W2M\Import\Filter;

use
	W2M\Import\Type;

/**
 * Class PassThroughImportMetaFilter
 *
 * Just a dummy filter which passes the given value through.
 *
 * @package W2M\Import\Filter
 */
class PassThroughImportMetaFilter implements ImportMetaFilterInterface {

	/**
	 * @param Type\ImportMetaInterface $meta
	 * @param int $object_id
	 *
	 * @return mixed (The filtered value)
	 */
	public function filter_value( Type\ImportMetaInterface $meta, $object_id ) {

		return $meta->value();
	}

}