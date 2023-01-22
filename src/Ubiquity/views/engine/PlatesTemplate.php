<?php

namespace Ubiquity\views\engine;

class PlatesTemplate extends \League\Plates\Template\Template {
	/**
	 * Returns template declared sections.
	 * @return array
	 */
	public function getSections(): array {
		return \array_keys($this->sections);
	}
}