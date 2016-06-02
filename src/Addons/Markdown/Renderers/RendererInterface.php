<?php
namespace Codex\Addons\Markdown\Renderers;

use Codex\Support\Collection;

interface RendererInterface
{
    /**
     * render method
     *
     * @param $string
     *
     * @return mixed
     */
    public function render($string);

    /**
     * setConfig method
     *
     * @param array|Collection $config
     *
     * @return mixed
     */
    public function setConfig($config = [ ]);

    public function getName();
}