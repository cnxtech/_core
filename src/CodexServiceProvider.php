<?php
/**
 * Part of the Codex PHP packages.
 *
 * MIT License and copyright information bundled with this package in the LICENSE file
 */
namespace Codex\Core;

use Codex\Core\Extensions\Filters\FrontMatterFilter;
use Codex\Core\Extensions\Filters\ParsedownFilter;
use Codex\Core\Log\Writer;
use Codex\Core\Traits\CodexProviderTrait;
use Illuminate\Contracts\Foundation\Application;
use Monolog\Logger as Monolog;
use Sebwite\Support\ServiceProvider;

/**
 * Codex service provider.
 *
 * @package   Codex\Core
 * @author    Codex Project Dev Team
 * @copyright Copyright (c) 2015, Codex Project
 * @license   https://tldrlegal.com/license/mit-license MIT License
 */
class CodexServiceProvider extends ServiceProvider
{
    use CodexProviderTrait;

    /**
     * @var string
     */
    protected $dir = __DIR__;

    /**
     * Collection of configuration files.
     *
     * @var array
     */
    protected $configFiles = [ 'codex' ];

    /**
     * Collection of bound instances.
     *
     * @var array
     */
    protected $provides = [ 'codex' ];

    /**
     * @var array
     */
    protected $viewDirs = [ 'views' => 'codex' ];

    /**
     * @var array
     */
    protected $assetDirs = [ 'assets' => 'codex' ];

    /**
     * @var array
     */
    protected $providers = [
        \Sebwite\Support\SupportServiceProvider::class,
        Providers\ConsoleServiceProvider::class,
        Providers\RouteServiceProvider::class
    ];

    protected $bindings = [
        'codex.project'  => \Codex\Core\Project::class,
        'codex.document' => \Codex\Core\Document::class,
        'codex.menu'     => \Codex\Core\Menu::class,

        'codex.projects'  => Components\Factory\Projects::class,
        'codex.menus'     => Components\Factory\Menus::class,
        'codex.documents' => Components\Project\Documents::class

    ];

    /**
     * @var array
     */
    protected $singletons = [
        'codex' => Factory::class
    ];

    /**
     * @var array
     */
    protected $aliases = [
        'codex'     => Contracts\Codex::class,
        'codex.log' => Contracts\Log::class
    ];

    public function boot()
    {
        $app = parent::boot(); // TODO: Change the autogenerated stub
        $app->make('codex')->stack('codex::stacks.page-actions');
    }


    /**
     * Register bindings in the container.
     *
     * @return Application
     */
    public function register()
    {
        $app = parent::register();
        $this->registerLogger($app);
        $this->registerFilters();

        Factory::extend('projects', Components\Factory\Projects::class);
        Factory::extend('menus', Components\Factory\Menus::class);
        Project::extend('documents', Components\Project\Documents::class);
        $this->codexRouteExclusion('_markdown');
    }

    /**
     * Register the core filters.
     *
     * @return void
     */
    protected function registerFilters()
    {
        $this->codexFilter('front_matter', FrontMatterFilter::class);
        $this->codexFilter('parsedown', ParsedownFilter::class);
    }

    /**
     * registerLogger method
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return \Codex\Core\Log\Writer
     */
    protected function registerLogger(Application $app)
    {
        $app->instance('codex.log', $log = new Writer(
            new Monolog($app->environment()),
            $app[ 'events' ]
        ));
        $log->useFiles($app[ 'config' ][ 'codex.log.path' ]);

        return $log;
    }
}
