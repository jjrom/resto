<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;

final class GroupsTest extends TestCase
{
    public function testCanUpdateGroupRights(): void
    {
        $utils = new Utils();

        $userName = uniqid("newuser");
        $userId = $utils->createAPIUser($userName);

        $groupName = uniqid("newGroup");
        $utils->adminCreateAPIGroup($userId, $groupName);

        $unauthorizedRight = ["createCollection" => true];
        $response = Utils::httpPost("http://" . $userName . ":dummy@localhost:5252/groups/" . $groupName . "/rights", json_encode($unauthorizedRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 400, $response);

        $createGroupItemRight = RestoGroup::createItemRight($groupName);
        $goodRight = [$createGroupItemRight => true];
        $response = Utils::httpPost("http://" . $userName . ":dummy@localhost:5252/groups/" . $groupName . "/rights", json_encode($goodRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
        $this->assertObjectHasProperty($createGroupItemRight, $decoded->rights, $response);
    }

    #[Group('only')]
    public function testCanPlayWithGroupRightCreate(): void
    {
        $utils = new Utils();

        $groupOwnerUserName = uniqid("groupowner");
        $groupOwnerId = $utils->createAPIUser($groupOwnerUserName);


        $inGroupUserName = uniqid("useringroup");
        $utils->createAPIUser($inGroupUserName);

        $randomUserName = uniqid("lequentin");
        $utils->createAPIUser($randomUserName);

        $groupName = uniqid("itemCreationGroup");
        $utils->adminCreateAPIGroup($groupOwnerId, $groupName);

        $utils->addUserToGroupAPI($groupOwnerUserName, $groupName, $inGroupUserName);

        $itemRight = [
            RestoGroup::createItemRight($groupName) => true,
        ];
        $collectionRight = [
            RestoGroup::createCollectionRight($groupName) => true,
        ];
        $response = Utils::httpPost("http://" . $groupOwnerUserName . ":dummy@localhost:5252/groups/" . $groupName . "/rights", json_encode($itemRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpPost("http://admin:admin@localhost:5252/users/" . $groupOwnerUserName . "/rights", json_encode($collectionRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $collectionName =  uniqid("collection");
        $collection = Utils::collection($collectionName, [$groupName]);
        $utils->createCollectionAPI($groupOwnerUserName, $collection);

        //inGroupUser is forbidden to create collection in group
        $response = Utils::httpPost("http://" . $inGroupUserName . ":dummy@localhost:5252/collections", json_encode($collection));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        //inGroupUser can create items in collection with group visibility
        $utils->createItemAPI($inGroupUserName, $collectionName, Utils::item(uniqid("item1"), []));

        //randomUser cannot see collection if not in group with visibility
        $response = Utils::httpGet("http://" . $randomUserName . ":dummy@localhost:5252/collections/" . $collectionName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        //randomUser cannot create items in collection with group visibility
        $response = Utils::httpPost("http://" . $randomUserName . ":dummy@localhost:5252/collections/" . $collectionName . "/items", json_encode(Utils::item(uniqid("item2"), [])));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);
    }

    public function testCanPlayWithGroupRightUpdate(): void
    {
        $utils = new Utils();

        $groupOwnerUserName = uniqid("groupowner");
        $groupOwnerId = $utils->createAPIUser($groupOwnerUserName);

        $inGroupUserName = uniqid("useringroup");
        $utils->createAPIUser($inGroupUserName);

        $inSecondGroupUserName = uniqid("userinsecondgroup");
        $utils->createAPIUser($inSecondGroupUserName);

        $randomUserName = uniqid("lequentin");
        $utils->createAPIUser($randomUserName);

        $groupName = uniqid("updateItemGroup");
        $utils->adminCreateAPIGroup($groupOwnerId, $groupName);

        $secondGroupName = uniqid("updateCollectionGroup");
        $utils->adminCreateAPIGroup($groupOwnerId, $secondGroupName);

        $utils->addUserToGroupAPI($groupOwnerUserName, $groupName, $inGroupUserName);
        $utils->addUserToGroupAPI($groupOwnerUserName, $secondGroupName, $inSecondGroupUserName);

        $groupRight = [
            RestoGroup::createItemRight($groupName) => true,
            RestoGroup::updateItemRight($groupName) => true,
            RestoGroup::updateCollectionRight($groupName) => true,
        ];

        $collectionRight = [
            RestoGroup::createCollectionRight($groupName) => true,
            RestoGroup::createCollectionRight($secondGroupName) => true,
        ];

        $response = Utils::httpPost("http://" . $groupOwnerUserName . ":dummy@localhost:5252/groups/" . $groupName . "/rights", json_encode($groupRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpPost("http://admin:admin@localhost:5252/users/" . $groupOwnerUserName . "/rights", json_encode($collectionRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $collectionName =  uniqid("collection");
        $collection = Utils::collection($collectionName, [$groupName, $secondGroupName]);
        $utils->createCollectionAPI($groupOwnerUserName, $collection);


        $collection['description'] = "updated description";
        $collection['title'] = uniqid('new title');

        //User in group cannot update visibility
        $response = Utils::httpPut("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName, json_encode($collection));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        unset($collection["visibility"]);

        // User in group with update right can update collection
        $response = Utils::httpPut("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName, json_encode($collection));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        // Check that collection was really updated
        $response = Utils::httpGet("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->description, $collection['description'], $response);
        $this->assertSame($decoded->title, $collection['title'], $response);


        $collection['description'] = "unauthorized updated description";
        $collection['title'] = uniqid('unauthorized new title');

        // Random user not in any group cannot see and update collection -> Not found
        $response = Utils::httpPut("http://" . $randomUserName . ":dummy@localhost:5252/collections/" . $collectionName, json_encode($collection));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        // User in second group with no update right on this collection cannot update collection -> Insufficient rights
        $response = Utils::httpPut("http://" . $inSecondGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName, json_encode($collection));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        //Update item

        $privateItem = Utils::item(uniqid("item1"), []);
        $utils->createItemAPI($groupOwnerUserName, $collectionName, $privateItem);

        $privateItem['description'] = "updated item description";

        // User in group cannot private update
        $response = Utils::httpPut("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $privateItem['id'], json_encode($privateItem));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        $inGroupItem = Utils::item(uniqid("ingroupitem1"), [$groupName]);
        $utils->createItemAPI($groupOwnerUserName, $collectionName, $inGroupItem);
        $inGroupItem['description'] = "in group updated item description";

        // User in group cannot update visibility
        $response = Utils::httpPut("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $inGroupItem['id'], json_encode($inGroupItem));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        unset($inGroupItem['properties']["visibility"]);

        // User in group with update right can update item
        $response = Utils::httpPut("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $inGroupItem['id'], json_encode($inGroupItem));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        // User in second group with no update right cannot update item -> Cannot see item
        $response = Utils::httpPut("http://" . $inSecondGroupUserName . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $inGroupItem['id'], json_encode($inGroupItem));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        // group owner with update right can update item visibility
        $inGroupItem['properties']['visibility'] = [$groupName];
        $response = Utils::httpPut("http://" . $groupOwnerUserName . ":dummy@localhost:5252/collections/" . $collectionName . "/items/" . $inGroupItem['id'], json_encode($inGroupItem));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $groupOwnerUserName . ":" . "dummy@localhost:5252/collections/" . $collectionName . "/items/" . $inGroupItem['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->properties->visibility, $inGroupItem['properties']['visibility'], $response);
    }

    public function testCanPlayWithGroupRightDelete(): void
    {
        $utils = new Utils();

        $groupOwnerUserName = uniqid("groupowner");
        $groupOwnerId = $utils->createAPIUser($groupOwnerUserName);

        $inGroupUserName = uniqid("useringroup");
        $utils->createAPIUser($inGroupUserName);

        $randomUserName = uniqid("lequentin");
        $utils->createAPIUser($randomUserName);

        $groupName = uniqid("itemCreationGroup");
        $groupRight = [
            RestoGroup::createItemRight($groupName) => true,
            RestoGroup::deleteItemRight($groupName) => true,
            RestoGroup::createCollectionRight($groupName) => true,
            RestoGroup::deleteCollectionRight($groupName) => true,
        ];
        $utils->adminCreateAPIGroup($groupOwnerId, $groupName);


        $utils->addRightToGroupAPI($groupOwnerUserName, $groupName, $groupRight);
        $utils->addUserToGroupAPI($groupOwnerUserName, $groupName, $inGroupUserName);

        //create colelciton
        $collectionId =  uniqid("collection");
        $collection = Utils::collection($collectionId, [$groupName]);
        $utils->createCollectionAPI($groupOwnerUserName, $collection);

        $itemId = uniqid("item");
        $item = Utils::item($itemId, [$groupName]);
        $utils->createItemAPI($groupOwnerUserName, $collectionId, $item);


        //random delete item
        $response = Utils::httpDelete("http://" . $randomUserName . ":dummy@localhost:5252/collections/" . $collectionId . "/items/" . $itemId);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);

        //ingroup delete item
        $response = Utils::httpDelete("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionId . "/items/" . $itemId);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
        //random delete collection
        $response = Utils::httpDelete("http://" . $randomUserName . ":dummy@localhost:5252/collections/" . $collectionId);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 404, $response);
        //ingroup delete collection
        $response = Utils::httpDelete("http://" . $inGroupUserName . ":dummy@localhost:5252/collections/" . $collectionId);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    public function testCanManageCatalogWithGroupRight(): void
    {
        $utils = new Utils();
        $groupOwnerUserName = uniqid("groupowner");
        $groupOwnerId = $utils->createAPIUser($groupOwnerUserName);

        $inGroupUserName = uniqid("useringroup");
        $utils->createAPIUser($inGroupUserName);

        $randomUserName = uniqid("lequentin");
        $utils->createAPIUser($randomUserName);

        $groupName = uniqid("catalogManagementGroup");
        $groupRight = [
            RestoGroup::createCatalogRight($groupName) => true,
            RestoGroup::updateCatalogRight($groupName) => true,
            RestoGroup::deleteCatalogRight($groupName) => true,
        ];
        $utils->adminCreateAPIGroup($groupOwnerId, $groupName);
        
        $utils->addRightToGroupAPI($groupOwnerUserName, $groupName, $groupRight);
        $utils->addUserToGroupAPI($groupOwnerUserName, $groupName, $inGroupUserName);

        //create catalog
        $catalogId =  uniqid("catalog");
        $catalog = Utils::catalog($catalogId, [$groupName]);
        $response = Utils::httpPost("http://" . $groupOwnerUserName . ":dummy@localhost:5252/catalogs/projects", json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        //inuser create catalog
        $inUserCatalogId =  uniqid("inusercatalog");
        $inUserCatalog = Utils::catalog($inUserCatalogId, [$groupName]);
        $response = Utils::httpPost("http://" . $inGroupUserName . ":dummy@localhost:5252/catalogs/projects", json_encode($inUserCatalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        //random create catalog
        $randomUserCatalogId =  uniqid("randomusercatalog");
        $randomUserCatalog = Utils::catalog($randomUserCatalogId, [$groupName]);
        $response = Utils::httpPost("http://" . $randomUserName . ":dummy@localhost:5252/catalogs/projects", json_encode($randomUserCatalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        //update catalog 
        $catalog['description'] = "updated description";
        unset($catalog["visibility"]);
        $response = Utils::httpPut("http://" . $inGroupUserName . ":dummy@localhost:5252/catalogs/projects/" . $catalogId, json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        //random update catalog
        $catalog['description'] = "unauthorized updated description";
        $response = Utils::httpPut("http://" . $randomUserName . ":dummy@localhost:5252/catalogs/projects/" . $catalogId, json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        //random user delete catalog
        $response = Utils::httpDelete("http://" . $randomUserName . ":dummy@localhost:5252/catalogs/projects/" . $catalogId);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        //in group user delete catalog
        $response = Utils::httpDelete("http://" . $inGroupUserName . ":dummy@localhost:5252/catalogs/projects/" . $catalogId);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        //cannot create collection in route catalog without right
        $catalog['type'] = 'Collection';
        $catalog['visibility'] = [$groupName];
        $response = Utils::httpPost("http://" . $inGroupUserName . ":dummy@localhost:5252/catalogs/projects", json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);
    }

    public function testAdminCanManageGroupCatalogVisibility(): void
    {

        $utils = new Utils();
        $groupOwnerUserName = uniqid("groupowner");
        $groupOwnerId = $utils->createAPIUser($groupOwnerUserName);

        $groupName = uniqid("catalogManagementGroup");
        $groupRight = [
            RestoGroup::createCatalogRight($groupName) => true,
            RestoGroup::updateCatalogRight($groupName) => true,
            RestoGroup::deleteCatalogRight($groupName) => true,
        ];
        $utils->adminCreateAPIGroup($groupOwnerId, $groupName);

        $utils->addRightToGroupAPI($groupOwnerUserName, $groupName, $groupRight);

        //create catalog
        $catalogId =  uniqid("catalog");
        $catalog = Utils::catalog($catalogId, [$groupName]);
        $response = Utils::httpPost("http://" . $groupOwnerUserName . ":dummy@localhost:5252/catalogs/projects", json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $catalog['visibility'] = ['default'];

        //admin can change the visibility to default
        $response = Utils::httpPut("http://admin:admin@localhost:5252/catalogs/projects/" . $catalog['id'], json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $catalog['visibility'] = [$groupName];

        //admin can change the visibility back to group
        $response = Utils::httpPut("http://admin:admin@localhost:5252/catalogs/projects/" . $catalog['id'], json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);


        $response = Utils::httpGet("http://admin:admin@localhost:5252/catalogs/projects/" . $catalog['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->id,  $catalog['id'], $response);

        $catalogDefaultVisibility = Utils::catalog(uniqid("newcatalogDefaultVisibility"), ['default']);

        $response = Utils::httpPost("http://admin:admin@localhost:5252/catalogs/projects/" . $catalog['id'], json_encode($catalogDefaultVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
}
