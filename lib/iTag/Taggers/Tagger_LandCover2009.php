<?php
/*
 * Copyright 2013 Jérôme Gasperi
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

class Tagger_LandCover2009 extends Tagger {

    /*
     * Data references
     */
    public $references = array(
        array(
            'dataset' => 'GlobCover 2009',
            'author' => 'ESA',
            'license' => 'Free of Charge for non-commercial use',
            'url' => 'http://due.esrin.esa.int/page_globcover.php'
        )
    );

    /*
     * Corine Land Cover
     */
    private $clcClassNames = array(
        100 => 'Urban',
        200 => 'Cultivated',
        310 => 'Forest',
        320 => 'Herbaceous',
        330 => 'Desert',
        335 => 'Ice',
        400 => 'Flooded',
        500 => 'Water'
    );

    /*
     * Global Land Cover class names
     */
    private $globcoverClassNames = array(
        11 => 'Post-flooding or irrigated croplands',
        14 => 'Rainfed croplands',
        20 => 'Mosaic cropland (50-70%) / vegetation (grassland/shrubland/forest) (20-50%)',
        30 => 'Mosaic vegetation (grassland/shrubland/forest) (50-70%) / cropland (20-50%)',
        40 => 'Closed to open (>15%) broadleaved evergreen or semi-deciduous forest (>5m)',
        50 => 'Closed (>40%) broadleaved deciduous forest (>5m)',
        60 => 'Open (15-40%) broadleaved deciduous forest/woodland (>5m)',
        70 => 'Closed (>40%) needleleaved evergreen forest (>5m)',
        90 => 'Open (15-40%) needleleaved deciduous or evergreen forest (>5m)',
        100 => 'Closed to open (>15%) mixed broadleaved and needleleaved forest (>5m)',
        110 => 'Mosaic forest or shrubland (50-70%) / grassland (20-50%)',
        120 => 'Mosaic grassland (50-70%) / forest or shrubland (20-50%)',
        130 => 'Closed to open (>15%) (broadleaved or needleleaved, evergreen or deciduous) shrubland (<5m)',
        140 => 'Closed to open (>15%) herbaceous vegetation (grassland, savannas or lichens/mosses)',
        150 => 'Sparse (<15%) vegetation',
        160 => 'Closed to open (>15%) broadleaved forest regularly flooded (semi-permanently or temporarily) - Fresh or brackish water',
        170 => 'Closed (>40%) broadleaved forest or shrubland permanently flooded - Saline or brackish water',
        180 => 'Closed to open (>15%) grassland or woody vegetation on regularly flooded or waterlogged soil - Fresh, brackish or saline water',
        190 => 'Artificial surfaces and associated areas (Urban areas >50%)',
        200 => 'Bare areas',
        210 => 'Water bodies',
        220 => 'Permanent snow and ice',
        230 => 'No data (burnt areas, clouds)'
    );

    /*
     * Corine Land Cover - Global Land Cover linkage
     */
    private $linkage = array(
        100 => array(22), // Urban
        200 => array(11, 14 ,20, 30), // Cultivated
        310 => array(40, 50, 60, 70, 90, 100, 110), // Forest
        320 => array(120, 130, 140, 150), // Herbaceous
        330 => array(200), // Desert
        335 => array(220), // Ice
        400 => array(160, 170, 180), // Flooded
        500 => array(210) // Water
    );

    /**
     * Constructor
     *
     * @param DatabaseHandler $dbh
     * @param array $config
     */
    public function __construct($dbh, $config) {
        parent::__construct($dbh, $config);
    }

    /**
     * Tag metadata
     *
     * @param array $metadata
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function tag($metadata, $options = array()) {
        parent::tag($metadata, $options);
        return $this->process($metadata['footprint'], $options);
    }

    /**
     *
     * Compute land cover from input WKT footprint
     *
     * @param string $footprint
     * @param array $options
     * 
     */
    private function process($footprint, $options) {

        /*
         * Superseed areaLimit
         */
        if (isset($options['areaLimit']) && $this->area > $options['areaLimit']) {
            return array(
                'landCover' => array()
            );
        }

        /*
         * Do not process if footprint area is greater
         * than the maximum area allowed
         */
        if (!$this->isValidArea($this->area)) {
            return array(
                'landCover' => array()
            );
        }

        /*
         * Get raw landcover
         */
        $rawLandCover = $this->retrieveRawLandCover($footprint);

        /*
         * Return full land use description
         */
        return array(
            'landCover' => array(
                'landUse' => $this->getLandUse($rawLandCover),
                'landUseDetails' => $this->getLandUseDetails($rawLandCover)
        ));
    }

    /**
     * Returns main land use
     *
     * @param array $rawLandCover
     */
    private function getLandUse($rawLandCover) {
        $sums = array();
        foreach ($this->linkage as $key => $value) {
            $sums[$key] = $this->sum($rawLandCover, $value);
        }
        arsort($sums);
        $landUse = array();
        foreach ($sums as $key => $val) {
            $pcover = $this->percentage($this->toSquareKm($val), $this->area);
            if ($val !== 0 && $pcover > 0) {
                $name = isset($this->clcClassNames[$key]) ? $this->clcClassNames[$key] : 'unknown';
                array_push($landUse, array(
                    'name' => $name,
                    'id' => 'landuse:' . strtolower($name),
                    'area' => $this->toSquareKm($val),
                    'pcover' => $pcover
                ));
            }
        }

        return $landUse;

    }

    /**
     * Returns land use details
     *
     * @param array $rawLandCover
     */
    private function getLandUseDetails($rawLandCover) {
        $landUseDetails = array();
        foreach ($rawLandCover as $key => $val) {
            if ($val['area'] !== 0) {
                $name = isset($this->globecoverClassNames[$key]) ? $this->globecoverClassNames[$key] : 'unknown';
                $area = $this->toSquareKm($val['area']);
                $details = array(
                    'name' => $name,
                    'id' => 'landuse_details:' . strtolower(str_replace(array('/', ',', ' '), '-', $name)),
                    'parentId' => 'landuse:' . strtolower($this->getCLCParent($key)),
                    'code' => $key,
                    'area' => $area,
                    'pcover' => $this->percentage($area, $this->area)
                );
                if ($this->config['returnGeometries'] && !empty($val['geometries'])) {
                    $details['geometry'] = 'MULTIPOLYGON(' . join(',', $val['geometries']) . ')';
                }
                array_push($landUseDetails, $details);
            }
        }
        return $landUseDetails;
    }

    /**
     * Retrieve landcover from iTag database
     *
     * @param string $footprint
     * @return array
     */
    private function retrieveRawLandCover($footprint) {
        $classes = array();
        $prequery = 'WITH prequery AS (SELECT ' . $this->postgisGeomFromText($footprint) . ' AS corrected_geometry)';
        if ($this->config['returnGeometries']) {
            $query = $prequery . ' SELECT dn as dn, ' . $this->postgisArea($this->postgisIntersection('wkb_geometry', 'corrected_geometry')) . ' as area, ' . $this->postgisAsWKT($this->postgisSimplify($this->postgisIntersection('wkb_geometry', 'corrected_geometry'))) . ' as wkt FROM prequery, datasources.landcover2009 WHERE st_intersects(wkb_geometry, corrected_geometry)';
        }
        else {
            $query = $prequery . ' SELECT dn as dn, ' . $this->postgisArea($this->postgisIntersection('wkb_geometry', 'corrected_geometry')) . ' as area FROM prequery, datasources.landcover WHERE st_intersects(wkb_geometry, corrected_geometry)';
        }
        $results = $this->query($query);
        if (!$results) {
          return $classes;
        }
        while ($result = pg_fetch_assoc($results)) {
            if (!isset($classes[$result['dn']])) {
                $classes[$result['dn']] = array(
                    'area' => 0,
                    'geometries' => array()
                );
            }
            $classes[$result['dn']]['area'] += $result['area'];
            if (isset($result['wkt']) && substr($result['wkt'], 0, 4) === 'POLY') {
                $classes[$result['dn']]['geometries'][] = '(' . substr($result['wkt'], 8, count($result['wkt']) - 2) . ')';
            }
        }

        return $classes;
    }

    /**
     * Return the sum of classes[$keys] values
     *
     * @param array $classes
     * @param array $keys
     */
    private function sum($classes, $keys) {
        $sum = 0;
        for ($i = count($keys); $i--;) {
            if (isset($classes[$keys[$i]])) {
                $sum += $classes[$keys[$i]]['area'];
            }
        }
        return $sum;
    }

    /**
     * Return CLC class name from child GlobeCover $code
     *
     * @param integer $code
     */
    private function getCLCParent($code) {
        foreach ($this->linkage as $key => $value) {
            if (in_array($code, $value) && isset($this->clcClassNames[$key])) {
                return $this->clcClassNames[$key];
            }
        }
        return 'unknown';
    }

}
