<?php
/*
 *
 * Readme.md
 *
 *  -Requires at least: requires.wp
 *  -Tested up to: Updated from the last version pulled to run the tests
 *  -Stable tag: Updated from the last STABLE tag in the repo in the format: vx.x.x , ignores tags such as vx.x.x-rc, v.x.x.x-beta-1, etc
 *  -Requires PHP: requires.php
 *
 * Plugin headers
 *
 *  -Plugin URI: urls.plugin
 *  -Version: Updated from the last repo tag including rc, alpha, anything that starts with vX
 *  -Author URI: urls.organization
 *  -Requires at least: requires.wp
 *  -Requires PHP: requires.php
 */

return [
    'slug' => 'coupons-plus-for-woocommerce',
    'requires' => [
        'wp' => 5.8,
        'php' => 7.4
    ],
    'urls' => [
        'plugin' => 'couponsplus.app',
        'organization' => 'neblabs.com'
    ],
    'files' => [
        'plugin' => [
            'in' => 'index.php',
            'out' => '((slug)).php'
        ],
        'readme' => 'readme.md'
    ]
];
