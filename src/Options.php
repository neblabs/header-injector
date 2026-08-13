<?php

namespace Neblabs\HeaderInjector;

readonly class Options
{

    public Env $env;
    public string $source;
    public string $target;
    public string $wpTestedVersion;
    public string $gitDir;

    public function __construct(
        string $source,
        string $target,
        string $wpTestedVersion,
        string $gitDir,
    ) {
        # dirs...
        $this->source = $this->resolveDir($source);
        $this->target = $this->resolveDir($target);
        $this->gitDir = $this->resolveDir($gitDir);

        $this->wpTestedVersion = $wpTestedVersion;
        $this->env = new Env("$this->source/" . HeaderInjectorCommand::ENV_FILENAME);
    }

    private function resolveDir(string $dir)
    {
        if ($dir === '.') {
            return getcwd();
        }

        return $dir;
    }
}