<?php declare(strict_types=1);
require_once(__DIR__.'/../src/loader.php');
require_once(__DIR__."/../vendor/autoload.php");
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase {
    public function testUserCanBeRegistered() {
        $user = new User();
        $this->assertNotFalse($user->register('test', 'test', 'test@test.net', '1994-04-20'));
    }

    public function testUsersCountCorrect() {
        // Registers two extra test users.
        $user = new User();
        $user->register('test3', 'test3', 'tes3t@test.net', '1994-04-20');
        $user = new User();
        $user->register('test1', 'test', 'test2@test.net', '2020-12-19');
        $user = new User(1);
        $database = Postgres::getInstance();
        $this->assertEquals(3, $database->db_count("SELECT * FROM users", array()));
    }

    public function testUserGetByEmailSucceed() {
        $user = new User();
        $this->assertNotFalse($user->getByEmail('test@test.net'));
        
    }

    public function testUserGetByEmailFail() {
        $user = new User();

        $this->assertFalse($user->getByEmail('testicles'));
    }

    public function testSwitchMainBlogSucceed() {
        $user = new User(1);
        $this->assertTrue($user->switchMainBlog(1));
    }

    public function testSwitchMainBlogFail() {
        $user = new User(2);
        $this->assertFalse($user->switchMainBlog(1));
    }

    public function testUserUpdateFail() {
        $user = new User(1);
        $this->assertFalse($user->updateEmail('test2@test.net'));
    }

    public function testUserUpdateSuccess() {
        $user = new User(3);
        $this->assertTrue($user->updateEmail('tesfsdfst2@test.net'));
    }

    public function testBlocking() {
        $user = new User(1);
        $user->block(2);
        $blockManager = new BlockManager(1);
        $this->assertTrue($blockManager->hasBlockedUser(2));
    }

    public function testLoggedOutBlockFail() {
        $blockManager = new BlockManager(1);
        $this->assertFalse($blockManager->hasBlockedUser(0));
    }
}