<?php

namespace Ubiquity\views\engine;

use League\Plates\Engine;
use League\Plates\Template\Functions;
use League\Plates\Template\Template;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\TwigTest;
use Twig\Loader\FilesystemLoader;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\Router;
use Ubiquity\controllers\Startup;
use Ubiquity\core\Framework;
use Ubiquity\events\EventsManager;
use Ubiquity\events\ViewEvents;
use Ubiquity\exceptions\ThemesException;
use Ubiquity\translation\TranslatorManager;
use Ubiquity\utils\base\UFileSystem;
use Ubiquity\themes\ThemesManager;
use Ubiquity\assets\AssetsManager;
use Ubiquity\views\View;

/**
 * Ubiquity Twig template engine.
 *
 * Ubiquity\views\engine$Twig
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.10
 *
 */
class Plates extends TemplateEngine {
	private $plates;

	public function __construct(array $options = []) {
		$dir = \ROOT . \DS . 'views' . \DS;
		if(isset($options['activeTheme'])){
			$dir = \ROOT . \DS . 'views' . \DS . 'themes' . \DS . $options['activeTheme'] . \DS;
		}
		$this->plates = new Engine($dir, null);
		$this->addFunction('css', function ($resource) {
			echo AssetsManager::css($resource);
		});

		$this->addFunction('js', function ($resource) {
			echo AssetsManager::js($resource);
		});

		$this->addFunction ( 'route', function ($name, array $params = [], bool $absolute = false) {
			echo Router::path ( $name, $params, $absolute );
		} );

		$this->addFunction ( 'url', function ($name, $params= [ ]) {
			echo Router::url ( $name, $params );
		} );

		$this->addFunction ('t',function ($context, $id, array $parameters = array (), $domain = null, $locale = null) {
			$trans = TranslatorManager::trans ( $id, $parameters, $domain, $locale );
			echo $trans;
			return $this->plates->createTemplate ( $trans )->render ( $context );
		});

		$this->addFunction ('tc',function ($context, $id, array $choice, array $parameters = array (), $domain = null, $locale = null) {
			$trans = TranslatorManager::transChoice ( $id, $choice, $parameters, $domain, $locale );
			echo $trans;
			return $this->plates->createTemplate ( $trans )->render ( $context );
		});
	}

	public function render($viewName, $pData, $asString) {
		$viewName = \str_replace('@activeTheme/','',$viewName);
		$pData ['config'] = Startup::getConfig();
		EventsManager::trigger(ViewEvents::BEFORE_RENDER, $viewName, $pData);
		$render = $this->plates->render($viewName, $pData);
		EventsManager::trigger(ViewEvents::AFTER_RENDER, $render, $viewName, $pData);
		if ($asString) {
			return $render;
		} else {
			echo $render;
		}
	}

	public function addFunction(string $name, $callback) {
		$this->plates->registerFunction($name, $callback);
	}

	public function getBlockNames($templateName){

	}

	public function getCode($templateName){

	}
}