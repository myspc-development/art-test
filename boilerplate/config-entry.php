<?php
return [
    'example_widget' => [
        'class'       => \ExampleWidget::class,
        'label'       => 'Example Widget',
        'description' => 'A starting point for custom widgets.',
        'roles'       => [ 'member' ],
        'icon'        => 'smiley',
        'category'    => 'general',
        'cache'       => true,
        // 'lazy' => true, // Uncomment for React widgets
    ],
];
