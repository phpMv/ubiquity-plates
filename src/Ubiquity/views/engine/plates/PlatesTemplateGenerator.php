<?php

namespace Ubiquity\views\engine\plates;

use Ubiquity\utils\base\UArray;
use Ubiquity\utils\base\UString;
use Ubiquity\views\engine\twig\TemplateParser;

/**
 * Plates template generator.
 * Ubiquity\views\engine\plates$PlatesTemplateGenerator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class PlatesTemplateGenerator extends \Ubiquity\views\engine\TemplateGenerator {

	public function __construct() {
		$this->openExpressionTag = '<?php ';
		$this->openVarTag = '<?=';
		$this->closeExpressionTag = $this->closeVarTag = '?>';
	}

	public function openBlock(string $name): string {
		return $this->openExpressionTag . "\$this->start('$name')" . $this->closeExpressionTag;
	}

	public function closeBlock(): string {
		return $this->openExpressionTag . '$this->stop()' . $this->closeExpressionTag;
	}

	public function asArray(array $array): string {
		return UArray::asPhpArray_($array);
	}

	public function insertVariable(string $name, bool $safe = false): string {
		if(!$this->isAssets($name)) {
			$name = \str_replace('.', '->', $name);
			if (UString::contains('(', $name) && !UString::contains('->', $name)) {
				return $this->openVarTag . $this->escape($name, $safe) . $this->closeVarTag;
			}
			return $this->openVarTag . $this->escape($this->asVariable($name), $safe) . $this->closeVarTag;
		}
		return $this->openVarTag . '$this->'.$name . $this->closeVarTag;
	}

	protected function isAssets(string $name): bool {
		$name=\trim($name);
		return UString::startswith($name,'css(') || UString::startswith($name,'js(');
	}

	public function escape(string $var, bool $isSafe=false): string {
		if($isSafe){
			return $var;
		}
		return "\$this->escape($var)";
	}

	public function asVariable(string $var): string {
		return '$' . \ltrim($var, '$');
	}

	public function includeFile(string $filename, bool $asVariable = false): string {
		$quote = "'";
		if ($asVariable) {
			$quote = '';
			$filename = $this->asVariable($filename);
		}
		return $this->openExpressionTag . "insert({$quote}{$filename}{$quote})" . $this->closeExpressionTag;
	}

	public function extendsTemplate(string $templateName, bool $asVariable = false): string {
		$quote = "'";
		if ($asVariable) {
			$quote = '';
			$templateName = $this->asVariable($templateName);
		}
		return $this->openExpressionTag . "\$this->layout({$quote}{$templateName}{$quote})" . $this->closeExpressionTag;
	}

	public function foreach(string $arrayName, string $value, ?string $key = null): string {
		$arrayName = $this->asVariable($arrayName);
		$value = $this->asVariable($value);
		if ($key != null) {
			$key = $this->asVariable($key);
			return $this->openExpressionTag . "foreach($arrayName as $key=>$value):" . $this->closeExpressionTag;
		}
		return $this->openExpressionTag . "foreach($arrayName as $value):" . $this->closeExpressionTag;
	}

	public function endForeach(): string {
		return $this->openExpressionTag . 'endforeach' . $this->closeExpressionTag;
	}

	public function condition(string $condition): string {
		$condition = $this->asVariable(\trim($condition));
		return $this->openExpressionTag . "if($condition)" . $this->closeExpressionTag;
	}

	public function endCondition(): string {
		return $this->openExpressionTag . 'endif' . $this->closeExpressionTag;
	}

	public function getNonce(): string {
		return $this->openVarTag . "\$nonce??''" . $this->closeVarTag;
	}

	public function getNonceArray(): string {
		return "['nonce'=>\$nonce??'']";
	}

	public function getSelf(): string {
		return $this->openVarTag . '$this->getName()' . $this->closeVarTag;
	}

	private function postProcess(string $code): string {
		return \str_replace('$nonce', '$nonce??""', $code);
	}

	public function parseFromTwig(string $code): string {
		$parser=new TemplateParser($this);
		return $parser->parseFileContent($code);
	}

}
