<?php

namespace Tests\Wallabag\ParamConverter;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wallabag\Entity\User;
use Wallabag\ParamConverter\UsernameFeedTokenConverter;
use Wallabag\Repository\UserRepository;

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
        $registry = $this->getMockBuilder(ManagerRegistry::class)
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
        $registry = $this->getMockBuilder(ManagerRegistry::class)
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
        $meta = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->once())
            ->method('getName')
            ->willReturn('nothingrelated');

        $em = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('superclass')
            ->willReturn($meta);

        $registry = $this->getMockBuilder(ManagerRegistry::class)
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
        $meta = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->once())
            ->method('getName')
            ->willReturn(User::class);

        $em = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with(User::class)
            ->willReturn($meta);

        $registry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->willReturn(['default' => null]);

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($em);

        $params = new ParamConverter(['class' => User::class]);
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
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('User not found');

        $repo = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findOneByUsernameAndFeedToken')
            ->with('test', 'test')
            ->willReturn(null);

        $em = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repo);

        $registry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($em);

        $params = new ParamConverter(['class' => User::class]);
        $converter = new UsernameFeedTokenConverter($registry);
        $request = new Request([], [], ['username' => 'test', 'token' => 'test']);

        $converter->apply($request, $params);
    }

    public function testApplyUserFound()
    {
        $user = new User();

        $repo = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findOneByUsernameAndFeedtoken')
            ->with('test', 'test')
            ->willReturn($user);

        $em = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repo);

        $registry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(User::class)
            ->willReturn($em);

        $params = new ParamConverter(['class' => User::class, 'name' => 'user']);
        $converter = new UsernameFeedTokenConverter($registry);
        $request = new Request([], [], ['username' => 'test', 'token' => 'test']);

        $converter->apply($request, $params);

        $this->assertSame($user, $request->attributes->get('user'));
    }
}
