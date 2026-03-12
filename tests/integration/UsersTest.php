<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;


final class UsersTest extends TestCase
{
    public function testCanCreateUser(): void
    {
        $response = Utils::httpPost("http://localhost:5252/users", json_encode(Utils::user(uniqid("newuser"), uniqid("newUser") . "@toto.fr")));

        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    public function testCanUpdateUser(): void
    {
        $userName = uniqid("newuser");
        $response = Utils::httpPost("http://localhost:5252/users", json_encode(Utils::user($userName, uniqid("newUser") . "@toto.fr")));

        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
        $profile = ["bio" => "This is John Doe biography - pretty empty"];
        $response = Utils::httpPut("http://" . $userName . ":" . "dummy@localhost:5252/users/" . $userName, json_encode($profile));

        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userName . ":" . "dummy@localhost:5252/users/" . $userName);

        $decoded = json_decode($response);
        $this->assertSame($decoded->bio, $profile['bio'], $response);
    }

    public function testCanGetUserOwnProfile(): void
    {
        $utils = new Utils();

        $userName = uniqid("newuser");
        $utils->createAPIUser($userName);

        $response = Utils::httpGet("http://" . $userName . ":" . "dummy@localhost:5252/users/" . $userName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->username, $userName, $response);

        $response = Utils::httpGet("http://" . $userName . ":" . "dummy@localhost:5252/me");
        $decoded = json_decode($response);
        $this->assertSame($decoded->username, $userName, $response);
    }

    #[Group('only')]
    public function testCanGetOtherUserProfile(): void
    {
        $utils = new Utils();

        $passiveUserName = uniqid("newuser");
        $utils->createAPIUser($passiveUserName);

        $activeUserName = uniqid("newuser");
        $utils->createAPIUser($activeUserName);

        $response = Utils::httpGet("http://" . $activeUserName . ":" . "dummy@localhost:5252/users/" . $passiveUserName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->username, $passiveUserName, $response);
        $this->assertArrayNotHasKey('firstname', array($decoded), $response);
    }

    public function testCanAuthenticateThroughToken(): void
    {
        $utils = new Utils();

        $userName = uniqid("newuser");
        $utils->createAPIUser($userName);
        $response = Utils::httpGet("http://admin:admin@localhost:5252/auth/create?duration=1&username=" . $userName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->username, $userName, $response);

        $token = $decoded->token;
        $response = Utils::httpGetWithHeader("http://localhost:5252/me", "Authorization: Bearer " . $token);
        $decoded = json_decode($response);
        $this->assertSame($decoded->username, $userName, $response);
    }

    public function testCanGetUserRights(): void
    {
        $utils = new Utils();

        $userName = uniqid("newuser");
        $utils->createAPIUser($userName);

        $rights = $utils->rights();

        $response = Utils::httpGet("http://" . $userName . ":" . "dummy@localhost:5252/users/" . $userName . "/rights");
        $this->assertJsonStringEqualsJsonString($response, json_encode(['rights' => $rights]), 'equals', $response);
    }
}
