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
	 * Template directories
	 *
	 * @access private
	 * @var array $template_directories
	 */
	private $template_directories = [];

	/**
	 * Variables
	 *
	 * @access private
	 * @var array $variables
	 */
	protected $variables = [];

	/**
	 * Variables to add to the environment
	 *
	 * @access protected
	 * @var array $environment
	 */
	protected $environment = [];

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
	 * Set template directory
	 *
	 * @access public
	 * @param string $template_directory
	 * @param string $namespace (optional)
	 * @param bool $prepend (optional)
	 */
	public function set_template_directory($template_directory, $namespace = null, $prepend = false) {
		$this->add_template_directory($template_directory, $namespace, $prepend);
	}

	/**
	 * Add template directory
	 *
	 * @access public
	 * @param string $template_directory
	 * @param string $namespace (optional)
	 * @param bool $prepend (optional)
	 */
	public function add_template_directory($template_directory, $namespace = null, $prepend = false) {
		$template_directory = [
			'directory' => $template_directory,
			'namespace' => $namespace
		];

		if ($prepend) {
			array_unshift($this->template_directories, $template_directory);
		} else {
			array_push($this->template_directories, $template_directory);
		}
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
	 * Add an environment variable
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function add_environment($key, $value) {
		$this->environment[$key] = $value;
	}

	/**
	 * Render
	 *
	 * @access public
	 * @param string $template
	 * @return string $html
	 */
	public function render($template) {
		if (strpos($template, '.') === false) {
			throw new \Exception('Please provide a valid template filename. Incorrect filename "' . $template . '"');
		}

		list($filename, $extension) = explode('.', basename($template));

		switch ($extension) {
			case 'twig':
				$renderer = new \Skeleton\Template\Twig\Twig();
				break;
			case 'tpl':
				$renderer = new \Skeleton\Template\Smarty\Smarty();
				break;
			default: throw new \Exception('Unknown template type');
		}

		if (count($this->template_directories) == 0) {
			throw new \Exception('No template directory set, please set $template->set_template_directory()');
		}

		// Set the template path
		foreach ($this->template_directories as $template_directory) {
			$renderer->add_template_directory($template_directory['directory'], $template_directory['namespace']);
		}

		// Pass the environment variables to the template renderer
		if (count($this->environment) > 0) {
			foreach ($this->environment as $key => $value) {
				$renderer->add_environment($key, $value);
			}
		}

		// Pass the variables to the template renderer
		foreach ($this->variables as $key => $value) {
			$renderer->assign($key, $value);
		}

		// Set the translation object
		if ($this->translation !== null) {
			$renderer->set_translation($this->translation);
		} else {
			if (class_exists('\Skeleton\I18n\Translation') AND class_exists('Skeleton\Core\Application')) {
				try {
					$language = \Skeleton\Core\Application::Get()->language;
					$application_name = \Skeleton\Core\Application::Get()->name;
					$translation = \Skeleton\I18n\Translation::Get($language, $application_name);
					$renderer->set_translation($translation);
				} catch (\Exception $e) { }
			}
		}

		try {
			return $renderer->render($template);
		} catch (\Twig_Error_Loader $e) {
			throw new Exception\Loader($e->getMessage());
		} catch (\SmartyException $e) {
			if (strpos($e->getMessage(), 'Unable to load') === 0) {
				throw new Exception\Loader($e->getMessage());
			} else {
				throw new Exception($e->getMessage());
			}
		}
	}
}
