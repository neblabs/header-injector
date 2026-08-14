<?php

namespace Neblabs\DynamicData;
use Neblabs\HeaderInjector\Env;
use Neblabs\HeaderInjector\HeaderInjectorCommand;
use Neblabs\HeaderInjector\Options;

function dynamicHeaders(Options $options, string $type) : array
{
    return match ($type) {
        'php' => array_filter(resolvePHPHeaders($options)),
        'md' => array_filter(resolveMDHeaders($options)),
    };
}

function resolvePHPHeaders(Options $options): array
{
    /*
     * -Plugin URI: urls.plugin
     *  -Version: Updated from the last repo tag including rc, alpha, anything that starts with vX
     *  -Author URI: urls.organization
     *  -Requires at least: requires.wp
     *  -Requires PHP: requires.php
     */

    return [
        'Plugin URI' => $options->env->get('urls.plugin', null),
        'Version' => (function () use ($options) {
            # first cd into the git dir if given one.
            $cd =  $options->gitDir ?: getcwd();
            $versionsFinder = getVersionsFinder();

            # any starting with v.x.x.x, version can be unstable
            $version = trim(shell_exec("cd \"$cd\" && bash $versionsFinder any --latest --output-strict-semver")) ?: null;

            return $version;
        })(),
        'Author URI' => $options->env->get('urls.organization', null),
        'Requires at least' => $options->env->get('requires.wp', null),
        'Requires PHP' => $options->env->get('requires.php', null),
    ];
}

function resolveMDHeaders(Options $options): array
{
    /*
     *   -Requires at least: requires.wp
     *  -Tested up to: Updated from the last version pulled to run the tests
     *  -Stable tag: Updated from the last STABLE tag in the repo in the format: vx.x.x , ignores tags such as vx.x.x-rc, v.x.x.x-beta-1, etc
     *  -Requires PHP: requires.php
     *
     */
    return [
        'Requires at least' => $options->env->get('requires.wp', null),
        'Requires PHP' => $options->env->get('requires.php', null),
        // so here's the actual juice
        'Stable tag' => (function () use ($options) {
            # first cd into the git dir if given one.
            $cd =  $options->gitDir ?: getcwd();
            $versionsFinder = getVersionsFinder();
            //only find the version that matches a stable semver, any other (unstable) gets ignored
            $version = trim(shell_exec("cd \"$cd\" &&  bash $versionsFinder stable --latest --output-strict-semver")) ?: null;

            return $version;
        })(),
        'Tested up to' => $options->wpTestedVersion,
    ];
}


/**
 * @return string
 */
function getVersionsFinder(): string
{
    $rootDir = dirname(__FILE__, 2);
    $versionsFinder = "$rootDir/bin/versions-finder";
    return $versionsFinder;
}
