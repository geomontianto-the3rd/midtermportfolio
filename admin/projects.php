<?php
declare(strict_types=1);
require __DIR__ . '/_crud.php';

crud_page([
    'table'    => 'projects',
    'title'    => 'Projects',
    'singular' => 'Project',
    'columns'  => ['title' => 'Title', 'category' => 'Category', 'image' => 'Image', 'sort_order' => 'Order'],
    'fields'   => [
        'title'       => ['label' => 'Project title', 'required' => true, 'max' => 120],
        'category'    => ['label' => 'Category', 'max' => 60, 'help' => 'Small label above the title, e.g. Web development.'],
        'description' => ['label' => 'Description', 'type' => 'textarea', 'required' => true, 'max' => 600, 'rows' => 4],
        'tags'        => ['label' => 'Technologies', 'max' => 255, 'help' => 'Separate with commas, e.g. HTML, CSS, JavaScript, MySQL.'],
        'image'       => ['label' => 'Screenshot / project image', 'type' => 'image', 'help' => 'JPG, PNG, WEBP or GIF, up to 2 MB.'],
        'placeholder' => ['label' => 'Drawing used when there is no image', 'type' => 'select', 'options' => placeholder_choices(), 'default' => 'dashboard'],
        'project_url' => ['label' => 'Project link (optional)', 'type' => 'url', 'max' => 255],
        'sort_order'  => ['label' => 'Display order (small numbers first)', 'type' => 'int', 'min' => 0, 'maxv' => 100000, 'default' => 0],
    ],
]);
