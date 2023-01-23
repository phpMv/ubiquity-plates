<?php

namespace Ubiquity\views\engine\plates;

class PlatesTemplate extends \League\Plates\Template\Template {
	/**
	 * Returns template declared sections.
	 * @return array
	 */
	public function getSections(): array {
		return \array_keys($this->sections);
	}

	public function stop() {
		$name=$this->sectionName;
		parent::stop();
		return $this->sections[$name];
	}
}