# skeleton-template

## Description

General templating for Skeleton. This library supports multiple templating
libraries as back-end. Currently only the Twig back-end is supported via
skeleton-template-twig

## Installation

Installation via composer:

    composer require tigron/skeleton-template

## Howto

	// Initialize a template object
	$template = new \Skeleton\Template\Template();

	// Add a template path
	$template->add_template_path($my_very_cool_path);

	// Set a translation object (optional);
	$template->set_translation(\Skeleton\I18n\Translation $translation);

	// Assign variables
	$template->assign('my_variable_name', 'content1');
	$template->assign('my_variable_name2', 'content2');

	// return the rendered template
	$html = $template->render('test.twig');

	// $html contains the rendered template
