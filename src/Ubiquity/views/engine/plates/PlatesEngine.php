<?php

namespace Ubiquity\views\engine\plates;

use League\Plates\Template\Template;

class PlatesEngine extends \League\Plates\Engine {
	public function make($name, array $data = array()) {
		$template = new PlatesTemplate($this, $name);
		$template->data($data);
		return $template;
	}

}