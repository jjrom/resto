<?php

/*
 * RESTo
 * 
 * RESTo - REstful Semantic search Tool for geOspatial 
 * 
 * Copyright 2014 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
 * 
 * 
 * This software is governed by the CeCILL-B license under French law and
 * abiding by the rules of distribution of free software.  You can  use,
 * modify and/ or redistribute the software under the terms of the CeCILL-B
 * license as circulated by CEA, CNRS and INRIA at the following URL
 * "http://www.cecill.info".
 *
 * As a counterpart to the access to the source code and  rights to copy,
 * modify and redistribute granted by the license, users are provided only
 * with a limited warranty  and the software's author,  the holder of the
 * economic rights,  and the successive licensors  have only  limited
 * liability.
 *
 * In this respect, the user's attention is drawn to the risks associated
 * with loading,  using,  modifying and/or developing or reproducing the
 * software by the user in light of its specific status of free software,
 * that may mean  that it is complicated to manipulate,  and  that  also
 * therefore means  that it is reserved for developers  and  experienced
 * professionals having in-depth computer knowledge. Users are therefore
 * encouraged to load and test the software's suitability as regards their
 * requirements in conditions enabling the security of their systems and/or
 * data to be ensured and,  more generally, to use and operate it in the
 * same conditions as regards security.
 *
 * The fact that you are presently reading this means that you have had
 * knowledge of the CeCILL-B license and that you accept its terms.
 * 
 */

/**
 * RESTo REST router for DELETE requests
 */
class RestoRouteDELETE extends RestoRoute {
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        parent::__construct($context, $user);
    }
   
    /**
     * 
     * Process HTTP DELETE request
     * 
     *    collections/{collection}                      |  Delete {collection}
     *    collections/{collection}/{feature}            |  Delete {feature}
     *    
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     *    
     * @param array $segments
     */
    public function route($segments) {
        switch($segments[0]) {
            case 'collections':
                return $this->DELETE_collections($segments);
            case 'users':
                return $this->DELETE_users($segments);
            default:
                return $this->processModuleRoute($segments);
        }
    }
    
    /**
     * 
     * Process HTTP DELETE request on collections
     * 
     *    collections/{collection}                      |  Delete {collection}
     *    collections/{collection}/{feature}            |  Delete {feature}
     * 
     * @param array $segments
     */
    private function DELETE_collections($segments) {
        
        /*
         * {collection} is mandatory and no modifier is allowed
         */
        if (!isset($segments[1]) || isset($segments[3])) {
            $this->httpError(404, null, __METHOD__);
        }
        
        $collection = new RestoCollection($segments[1], $this->context, $this->user, array('autoload' => true));
        if (isset($segments[2])) {
            $feature = new RestoFeature($segments[2], $this->context, $this->user, $collection);
        }
        
        /*
         * Check credentials
         */
        if (!$this->user->canDelete($collection->name, $feature->identifier)) {
            $this->httpError(403, null, __METHOD__);
        }

        /*
         * collections/{collection}
         */
        if (!isset($feature)) {
            $collection->removeFromStore();
            $this->storeQuery('remove', $collection->name, null);
            return $this->success('Collection ' . $collection->name . ' deleted');
        }
        /*
         * collections/{collection}/{feature}
         */
        else {
            $feature->removeFromStore();
            $this->storeQuery('remove', $collection->name, $feature->identifier);
            return $this->success('Feature ' . $feature->identifier . ' deleted', array(
                'featureIdentifier' => $feature->identifier
            ));
        }
        
    }
    
    
    /**
     * 
     * Process HTTP DELETE request on users
     * 
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     * 
     * @param array $segments
     */
    private function DELETE_users($segments) {
        
        /*
         * Mandatory {itemid}
         */
        if (!isset($segments[3])) {
            $this->httpError(404, null, __METHOD__);
        }
        
        if ($segments[1] === 'cart') {
            return $this->DELETE_userCart($segments[1], $segments[3]);
        }
        else {
            $this->httpError(404, null, __METHOD__);
        }
        
    }
    
    
    /**
     * 
     * Process HTTP DELETE request on users cart
     * 
     *    users/{userid}/cart/{itemid}                  |  Remove {itemid} from {userid} cart
     * 
     * @param string $emailOrId
     * @param string $itemId
     */
    private function DELETE_userCart($emailOrId, $itemId) {
        
        /*
         * Cart can only be modified by its owner or by admin
         */
        $user = $this->user;
        $userid = $this->userid($emailOrId);
        if ($user->profile['userid'] !== $userid) {
            if ($user->profile['groupname'] !== 'admin') {
                $this->httpError(403, null, __METHOD__);
            }
            else {
                $user = new RestoUser($this->context->dbDriver->getUserProfile($userid), $this->context);
            }
        }
        
        /*
         * users/{userid}/cart/{itemid} 
         */
        if ($user->removeFromCart($itemId, true)) {
            return $this->success('Item removed from cart', array(
                'itemid' => $itemId
            ));
        }
        else {
            return $this->error('Item cannot be removed', array(
                'itemid' => $itemId
            ));
        }
    }
}