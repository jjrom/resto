<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Group;

final class CatalogsTest extends TestCase
{
    public function testCanCreateCatalog(): void
    {
        //Create  catalog with group right
        $utils = new Utils();
        $userHasCatalogRight = uniqid("userwithcatalogright");
        $utils->createAPIUser($userHasCatalogRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createCatalogRight = ["createCatalog" => true];

        $utils->adminAddRightsToUserAPI($userHasCatalogRight, $createCatalogRight);
        $catalogDefaultVisibility = Utils::catalog(uniqid("newcatalog"), ['default']);

        $catalogNoVisibilityName = uniqid("newcatalognovisibility");
        $catalogNoVisibility = Utils::catalog($catalogNoVisibilityName, []);
        unset($catalogNoVisibility['visibility']);

        $catalogVisibility = Utils::catalog(uniqid("newcatalog"), [$userHasCatalogRight]);

        //not allowed to create catalog outside of /projects and /users
        $response = Utils::httpPost("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs", json_encode($catalogVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "addCatalog - Forbidden", $response);

        //Create catalog without visibility under /projects with user with right to create catalog, the default visibility should be applied, in this case the user private group   
        $utils->createCatalogAPI($userHasCatalogRight, $catalogNoVisibility);
        $response = Utils::httpGet("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibilityName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->title, $catalogNoVisibility['title'], $response);

        //not allowed to get private catalog if you don't have the right to see it
        $response = Utils::httpGet("http://" . $userWithoutRights . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibilityName);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "processPath - You are not allowed to access this catalog", $response);

        //Create catalog with default visibility, should return error because the user doesn't have the right to set default visibility
        $response = Utils::httpPost("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects", json_encode($catalogDefaultVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "addCatalog - You are not allowed to set the visibility of the default group", $response);

        //not allowed to create catalog without visibility if you don't have the right to create catalog
        $response = Utils::httpPost("http://" . $userWithoutRights . ":dummy@localhost:5252/catalogs/projects", json_encode($catalogNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "addCatalog - No visibility set for catalog and you don't have global right to create catalog", $response);

        //create child catalog
        $childCatalogNoVisibility = Utils::catalog(uniqid("newchildcatalog"), []);
        unset($childCatalogNoVisibility['visibility']);

        $createCatalogRight = ["projects/" . $catalogNoVisibility['id'] => ["createCatalog" => true]];
        $response = Utils::httpPut("http://" . $userHasCatalogRight . ":dummy@localhost:5252/users/" . $userHasCatalogRight . "/rights/catalogs/", json_encode($createCatalogRight));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $catalogNoVisibility['links'] = [[
            "rel" => "child",
            "type" => "application/json",
            "href" => "http://127.0.0.1:5252/catalogs/projects/" . $childCatalogNoVisibility['id']
        ]];
        $response = Utils::httpPost("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id'], json_encode($childCatalogNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->links[3]->rel, "child", $response);
        $this->assertStringContainsString($childCatalogNoVisibility['id'], $decoded->links[3]->href, $response);
    }

    public function testCanUpdateCatalog(): void
    {
        $utils = new Utils();
        $userHasCatalogRight = uniqid("userwithcatalogright");
        $utils->createAPIUser($userHasCatalogRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createCatalogRight = ["createCatalog" => true];

        $utils->adminAddRightsToUserAPI($userHasCatalogRight, $createCatalogRight);
        $catalogNoVisibility = Utils::catalog(uniqid("newcatalognovisibility"), []);
        $utils->createCatalogAPI($userHasCatalogRight, $catalogNoVisibility);

        $catalogNoVisibility['description'] = "updated description";
        $catalogNoVisibility['title'] = uniqid('new title');

        $response = Utils::httpPut("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id'], json_encode($catalogNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $response = Utils::httpGet("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->description, $catalogNoVisibility['description'], $response);
        $this->assertSame($decoded->title, $catalogNoVisibility['title'], $response);

        $catalogNoVisibility['description'] = "unauthorized updated description";
        $catalogNoVisibility['title'] = uniqid('unauthorized new title');

        $response = Utils::httpPut("http://" . $userWithoutRights . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id'], json_encode($catalogNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorMessage, "updateCatalog - Insufficient rights to update a catalog", $response);
    }

    public function testCanDeleteCatalog(): void
    {
        $utils = new Utils();
        $userHasCatalogRight = uniqid("userwithcatalogright");
        $utils->createAPIUser($userHasCatalogRight);
        $userWithoutRights = uniqid("userwithoutrights");
        $utils->createAPIUser($userWithoutRights);
        $createCatalogRight = ["createCatalog" => true];

        $utils->adminAddRightsToUserAPI($userHasCatalogRight, $createCatalogRight);
        $catalogNoVisibility = Utils::catalog(uniqid("newcatalognovisibility"), []);
        $utils->createCatalogAPI($userHasCatalogRight, $catalogNoVisibility);

        $response = Utils::httpDelete("http://" . $userWithoutRights . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->ErrorCode, 403, $response);

        $response = Utils::httpDelete("http://" . $userHasCatalogRight . ":dummy@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id']);
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    #[Group('only')]
    public function testAdminCanManageCatalogVisibility(): void
    {
        $utils = new Utils();
        $userHasCatalogRight = uniqid("userwithcatalogright");
        $utils->createAPIUser($userHasCatalogRight);
        $createCatalogRight = ["createCatalog" => true];

        $utils->adminAddRightsToUserAPI($userHasCatalogRight, $createCatalogRight);
        $catalogNoVisibility = Utils::catalog(uniqid("newcatalognovisibility"), []);
        $utils->createCatalogAPI($userHasCatalogRight, $catalogNoVisibility);

        $catalogNoVisibility['visibility'] = ['default'];

        //admin can change the visibility to default
        $response = Utils::httpPut("http://admin:admin@localhost:5252/catalogs/projects/" . $catalogNoVisibility['id'], json_encode($catalogNoVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);

        $catalogDefaultVisibility = Utils::catalog(uniqid("newcatalogDefaultVisibility"), ['default']);
        
        $response = Utils::httpPost("http://admin:admin@localhost:5252/catalogs/projects", json_encode($catalogDefaultVisibility));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
}
