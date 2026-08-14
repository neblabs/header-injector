<?php

namespace Neblabs\HeaderInjector;

readonly class Options
{

    public ?Env $env;
    public string $source;
    public ?string $target;
    public string $wpTestedVersion;
    public string $gitDir;

    public function __construct(
        string $source,
        string $wpTestedVersion,
        string $gitDir,
        ?string $target = null,
    ) {
        # dirs...
        $this->source = $this->resolveDir($source);
        $this->gitDir = $this->resolveDir($gitDir);

        $this->wpTestedVersion = $wpTestedVersion;

        if ($target) {
            $this->env = new Env("$this->source/" . HeaderInjectorCommand::ENV_FILENAME);
            $this->target = $this->resolveDir($target);
        }
    }

    private function resolveDir(string $dir)
    {
        if ($dir === '.') {
            return getcwd();
        }

        return $dir;
    }
}