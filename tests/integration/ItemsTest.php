<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;

final class ItemsTest extends TestCase
{
    public function testCanCreateItem(): void
    {
        $utils = new Utils();
        $userHasItemRight = uniqid("userwithitemright");
        $utils->createAPIUser($userHasItemRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createItemRight = ["createItem" => true, "createCollection" => true, "createCatalog" => true];
        $utils->adminAddRightsToUserAPI($userHasItemRight, $createItemRight);

        $collectionName = uniqid("newcollection");
        $collectionNoVisibility = Utils::collection($collectionName, []);
        $utils->createCollectionAPI($userHasItemRight, $collectionNoVisibility);

        $item1 = Utils::item(uniqid("newitem1"));

        $item2 = Utils::item(uniqid("newitem2"));
        $utils->createItemAPI($userHasItemRight, $collectionName, $item2);

        $response = Utils::httpGet("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item2['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->properties->productIdentifier, $item2['id'], $response);
        $itemId = $decoded->id;

        $response = Utils::httpPost("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items", json_encode($item1));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpPost("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName . "/items", json_encode($item2));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        //add item to catalog
        $catalogName = uniqid("newcatalog");
        $catalogNoVisibility = Utils::catalog($catalogName, []);
        $utils->createCatalogAPI($userHasItemRight, $catalogNoVisibility);

        $catalogNoVisibility['links'] = [[
            "rel" => "item",
            "type" => "application/json",
            "href" => "http://127.0.0.1:5252/collections/" . $collectionName . "/items/" . $item2['id']
        ]];
        $response = Utils::httpPut("http://" . $userHasItemRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogName, json_encode($catalogNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userHasItemRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->links[3]->rel, "item", $response);
        $this->assertSame($decoded->links[3]->id, $itemId, $response);
    }

    public function testCanUpdateItem(): void
    {
        $utils = new Utils();
        $userHasItemRight = uniqid("userwithitemright");
        $utils->createAPIUser($userHasItemRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createItemRight = ["createItem" => true, "createCollection" => true, "createCatalog" => true];
        $utils->adminAddRightsToUserAPI($userHasItemRight, $createItemRight);

        $collectionName = uniqid("newcollection");
        $collectionNoVisibility = Utils::collection($collectionName, []);
        $utils->createCollectionAPI($userHasItemRight, $collectionNoVisibility);

        $item1 = Utils::item(uniqid("newitem1"));
        $utils->createItemAPI($userHasItemRight, $collectionName, $item1);

        $item1['properties']['description'] = "updated description";

        $response = Utils::httpPut("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item1['id'], json_encode($item1));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        $response = Utils::httpPut("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item1['id'], json_encode($item1));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userHasItemRight . ":" . "dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item1['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->properties->description, $item1['properties']['description'], $response);
    }

    public function testCanDeleteItem(): void
    {
        $utils = new Utils();
        $userHasItemRight = uniqid("userwithitemright");
        $utils->createAPIUser($userHasItemRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createItemRight = ["createItem" => true, "createCollection" => true, "createCatalog" => true];
        $utils->adminAddRightsToUserAPI($userHasItemRight, $createItemRight);

        $collectionName = uniqid("newcollection");
        $collectionNoVisibility = Utils::collection($collectionName, []);
        $utils->createCollectionAPI($userHasItemRight, $collectionNoVisibility);

        $item1 = Utils::item(uniqid("newitem1"));
        $utils->createItemAPI($userHasItemRight, $collectionName, $item1);

        $response = Utils::httpDelete("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item1['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        $response = Utils::httpDelete("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item1['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userHasItemRight . ":" . "dummy@localhost:5252/collections/" . $collectionName . "/items/" . $item1['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);
    }
}
