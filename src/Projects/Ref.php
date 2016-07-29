<?php
/**
 * Part of the Codex Project packages.
 *
 * License and copyright information bundled with this package in the LICENSE file.
 *
 * @author    Robin Radic
 * @copyright Copyright 2016 (c) Codex Project
 * @license   http://codex-project.ninja/license The MIT License
 */
namespace Codex\Projects;


use Codex\Codex;
use Codex\Contracts;
use Codex\Exception\CodexException;
use Codex\Support\Extendable;
use Codex\Traits;
use Sebwite\Support\Str;
use Symfony\Component\Yaml\Yaml;
use vierbergenlars\SemVer\SemVerException;
use vierbergenlars\SemVer\version;

/**
 * This is the class Ref.
 *
 * @property \Codex\Documents\Documents    $documents
 * @property \Codex\Addon\Phpdoc\PhpdocRef $phpdoc
 *
 * @package        Codex\Projects
 * @author         CLI
 * @copyright      Copyright (c) 2015, CLI. All rights reserved
 *
 *
 */
class Ref extends Extendable
{
    use Traits\FilesTrait,
        Traits\ConfigTrait;

    /** @var string */
    protected $name;

    /** @var Project */
    protected $project;

    /** @var Refs */
    protected $refs;

    protected $version;

    protected $path;

    /**
     * Ref constructor.
     *
     * @param \Codex\Codex            $codex
     * @param \Codex\Projects\Project $project
     * @param \Codex\Projects\Refs    $refs
     * @param           string        $name
     */
    public function __construct(Codex $codex, Project $project, Refs $refs, $name)
    {
        $this->setCodex($codex);
        $this->setFiles($project->getFiles());

        $this->name    = $name;
        $this->project = $project;
        $this->refs    = $refs;
        $this->path    = $project->path($name);

        $this->hookPoint('refs:construct', [ $this ]);

        $this->resolve();

        $this->hookPoint('refs:constructed', [ $this ]);
    }

    protected function resolve()
    {
        $fs = $this->getFiles();
        if ( $fs->exists($this->path('codex.yml')) ) {
            $yaml = $fs->get($this->path('codex.yml'));
        } elseif ( $fs->exists($this->path('menu.yml')) ) {
            $yaml = $fs->get($this->path('menu.yml'));
        }

        isset($yaml) && $this->setConfig(Yaml::parse($yaml));
    }

    /**
     * Checks if this ref is a version. Versions are refs that are named using semver specification (as in 1.0.0, 2.3.4-beta)
     * @return bool
     */
    public function isVersion()
    {
        if ( $this->version === null ) {
            try {
                $this->version = new version($this->name);
            }
            catch (SemVerException $e) {
                $this->version = false;
            }
        }
        return $this->version instanceof version;
    }

    public function getVersion()
    {
        if ( $this->isVersion() === false ) {
            throw CodexException::because("Can not getVersion for Ref {$this->project}/{$this}. The Ref is not a semver. Check by using isVersion() first.");
        }
        return $this->version;
    }


    /**
     * Checks if this ref is a branch. Branches are refs that are not named using semver specification. (as in master, develop, hi-this-is-version-1-lol)
     * @return bool
     */
    public function isBranch()
    {
        return $this->isVersion() === false;
    }


    public function __toString()
    {
        return $this->name;
    }

    public function path($path = null)
    {
        return $path === null ? $this->name : path_join($this->name, $path);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }



    /**
     * resolveSidebarMenu method
     *
     * @param array|null|mixed $items
     * @param string           $parentId
     *
     * @throws \Codex\Exception\CodexException
     */
    protected function resolveSidebarMenu($items = null, $parentId = 'root')
    {
        #if($this->config('menu'))
        $this->hookPoint('project:sidebar:resolving', [ $items, $parentId ]);
        if ( $items === null ) {
            $items = $this->config('menu', []);
            $this->codex->menus->forget('sidebar');
        }

        $menu = $this->codex->menus->add('sidebar');
        $menu->setView($this->codex->view('menus.sidebar'));

        if ( !is_array($items) ) {
            throw CodexException::invalidMenuConfiguration(": menu.yml in [{$this}]");
        }

        foreach ( $items as $item ) {
            $link = '#';
            if ( array_key_exists('document', $item) ) {
                // remove .md extension if present
                $path = ends_with($item[ 'document' ], [ '.md' ]) ? Str::remove($item[ 'document' ], '.md') : $item[ 'document' ];
                $link = $this->codex->url($this->getProject(), $this->name, $path);
            } elseif ( array_key_exists('href', $item) ) {
                $link = $item[ 'href' ];
            }

            $id = md5($item[ 'name' ] . $link);

            $node = $menu->add($id, $item[ 'name' ], $parentId);
            $node->setAttribute('href', $link);
            $node->setAttribute('id', $id);

            if ( isset($item[ 'icon' ]) ) {
                $node->setMeta('icon', $item[ 'icon' ]);
            }

            if ( isset($item[ 'children' ]) ) {
                $this->resolveSidebarMenu($item[ 'children' ], $id);
            }
        }
        $this->hookPoint('project:sidebar:resolved', [ $menu ]);
    }

    /**
     * Returns the menu for this project
     * @deprecated
     * @return \Codex\Menus\Menu
     */
    public function getSidebarMenu()
    {
        if ( false === $this->codex->menus->has('sidebar') ) {
            $this->resolveSidebarMenu();
        }
        return $this->getCodex()->menus->get('sidebar');
    }

    /** @deprecated */
    public function renderSidebarMenu()
    {
        return $this->getSidebarMenu()->render();
    }


}
