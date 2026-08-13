<?php

namespace Neblabs\HeaderInjector;

readonly class Options
{

    public function __construct(
        public string $source,
        public string $target,
        public string $wpTestedVersion,
        public string $gitDir,
        public Env $env
    ) {}
}