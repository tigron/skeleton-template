<?php
/**
 * migration:create command for Skeleton Console
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

namespace Skeleton\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Skeleton\Database\Migration\Config;

class Template_Validate extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('template:validate');
		$this->setDescription('Validate the syntax of templates');
		$this->addArgument('path', InputArgument::REQUIRED, 'Path(s) to the templates (comma separated)');
	}

	/**
	 * Execute the Command
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int {
		$error_count = 0;
		$error = '';
		$root_path = getcwd();
		$paths = $input->getArgument('path');
		foreach (explode(',', $paths) as $path) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($root_path . '/' . $path, \RecursiveDirectoryIterator::SKIP_DOTS),
				\RecursiveIteratorIterator::SELF_FIRST
			);
			foreach ($iterator as $file) {
				$path = $file->getPathname();
				if ($file->isFile()) {
					$template = new \Skeleton\Template\Template();
					if ($template->validate($path, $error) === false) {
						$error_count++;
						$output->writeln($error);
					}
				}
			}
		}
		if ($error_count > 0) {
			return 1;
		}
		return 0;
	}
}
