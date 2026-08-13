<?php

namespace Neblabs\HeaderInjector;

use Symfony\Component\Console\Attribute\Option;

class HeaderValidatorCommand
{
    public function __construct(
        #[Argument(description: 'Where the data (plugin and readme) will be read (dir).')] string $source,
        #[Option(description: "Used for getting versions from tags. Defaults to cwd.", name: 'git-source' )] string $gitDir = '',
    ) {}
}