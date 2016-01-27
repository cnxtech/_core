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
use Radic\BladeExtensions\BladeExtensionsServiceProvider;
use Sebwite\Support\LocalFilesystem;
use Sebwite\Support\ServiceProvider;
use Sebwite\Support\SupportServiceProvider;

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

    protected $dir = __DIR__;

    protected $configFiles = [ 'codex' ];

    protected $viewDirs = [ 'views' => 'codex' ];

    protected $assetDirs = ['assets/codex-theme/dist' => 'codex'];

    protected $provides = [ 'codex', 'codex.log' ];

    protected $providers = [
        SupportServiceProvider::class,
        BladeExtensionsServiceProvider::class,
        Providers\ConsoleServiceProvider::class,
        Providers\RouteServiceProvider::class
    ];

    protected $bindings = [
        'codex.project'  => Project::class,
        'codex.document' => Document::class,
        'codex.menu'     => Menu::class,

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
