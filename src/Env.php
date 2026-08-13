<?php

namespace Neblabs\HeaderInjector;

use Adbar\Dot;

class Env
{
    private Dot $data;

    public function __construct(string $source) {
        $this->data = new Dot(require $source);
    }

    public function get($path) : string|array
    {
        $value = $this->data->get($path);

        return str_replace(search: '((slug))', replace: $this->data->get('slug'), subject: $value);
        //return $value === '((slug))' ? $this->data->get('slug') : $value;
    }
}