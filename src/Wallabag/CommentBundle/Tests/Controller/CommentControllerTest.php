<?php

namespace Wallabag\CommentBundle\Tests\Controller;

use Wallabag\CommentBundle\Tests\WallabagCommentTestCase;

class CommentControllerTest extends WallabagCommentTestCase
{
    public function testGetComments()
    {
        $comment = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCommentBundle:Comment')
            ->findOneBy(array('user' => 1));

        if (!$comment) {
            $this->markTestSkipped('No content found in db.');
        }
        $this->logInAs('admin');
        $crawler = $this->client->request('GET', 'annotations/'.$comment->getEntry()->getId().'.json');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, $content['total']);
        $this->assertEquals($comment->getText(), $content['rows'][0]['text']);
    }

    public function testSetcomment()
    {
        $this->logInAs('admin');

        $headers = array('CONTENT_TYPE' => 'application/json');
        $content = json_encode(array(
            'text' => 'my comment',
            ));
        $crawler = $this->client->request('POST', 'annotations/4.json', array(), array(), $headers, $content);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $comment = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCommentBundle:Comment')
            ->findLastCommentByPageId(4, 1);

        $this->assertEquals('my comment', $comment->getText());
    }

    public function testEditcomment()
    {
        $comment = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCommentBundle:Comment')
            ->findOneBy(array('user' => 1));

        $this->logInAs('admin');

        $headers = array('CONTENT_TYPE' => 'application/json');
        $content = json_encode(array(
            'text' => 'a modified comment',
            ));
        $crawler = $this->client->request('PUT', 'annotations/'.$comment->getId().'.json', array(), array(), $headers, $content);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('a modified comment', $content['text']);

        $commentUpdated = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCommentBundle:Comment')
            ->findCommentById($comment->getId());
        $this->assertEquals('a modified comment', $commentUpdated->getText());
    }
}
