<?php
/*
 * Copyright 2018 Jérôme Gasperi
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

/*
 * This class is use to keep reference of objects retrieve in database
 * to avoid multiple database calls
 */
class RestoKeeper
{
    
    private $restoCollectionsObject = null;
    private $restoCollectionObjects = array();

    /**
     * Constructor
     *
     * @throws Exception
     */
    public function __construct($context)
    { 
        $this->context = $context;
    }

    /**
     * Return RestoCollection object
     * 
     * @param string $collectionId
     * @param RestoUser $user
     * @return RestoCollection
     */
    public function getRestoCollection($collectionId, $user)
    {
        if ( !isset($this->restoCollectionObjects[$collectionId]) ) {
            $this->restoCollectionObjects[$collectionId] = new RestoCollection($collectionId, $this->context, $user);
        }
        
        return $this->restoCollectionObjects[$collectionId];
    }

    /**
     * Return RestoCollections object
     * 
     * @param RestoUser $user
     * @return RestoCollections
     */
    public function getRestoCollections($user)
    {
        if ( !isset($this->restoCollectionsObject) ) {
            $this->restoCollectionsObject = new RestoCollections($this->context, $user);
        }
        return $this->restoCollectionsObject;
    }

}
