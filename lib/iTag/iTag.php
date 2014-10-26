<?php

/*
 * iTag
 *
 * iTag - Semantic enhancement of Earth Observation data
 *
 * Copyright 2013 Jérôme Gasperi <https://github.com/jjrom>
 * 
 * jerome[dot]gasperi[at]gmail[dot]com
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

class iTag {

    /*
     * iTag version
     */
    const version = '2.0';
    
    /*
     * Database handler
     */
    private $dbh;
    
    private $schema = 'datasources';
    private $gazetteerSchema = 'gazetteer';
    
    /**
     * Constructor
     * 
     * @param array $options : database configuration array 
     */
    public function __construct($options) {
        
        if (isset($options['dbh'])) {
            $this->dbh = $options['dbh'];
        }
        else {
            try {
                $this->dbh = pg_connect(join(' ', array(
                    'host=' . (isset($options['host']) ? $options['host'] : 'localhost'),
                    'port=' . (isset($options['port']) ? $options['port'] : '5432'),
                    'dbname=' . (isset($options['dbname']) ? $options['dbname'] : 'itag'),
                    'user=' . (isset($options['user']) ? $options['user'] : 'itag'),
                    'password=' . (isset($options['password']) ? $options['password'] : 'itag'),
                )));
                if (!$this->dbh) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                throw new Exception(__METHOD__ . ' - Database connection error', 500);
            }
        }
    }
    
    /**
     * Tag a polygon
     * 
     * Structure of options :
     *  
     *      array(
     *          'countries' => true|false,
     *          'continents' => true|false,
     *          'cities' => main|all|null,
     *          'geophysical' => true|false,
     *          'population' => true|false,
     *          'landcover' => true|false,
     *          'regions' => true|false,
     *          'french' => true|false,
     *          'hierarchical' => true|false
     *          'ordered' => true|false
     *      );
     * 
     * @param string $footprint
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($footprint, $options = array()) {
        
        // Initialize Feature
        $feature = array(
            'type' => 'Feature',
            'geometry' => $this->wktPolygon2GeoJSONGeometry($footprint),
            'properties' => array()
        );

        if ($options['french']) {
            $feature['properties']['political'] = $this->getFrenchPolitical($footprint, $options);
        } elseif ($options['countries'] || $options['cities'] || $options['regions'] || $options['continents']) {
            $feature['properties']['political'] = $this->getPolitical($footprint, $options);
        }

        if ($options['geophysical']) {
            $feature['properties']['geophysical'] = $this->getGeophysical($footprint);
        }

        if ($options['landcover']) {
            $feature['properties']['landCover'] = $this->getLandCover($footprint, $options);
        }

        /*
          if ($options['population'] && GPW2PGSQL_URL) {
          $gpwResult = getRemoteData(GPW2PGSQL_URL . urlencode($footprint), null);
          if ($gpwResult !== "") {
          $feature['properties']['population'] = trim($gpwResult);
          }
          }
         */

        return $feature;
    }

    /**
     * 
     * Compute land cover from input WKT footprint
     * 
     * @param string $footprint
     * @param array $options
     * 
     */
    private function getLandCover($footprint, $options) {

        /*
         * Do not process if $footprint is more than 2x2 degrees
         */
        $bbox = $this->bbox($footprint);
        if (abs($bbox['ulx'] - $bbox['lrx']) > 2 || abs($bbox['uly'] - $bbox['lry']) > 2) {
            return null;
        }

        // Crop data
        $geom = "ST_GeomFromText('" . $footprint . "', 4326)";
        $query = "SELECT dn as dn, st_area($geom) as totalarea, st_area(st_intersection(wkb_geometry, $geom)) as area FROM " . $this->schema . ".landcover WHERE st_intersects(wkb_geometry, $geom)";
        $results = pg_query($this->dbh, $query);
        if (!isset($results)) {
            $this->error();
        }

        // Store results in $out array
        $out = array();
        for ($i = 1; $i <= 22; $i++) {
            $out[$i] = 0;
        }
        while ($product = pg_fetch_assoc($results)) {
            if (isset($out[$product['dn']])) {
                $out[$product['dn']] += $product['area'];
            }
            $totalarea = $product['totalarea'];
        }

        // Compute parent classes
        $parent = array();
        $parent[100] = $out[22];
        $parent[200] = $out[15] + $out[16] + $out[17] + $out[18];
        $parent[310] = $out[1] + $out[2] + $out[3] + $out[4] + $out[5] + $out[6];
        $parent[320] = $out[9] + $out[11] + $out[12] + $out[13];
        $parent[330] = $out[10] + $out[14] + $out[19];
        $parent[335] = $out[21];
        $parent[400] = $out[7] + $out[8];
        $parent[500] = $out[20];
        
        $linkage = array(
            '1' => 310,
            '2' => 310,
            '3' => 310,
            '4' => 310,
            '5' => 310,
            '6' => 310,
            '7' => 400,
            '8' => 400,
            '9' => 320,
            '10' => 330,
            '11' => 320,
            '12' => 320,
            '13' => 320,
            '14' => 330,
            '15' => 200,
            '16' => 200,
            '17' => 200,
            '18' => 200,
            '19' => 330,
            '20' => 500,
            '21' => 335,
            '22' => 100
        );
        
        // Get the 3 main landuse
        arsort($parent);
        $landUse = array();
        $count = 0;
        foreach ($parent as $key => $val) {
            $count++;
            $pcover = $this->percentage($val, $totalarea);
            if ($val !== 0 && $pcover > 20) {
                if ($options['ordered']) {
                    array_push($landUse, array('name' => $this->getGLCClassName($key), 'pcover' => $pcover));
                } else {
                    array_push($landUse, $this->getGLCClassName($key));
                }
            }
            if ($count > 2) {
                break;
            }
        }

        /*
         * Add feature
         */
        $result = array(
            'area' => $totalarea,
            'landUse' => $landUse,
            'landUseDetails' => array()
        );

        foreach ($out as $key => $val) {
            if ($val !== 0) {
                array_push($result['landUseDetails'], array('name' => $this->getGLCClassName($key), 'parent' => $this->getGLCClassName($linkage[$key]), 'code' => $key, 'pcover' => $this->percentage($val, $totalarea)));
            }
        }

        return $result;
    }

    /**
     *
     * Returns GeoJSON geometry from a WKT POLYGON
     *
     * Example of WKT POLYGON :
     *     POLYGON((-180.0044642857 89.9955356663,-180.0044642857 87.9955356727,-178.0044642921 87.9955356727,-178.0044642921 89.9955356663,-180.0044642857 89.9955356663))
     *
     * @param <string> $wkt : WKT
     *
     */
    private function wktPolygon2GeoJSONGeometry($wkt) {
        $rep = array("(", ")", "multi", "polygon");
        $pairs = preg_split('/,/', str_replace($rep, "", strtolower($wkt)));
        $linestring = array();
        for ($i = 0; $i < count($pairs); $i++) {
            $coords = preg_split('/ /', trim($pairs[$i]));
            $x = floatval($coords[0]);
            $y = floatval($coords[1]);
            array_push($linestring, array($x, $y));
        }

        return array(
            'type' => "Polygon",
            'coordinates' => array($linestring)
        );
    }

    /**
     * 
     * Compute intersected politicals information (i.e. continent, countries, cities)
     * from input WKT footprint
     * 
     * @param {string} $footprint - WKT POLYGON
     * @param {array} $options - processing options
     *                  {
     *                      'hierarchical' => // if true return keywords by descending area of intersection
     *                  }
     */
    private function getPolitical($footprint, $options) {

        $result = array();

        // Continents
        if ($options['continents'] && !isset($options['countries'])) {
            if ($options['ordered']) {
                $query = "SELECT continent as continent, lower(unaccent(continent)) as id, st_area(st_intersection(geom, ST_GeomFromText('" . $footprint . "', 4326))) as area FROM " . $this->schema . ".continents WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326)) ORDER BY area DESC";
                $results = pg_query($this->dbh, $query);
                $continents = array();
                if (!isset($results)) {
                    $this->error();
                }
                while ($element = pg_fetch_assoc($results)) {
                    array_push($continents, array('name' => $element['continent'], 'id' => 'continent:'.$element['id']));
                }
            } else {
                $continents = getKeywords($this->schema . ".continents", "continent", $footprint, "continent");
            }
            if (count($continents) > 0) {
                $result['continents'] = $continents;
            }
        }

        // Countries
        if ($options['countries']) {

            // Continents and countries
            if ($options['ordered']) {
                $query = "SELECT name as name, lower(unaccent(name)) as id, continent as continent, lower(unaccent(continent)) as continentid, st_area(st_intersection(geom, ST_GeomFromText('" . $footprint . "', 4326))) as area, st_area(ST_GeomFromText('" . $footprint . "', 4326)) as totalarea FROM " . $this->schema . ".countries WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326)) ORDER BY area DESC";
            } else {
                $query = "SELECT name as name, lower(unaccent(name)) as id, continent as continent, lower(unaccent(continent)) as continentid FROM " . $this->schema . ".countries WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326))";
            }
            try {
                $results = pg_query($this->dbh, $query);
            } catch (Exception $e) {
                $this->error();
            }
            $countries = array();
            $continents = array();
            if (!isset($results)) {
                $this->error();
            }
            while ($element = pg_fetch_assoc($results)) {
                if ($options['hierarchical']) {
                    $index = -1;
                    for ($i = count($continents); $i--;) {
                        if ($continents[$i]['name'] === $element['continent']) {
                            $index = $i;
                            break;
                        }
                    }
                    if ($index === -1) {
                        array_push($continents, array(
                            'name' => $element['continent'],
                            'id' => 'continent:'.$element['continentid'],
                            'countries' => array()
                        ));
                        $index = count($continents) - 1;
                    }
                    if ($options['ordered']) {
                        array_push($continents[$index]['countries'], array('name' => $element['name'], 'id' => 'country:'.$element['id'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                    } else {
                        array_push($continents[$index]['countries'], array('name' => $element['name'], 'id' => 'country:'.$element['id']));
                    }
                } else {
                    $continents[] = array('name' => $element['continent'], 'id' => 'continent:'.$element['continentid']);
                    if ($options['ordered']) {
                        array_push($countries, array('name' => $element['name'], 'id' => 'country:'.$element['id'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                    } else {
                        array_push($countries, array('name' => $element['name'], 'id' => 'country:'.$element['id']));
                    }
                }
            }
            if (count($continents) > 0) {
                if ($options['hierarchical']) {
                    $result['continents'] = $continents;
                } else {
                    $result['countries'] = $countries;
                    $result['continents'] = $continents;
                }
            }
        }

        // Regions
        if ($options['regions']) {
            if ($options['ordered']) {
                $query = "SELECT region, name as state, lower(unaccent(name)) as stateid, lower(unaccent(region)) as regionid, adm0_a3 as isoa3, st_area(st_intersection(geom, ST_GeomFromText('" . $footprint . "', 4326))) as area, st_area(ST_GeomFromText('" . $footprint . "', 4326)) as totalarea FROM " . $this->schema . ".worldadm1level WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326)) ORDER BY area DESC";
            } else {
                $query = "SELECT region, name as state, lower(unaccent(name)) as stateid, lower(unaccent(region)) as regionid, adm0_a3 as isoa3 FROM " . $this->schema . ".worldadm1level WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326)) ORDER BY region";
            }
            $results = pg_query($this->dbh, $query);
            $regions = array();
            $states = array();
            if (!isset($results)) {
                $this->error();
            }
            while ($element = pg_fetch_assoc($results)) {

                if ($options['hierarchical']) {

                    /*
                     * Set regions under countries
                     */
                    if ($options['countries']) {
                        if (isset($result['continents'])) {
                            for ($i = count($result['continents']); $i--;) {
                                for ($j = count($result['continents'][$i]['countries']); $j--;) {
                                    if ($result['continents'][$i]['countries'][$j]['name'] === $this->getCountryName($element['isoa3'])) {
                                        if (!isset($result['continents'][$i]['countries'][$j]['regions'])) {
                                            $result['continents'][$i]['countries'][$j]['regions'] = array();
                                        }
                                        $index = -1;
                                        for ($k = count($result['continents'][$i]['countries'][$j]['regions']); $k--;) {
                                            if (!$element['regionid'] && !isset($result['continents'][$i]['countries'][$j]['regions'][$k]['id'])) {
                                                $index = $k;
                                                break;
                                            }
                                            else if (isset($result['continents'][$i]['countries'][$j]['regions'][$k]['id']) && $result['continents'][$i]['countries'][$j]['regions'][$k]['id'] === $element['regionid']) {
                                                $index = $k;
                                                break;
                                            }
                                        }
                                        if ($index === -1) {
                                            if (!isset($element['regionid']) || !$element['regionid']) {
                                                array_push($result['continents'][$i]['countries'][$j]['regions'], array(
                                                    'states' => array()
                                                ));
                                            }
                                            else {
                                                array_push($result['continents'][$i]['countries'][$j]['regions'], array(
                                                    'name' => $element['region'],
                                                    'id' => 'region:'.$element['regionid'],
                                                    'states' => array()
                                                ));
                                            }
                                            $index = count($result['continents'][$i]['countries'][$j]['regions']) - 1;
                                        }
                                        if (isset($result['continents'][$i]['countries'][$j]['regions'][$index]['states'])) {
                                            if ($options['ordered']) {
                                                array_push($result['continents'][$i]['countries'][$j]['regions'][$index]['states'], array('name' => $element['state'], 'id' => 'state:'.$element['stateid'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                                            } else {
                                                array_push($result['continents'][$i]['countries'][$j]['regions'][$index]['states'], array('name' => $element['state'], 'id' => 'state:'.$element['stateid']));
                                            }
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($element['region']) {
                        $regions[] = array('name' => $element['region'], 'id' => 'region:'.$element['regionid']);
                    }
                    if ($element['state']) {
                        if ($options['ordered']) {
                            array_push($states, array('name' => $element['state'], 'id' => 'state:'.$element['stateid'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                        } else {
                            array_push($states, array('name' => $element['state'], 'id' => 'state:'.$element['stateid']));
                        }
                    }
                }
            }
            if (count($regions) > 0) {
                $result['regions'] = $regions;
            }
            if (count($states) > 0) {
                $result['states'] = $states;
            }
        }

        // Cities
        if ($options['cities']) {
            if ($options['cities'] === "all") {

                /*
                 * Do not process if $footprint is more than 2x2 degrees
                 */
                $bbox = $this->bbox($footprint);
                if (abs($bbox['ulx'] - $bbox['lrx']) >  2 || abs($bbox['uly'] - $bbox['lry']) > 2) {
                    return $result;
                }
                $query = "SELECT g.name, g.countryname as country, d.region as region, d.name as state, d.adm0_a3 as isoa3 FROM " . $this->gazetteerSchema . ".geoname g LEFT OUTER JOIN " . $this->schema . ".worldadm1level d ON g.country || '.' || g.admin2 = d.gn_a1_code WHERE st_intersects(g.geom, ST_GeomFromText('" . $footprint . "', 4326)) and g.fcode in ('PPL', 'PPLC', 'PPLA', 'PPLA2', 'PPL3', 'PPL4', 'STLMT') ORDER BY g.name";
            }
            else {
                $query = "SELECT g.name, g.countryname as country, d.region as region, d.name as state, d.adm0_a3 as isoa3 FROM " . $this->gazetteerSchema . ".geoname g LEFT OUTER JOIN " . $this->schema . ".worldadm1level d ON g.country || '.' || g.admin2 = d.gn_a1_code WHERE st_intersects(g.geom, ST_GeomFromText('" . $footprint . "', 4326)) and g.fcode in ('PPLA','PPLC') ORDER BY g.name";
            }
            $results = pg_query($this->dbh, $query);
            $cities = array();
            if (!isset($results)) {
                $this->error();
            }
            while ($element = pg_fetch_assoc($results)) {
                if ($options['countries'] && $options['hierarchical']) {
                    if (!isset($options['regions'])) {
                        foreach (array_keys($result['continents']) as $continent) {
                            foreach (array_keys($result['continents'][$continent]['countries']) as $country) {
                                if ($result['continents'][$continent]['countries'][$country]['name'] === $element['country']) {
                                    if (!isset($result['continents'][$continent]['countries'][$country]['cities'])) {
                                        $result['continents'][$continent]['countries'][$country]['cities'] = array();
                                    }
                                    array_push($result['continents'][$continent]['countries'][$country]['cities'], $element['name']);
                                }
                            }
                        }
                    } else {
                        foreach (array_keys($result['continents']) as $continent) {
                            foreach (array_keys($result['continents'][$continent]['countries']) as $country) {
                                if ($result['continents'][$continent]['countries'][$country]['name'] === $element['country']) {
                                    foreach (array_keys($result['continents'][$continent]['countries'][$country]['regions'][$element['region']]['states']) as $state) {
                                        if ($result['continents'][$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['name'] === $element['state']) {
                                            if (!isset($result['continents'][$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['cities'])) {
                                                $result['continents'][$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['cities'] = array();
                                            }
                                            array_push($result['continents'][$continent]['countries'][$country]['regions'][$element['region']]['states'][$state]['cities'], $element['name']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    array_push($cities, $element['name']);
                }
            }

            if (count($cities) > 0) {
                $result['cities'] = $cities;
            }
        }

        return $result;
    }

    /**
     *
     * Compute intersected politicals information (i.e. continent, countries, cities)
     * from input WKT footprint using French IGN datas. (Will only return information regarding France)
     *
     * @param {string} $footprint - WKT POLYGON
     * @param {array} $options - processing options
     *                  {
     *                      'hierarchical' => // if true return keywords by descending area of intersection
     *                  }
     *
     */
    private function getFrenchPolitical($footprint, $options) {

        // Continents
        if ($options['continents'] && !isset($options['countries'])) {
            if ($options['ordered']) {
                $query = "SELECT continent as continent, st_area(st_intersection(geom, ST_GeomFromText('" . $footprint . "', 4326))) as area FROM " . $this->schema . ".continents WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326)) ORDER BY area DESC";
                $results = pg_query($this->dbh, $query);
                $continents = array();
                if (!isset($results)) {
                    $this->error();
                }
                while ($element = pg_fetch_assoc($results)) {
                    array_push($continents, $element['continent']);
                }
            } else {
                $continents = $this->getKeywords($this->schema . ".continents", "continent", $footprint, "continent");
            }
            if (count($continents) > 0) {
                $result['continents'] = $continents;
            }
        }

        // Countries
        if ($options['countries']) {

            // Continents and countries
            if ($options['ordered']) {
                $query = "SELECT name as name, continent as continent, st_area(st_intersection(geom, ST_GeomFromText('" . $footprint . "', 4326))) as area, st_area(ST_GeomFromText('" . $footprint . "', 4326)) as totalarea FROM " . $this->schema . ".countries WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326)) ORDER BY area DESC";
            }
            else {
                $query = "SELECT name as name, continent as continent FROM " . $this->schema . ".countries WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326))";
            }
            try {
                $results = pg_query($this->dbh, $query);
            } catch (Exception $e) {
                $this->error();
            }
            $countries = array();
            $continents = array();
            if (!isset($results)) {
                $this->error();
            }
            while ($element = pg_fetch_assoc($results)) {
                if ($options['hierarchical']) {
                    if (!isset($continents[$element['continent']])) {
                        $continents[$element['continent']] = array(
                            'countries' => array()
                        );
                    }
                    if ($options['ordered']) {
                        array_push($continents[$element['continent']]['countries'], array('name' => $element['name'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                    } else {
                        array_push($continents[$element['continent']]['countries'], array('name' => $element['name']));
                    }
                } else {
                    $continents[$element['continent']] = $element['continent'];
                    if ($options['ordered']) {
                        array_push($countries, array('name' => $element['name'], 'pcover' => $this->percentage($element['area'], $element['totalarea'])));
                    } else {
                        array_push($countries, array('name' => $element['name']));
                    }
                }
            }
            if (count($continents) > 0) {
                if ($options['hierarchical']) {
                    $result['continents'] = $continents;
                } else {
                    $result['countries'] = $countries;
                    $result['continents'] = array_keys($continents);
                }
            }
        }

        // Regions
        $result['regions'] = $this->getRegions($footprint);
        $result['states'] = $this->getDepartements($footprint);

        // Cities
        $result['cities'] = $this->getCommunes($footprint);

        return $result;
    }
    
    /**
     * Get french regions with codes
     *
     * @param string $footprint
     *
     */
    private function getRegions($footprint) {

        $query = "SELECT r.nom_region, r.code_reg from " . $this->schema . ".deptsfrance as r where st_intersects(r.geom, ST_GeomFromText('" . $footprint . "', 4326)) order by st_area(st_intersection(r.geom, ST_GeomFromText('" . $footprint . "', 4326))) desc";
        $results = pg_query($this->dbh, $query);
        if (!isset($results)) {
            $this->error();
        }
        $result = array_unique(pg_fetch_all($results), SORT_REGULAR);

        if ($result == false) {
            $query = "SELECT distinct(admin1) as nom_region, admin1 as code_region FROM " . $this->gazetteerSchema . ".geoname WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326))";
            $results = pg_query($this->dbh, $query);
            if (!isset($results)) {
                $this->error();
            }
            $result = pg_fetch_all($results);
        }

        return $result;
    }

    /**
     *
     * Get french departements with codes
     *
     * @param string $footprint
     *
     */
    private function getDepartements($footprint) {
        $query = "SELECT r.nom_dept, r.code_dept from " . $this->schema . ".deptsfrance as r where st_intersects(r.geom, ST_GeomFromText('" . $footprint . "', 4326)) order by st_area(st_intersection(r.geom, ST_GeomFromText('" . $footprint . "', 4326))) desc";
        $results = pg_query($this->dbh, $query);
        if (!isset($results)) {
            $this->error();
        }
        $result = array_unique(pg_fetch_all($results), SORT_REGULAR);

        if ($result == false) {
            $query = "SELECT distinct(admin2) as nom_dept, admin2 as code_dept FROM " . $this->gazetteerSchema . ".geoname WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326))";
            $results = pg_query($this->dbh, $query);
            if (!isset($results)) {
                $this->error();
            }
            $result = pg_fetch_all($results);
        }

        return $result;
    }

    /**
     *
     * Get french communes namees and intersected ratio
     *
     * @param string $footprint
     *
     */
    private function getCommunes($footprint) {
        $query = "SELECT record.nom_comm from (SELECT nom_comm, ST_Area(ST_Intersection(geom, ST_GeomFromText('" . $footprint . "', 4326))) as area_intersect, population, superficie from " . $this->schema . ".commfrance) as record where area_intersect > 0 order by record.area_intersect*(record.population/record.superficie) desc limit 20";
        $results = pg_query($this->dbh, $query);
        if (!isset($results)) {
            $this->error();
        }
        $result = pg_fetch_all($results);

        if ($result == false) {
            $query = "SELECT distinct(asciiname) as nom_comm FROM " . $this->gazetteerSchema . ".geoname WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326))";
            $results = pg_query($this->dbh, $query);
            if (!isset($results)) {
                $this->error();
            }
            $result = pg_fetch_all($results);
        }

        return $result;
    }

    /**
     * 
     * Compute intersected geophysical information (i.e. plates, faults, volcanoes, etc.)
     * from input WKT footprint
     * 
     * @param string footprint
     * 
     */
    private function getGeophysical($footprint) {

        $result = array();

        // Plates
        $plates = $this->getKeywords($this->schema . ".plates", "name", $footprint);
        if (count($plates) > 0) {
            $result['plates'] = $plates;
        }

        // Faults
        $faults = $this->getKeywords($this->schema . ".faults", "type", $footprint);
        if (count($faults) > 0) {
            $result['faults'] = $faults;
        }

        // Volcanoes
        $volcanoes = $this->getKeywords($this->schema . ".volcanoes", "name", $footprint);
        if (count($volcanoes) > 0) {
            $result['volcanoes'] = $volcanoes;
        }

        // Glaciers
        $glaciers = $this->getKeywords($this->schema . ".glaciers", "objectid", $footprint);
        if (count($glaciers) > 0) {
            $result['hasGlaciers'] = true;
        }

        return $result;
    }

    /**
     *
     * Get french arrondissements with codes
     *
     * @param string footprint
     *
     */
    private function getArrondissements($footprint) {
        $query = "SELECT record.nom_chf, record.code_arr, record.area, record.coverageratio from (SELECT distinct nom_chf, code_arr, ST_Area(geom) as area, round((ST_Area(ST_Intersection(geom, ST_GeomFromText('" . $footprint . "', 4326)))/ST_Area(geom))::numeric, 2) as coverageratio from " . $this->schema . ".arrsfrance order by coverageratio desc, area desc) as record where coverageratio > 0";
        $results = pg_query($this->dbh, $query);
        $keywords = array();
        if (!$results) {
            $this->error();
        }
        while ($result = pg_fetch_assoc($results)) {
            // temporary variable to display only nom_comm and coverageration fields
            $resulttmp['nom_chf'] = $result['nom_chf'];
            $resulttmp['code_arr'] = $result['code_arr'];
            array_push($keywords, $resulttmp);
        }

        return $keywords;
    }

    /**
     * 
     * Generic keywords returning function
     * 
     * @param {DatabaseConnection} $this->dbh
     * 
     */
    private function getKeywords($tableName, $columnName, $footprint, $order = null) {
        $orderBy = "";
        if (isset($order)) {
            $orderBy = " ORDER BY " . $order;
        }

        $query = "SELECT distinct(" . $columnName . ") FROM " . $tableName . " WHERE st_intersects(geom, ST_GeomFromText('" . $footprint . "', 4326))" . $orderBy;

        $results = pg_query($this->dbh, $query);
        $keywords = array();
        if (!isset($results)) {
            $this->error();
        }
        while ($result = pg_fetch_assoc($results)) {
            array_push($keywords, $result[$columnName]);
        }

        return $keywords;
    }

    /**
     *
     * Returns bounding box [ulx, uly, lrx, lry] from a WKT
     *
     * ULx,ULy
     *    +------------------+
     *    |                  |
     *    |                  |
     *    |                  |
     *    |                  |
     *    +------------------+
     *                     LRx,LRy
     *
     * Example of WKT POLYGON :
     *     POLYGON((-180.0044642857 89.9955356663,-180.0044642857 87.9955356727,-178.0044642921 87.9955356727,-178.0044642921 89.9955356663,-180.0044642857 89.9955356663))
     *
     * @param <string> $wkt : WKT
     * @return string : random table name
     *
     */
    private function bbox($wkt) {
        $ulx = 180.0;
        $uly = -90.0;
        $lrx = -180.0;
        $lry = 90.0;
        $rep = array("(", ")", "multi", "polygon", "point", "linestring");
        $pairs = preg_split('/,/', str_replace($rep, "", strtolower($wkt)));
        for ($i = 0; $i < count($pairs); $i++) {
            $coords = preg_split('/ /', trim($pairs[$i]));
            $x = floatval($coords[0]);
            $y = floatval($coords[1]);
            if ($x < $ulx) {
                $ulx = $x;
            } else if ($x > $lrx) {
                $lrx = $x;
            }
            if ($y > $uly) {
                $uly = $y;
            } else if ($y < $lry) {
                $lry = $y;
            }
        }

        return array('ulx' => $ulx, 'uly' => $uly, 'lrx' => $lrx, 'lry' => $lry);
    }

    /**
     * Return percentage of $part regarding $total
     * @param <float> $part
     * @param <float> $total
     * @return <float>
     */
    private function percentage($part, $total) {
        return min(array(100, floor(10000 * ($part / $total)) / 100));
    }

    /**
     * Return country name from ISO A3 or ISO A2 code
     * 
     * @param string $code
     * @return string
     */
    private function getCountryName($code) {

        $countryNames = array(
            'AD' => 'Andorra',
            'AND' => 'Andorra',
            'AF' => 'Afghanistan',
            'AFG' => 'Afghanistan',
            'AG' => 'Antigua and Barbuda',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'ALB' => 'Albania',
            'AN' => 'Netherlands Antilles',
            'AO' => 'Angola',
            'AGO' => 'Angola',
            'AQ' => 'Antarctica',
            'AR' => 'Argentina',
            'AE' => 'United Arab Emirates',
            'ARE' => 'United Arab Emirates',
            'ARG' => 'Argentina',
            'AM' => 'Armenia',
            'ARM' => 'Armenia',
            'AS' => 'American Samoa',
            'AT' => 'Austria',
            'ATA' => 'Antarctica',
            'ATF' => 'French Southern and Antarctic Lands',
            'AU' => 'Australia',
            'AUS' => 'Australia',
            'AUT' => 'Austria',
            'AW' => 'Aruba',
            'AX' => 'Aland Islands',
            'AZ' => 'Azerbaijan',
            'AZE' => 'Azerbaijan',
            'BA' => 'Bosnia and Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BDI' => 'Burundi',
            'BE' => 'Belgium',
            'BEL' => 'Belgium',
            'BEN' => 'Benin',
            'BF' => 'Burkina Faso',
            'BFA' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BGD' => 'Bangladesh',
            'BGR' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BHR' => 'Bahrain',
            'BHS' => 'Bahamas',
            'BI' => 'Burundi',
            'BIH' => 'Bosnia and Herzegovina',
            'BJ' => 'Benin',
            'BL' => 'Saint Barthelemy',
            'BLR' => 'Belarus',
            'BLZ' => 'Belize',
            'BM' => 'Bermuda',
            'BN' => 'Brunei',
            'BO' => 'Bolivia',
            'BOL' => 'Bolivia',
            'BQ' => 'Bonaire, Saint Eustatius and Saba ',
            'BR' => 'Brazil',
            'BRA' => 'Brazil',
            'BRN' => 'Brunei',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BTN' => 'Bhutan',
            'BV' => 'Bouvet Island',
            'BW' => 'Botswana',
            'BWA' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CAF' => 'Central African Republic',
            'CAN' => 'Canada',
            'CC' => 'Cocos Islands',
            'CD' => 'Democratic Republic of the Congo',
            'CF' => 'Central African Republic',
            'CG' => 'Republic of the Congo',
            'CH' => 'Switzerland',
            'CHE' => 'Switzerland',
            'CHL' => 'Chile',
            'CHN' => 'China',
            'CI' => 'Ivory Coast',
            'CIV' => 'Ivory Coast',
            'CK' => 'Cook Islands',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CMR' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'COD' => 'Democratic Republic of the Congo',
            'COG' => 'Republic of the Congo',
            'COL' => 'Colombia',
            'CR' => 'Costa Rica',
            'CRI' => 'Costa Rica',
            'CS' => 'Serbia and Montenegro',
            'CU' => 'Cuba',
            'CUB' => 'Cuba',
            'CV' => 'Cape Verde',
            'CW' => 'Curacao',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'CYN' => 'Northern Cyprus',
            'CYP' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CZE' => 'Czech Republic',
            'DE' => 'Germany',
            'DEU' => 'Germany',
            'DJ' => 'Djibouti',
            'DJI' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DNK' => 'Denmark',
            'DO' => 'Dominican Republic',
            'DOM' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'DZA' => 'Algeria',
            'EC' => 'Ecuador',
            'ECU' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'EGY' => 'Egypt',
            'EH' => 'Western Sahara',
            'ER' => 'Eritrea',
            'ERI' => 'Eritrea',
            'ES' => 'Spain',
            'ESP' => 'Spain',
            'EST' => 'Estonia',
            'ET' => 'Ethiopia',
            'ETH' => 'Ethiopia',
            'FI' => 'Finland',
            'FIN' => 'Finland',
            'FJ' => 'Fiji',
            'FJI' => 'Fiji',
            'FK' => 'Falkland Islands',
            'FLK' => 'Falkland Islands',
            'FM' => 'Micronesia',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'FRA' => 'France',
            'GA' => 'Gabon',
            'GAB' => 'Gabon',
            'GB' => 'United Kingdom',
            'GBR' => 'United Kingdom',
            'GD' => 'Grenada',
            'GE' => 'Georgia',
            'GEO' => 'Georgia',
            'GF' => 'French Guiana',
            'GG' => 'Guernsey',
            'GH' => 'Ghana',
            'GHA' => 'Ghana',
            'GI' => 'Gibraltar',
            'GIB' => 'Gibraltar',
            'GI' => 'Guinea',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GMB' => 'Gambia',
            'GN' => 'Guinea',
            'GNB' => 'Guinea-Bissau',
            'GNQ' => 'Equatorial Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GRC' => 'Greece',
            'GRL' => 'Greenland',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'GT' => 'Guatemala',
            'GTM' => 'Guatemala',
            'GU' => 'Guam',
            'GUY' => 'Guyana',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong',
            'HKG' => 'Hong Kong',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HND' => 'Honduras',
            'HR' => 'Croatia',
            'HRV' => 'Croatia',
            'HT' => 'Haiti',
            'HTI' => 'Haiti',
            'HU' => 'Hungary',
            'HUN' => 'Hungary',
            'ID' => 'Indonesia',
            'IDN' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IM' => 'Isle of Man',
            'IN' => 'India',
            'IND' => 'India',
            'IO' => 'British Indian Ocean Territory',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IRL' => 'Ireland',
            'IRN' => 'Iran',
            'IRQ' => 'Iraq',
            'IS' => 'Iceland',
            'ISL' => 'Iceland',
            'ISR' => 'Israel',
            'IT' => 'Italy',
            'ITA' => 'Italy',
            'JAM' => 'Jamaica',
            'JE' => 'Jersey',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JOR' => 'Jordan',
            'JP' => 'Japan',
            'JPN' => 'Japan',
            'KAS' => 'Kashmir',
            'KAZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KEN' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KGZ' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KHM' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Kitts and Nevis',
            'KOR' => 'Korea',
            'KOS' => 'Kosovo',
            'KP' => 'North Korea',
            'KR' => 'South Korea',
            'KW' => 'Kuwait',
            'KWT' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LAO' => 'Laos',
            'LB' => 'Lebanon',
            'LBN' => 'Lebanon',
            'LBR' => 'Liberia',
            'LBY' => 'Libya',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LKA' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LSO' => 'Lesotho',
            'LT' => 'Lithuania',
            'LTU' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LUX' => 'Luxembourg',
            'LV' => 'Latvia',
            'LVA' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MAR' => 'Morocco',
            'MC' => 'Monaco',
            'MCO' => 'Monaco',
            'MD' => 'Moldova',
            'MDA' => 'Moldova',
            'MDG' => 'Madagascar',
            'ME' => 'Montenegro',
            'MEX' => 'Mexico',
            'MF' => 'Saint Martin',
            'MG' => 'Madagascar',
            'MH' => 'Marshall Islands',
            'MK' => 'Macedonia',
            'MKD' => 'Macedonia',
            'ML' => 'Mali',
            'MLI' => 'Mali',
            'MM' => 'Myanmar',
            'MMR' => 'Myanmar',
            'MN' => 'Mongolia',
            'MNE' => 'Montenegro',
            'MNG' => 'Mongolia',
            'MO' => 'Macao',
            'MOZ' => 'Mozambique',
            'MP' => 'Northern Mariana Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MRT' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MWI' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MYS' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NAM' => 'Namibia',
            'NC' => 'New Caledonia',
            'NCL' => 'New Caledonia',
            'NE' => 'Niger',
            'NER' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NGA' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NIC' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NLD' => 'Netherlands',
            'NO' => 'Norway',
            'NOR' => 'Norway',
            'NP' => 'Nepal',
            'NPL' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'NZL' => 'New Zealand',
            'OM' => 'Oman',
            'OMN' => 'Oman',
            'PA' => 'Panama',
            'PAK' => 'Pakistan',
            'PAN' => 'Panama',
            'PE' => 'Peru',
            'PER' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PHL' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn',
            'PNG' => 'Papua New Guinea',
            'POL' => 'Poland',
            'PR' => 'Puerto Rico',
            'PRI' => 'Puerto Rico',
            'PRK' => 'North Korea',
            'PRT' => 'Portugal',
            'PRY' => 'Paraguay',
            'PS' => 'Palestinian Territory',
            'PSX' => 'Palestine',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'QAT' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'ROU' => 'Romania',
            'RS' => 'Serbia',
            'RU' => 'Russia',
            'RUS' => 'Russia',
            'RW' => 'Rwanda',
            'RWA' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SAH' => 'Western Sahara',
            'SAU' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SDN' => 'Sudan',
            'SDS' => 'South Sudan',
            'SE' => 'Sweden',
            'SEN' => 'Senegal',
            'SG' => 'Singapore',
            'SGP' => 'Singapore',
            'SH' => 'Saint Helena',
            'SI' => 'Slovenia',
            'SJ' => 'Svalbard and Jan Mayen',
            'SK' => 'Slovakia',
            'SL' => 'Sierra Leone',
            'SLB' => 'Solomon Islands',
            'SLE' => 'Sierra Leone',
            'SLV' => 'El Salvador',
            'SM' => 'San Marino',
            'SMR' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SOL' => 'Somaliland',
            'SOM' => 'Somalia',
            'SR' => 'Suriname',
            'SRB' => 'Serbia',
            'SS' => 'South Sudan',
            'ST' => 'Sao Tome and Principe',
            'SUR' => 'Suriname',
            'SV' => 'El Salvador',
            'SVK' => 'Slovakia',
            'SVN' => 'Slovenia',
            'SWE' => 'Sweden',
            'SWZ' => 'Swaziland',
            'SX' => 'Sint Maarten',
            'SY' => 'Syria',
            'SYR' => 'Syria',
            'SZ' => 'Swaziland',
            'TC' => 'Turks and Caicos Islands',
            'TCD' => 'Chad',
            'TD' => 'Chad',
            'TF' => 'French Southern Territories',
            'TG' => 'Togo',
            'TGO' => 'Togo',
            'TH' => 'Thailand',
            'THA' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TJK' => 'Tajikistan',
            'TK' => 'Tokelau',
            'TKM' => 'Turkmenistan',
            'TL' => 'East Timor',
            'TLS' => 'Timor-Leste',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TTO' => 'Trinidad and Tobago',
            'TUN' => 'Tunisia',
            'TUR' => 'Turkey',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TWN' => 'Taiwan',
            'TZ' => 'Tanzania',
            'TZA' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UGA' => 'Uganda',
            'UKR' => 'Ukraine',
            'UM' => 'United States Minor Outlying Islands',
            'URY' => 'Uruguay',
            'US' => 'United States',
            'USA' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'UZB' => 'Uzbekistan',
            'VA' => 'Vatican',
            'VAT' => 'Vatican',
            'VC' => 'Saint Vincent and the Grenadines',
            'VE' => 'Venezuela',
            'VEN' => 'Venezuela',
            'VG' => 'British Virgin Islands',
            'VI' => 'U.S. Virgin Islands',
            'VN' => 'Vietnam',
            'VNM' => 'Vietnam',
            'VU' => 'Vanuatu',
            'VUT' => 'Vanuatu',
            'WF' => 'Wallis and Futuna',
            'WS' => 'Samoa',
            'XK' => 'Kosovo',
            'YE' => 'Yemen',
            'YEM' => 'Yemen',
            'YT' => 'Mayotte',
            'ZA' => 'South Africa',
            'ZAF' => 'South Africa',
            'ZM' => 'Zambia',
            'ZMB' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'ZWE' => 'Zimbabwe'
        );

        return $countryNames[$code] ? $countryNames[$code] : $code;
    }

    /**
     * Return GLC class name from input code
     * 
     * Note: GLC 2000 defines 22 landuse classes
     *
      1=>"Tree Cover, broadleaved, evergreen",
      2=>"Tree Cover, broadleaved, deciduous, closed",
      3=>"Tree Cover, broadleaved, deciduous, open",
      4=>"Tree Cover, needle-leaved, evergreen",
      5=>"Tree Cover, needle-leaved, deciduous",
      6=>"Tree Cover, mixed leaf type",
      7=>"Tree Cover, regularly flooded, fresh  water",
      8=>"Tree Cover, regularly flooded, saline water",
      9=>"Mosaic: Tree cover / Other natural vegetation",
      10=>"Tree Cover, burnt",
      11=>"Shrub Cover, closed-open, evergreen",
      12=>"Shrub Cover, closed-open, deciduous",
      13=>"Herbaceous Cover, closed-open",
      14=>"Sparse Herbaceous or sparse Shrub Cover",
      15=>"Regularly flooded Shrub and/or Herbaceous Cover",
      16=>"Cultivated and managed areas",
      17=>"Mosaic: Cropland / Tree Cover / Other natural vegetation",
      18=>"Mosaic: Cropland / Shrub or Grass Cover",
      19=>"Bare Areas",
      20=>"Water Bodies",
      21=>"Snow and Ice",
      22=>"Artificial surfaces and associated areas"
     * 
     * @param <Integer> $code
     * @return <String>
     * 
     */
    private function getGLCClassName($code) {

        // GLC has 22 landuse classes
        $classNames = array(
            1 => "Tree Cover, broadleaved, evergreen",
            2 => "Tree Cover, broadleaved, deciduous, closed",
            3 => "Tree Cover, broadleaved, deciduous, open",
            4 => "Tree Cover, needle-leaved, evergreen",
            5 => "Tree Cover, needle-leaved, deciduous",
            6 => "Tree Cover, mixed leaf type",
            7 => "Tree Cover, regularly flooded, fresh  water",
            8 => "Tree Cover, regularly flooded, saline water",
            9 => "Mosaic - Tree cover / Other natural vegetation",
            10 => "Tree Cover, burnt",
            11 => "Shrub Cover, closed-open, evergreen",
            12 => "Shrub Cover, closed-open, deciduous",
            13 => "Herbaceous Cover, closed-open",
            14 => "Sparse Herbaceous or sparse Shrub Cover",
            15 => "Regularly flooded Shrub and/or Herbaceous Cover",
            16 => "Cultivated and managed areas",
            17 => "Mosaic - Cropland / Tree Cover / Other natural vegetation",
            18 => "Mosaic - Cropland / Shrub or Grass Cover",
            19 => "Bare Areas",
            20 => "Water Bodies",
            21 => "Snow and Ice",
            22 => "Artificial surfaces and associated areas",
            100 => "Urban",
            200 => "Cultivated",
            310 => "Forest",
            320 => "Herbaceous",
            330 => "Desert",
            335 => "Ice",
            400 => "Flooded",
            500 => "Water"
        );

        if (is_int($code) && $code > 0) {
            return isset($classNames[$code]) ? $classNames[$code] : "";
        }

        return "";
    }

    /**
     * Throws exception
     * 
     * @param string $message
     * @param integer $code
     * @throws Exception
     */
    private function error($message = 'Database Connection Error', $code = 500) {
        throw new Exception($message, $code);
    }

}
