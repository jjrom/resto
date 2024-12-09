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

/**
 * Simple query analyzer
 */
class RestoQueryAnalyzer
{
    /*
     * RestoContext
     */
    private $context;

    /*
     * RestoUser
     */
    private $user;

    /*
     * Reference to Gazetteer add-on
     */
    private $gazetteer;

    /**
     * Constructor
     *
     * @param RestoContext $context
     * @param RestoUser $user
     */
    public function __construct($context, $user)
    {
        $this->context = $context;
        $this->user = $user;
        $gazetteerClassName = 'Gazetteer';
        if (isset($this->context->addons[$gazetteerClassName])) {
            $this->gazetteer = new $gazetteerClassName($this->context, $this->user);
        }
    }

    /**
     * Query analyzer process searchTerms and modify query parameters accordingly
     *
     * @param array $params
     * @param RestoModel $model
     * @return array
     */
    public function analyze($params, $model)
    {
        /*
         * Store original params
         */
        $inputFilters = $params;
        
        /*
         * [STAC][WFS] datetime is converted into start/end
         */
        if (isset($params['resto:datetime'])) {
            $this->splitDatetime($params['resto:datetime'], $params);
            unset($params['resto:datetime']);
        }

        /*
         * Check dates
         */
        if (isset($params['time:start']) && isset($params['time:end']) && $params['time:start'] > $params['time:end']) {
            RestoLogUtil::httpError(400, 'Invalid dates range - start cannot be greater than end');
        }

        /*
         * Details analysis
         */
        $details = array(
            'language' => $this->context->lang,
            'What' => array(),
            'When' => array(),
            'Where' => array(),
            'Errors' => array(),
            'Explained' => array()
        );

        $hashTodiscard = null;

        /*
         * Query Analyzer on searchTerms
         */
        $cleanSearchTerms = array();

        if ( isset($params['searchTerms']) ) {

            $searchTerms = explode(' ', $params['searchTerms']);
            for ($i = 0, $ii = count($searchTerms); $i < $ii; $i++) {
                $keyValue = explode(RestoConstants::CONCEPT_SEPARATOR, $searchTerms[$i]);
                if (count($keyValue) === 1) {
                    $cleanSearchTerms[] = $keyValue[0];
                    continue;
                }
                /**
                 * Special case for toponym search
                 */
                if ($keyValue[0] === $model->searchFilters['geo:name']['osKey'] && !isset($params['geo:name']) && isset($this->gazetteer)) {
                    $params['geo:name'] = $keyValue[1];
                    $this->extractToponym($params, $details, $hashTodiscard);
                }
                else {
                    $cleanFilter = $model->getFiltersFromQuery(array(
                        $keyValue[0] => $keyValue[1]
                    ));
                    if ( isset($cleanFilter) ) {
                        foreach (array_keys($cleanFilter) as $key) {
                            if ($key === 'searchTerms') {
                                $cleanSearchTerms[] = $cleanFilter[$key];
                            }
                            else {
                                $params[$key] = $cleanFilter[$key];
                            }
                            
                        }
                    }
                    
                }
                
            }

        }

        $details['What'] = array(
            'searchTerms' => $this->appendSkos($cleanSearchTerms)
        );

        /*
         * Not understood
         */
        if (isset($params['searchTerms']) && empty($details['What']) && empty($details['When']) && empty($details['Where'])) {
            $details['appliedFilters'] = $this->addOperation($params, $model->searchFilters);
            return array(
                'inputFilters' => $inputFilters,
                'notUnderstood' => true,
                'details' => $details
            );
        }

        /*
         * Where, When, What
         */
        $details['appliedFilters'] = $this->addOperation($this->setWhereFilters($details['Where'], $this->setWhenFilters($details['When'], $this->setWhatFilters($details['What'], $params)), $hashTodiscard), $model->searchFilters);
        return array(
            'inputFilters' => $inputFilters,
            'details' => $details
        );

    }

    /**
     * Parse input searchTerms array and replace individual searchTerm with skos related
     * searchTerm.
     *
     * @param array $searchTerms
     * @return array
     */
    private function appendSkos($searchTerms)
    {

        $skosClassName = 'SKOS';

        for ($i = 0, $ii = count($searchTerms); $i < $ii; $i++) {
            /*
             * If resto-addon-sosa add-on exists, check for searchTerm last character:
             *  - if ends with "!" character, then search for broader search terms
             *  - if ends with "*" character, then search for narrower search terms
             *  - if ends with "~" character, then search for related search terms
             */
            $lastCharacter = substr($searchTerms[$i], -1);
            if (in_array($lastCharacter, array('!', '*', '~')) && class_exists('SKOS')) {
                $searchTerms[$i] = substr($searchTerms[$i], 0, -1);
                $relations = array(
                    '!' => $skosClassName::$SKOS_BROADER,
                    '*' => $skosClassName::$SKOS_NARROWER,
                    '~' => $skosClassName::$SKOS_RELATED
                );
                // Don't forget to trim # prefix
                $relations = (new $skosClassName($this->context, $this->user))->retrieveRecursiveRelations(substr($searchTerms[$i], 1), $relations[$lastCharacter]);
                if (count($relations) > 0) {
                    $searchTerms[$i] = $searchTerms[$i] . '|' . join('|', $relations);
                }
            }
        }
        
        return $searchTerms;
    }

    /**
     * Extract toponym from gazetteer
     *
     * @param array $params
     * @param array $details
     * @param array $hashToDiscard
     */
    private function extractToponym($params, &$details, &$hashToDiscard)
    {
        $foundLocation = null;

        /*
         * Search on toponym name
         */
        if (isset($params['geo:name']) && ! isset($params['geo:lon']) && ! isset($params['geo:geometry'])) {

            
            /*
             * Search on toponym identifier i.e. geo:name starts with geouid
             */
            if (strpos($params['geo:name'], 'geouid:') === 0) {
                $location = $this->gazetteer->getToponym(array(
                    'id' => substr($params['geo:name'], strlen('geouid:')),
                    'index' => $this->context->core['planet']
                ));
                if (isset($location['_source'])) {
                    $foundLocation = array_merge(array('_id' => $location['_id']), $location['_source']);
                    if (isset($foundLocation['hash'])) {
                        $hashToDiscard = $foundLocation['hash'];
                    }
                    if (isset($foundLocation['wkt'])) {
                        $params['geo:geometry'] = $foundLocation['wkt'];
                    } else {
                        $coordinates = explode(',', $foundLocation['coordinates']);
                        $params['geo:geometry'] = 'POINT(' . trim($coordinates[1]) . ' ' . trim($coordinates[0]) . ')';
                    }
                }
            }
            else {
                $locations = $this->gazetteer->search(array(
                    'q' => $params['geo:name'],
                    'index' => $this->context->core['planet']
                ));
                if (isset($locations['hits']) && count($locations['hits']['hits']) > 0) {
                    $foundLocation = array_merge(array('_id' => $locations['hits']['hits'][0]['_id']), $locations['hits']['hits'][0]['_source']);
                    if (isset($foundLocation['wkt'])) {
                        $params['geo:geometry'] = $foundLocation['wkt'];
                    } elseif (isset($foundLocation['coordinates'])) {
                        $coordinates = explode(',', $foundLocation['coordinates']);
                        $params['geo:lon'] = floatval(trim($coordinates[1]));
                        $params['geo:lat'] = floatval(trim($coordinates[0]));
                    }
                }
            }
        
        }

        if (isset($foundLocation)) {
            $details['Where'] = array_merge(array($foundLocation), $details['Where']);
            $details['Explained'] = array_merge(array(
                'processor' => 'WhereProcessor::processIn',
                'word' => $foundLocation['name']
            ), $details['Explained']);
        }
    }


    /**
     * Set location filters from query analysis
     *
     * @param array $where
     * @param array $params
     * @param string $hashTodiscard
     */
    private function setWhereFilters($where, $params, $hashTodiscard = null)
    {
        for ($i = count($where); $i--;) {
            /*
             * Geometry
             */
            if (isset($where[$i]['wkt'])) {
                $params['geo:geometry'] = $where[$i]['wkt'];
            }
            /*
             * Only one toponym is supported (the last one)
             */
            elseif (isset($where[$i]['coordinates'])) {
                $coordinates = array_map('trim', explode(',', $where[$i]['coordinates']));
                $params['geo:lon'] = floatval($coordinates[1]);
                $params['geo:lat'] = floatval($coordinates[0]);
            }
            /*
             * Searching for hash/keywords is faster than geometry
             */
            elseif (isset($where[$i]['searchTerms'])) {
                $params['searchTerms'][] = $where[$i]['searchTerms'];
            } elseif (isset($where[$i]['geouid'])) {
                if (!isset($hashTodiscard) || $where[$i]['hash'] !== $hashTodiscard) {
                    $params['searchTerms'][] = 'geouid:' . $where[$i]['geonameid'];
                }
            }
        }
        if (count($params['searchTerms']) > 0) {
            $params['searchTerms'] = join(' ', $params['searchTerms']);
        } else {
            unset($params['searchTerms']);
        }
        return $params;
    }

    /**
     * Set what filters from query analysis
     *
     * @param array $what
     * @param array $params
     */
    private function setWhatFilters($what, $params)
    {
        $params['searchTerms'] = array();
        foreach ($what as $key => $value) {
            if ($key === 'searchTerms') {
                for ($i = count($value); $i--;) {
                    $params['searchTerms'][] = $value[$i];
                }
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * Set when filters from query analysis
     *
     * @param array $when
     * @param array $params
     */
    private function setWhenFilters($when, $params)
    {
        foreach ($when as $key => $value) {
            /*
             * times is an array of time:start/time:end pairs
             * [TODO] : Currently only one pair is supported
             * [UDPATE] : Are you sure ?
             */
            if ($key === 'times') {
                $params = array_merge($params, $this->timesToOpenSearch($value));
            } else {
                $params['searchTerms'][] = $key . RestoConstants::ITAG_SEPARATOR . $value;
            }
        }
        return $params;
    }

    /**
     *
     * @param array $times
     */
    private function timesToOpenSearch($times)
    {
        $params = array();
        for ($i = 0, $ii = count($times); $i < $ii; $i++) {
            foreach ($times[$i] as $key => $value) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    /**
     * Convert datetime to start/end filters
     *
     * @param string $datetime
     * @param array $params
     */
    private function splitDatetime($datetime, &$params)
    {
        $dates = explode('/', trim($datetime));

        /*
         * Double-open-ended queries are not allowed in STAC API
         */
        if (count($dates) > 2) {
            RestoLogUtil::httpError(400, 'Invalid dates range - too many /');
        } elseif (count($dates) == 2 && in_array($dates[0], array('', '..')) && in_array($dates[1], array('', '..'))) {
            RestoLogUtil::httpError(400, 'Invalid dates range - double-open-ended queries are not allowed in STAC API /');
        }

        $model = new DefaultModel();

        if (isset($dates[0]) && !in_array($dates[0], array('', '..'))) {
            $filterKey = $model->getFilterName('start');
            $params[$filterKey] = preg_replace('/<.+?>/', '', $dates[0]);
            $model->validateFilter($filterKey, $params[$filterKey]);
        }
        if (isset($dates[1]) && !in_array($dates[1], array('', '..'))) {
            $filterKey = $model->getFilterName('end');
            $params[$filterKey] = preg_replace('/<.+?>/', '', $dates[1]);
            $model->validateFilter($filterKey, $params[$filterKey]);
        }
    }

    /**
     * Return parameters with value and operation
     *
     * @param array $params
     * @param array searchFilters
     * @return array
     */
    private function addOperation($params, $searchFilters)
    {
        $paramsWithOperation = array();
        foreach ($params as $key => $value) {
            // Only add operation if not already there
            if (is_string($value) || ! isset($value['operation'])) {
                $paramsWithOperation[$key] = array(
                    'value' => $value,
                    'operation' => $searchFilters[$key]['operation'] ?? null
                );
            } else {
                $paramsWithOperation[$key] = $value;
            }
        }
        return $paramsWithOperation;
    }
}
