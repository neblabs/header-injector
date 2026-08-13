<?php

namespace Neblabs\HeaderInjector;

use http\Message\Body;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Input\InputInterface;
use function Neblabs\DynamicData\dynamicHeaders;
use function Neblabs\HeaderParser\parse;

class HeaderInjectorCommand
{
    public const string ENV_FILENAME = 'env.php';
    #[AsCommand(name: 'inject', description: 'Injects header data into plugin and readme files')]
    public function __invoke(
        #[Argument(description: 'Where the data will be read (dir). Usually the plugin dir in some shape.')] string $source,
        #[Argument(description: 'Where the data will be written (dir).')] string $target,
        #[Argument(description: 'Where the data will be written (dir).')] string $wpTestedVersion,
        #[Option(description: "Used for getting versions from tags. Defaults to cwd.", name: 'git-source' )] string $gitDir = ''
    ): void
    {
        # read the env from source dir
        $options = new Options(
            source: $source,
            target: $target,
            wpTestedVersion: $wpTestedVersion,
            gitDir: $gitDir,
            env: new Env("$source/".static::ENV_FILENAME)
        );

        # get the plugin in and put
        $fileSources = [
            [
                'type' => 'php',
                'source' => $options->env->get('files.plugin.in'),
                'target' => $options->env->get('files.plugin.out') ?? $options->env->get('files.plugin.in'),
            ],
            [
                'type' => 'md',
                'source' => $options->env->get('files.readme'),
                'target' => $options->env->get('files.readme'),
            ],
        ];

        foreach ($fileSources as $fileSource) {
            # read the source files into memory
            $contents = file_get_contents("{$source}/{$fileSource['source']}");
            # read the source headers into memory
            $sourceHeaders = parse($contents, $fileSource['type']);
            $sourceHeadersData = $sourceHeaders->values;
            # read the dynamic headers to inject
            $headersToInjectData = dynamicHeaders($options, $fileSource['type']);

            # inject the data into a new array (merge)
            $headersWithInjectedData = $this->removeInvalidValues(array_merge($sourceHeadersData, $headersToInjectData));

            # write the data to the targets.
            $newHeaders = "";

            $iterations = 0;
            foreach ($headersWithInjectedData as $name => $value) {
                $iterations++;
                $isLast = $iterations === count($headersWithInjectedData);
                if ($fileSource['type'] === 'php') {
                    $newHeaders .= " * $name: $value\n";
                } else if ($fileSource['type'] === 'md') {
                    $newHeaders .= "$name: $value\n";
                    $isLast && ($newHeaders = rtrim($newHeaders));
                }
            }

            $newContent = substr_replace(
                string: $contents,
                replace: "\n$newHeaders",
                offset: $sourceHeaders->boundaries->innerStart,
                length: $sourceHeaders->boundaries->innerEnd - $sourceHeaders->boundaries->innerStart
            );

            // I dont know if mkdir recursive works the same as mkdir -p in that it also ignores existing dirs so to prevent wiping lets check for it first.
            !is_dir($target) && mkdir($target, recursive: true);

            file_put_contents("$target/{$fileSource['target']}", $newContent);
        }
    }

    private function removeInvalidValues(array $headers) : array
    {
        return array_filter(callback: fn($value) => $value !== 'unknown' && $value, array: $headers);
    }
}
