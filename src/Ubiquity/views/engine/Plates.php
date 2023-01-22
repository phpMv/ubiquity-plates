<?php

namespace Ubiquity\views\engine;

use League\Plates\Engine;
use League\Plates\Template\Functions;
use League\Plates\Template\Template;
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
use Ubiquity\views\engine\TemplateGenerator;
/**
 * Ubiquity Plates template engine.
 *
 * Ubiquity\views\engine$Plates
 * This class is part of Ubiquity
 *
 * @author jcheron <guillaume.jacopin@sts-sio-caen.info>
 * @version 0.0.0
 *
 */
class Plates extends TemplateEngine {
	private Engine $plates;

	public function __construct(array $options = []) {

		$this->plates = new Engine(\ROOT . \DS . 'views' . \DS, null);
		$this->addPath(Startup::getFrameworkDir() . '/../core/views/engines/plates', 'framework');

		if (isset ($options ['activeTheme'])) {
			ThemesManager::setActiveThemeFromTwig($options ['activeTheme']);
			$this->setTheme($options ['activeTheme'], ThemesManager::THEMES_FOLDER);
			unset ($options ['activeTheme']);
		} else {
			$this->addPath(\ROOT . \DS . 'views', 'activeTheme');
		}
		$this->addFunctions();

		$this->addFunction ('t',function ($context, $id, array $parameters = array (), $domain = null, $locale = null) {
			$trans = TranslatorManager::trans ( $id, $parameters, $domain, $locale );
			return $trans;
		});

		$this->addFunction ('tc',function ($context, $id, array $choice, array $parameters = array (), $domain = null, $locale = null) {
			$trans = TranslatorManager::transChoice ( $id, $choice, $parameters, $domain, $locale );
			return $trans;
		});
	}

	public function render(string $viewName, $pData=null, bool $asString=false) {
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

	public function addFunction(string $name, $callback, array $options=[]): void {
		$this->plates->registerFunction($name, $callback);
	}

	public function getBlockNames(string $templateName): array {
		$template=new PlatesTemplate($this->plates,$templateName);
		return $template->getSections();
	}

	public function getCode(string $templateName): string {
		$template=new PlatesTemplate($this->plates,$templateName);
		return \file_get_contents($template->path());
	}

	protected function addFilter(string $name, $callback, array $options = []): void {
		$this->plates->registerFunction($name, $callback);
	}

	protected function addExtension($extension): void {
		$this->plates->loadExtension($extension);
	}

	public function getGenerator(): ?TemplateGenerator {
		return null;
	}

	/**
	 * Adds a new path in a namespace
	 *
	 * @param string $path The path to add
	 * @param string $namespace The namespace to use
	 */
	public function addPath(string $path, string $namespace) {
		$this->plates->addFolder('@'.\ltrim($namespace,'@'), $path);
	}

	/**
	 * @param string $theme
	 * @param string $themeFolder
	 * @return string
	 * @throws ThemesException
	 */
	public function setTheme(string $theme, string $themeFolder = ThemesManager::THEMES_FOLDER): string {
		$path = parent::setTheme($theme, $themeFolder);
		$this->addPath($path, 'activeTheme');
		return $path;
	}
}