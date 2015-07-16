<?php
/*
 * Copyright 2014 Jérôme Gasperi
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
 * RESTo REST router
 * 
 * See list of routes per HTTP verb in Routes/*.php
 * 
 */
abstract class RestoRoute {
    
    /*
     * RestoContext
     */
    protected $context;
    
    /*
     * RestoUser
     */
    protected $user;
    
    /**
     * Constructor
     */
    public function __construct($context, $user) {
        $this->context = $context;
        $this->user = $user;
    }
   
    /**
     * Route to resource
     * 
     * @param array $segments : path as route segments
     */
    abstract public function route($segments);
    
    /**
     * Launch module run() function if exist otherwise returns 404 Not Found
     * 
     * @param array $segments - path (i.e. a/b/c/d) exploded as an array (i.e. array('a', 'b', 'c', 'd')
     * @param array $data - data (POST or PUT)
     */
    protected function processModuleRoute($segments, $data = array()) {
        
        $module = null;
        
        foreach (array_keys($this->context->modules) as $moduleName) {
            
            if (isset($this->context->modules[$moduleName]['route'])) {
                
                $moduleSegments = explode('/', $this->context->modules[$moduleName]['route']);
                $routeIsTheSame = true;
                $count = 0;
                for ($i = 0, $l = count($moduleSegments); $i < $l; $i++) {
                    $count++;
                    if (!isset($segments[$i]) || $moduleSegments[$i] !== $segments[$i]) {
                        $routeIsTheSame = false;
                        break;
                    } 
                }
                if ($routeIsTheSame) {
                    $module = RestoUtil::instantiate($moduleName, array($this->context, $this->user));
                    for ($i = $count; $i--;) {
                        array_shift($segments);
                    }
                    return $module->run($segments, $data);
                }
            }
        }
        if (!isset($module)) {
            RestoLogUtil::httpError(404);
        }
    }

}
