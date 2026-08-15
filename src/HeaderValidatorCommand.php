<?php

namespace Neblabs\HeaderInjector;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use function Neblabs\DynamicData\dynamicHeaders;
use function Neblabs\DynamicData\getVersionsFinder;
use function Neblabs\HeaderParser\parse;

class HeaderValidatorCommand
{
    #[AsCommand(name: 'validate', description: 'Validates plugin and readme header against a git dir.')]
    public function __invoke(
        #[Argument(description: 'The path to the plugins main file')] string $pluginFile,
        #[Argument(description: 'The path to the plugins readme')] string $pluginReadme,
        #[Argument(description: 'Where the data will be written (dir).')] string $wpTestedVersion,
        #[Option(description: "Used for getting versions from tags. Defaults to cwd.", name: 'git-source' )] string $gitDir = '.',
    ) {
        $gitDir = $gitDir === '.' ? getcwd() : $gitDir;
        # read the env from source dir
        $fileSources = [
            [
                'type' => 'php',
                'source' => $pluginFile,
            ],
            [
                'type' => 'md',
                'source' => $pluginReadme,
            ],
        ];

        foreach ($fileSources as $fileSource) {
            # read the source files into memory
            $contents = file_get_contents("{$fileSource['source']}");
            # read the source headers into memory
            $sourceHeaders = parse($contents, $fileSource['type']);
            $sourceHeadersData = $sourceHeaders->values;
            # read the dynamic headers to inject
            //$dynamicHeaders = dynamicHeaders($options, $fileSource['type']);

            # so here we run the validators

            $this->validateNonNullValues($sourceHeadersData);
            # dynamic headers: check that all exist in source headers
            //$this->validateAllDynamicHeadersExistInSource($sourceHeadersData, $dynamicHeaders);
            # check tag versions are correct from the local tags - repo
            $this->validateTagVersions($sourceHeadersData, $fileSource['type'], $gitDir);
            # check all versions are valid numbers
            $this->checkAllVersionsAreValidNumbers($sourceHeadersData);
            # check wp matches given wp version
            $fileSource['type'] === 'md' && $this->checkWPVersionMatches($sourceHeadersData, $wpTestedVersion);
        }

        return 0; # ok
    }

    private function validateNonNullValues(array $sourceHeadersData): void
    {
        foreach ($sourceHeadersData as $key => $value) {
            if (
                !trim($value) ||
                in_array(needle: strtolower(trim($value)), haystack: ['unknown', 'dev'])
            ) {
                throw new \Exception("Invalid header: $key, with value: $value");
            }
        }
    }

    private function validateAllDynamicHeadersExistInSource(array $sourceHeadersData, array $dynamicHeaders): void
    {
        $intersection = array_intersect_assoc($dynamicHeaders, $sourceHeadersData);

        if ($intersection !== $dynamicHeaders) {
            echo "Dynamic headers:\n";
            var_dump($dynamicHeaders);
            echo "Source headers:\n";
            var_dump($sourceHeadersData);
            throw new \Exception("Dynamic headers don't match the source headers.");
        }
    }

    private function validateTagVersions(array $sourceHeadersData, mixed $type, string $gitDir)
    {
        if ($type === 'php') {
            # check for the latest version.
            $version = $sourceHeadersData['Version'];
            $versionsType = 'any';
        } else {
            # check for the latest STABLE version
            $version = $sourceHeadersData['Stable tag'];
            $versionsType = 'stable';
        }

        $latestVersion = trim(system("cd $gitDir && versions-finder $versionsType --latest --output-strict-semver"));

        if (!$latestVersion) {
            throw new \Exception("No $version version found in this repo: $gitDir");
        }

        if (!trim($version)) {
            throw new \Exception("No version found in the source file: $type");
        }

        if ($latestVersion !== $version) {
            throw new \Exception("Version in $type header is not the same as the '$versionsType' tag version. $type $version, latest $latestVersion");
        }

    }

    private function checkAllVersionsAreValidNumbers(array $sourceHeadersData)
    {
        $versions = [
            'Version', 'Requires at least', 'Requires PHP', 'Stable tag', 'Tested up to'
        ];

        foreach ($sourceHeadersData as $key => $value) {
            if (in_array(needle: strtolower($key), haystack: array_map(strtolower(...), $versions))) {
                if (!preg_match('/^[0-9]+/', $value)) {
                    throw new \Exception("Version is not formatted correctly. needs to start with a number. key $key value $value");
                }
            }
        }
    }

    private function checkWPVersionMatches(array $sourceHeadersData, string $wpTestedVersion)
    {
        if ($sourceHeadersData['Tested up to'] !== $wpTestedVersion) {
            throw new \Exception("tested up to doesn't match. source: {$sourceHeadersData['Tested up to']}, given: $wpTestedVersion");
        }
    }
}