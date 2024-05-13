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

    /**
     * Catalog prefix is systematically added internally
     * to catalog identifier
     */
    private $prefix = 'catalog:';

    /**
     * Constructor
     *
     * @param RestoContext $context : RESTo context
     * @param RestoUser $user : RESTo user
     */
    public function __construct($context, $user)
    {
        parent::__construct($context, $user);
    }

    /**
     * Add a catalog as a facet entry
     * 
     *    @OA\Post(
     *      path="/catalogs/*",
     *      summary="Add a STAC catalog",
     *      description="Add a STAC catalog as a facet entry",
     *      tags={"STAC"},
     *      @OA\Property(
     *          property="pid",
     *          type="string",
     *          description="Catalog parent id. Must be provided if input catalog has a rel=parent in links referencing a local path in href instead of an absolute url. In the latter case, the parent identifier is retrieved automatically by resolving the url."
     *      ),
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
        if ( !is_array($body['links']) ) {
            return RestoLogUtil::httpError(400, 'Invalid links array');
        }

        return $this->storeCatalogAsFacet($body, $params['pid'] ?? null);

    }

    /**
     * Update catalog as a facet entry
     * 
     *    @OA\Put(
     *      path="/catalogs/{catalogId}",
     *      summary="Update catalog",
     *      description="Update catalog as a facet entry",
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

        $facetId = $params['segments'][count($params['segments']) - 1 ];
        $facets = (new FacetsFunctions($this->context->dbDriver))->getFacets(array('id' => $facetId));
        
        if ( empty($facets) ) {
            return RestoLogUtil::httpError(404);
        }

        // If user has not the right to update ALL facets then 403
        for ($i = count($facets); $i--;) {
            if ( !$this->user->hasRightsTo(RestoUser::UPDATE_CATALOG, array('catalog' => $facets[$i])) ) {
                return RestoLogUtil::httpError(403);
            }
        }

        // Only title and description can be updated
        $newFacet = array(
            'id' => $facetId
        );

        $updatable = array('title', 'description', 'owner');
        for ($i = count($updatable); $i--;) {
            if ( isset($body[$updatable[$i]]) ) {
                $newFacet[$updatable[$i] === 'title' ? 'value' : $updatable[$i]] = $body[$updatable[$i]];
            }    
        }

        return RestoLogUtil::success('Catalog updated', (new FacetsFunctions($this->context->dbDriver))->updateFacet($newFacet));
        
    }

    /**
     * Delete catalog as a facet entry
     * 
     *    @OA\Delete(
     *      path="/catalogs/{catalogId}",
     *      summary="Delete catalog",
     *      description="Delete catalog as a facet entry - update feature keywords accordingly",
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

        $facetId = $params['segments'][count($params['segments']) - 1 ];
        $facets = (new FacetsFunctions($this->context->dbDriver))->getFacets(array('id' => $facetId));
        
        if ( empty($facets) ) {
            return RestoLogUtil::httpError(404);
        }

        // If user has not the right to delete ALL facets then 403
        for ($i = count($facets); $i--;) {
            if ( !$this->user->hasRightsTo(RestoUser::DELETE_CATALOG, array('catalog' => $facets[$i])) ) {
                return RestoLogUtil::httpError(403);
            }
        }

        // If catalog has childs it cannot be removed
        $childs = (new FacetsFunctions($this->context->dbDriver))->getFacets(array('pid' => $facetId));
        if ( !empty($childs) ) {
            return RestoLogUtil::httpError(400, 'The catalog cannot be deleted because it has ' . count($childs) . ' childs');
        }
        
        return RestoLogUtil::success('Catalog deleted', (new FacetsFunctions($this->context->dbDriver))->removeFacet($facetId));
        
    }

    /**
     * Check if catalog exists
     *
     * @param string $catalogId
     * @return boolean
     * @throws Exception
     */
    private function catalogExists($catalogId, $parentId, $collectionId)
    {

        // Facet primary key is (is, pid, collection)    
        $results = $this->context->dbDriver->fetch($this->context->dbDriver->pQuery('SELECT id FROM ' . $this->context->dbDriver->targetSchema . '.facet WHERE id=$1 AND pid=$2 AND collection=$3', array(
            $catalogId,
            $parentId,
            $collectionId
        )));

        return !empty($results);
    }

    /**
     * 
     * Store catalog as facet
     * 
     * @param array $catalog
     * @param string $parentId
     * @return array
     * 
     */
    private function storeCatalogAsFacet($catalog, $parentId)
    {

        if ( !isset($parentId) ) {
            $parentId = $this->parentIdFromLinks($catalog['links'] ?? array());
        }

        $isLeaf = true;

        // Catalog is a leaf if it has no child
        for ($i = 0, $ii = count($catalog['links']); $i < $ii; $i++) {
            if (isset($catalog['links'][$i]['rel']) && $catalog['links'][$i]['rel'] === 'child') {
                $isLeaf = false;
                break;
            }
        }
        
        /*
         * Remove "catalog:" prefix from id
         */
        if ( str_starts_with($catalog['id'], $this->prefix) ) {
            $catalog['id'] = substr($catalog['id'], strlen($this->prefix));
        }

        $parentId = isset($parentId) ? (str_starts_with($parentId, $this->prefix) ? $parentId : $this->prefix . $parentId) : 'root';

        /*
         * Catalog already exist
         */
        if ( $this->catalogExists($this->prefix . $catalog['id'], $parentId, '*') ) {
            return RestoLogUtil::httpError(409, 'Catalog ' . $catalog['id'] . ' already exist');
        }

        try {
            (new FacetsFunctions($this->context->dbDriver))->storeFacets(array(
                array(
                    'id' => $this->prefix . $catalog['id'],
                    // Prefix parentId with $prefix if needed
                    'parentId' => $parentId,
                    'value' => $catalog['title'] ?? $catalog['id'],
                    'type' => substr($this->prefix, 0, -1),
                    'description' => $catalog['description'],
                    'isLeaf' => $isLeaf,
                    'counter' => 0
                )
            ), $this->user->profile['id']);
        } catch (Exception $e) {
            return RestoLogUtil::httpError(500, 'Cannot insert catalog ' . $catalog['id']);
        } 

        return RestoLogUtil::success('Catalog ' . $catalog['id'] . ' created' . (isset($parentId) ? ' with parent ' . $parentId : ''));

    }

    /**
     * Return parent identifier from an array links
     * 
     * @param array $links
     * @return string
     */
    private function parentIdFromLinks($links)
    {

        $parentId = null;

        // Retrieve parent if any
        for ($i = 0, $ii = count($links); $i < $ii; $i++ ) {

            if ( isset($links[$i]['rel']) && $links[$i]['rel'] === 'parent' ) {

                // [IMPORTANT] Can only process http(s) urls not local file
                if ( strpos(strtolower($links[$i]['href']), 'http') !== 0 ) {
                    return RestoLogUtil::httpError(400, 'Parent href must be an url i.e. start with http(s). Correct or use additionnal parentId query params otherwise');
                }

                try {
                    $curl = new Curly();
                    $parent = json_decode($curl->get($links[$i]['href']), true);
                    $curl->close();
                } catch (Exception $e) {
                    $curl->close();
                    return RestoLogUtil::httpError(400, 'Cannot process catalog parent with href ' - $links[$i]['href']);
                }

                /*
                 * Return empty result
                 */
                if (isset($parent) && isset($parent['id'])) {
                    $parentId = $parent['id'];
                }

                break;

            }
        }

        return $parentId;

    }

}
