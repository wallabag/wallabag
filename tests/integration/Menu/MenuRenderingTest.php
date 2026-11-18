<?php

namespace Wallabag\Tests\Integration\Menu;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class MenuRenderingTest extends WallabagKernelTestCase
{
    public function testFabUsesTheFinalMenuContents(): void
    {
        $container = static::getContainer();
        $menu = $container->get('knp_menu.factory')->createItem('fab');
        $renderer = $container->get('knp_menu.renderer.twig');
        $options = ['template' => 'Menu/fab.html.twig'];

        $this->assertSame('', trim($renderer->render($menu, $options)));

        $menu->addChild('divider', ['extras' => ['kind' => 'separator']]);
        $this->assertSame('', trim($renderer->render($menu, $options)));

        $menu->addChild('extension_action', ['uri' => '/extension', 'label' => 'Extension action']);
        $this->assertStringContainsString('href="/extension"', $renderer->render($menu, $options));
    }

    public function testFormsKeepPostCsrfAndEscapedLabels(): void
    {
        $container = static::getContainer();
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));
        $container->get('request_stack')->push($request);
        $menu = $container->get('knp_menu.factory')->createItem('test');
        $menu->addChild('delete', [
            'label' => '<script>alert("label")</script>',
            'uri' => '/delete/1',
            'linkAttributes' => ['title' => '<img src=x>'],
            'extras' => ['kind' => 'form', 'csrf_token_id' => 'delete-entry'],
        ]);

        $html = $container->get('knp_menu.renderer.twig')->render($menu);
        $crawler = new Crawler($html);

        $this->assertCount(1, $crawler->filter('form[method="post"][action="/delete/1"] input[name="token"]'));
        $this->assertNotEmpty($crawler->filter('input[name="token"]')->attr('value'));
        $this->assertCount(1, $crawler->filter('button[type="submit"]'));
        $this->assertSame('<img src=x>', $crawler->filter('button[type="submit"]')->attr('title'));
        $this->assertCount(0, $crawler->filter('li.first, li.last, script, img'));
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }
}
