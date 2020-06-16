<?php

namespace Tests\Wallabag\CoreBundle\ParamConverter;

use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\ParamConverter\UsernameFeedTokenConverter;
use Wallabag\UserBundle\Entity\User;

class UsernameFeedTokenConverterTest extends TestCase
{
    public function testSupportsWithNoRegistry()
    {
        $params = new ParamConverter([]);
        $converter = new UsernameFeedTokenConverter();

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithNoRegistryManagers()
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->willReturn([]);

        $params = new ParamConverter([]);
        $converter = new UsernameFeedTokenConverter($registry);

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithNoConfigurationClass()
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->willReturn(['default' => null]);

        $params = new ParamConverter([]);
        $converter = new UsernameFeedTokenConverter($registry);

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithNotTheGoodClass()
    {
        $meta = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->once())
            ->method('getName')
            ->willReturn('nothingrelated');

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('superclass')
            ->willReturn($meta);

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->willReturn(['default' => null]);

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('superclass')
            ->willReturn($em);

        $params = new ParamConverter(['class' => 'superclass']);
        $converter = new UsernameFeedTokenConverter($registry);

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithGoodClass()
    {
        $meta = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->once())
            ->method('getName')
            ->willReturn('Wallabag\UserBundle\Entity\User');

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('WallabagUserBundle:User')
            ->willReturn($meta);

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->willReturn(['default' => null]);

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('WallabagUserBundle:User')
            ->willReturn($em);

        $params = new ParamConverter(['class' => 'WallabagUserBundle:User']);
        $converter = new UsernameFeedTokenConverter($registry);

        $this->assertTrue($converter->supports($params));
    }

    public function testApplyEmptyRequest()
    {
        $params = new ParamConverter([]);
        $converter = new UsernameFeedTokenConverter();

        $res = $converter->apply(new Request(), $params);

        $this->assertFalse($res);
    }

    public function testApplyUserNotFound()
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        $this->expectExceptionMessage('User not found');

        $repo = $this->getMockBuilder('Wallabag\UserBundle\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findOneByUsernameAndFeedToken')
            ->with('test', 'test')
            ->willReturn(null);

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getRepository')
            ->with('WallabagUserBundle:User')
            ->willReturn($repo);

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('WallabagUserBundle:User')
            ->willReturn($em);

        $params = new ParamConverter(['class' => 'WallabagUserBundle:User']);
        $converter = new UsernameFeedTokenConverter($registry);
        $request = new Request([], [], ['username' => 'test', 'token' => 'test']);

        $converter->apply($request, $params);
    }

    public function testApplyUserFound()
    {
        $user = new User();

        $repo = $this->getMockBuilder('Wallabag\UserBundle\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findOneByUsernameAndFeedtoken')
            ->with('test', 'test')
            ->willReturn($user);

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getRepository')
            ->with('WallabagUserBundle:User')
            ->willReturn($repo);

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('WallabagUserBundle:User')
            ->willReturn($em);

        $params = new ParamConverter(['class' => 'WallabagUserBundle:User', 'name' => 'user']);
        $converter = new UsernameFeedTokenConverter($registry);
        $request = new Request([], [], ['username' => 'test', 'token' => 'test']);

        $converter->apply($request, $params);

        $this->assertSame($user, $request->attributes->get('user'));
    }
}
