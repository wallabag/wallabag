<?php

namespace Wallabag\CoreBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Wallabag\CoreBundle\ParamConverter\UsernameRssTokenConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Entity\User;

class UsernameRssTokenConverterTest extends KernelTestCase
{
    public function testSupportsWithNoRegistry()
    {
        $params = new ParamConverter(array());
        $converter = new UsernameRssTokenConverter();

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithNoRegistryManagers()
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->will($this->returnValue(array()));

        $params = new ParamConverter(array());
        $converter = new UsernameRssTokenConverter($registry);

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithNoConfigurationClass()
    {
        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->will($this->returnValue(array('default' => null)));

        $params = new ParamConverter(array());
        $converter = new UsernameRssTokenConverter($registry);

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithNotTheGoodClass()
    {
        $meta = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('nothingrelated'));

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('superclass')
            ->will($this->returnValue($meta));

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->will($this->returnValue(array('default' => null)));

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('superclass')
            ->will($this->returnValue($em));

        $params = new ParamConverter(array('class' => 'superclass'));
        $converter = new UsernameRssTokenConverter($registry);

        $this->assertFalse($converter->supports($params));
    }

    public function testSupportsWithGoodClass()
    {
        $meta = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $meta->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Wallabag\CoreBundle\Entity\User'));

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with('WallabagCoreBundle:User')
            ->will($this->returnValue($meta));

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagers')
            ->will($this->returnValue(array('default' => null)));

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('WallabagCoreBundle:User')
            ->will($this->returnValue($em));

        $params = new ParamConverter(array('class' => 'WallabagCoreBundle:User'));
        $converter = new UsernameRssTokenConverter($registry);

        $this->assertTrue($converter->supports($params));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Route attribute is missing
     */
    public function testApplyEmptyRequest()
    {
        $params = new ParamConverter(array());
        $converter = new UsernameRssTokenConverter();

        $converter->apply(new Request(), $params);
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage User not found
     */
    public function testApplyUserNotFound()
    {
        $repo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findOneByUsernameAndRsstoken')
            ->with('test', 'test')
            ->will($this->returnValue(null));

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getRepository')
            ->with('WallabagCoreBundle:User')
            ->will($this->returnValue($repo));

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('WallabagCoreBundle:User')
            ->will($this->returnValue($em));

        $params = new ParamConverter(array('class' => 'WallabagCoreBundle:User'));
        $converter = new UsernameRssTokenConverter($registry);
        $request = new Request(array(), array(), array('username' => 'test', 'token' => 'test'));

        $converter->apply($request, $params);
    }

    public function testApplyUserFound()
    {
        $user = new User();

        $repo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repo->expects($this->once())
            ->method('findOneByUsernameAndRsstoken')
            ->with('test', 'test')
            ->will($this->returnValue($user));

        $em = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $em->expects($this->once())
            ->method('getRepository')
            ->with('WallabagCoreBundle:User')
            ->will($this->returnValue($repo));

        $registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();

        $registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('WallabagCoreBundle:User')
            ->will($this->returnValue($em));

        $params = new ParamConverter(array('class' => 'WallabagCoreBundle:User', 'name' => 'user'));
        $converter = new UsernameRssTokenConverter($registry);
        $request = new Request(array(), array(), array('username' => 'test', 'token' => 'test'));

        $converter->apply($request, $params);

        $this->assertEquals($user, $request->attributes->get('user'));
    }
}
