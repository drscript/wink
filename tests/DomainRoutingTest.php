<?php

namespace Wink\Tests;

class DomainRoutingTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        $app['config']->set('wink.domain', 'blog.test');
    }

    public function test_routes_are_limited_to_the_configured_domain(): void
    {
        $login = app('router')->getRoutes()->getByName('wink.auth.login');
        $posts = app('router')->getRoutes()->getByName('wink.posts.index');

        $this->assertSame('blog.test', $login->getDomain());
        $this->assertSame('blog.test', $posts->getDomain());
    }
}
