<?php
return array(
	'example_widget' => array(
		'class'       => \ExampleWidget::class,
		'label'       => 'Example Widget',
		'description' => 'A starting point for custom widgets.',
		'roles'       => array( 'member' ),
		'icon'        => 'smiley',
		'category'    => 'general',
		'cache'       => true,
		// 'lazy' => true, // Uncomment for React widgets
	),
);
