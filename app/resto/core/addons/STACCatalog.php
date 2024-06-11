<?php
/*
 * Copyright 2024 Jérôme Gasperi
 *
 * Licensed under the Apache License, version 2.0 (the "License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

/**
 *  STAC Catalog - additional STAC capabilities
 */
class STACCatalog extends RestoAddOn
{

    private $prefix = 'catalog:';

    private $catalogsFunctions;

    /**
     * Constructor
     *
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     */
    public function __construct($context, $user)
    {
        parent::__construct($context, $user);
        $this->catalogsFunctions = new CatalogsFunctions($this->context->dbDriver);
    }

    /**
     * Add a catalog
     * 
     *    @OA\Post(
     *      path="/catalogs/*",
     *      summary="Add a STAC catalog",
     *      description="Add a STAC catalog",
     *      tags={"STAC"},
     *      @OA\RequestBody(
     *          description="A valid STAC Catalog",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Catalog")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The catalog is created",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Catalog created"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="400",
     *          description="Missing one of the mandatory input property",
     *          @OA\JsonContent(ref="#/components/schemas/BadRequestError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *createCatalog* rights can create a catalog",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *    )
     * 
     * @param array $params
     * @param array $body
     */
    public function addCatalog($params, $body)
    {
        
        if (!$this->user->hasRightsTo(RestoUser::CREATE_CATALOG)) {
            return RestoLogUtil::httpError(403);
        }

        /*
         * Check mandatory properties
         */
        /*if ( isset($body['stac_version']) ) {
            return RestoLogUtil::httpError(400, 'Missing mandatory catalog stac_version - should be set to ' . STAC::STAC_VERSION );
        }*/
        if ( !isset($body['id']) ) {
            return RestoLogUtil::httpError(400, 'Missing mandatory catalog id');
        }
        if ( !isset($body['description']) ) {
            return RestoLogUtil::httpError(400, 'Missing mandatory description');
        }
        if ( !isset($body['links']) ) {
            $body['links'] = array();
        }

        /*
         * Convert input catalog to resto:catalog
         * i.e. add rtype and hashtag properties and convert id to a path
         */
        $body['rtype'] = 'catalog';
        $body['hashtag'] = 'catalog' . RestoConstants::TAG_SEPARATOR . $body['id'];
        $body['id'] = $this->getIdPath($body);

        if ($this->catalogsFunctions->getCatalog($body['id']) !== null) {
            RestoLogUtil::httpError(409, 'Catalog ' . $body['id'] . ' already exists');
        }
        $baseUrl = $this->context->core['baseUrl'];
        return RestoLogUtil::success('Catalog added', $this->catalogsFunctions->storeCatalog($body, $this->user->profile['id'], $baseUrl, null, null));

    }

    /**
     * Update catalog
     * 
     *    @OA\Put(
     *      path="/catalogs/*",
     *      summary="Update catalog",
     *      description="Update catalog",
     *      tags={"STAC"},
     *      @OA\Parameter(
     *         name="catalogId",
     *         in="path",
     *         required=true,
     *         description="Catalog identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\RequestBody(
     *          description="Catalog fields to be update limited to title and description",
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Catalog")
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Catalog is updated",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Catalog updated"
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *updateCatalog* rights can update a catalog",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *    )
     * 
     * @param array $params
     * @param array $body
     */
    public function updateCatalog($params, $body)
    {

        // Get catalogs and childs
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => join('/', $params['segments'])
        ), true);
        
        if ( count($catalogs) === 0 ){
            RestoLogUtil::httpError(404);
        }


        // If user has not the right to update catalog then 403
        if ( !$this->user->hasRightsTo(RestoUser::UPDATE_CATALOG, array('catalog' => $catalogs[0])) ) {
            return RestoLogUtil::httpError(403);
        }

        $updatable = array('title', 'description', 'owner');
        for ($i = count($updatable); $i--;) {
            if ( isset($body[$updatable[$i]]) ) {
                $catalogs[0][$updatable[$i]] = $body[$updatable[$i]];
            }    
        }
        
        return RestoLogUtil::success('Catalog updated', $this->catalogsFunctions->updateCatalog($catalogs[0]));
    }

    /**
     * Delete catalog
     * 
     *    @OA\Delete(
     *      path="/catalogs/*",
     *      summary="Delete catalog",
     *      description="Delete catalog",
     *      tags={"STAC"},
     *      @OA\Parameter(
     *         name="catalogId",
     *         in="path",
     *         required=true,
     *         description="Catalog identifier",
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="Force catalog removal even if this catalog has child. In this case, catalogs childs are attached to the remove catalog parent",
     *         @OA\Schema(
     *             type="boolean"
     *         )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="Catalog deleted",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  description="Status is *success*"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string",
     *                  description="Message information"
     *              ),
     *              example={
     *                  "status": "success",
     *                  "message": "Catalog deleted",
     *                  "featuresUpdated": 345
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response="404",
     *          description="Not found",
     *          @OA\JsonContent(ref="#/components/schemas/NotFoundError")
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="Unauthorized",
     *          @OA\JsonContent(ref="#/components/schemas/UnauthorizedError")
     *      ),
     *      @OA\Response(
     *          response="403",
     *          description="Only user with *deleteCatalog* rights can delete a catalog",
     *          @OA\JsonContent(ref="#/components/schemas/ForbiddenError")
     *      ),
     *      security={
     *          {"basicAuth":{}, "bearerAuth":{}, "queryAuth":{}}
     *      }
     *    )
     * 
     * @param array $params
     * @param array $body
     */
    public function removeCatalog($params)
    {

        // Get catalogs and childs
        $catalogs = $this->catalogsFunctions->getCatalogs(array(
            'id' => join('/', $params['segments'])
        ), true);
        
        if ( count($catalogs) === 0 ){
            RestoLogUtil::httpError(404);
        }

        // If user has not the right to delete catalog then 403
        if ( !$this->user->hasRightsTo(RestoUser::DELETE_CATALOG, array('catalog' => $catalogs[0])) ) {
            return RestoLogUtil::httpError(403);
        }
        
        // If catalog has childs it cannot be removed
        for ($i = 1, $ii = count($catalogs); $i < $ii; $i--) {
            if (isset($params['force']) && filter_var($params['force'], FILTER_VALIDATE_BOOLEAN) === true) {
                return RestoLogUtil::httpError(400, 'TODO - force removal of non empty catalog is not implemented');
            }
            else {
                return RestoLogUtil::httpError(400, 'The catalog cannot be deleted because it has ' . (count($catalogs) - 1) . ' childs');
            }    
        }
        
        return RestoLogUtil::success('Catalog deleted', $this->catalogsFunctions->removeCatalog($catalogs[0]['id']));

    }
    
    /**
     * Return path identifier from input catlaog
     * 
     * @param array $catalog
     * @return string
     */
    private function getIdPath($catalog)
    {

        $parentId = '';

        // Retrieve parent if any
        for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++ ) {
            if ( isset($catalog['links'][$i]['rel']) &&$catalog['links'][$i]['rel'] === 'parent' ) {
                $theoricalUrl = $this->context->core['baseUrl'] . RestoRouter::ROUTE_TO_CATALOGS; 
                $exploded = explode($theoricalUrl, $catalog['links'][$i]['href']);
                if (count($exploded) !== 2) {
                    return RestoLogUtil::httpError(400, 'Parent link is set but it\'s url is invalid - should starts with ' . $theoricalUrl);
                }
                $parentId = str_starts_with($exploded[1], '/') ? substr($exploded[1], 1) : $exploded[1];
                break;
            }
        }

        return ($parentId === '' ? '' : $parentId . '/') . $catalog['id'];

    }


}
