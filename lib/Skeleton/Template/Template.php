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
	 * Template template_paths
	 *
	 * @access private
	 * @var array $template_paths
	 */
	private $template_paths = [];

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
	 * @Deprecated: use add_template_path()
	 *
	 * @access public
	 * @param string $template_directory
	 * @param string $namespace (optional)
	 * @param bool $prepend (optional)
	 */
	public function set_template_directory($template_directory, $namespace = null, $prepend = false) {
		$this->add_template_path($template_directory, $namespace, $prepend);
	}

	/**
	 * Add template path
	 *
	 * @access public
	 * @param string $template_path
	 * @param string $namespace (optional)
	 * @param bool $prepend (optional)
	 */
	public function add_template_path($template_path, $namespace = null, $prepend = false) {
		$template_path = [
			'path' => $template_path,
			'namespace' => $namespace
		];

		if ($prepend) {
			array_unshift($this->template_paths, $template_path);
		} else {
			array_push($this->template_paths, $template_path);
		}
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
		/**
		 * @Deprecated: this is for backwards compatibility
		 */
		$this->add_template_path($template_directory, $namespace, $prepend);
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

		$extension = pathinfo($template, PATHINFO_EXTENSION);

		switch ($extension) {
			case 'twig':
				$renderer = new \Skeleton\Template\Twig\Twig();
				break;
			case 'tpl':
				$renderer = new \Skeleton\Template\Smarty\Smarty();
				break;
			default: throw new \Exception('Unknown template type');
		}

		if (count($this->template_paths) == 0) {
			throw new \Exception('No template path set, please add a path via $template->add_template_path()');
		}

		// Set the template path
		foreach ($this->template_paths as $template_path) {
			$renderer->add_template_path($template_path['path'], $template_path['namespace']);
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
		}

		// Render
		try {
			return $renderer->render($template);
		} catch (\Twig_Error_Loader $e) {
			throw new Exception\Loader($e->getMessage());
		} catch (\Twig\Error\LoaderError $e) {
			throw new Exception\Loader($e->getMessage());
		} catch (\SmartyException $e) {
			if (strpos($e->getMessage(), 'Unable to load') === 0) {
				throw new Exception\Loader($e->getMessage());
			} else {
				throw new Exception($e->getMessage());
			}
		}
	}

	/**
	 * Validate
	 *
	 * @access public
	 * @param string $template
	 */
	public function validate($template, &$error): bool {
		if (strpos($template, '.') === false) {
			throw new \Exception('Please provide a valid template filename. Incorrect filename "' . $template . '"');
		}

		$extension = pathinfo($template, PATHINFO_EXTENSION);

		switch ($extension) {
			case 'twig':
				$validator = new \Skeleton\Template\Twig\Twig();
				break;
			case 'tpl':
				$validator = new \Skeleton\Template\Smarty\Smarty();
				break;
			default: throw new \Exception('Unknown template type');
		}

		return $validator->validate($template, $error);
	}
}
