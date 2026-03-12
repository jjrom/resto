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

        $itemDefaultVisibility = Utils::item(uniqid("newitem"), ['default']);

        $itemNoVisibility = Utils::item(uniqid("newitemnovisibility"), []);
        $utils->createItemAPI($userHasItemRight, $collectionName, $itemNoVisibility);

        $response = Utils::httpGet("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->properties->productIdentifier, $itemNoVisibility['id'], $response);
        $itemId = $decoded->id;

        $response = Utils::httpPost("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items", json_encode($itemDefaultVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "insertFeatures - You are not allowed to set the visibility of the default group", $response);

        $response = Utils::httpPost("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName . "/items", json_encode($itemNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        //add item to catalog
        $catalogName = uniqid("newcatalog");
        $catalogNoVisibility = Utils::catalog($catalogName, []);
        $utils->createCatalogAPI($userHasItemRight, $catalogNoVisibility);

        $catalogNoVisibility['links'] = [[
            "rel" => "item",
            "type" => "application/json",
            "href" => "http://127.0.0.1:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id']
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

        $itemNoVisibility = Utils::item(uniqid("newitemnovisibility"), []);
        $utils->createItemAPI($userHasItemRight, $collectionName, $itemNoVisibility);

        $itemNoVisibility['description'] = "updated description";

        $response = Utils::httpPut("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id'], json_encode($itemNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        $response = Utils::httpPut("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id'], json_encode($itemNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        //TODO do we care for item public visibility given it is in a collection or catalog
        // $itemNoVisibility['properties']+= ['visibility' => ['default']];
        // print_r(json_encode($itemNoVisibility));
        //  $response = Utils::httpPut("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id'] , json_encode($itemNoVisibility));
        // $decoded = json_decode($response);
        // $this->assertSame($decoded->ErrorCode, "403", $response);
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

        $itemNoVisibility = Utils::item(uniqid("newitemnovisibility"), []);
        $utils->createItemAPI($userHasItemRight, $collectionName, $itemNoVisibility);

        $response = Utils::httpDelete("http://" . $userWithoutRights . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        $response = Utils::httpDelete("http://" . $userHasItemRight . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $itemNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
}
