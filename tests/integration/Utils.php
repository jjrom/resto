<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

final class Utils extends Assert
{
    public static function httpPost(string $url, string $data): string
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function httpPut(string $url, string $data): string
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public static function httpDelete(string $url): string|bool
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    public static function httpGet(string $url): string
    {
        return Utils::httpGetWithHeader($url, '');
    }

    public static function httpGetWithHeader(string $url, string $headerContent): string
    {
        $curl = curl_init($url);
        $headerArray = [
            'Content-Type: application/json',
            $headerContent,
        ];
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headerArray);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function createAPIUser(string $userName): void
    {
        $response = Utils::httpPost("http://localhost:5252/users", json_encode(Utils::user($userName, $userName . "@toto.fr")));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
    public function createAPIGroup(string $userName, string $groupName): void
    {
        $response = Utils::httpPost("http://" . $userName . ":dummy@localhost:5252/groups", json_encode(Utils::group($groupName)));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    public function addUserToGroupAPI(string $ownerName, string $groupName, string $userName): void
    {
        $response = Utils::httpPost("http://" . $ownerName . ":dummy@localhost:5252/groups/" . $groupName . "/users", json_encode(["username" => $userName]));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    public function addRightToGroupAPI(string $ownerName, string $groupName, array $rights): void
    {
        $response = Utils::httpPost("http://" . $ownerName . ":dummy@localhost:5252/groups/" . $groupName . "/rights", json_encode($rights));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    /**
     * @param array<int,mixed> $catalog
     */
    public function createCatalogAPI(string $ownerName, array $catalog): void
    {
        $response = Utils::httpPost("http://" . $ownerName . ":dummy@localhost:5252/catalogs/projects", json_encode($catalog));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
    
    /**
     * @param array<int,mixed> $collection
     */
    public function createCollectionAPI(string $ownerName, array $collection): void
    {
        $response = Utils::httpPost("http://" . $ownerName . ":dummy@localhost:5252/collections", json_encode($collection));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    /**
     * @param array<int,mixed> $item
     */
    public function createItemAPI(string $ownerName, string $collectionName, array $item): void
    {
        $response = Utils::httpPost("http://" . $ownerName . ":dummy@localhost:5252/collections/" . $collectionName . "/items", json_encode($item));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }

    /**
     * @param array<int,mixed> $rights
     */
    public function adminAddRightsToUserAPI(string $userName, array $rights): void
    {
        $response = Utils::httpPost("http://admin:admin@localhost:5252/users/" . $userName . "/rights", json_encode($rights));
        $decoded = json_decode($response);
        $this->assertSame($decoded->status, "success", $response);
    }
    /**
     * @return array<string,string>
     */
    public static function user(string $username, string $email): array
    {
        return [
            "username" => $username,
            "firstname" => "John",
            "lastname" => "Doe",
            "email" => $email,
            "password" => "dummy",
        ];
    }
    /**
     * @return array<string,string>
     */
    public static function group(string $groupName): array
    {
        return [
            "name" => $groupName,
            "description" => "Any user can create a group.",
        ];
    }

    /**
     * @return array<string,string>
     * @param array<string> $visibility
     */
    public static function catalog(string $catalogName, array $visibility): array
    {
        $value = [
            "id" => $catalogName,
            "type" => "Catalog",
            "title" => $catalogName,
            "description" => "This is a simple catalog.",
            "stac_version" => "1.0.0",

        ];

        if ($visibility) {
            $value['visibility'] = $visibility;
        }
        return $value;
    }

    /**
     * @return array<string,string>
     * @param array<string> $visibility
     */
    public static function collection(string $collectionName, array $visibility): array
    {
        $value = [
            "id" => $collectionName,
            "type" => "Collection",
            "title" => $collectionName,
            "description" => "My beautiful collection.",
        ];

        if ($visibility) {
            $value['visibility'] = $visibility;
        }
        return $value;
    }

    /**
     * @return array<string,string>
     * @param array<string> $visibility
     */
    public static function item(string $itemName, array $visibility): array
    {
        $value = [
            "datetime" => "2024-06-21T16:27:00Z",
            "description" => "This is test item",
        ];
        if ($visibility) {
            $value['visibility'] = $visibility;
        }

        return [
            "id" => $itemName,
            "type" => "Feature",
            "properties" => $value,
            "geometry" => [
                "coordinates" => [
                    [
                        [-64.8,              32.3],
                        [-65.5,              18.3],
                        [-80.3,              25.2],
                        [-64.8,              32.3],
                    ],
                ],
                "type" => "Polygon",
            ],
        ];
    }
    /**
     * @return array<string,mixed>
     */
    public static function rights(): array
    {
        return [
            "createCollection" => false,
            "deleteAnyCollection" => false,
            "updateAnyCollection" => false,
            "createCatalog" => false,
            "createAnyCatalog" => false,
            "deleteAnyCatalog" => false,
            "updateAnyCatalog" => false,
            "createFeature" => false,
            "createAnyFeature" => false,
            "deleteAnyFeature" => false,
            "updateAnyFeature" => false,
        ];
    }
}
