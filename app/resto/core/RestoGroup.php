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


class RestoGroup
{
    public static function createItemRight($groupName){
        return "create_". $groupName."_item";
    }

     public static function createCatalogRight($groupName){
        return "create_". $groupName."_catalog";    }

     public static function createCollectionRight($groupName){
        return "create_". $groupName."_collection";
    }

        public static function updateItemRight($groupName){
        return "update_". $groupName."_item";
    }

     public static function updateCatalogRight($groupName){
        return "update_". $groupName."_catalog";    }

     public static function updateCollectionRight($groupName){
        return "update_". $groupName."_collection";
    }

          public static function deleteItemRight($groupName){
        return "delete_". $groupName."_item";
    }

     public static function deleteCatalogRight($groupName){
        return "delete_". $groupName."_catalog";    }

     public static function deleteCollectionRight($groupName){
        return "delete_". $groupName."_collection";
    }
    public static function getAllRights($groupName){
        return array(RestoGroup::createItemRight($groupName), 
                     RestoGroup::createCatalogRight($groupName),
                     RestoGroup::createCollectionRight($groupName),
                     RestoGroup::updateItemRight($groupName),
                     RestoGroup::updateCatalogRight($groupName),
                     RestoGroup::updateCollectionRight($groupName),
                     RestoGroup::deleteItemRight($groupName),
                     RestoGroup::deleteCatalogRight($groupName),
                     RestoGroup::deleteCollectionRight($groupName)
        );
    }
    
}