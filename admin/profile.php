<?php
declare(strict_types=1);
require __DIR__ . '/_crud.php';

crud_page([
    'table'    => 'profile',
    'single'   => true,
    'title'    => 'Personal information',
    'singular' => 'Profile',
    'fields'   => [
        'full_name'      => ['label' => 'Full name', 'required' => true, 'max' => 100],
        'display_name'   => ['label' => 'Display name (used in “Hi, I’m …”)', 'required' => true, 'max' => 60],
        'role_text'      => ['label' => 'Role / title (typing effect)', 'required' => true, 'max' => 200, 'help' => 'Separate several titles with commas, e.g. BSIT Student, Web Developer.'],
        'intro'          => ['label' => 'Introduction (home page)', 'type' => 'textarea', 'required' => true, 'max' => 600, 'rows' => 3],
        'profile_image'  => ['label' => 'Profile picture', 'type' => 'image'],
        'image_alt'      => ['label' => 'Picture description (for screen readers)', 'max' => 200],
        'study_label'    => ['label' => 'Badge label', 'max' => 60],
        'study_value'    => ['label' => 'Badge text', 'max' => 100],
        'about_heading'  => ['label' => 'About: heading', 'required' => true, 'max' => 150],
        'about_text1'    => ['label' => 'About: biography (paragraph 1)', 'type' => 'textarea', 'required' => true, 'max' => 1500, 'rows' => 4],
        'about_text2'    => ['label' => 'About: biography (paragraph 2)', 'type' => 'textarea', 'max' => 1500, 'rows' => 4],
        'fact_education' => ['label' => 'Quick fact: Education', 'max' => 120],
        'fact_focus'     => ['label' => 'Quick fact: Focus', 'max' => 120],
        'fact_interests' => ['label' => 'Quick fact: Interests', 'max' => 120],
        'email'          => ['label' => 'Contact email', 'type' => 'email', 'required' => true, 'max' => 150],
        'facebook_url'   => ['label' => 'Facebook (or other profile) URL', 'type' => 'url', 'max' => 255],
        'phone'          => ['label' => 'Phone (optional)', 'max' => 40],
        'location'       => ['label' => 'Location (optional)', 'max' => 100],
    ],
]);
