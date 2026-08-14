<?php

namespace Neblabs\HeaderInjector;

use function Neblabs\DynamicData\dynamicHeaders;
use function Neblabs\HeaderParser\parse;

class fileReader
{
    /**
     * @var array|array[]
     */
    private array $fileSources;

    public function __construct()
    {
        $this->fileSources = [
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
        ]
    }

    public function read(callable $operator)
    {
        foreach ($fileSources as $fileSource) {
            # read the source files into memory
            $contents = file_get_contents("{$options->source}/{$fileSource['source']}");
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

            $targetFile = "$target/{$fileSource['target']}";
            $result = file_put_contents($targetFile, $newContent);

            if (!$result) {
                throw new \Exception("could not write target file $targetFile");
            }

            if (!$silent) {
                echo "$targetFile\n";
            }
        }
    }
}