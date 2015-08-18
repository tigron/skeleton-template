<?php
/**
 * Template class
 *
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author Gerry Demaret <gerry@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Template;

class Template {

	/**
	 * Translation
	 *
	 * @access private
	 * @var Translation $translation
	 */
	private $translation = null;

	/**
	 * Variables
	 *
	 * @access private
	 * @var array $variables
	 */
	protected $variables = [];

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Set translation
	 *
	 * @access public
	 * @param Translation $translation
	 */
	public function set_translation(\Skeleton\I18n\Translation $translation) {
		$this->translation = $translation;
	}

	/**
	 * Assign a variable
	 *
	 * @access public
	 * @param string $key
	 * @param mixed $value
	 */
	public function assign($key, $value) {
		$this->variables[$key] = $value;
	}

	/**
	 * Render
	 *
	 * @access public
	 * @param string $template
	 * @return string $html
	 */
	public function render($template) {
		list($filename, $extension) = explode('.', basename($template));

		switch ($extension) {
			case 'twig':
				$renderer = new \Skeleton\Template\Twig\Twig();
				break;
			default: throw new Exception('Unknown template type');
		}

		if (Config::$template_directory === null) {
			throw new \Exception('No template directory set, please set Config::$template_directory');
		}

		// Set the template path
		$renderer->set_template_directory(Config::$template_directory);

		// Pass the variables to the template renderer
		foreach ($this->variables as $key => $value) {
			$renderer->assign($key, $value);
		}

		// Set the translation object
		if ($this->translation !== null) {
			$renderer->set_translation($this->translation);
		} else {
			$translation = \Skeleton\I18n\Translation::Get(\Skeleton\Core\Application::Get()->language, \Skeleton\Core\Application::Get()->name);
			$renderer->set_translation($translation);
		}

		return $renderer->render($template);
	}
}
