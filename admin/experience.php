<?php
declare(strict_types=1);
require __DIR__ . "/_crud.php";

crud_page([
    "table"    => "experience",
    "title"    => "Experience",
    "singular" => "Experience",
    "columns"  => ["title" => "Title", "organization" => "Organization", "period" => "Period", "sort_order" => "Order"],
    "fields"   => [
        "title"        => ["label" => "Position / activity", "required" => true, "max" => 150],
        "organization" => ["label" => "Company or organization", "max" => 150],
        "kicker"       => ["label" => "Small label", "max" => 60, "help" => "Shown above the title, e.g. Current, Training, Certificate."],
        "period"       => ["label" => "Date / period", "max" => 60, "help" => "e.g. 2024 - Present"],
        "description"  => ["label" => "Description", "type" => "textarea", "max" => 600, "rows" => 4],
        "image"        => ["label" => "Image (optional)", "type" => "image", "help" => "If empty, the icon below is shown."],
        "icon"         => ["label" => "Icon", "type" => "select", "options" => icon_choices(), "default" => "cap"],
        "sort_order"   => ["label" => "Display order (small numbers first)", "type" => "int", "min" => 0, "maxv" => 100000, "default" => 0],
    ],
]);
