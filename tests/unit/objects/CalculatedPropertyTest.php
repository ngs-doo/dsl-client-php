<?php

class CalculatedPropertyTest extends BaseTestCase
{
    private $post;

    public function setUp()
    {
        $this->tearDown();

        $post = new \Blog\Post();
        $post->Title = 'test';
        $post->Content = 'test';

        $user1 = new \Blog\User(array(
            'Name' => 'userOne',
            'Email'=>'one@example.com'));
        $user2 = new \Blog\User(array(
            'Name' => null,
            'Email'=>'two@example.com'));
        $user1->persist();
        $user2->persist();

        $comment1 = new \Blog\Comment(array(
            'User' => $user1,
            'Content' => 'one',
            'Score' => 1,
            'CreatedAt' => '2000-31-12',
        ));
        $comment2 = new \Blog\Comment(array(
            'User' => $user2,
            'Content' => 'two',
            'Score' => 2,
            'CreatedAt' => '2013-04-16',
        ));
        $post->Comments = array($comment1, $comment2);
        $post->persist();

        $this->post = $post;
    }

    public function tearDown()
    {
        $this->deleteAll('Blog\Post');
        $this->deleteAll('Blog\User');
        //array_map(function($post) { $post->delete(); }, \Blog\Post::findAll());
        //array_map(function($user) { $user->delete(); }, \Blog\User::findAll());
    }

    public function testCalculatedProperties()
    {
        $post = $this->post;

        //$this->assertSame(2, $post->TotalComments);
        $this->assertSame(true, $post->HasComments);

        $emails = array('one@example.com', 'two@example.com');
        $this->assertSame($emails, $post->UsersEmails);

        $names = array('UserOne', null);
        $this->assertSame($emails, $post->UsersEmails);

        $dates = array(new \NGS\LocalDate('2000-31-12'),
            new \NGS\LocalDate('2013-04-16'),);
        $this->assertEquals($dates, $post->CommentDates);
    }

    public function testCalculatedPropertyWithCalculatedPropertyInLambda()
    {
        $post = $this->post;
        $this->assertSame(true, $post->HasCommentsWithPositiveScore);

        $newPost = new \Blog\Post();
        $newPost->persist();
        $this->assertSame(false, $newPost->HasCommentsWithPositiveScore);
    }
}

//calculated CommentUsers from 'it => it.Comments.Select(c => c.User)';
//calculated string[] Emails from 'it => it.Comments.Select(o => o.BlogURI).ToArray()';
