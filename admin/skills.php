<?php
declare(strict_types=1);
require __DIR__ . '/_crud.php';

crud_page([
    'table'    => 'skills',
    'title'    => 'Skills',
    'singular' => 'Skill',
    'order'    => 'sort_order ASC, id ASC',
    'columns'  => ['name' => 'Skill', 'category' => 'Category', 'level' => 'Level %', 'sort_order' => 'Order'],
    'fields'   => [
        'category'    => ['label' => 'Category', 'required' => true, 'max' => 60,
                          'help' => 'Skills with the same category are grouped together on the website.',
                          'suggest' => db()->query('SELECT DISTINCT category FROM skills ORDER BY category')->fetchAll(PDO::FETCH_COLUMN)],
        'name'        => ['label' => 'Skill name', 'required' => true, 'max' => 80],
        'description' => ['label' => 'Short description', 'max' => 255],
        'level'       => ['label' => 'Level (0-100)', 'type' => 'int', 'required' => true, 'min' => 0, 'maxv' => 100, 'default' => 50],
        'sort_order'  => ['label' => 'Display order (small numbers first)', 'type' => 'int', 'min' => 0, 'maxv' => 100000, 'default' => 0],
    ],
]);
