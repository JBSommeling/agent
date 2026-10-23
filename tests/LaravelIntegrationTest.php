<?php

namespace JBSommeling\Agent\Tests;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Request;
use JBSommeling\Agent\Agent;
use JBSommeling\Agent\AgentServiceProvider;
use JBSommeling\Agent\Facades\Agent as AgentFacade;
use Orchestra\Testbench\TestCase;

class LaravelIntegrationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [AgentServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Agent' => AgentFacade::class];
    }

    public function testAgentAndClassBindingResolveToSameInstance()
    {
        $this->assertInstanceOf(Agent::class, $this->app->make('agent'));
        $this->assertSame($this->app->make('agent'), $this->app->make(Agent::class));
    }

    public function testFacadeUsesRequestUserAgent()
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36';
        $this->app->instance('request', Request::create('/', 'GET', [], [], [], ['HTTP_USER_AGENT' => $userAgent]));

        $this->assertSame('Chrome', AgentFacade::browser());
    }

    public function testProviderIsDeferred()
    {
        $this->assertInstanceOf(DeferrableProvider::class, new AgentServiceProvider($this->app));
        $this->assertSame(['agent', Agent::class], (new AgentServiceProvider($this->app))->provides());
    }

    public function testBindingIsScoped()
    {
        $firstInstance = $this->app->make('agent');
        $this->assertSame($firstInstance, $this->app->make('agent'));

        $this->app->forgetScopedInstances();

        $this->assertNotSame($firstInstance, $this->app->make('agent'));
    }
}
