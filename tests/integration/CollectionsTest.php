<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;

final class CollectionsTest extends TestCase
{
    public function testCanCreateCollection(): void
    {
        $utils = new Utils();
        $userHasCollectionRight = uniqid("userwithcollectionright");
        $utils->createAPIUser($userHasCollectionRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createCollectionRight = ["createCollection" => true];

        $utils->adminAddRightsToUserAPI($userHasCollectionRight, $createCollectionRight);
        $collectionDefaultVisibility = Utils::collection(uniqid("newcollection"), ['default']);

        $collectionNoVisibility = Utils::collection(uniqid("newcollectionnovisibility"), []);
        $utils->createCollectionAPI($userHasCollectionRight, $collectionNoVisibility);

        $response = Utils::httpPost("http://" . $userHasCollectionRight . ":dummy@localhost:5252/collections", json_encode($collectionDefaultVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "cleanJSON - You are not allowed to set the visibility of the default group", $response);

        $response = Utils::httpPost("http://" . $userWithoutRights . ":dummy@localhost:5252/collections", json_encode($collectionNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "createCollection - Forbidden", $response);
    }

    public function testCanUpdateCollection(): void
    {
        $utils = new Utils();
        $userHasCollectionRight = uniqid("userwithcollectionright");
        $utils->createAPIUser($userHasCollectionRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createCollectionRight = ["createCollection" => true];

        $utils->adminAddRightsToUserAPI($userHasCollectionRight, $createCollectionRight);
        $collectionName = uniqid("newcollection");
        $collectionNoVisibility = Utils::collection($collectionName, []);
        $utils->createCollectionAPI($userHasCollectionRight, $collectionNoVisibility);

        $collectionNoVisibility['description'] = "updated description";
        $collectionNoVisibility['title'] = uniqid('new title');

        $response = Utils::httpPut("http://" . $userHasCollectionRight . ":dummy@localhost:5252/collections/" . $collectionName, json_encode($collectionNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userHasCollectionRight . ":dummy@localhost:5252/collections/" . $collectionName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->description, $collectionNoVisibility['description'], $response);
        $this->assertSame($decoded->title, $collectionNoVisibility['title'], $response);

        $collectionNoVisibility['description'] = "unauthorized updated description";
        $collectionNoVisibility['title'] = uniqid('unauthorized new title');

        $response = Utils::httpPut("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName, json_encode($collectionNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);
    }

    public function testCanDeleteCollection(): void
    {
        $utils = new Utils();
        $userHasCollectionRight = uniqid("userwithcollectionright");
        $utils->createAPIUser($userHasCollectionRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createCollectionRight = ["createCollection" => true];

        $utils->adminAddRightsToUserAPI($userHasCollectionRight, $createCollectionRight);
        $collectionName = uniqid("newcollection");
        $collectionNoVisibility = Utils::collection($collectionName, []);
        $utils->createCollectionAPI($userHasCollectionRight, $collectionNoVisibility);

        //add item to check that collection with item inside cannot be deleted
        $item = Utils::item(uniqid("newitem"), []);
        $utils->createItemAPI($userHasCollectionRight, $collectionName, $item);

        $response = Utils::httpDelete("http://" . $userHasCollectionRight . ":dummy@localhost:5252/collections/" . $collectionName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        //remove item to be able to delete collection
        $response = Utils::httpDelete("http://" . $userHasCollectionRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpDelete("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        $response = Utils::httpDelete("http://" . $userHasCollectionRight . ":dummy@localhost:5252/collections/" . $collectionName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
}
