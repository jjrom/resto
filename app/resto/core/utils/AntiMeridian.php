<?php
/*
 * Fix shapes that cross the antimeridian. 
 * 
 * PHP version of [antimeridian](https://github.com/gadomski/antimeridian) by Pete Gadomski 
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

require_once('antimeridian/Polygon.php');
require_once('antimeridian/MultiPolygon.php');
require_once('antimeridian/LineString.php');
require_once('antimeridian/MultiLineString.php');
require_once('antimeridian/LinearRing.php');
require_once('antimeridian/IndexAndLatitude.php');

class AntiMeridian {

    private $roundPrecision = 7;

    /**
     * Constructor
     */
    public function __construct()
    {}

    /**
     * Fixes a GeoJSON object that crosses the antimeridian.
     *
     * If the object does not cross the antimeridian, it is returned unchanged.
     *
     * @param array $geojson GeoJSON object as an associative array
     * @param bool $force_north_pole Force joined segments to enclose the north pole
     * @param bool $force_south_pole Force joined segments to enclose the south pole
     * @param bool $fix_winding Reverse coordinates if the polygon is wound clockwise
     * @param bool $great_circle Compute meridian crossings on the sphere
     * @return array The same GeoJSON with a fixed geometry or geometries
     * @throws Exception If required fields are missing
     */
    public function fixGeoJSON(array $geojson,
        bool $force_north_pole = false,
        bool $force_south_pole = false,
        bool $fix_winding = true,
        bool $great_circle = true
    ): array
    {
        $type = $geojson['type'] ?? null;
        if ($type === null) {
            throw new Exception('No type field found in GeoJSON');
        }
        
        if ($type === 'Feature') {
            $geometry = $geojson['geometry'] ?? null;
            if ($geometry === null) {
                throw new Exception('No geometry field found in GeoJSON Feature');
            }
            $geojson['geometry'] = $this->fixShape(
                $geometry,
                $force_north_pole,
                $force_south_pole,
                $fix_winding,
                $great_circle
            );
            return $geojson;
        } elseif ($type === 'FeatureCollection') {
            $features = $geojson['features'] ?? null;
            if ($features === null) {
                throw new Exception('No features field found in GeoJSON FeatureCollection');
            }
            foreach ($features as $i => $feature) {
                $features[$i] = $this->fixGeoJSON(
                    $feature,
                    $force_north_pole,
                    $force_south_pole,
                    $fix_winding,
                    $great_circle
                );
            }
            $geojson['features'] = $features;
            return $geojson;
        } else {
            return $this->fixShape(
                $geojson,
                $force_north_pole,
                $force_south_pole,
                $fix_winding,
                $great_circle
            );
        }
    }

    /**
     * Fixes a shape that crosses the antimeridian.
     *
     * @param array $shape A polygon, multi-polygon, line string, or multi-line string
     * @param bool $force_north_pole Force joined segments to enclose the north pole
     * @param bool $force_south_pole Force joined segments to enclose the south pole
     * @param bool $fix_winding Reverse coordinates if the polygon is wound clockwise
     * @param bool $great_circle Compute meridian crossings on the sphere
     * @return array The fixed shape as an associative array
     * @throws Exception If the geometry type is unsupported
     */
    private function fixShape(
        array $shape,
        bool $force_north_pole = false,
        bool $force_south_pole = false,
        bool $fix_winding = true,
        bool $great_circle = true
    ): array {
        
        $geom = $shape['geometry'] ?? $shape;
        switch ($geom['type']) {
            case 'Polygon':
                return $this->fixPolygon(
                    new Polygon($geom),
                    $force_north_pole,
                    $force_south_pole,
                    $fix_winding,
                    $great_circle
                )->toGeoJSON();

            case 'MultiPolygon':
                return $this->fixMultiPolygon(
                    new MultiPolygon($geom),
                    $force_north_pole,
                    $force_south_pole,
                    $fix_winding,
                    $great_circle
                )->toGeoJson();

            case 'LineString':
                return $this->fixLineString(
                    new LineString($geom),
                    $great_circle
                )->toGeoJSON();
                
            case 'MultiLineString':
                return $this->fixMultiLineString(
                    new MultiLineString($geom),
                    $great_circle
                )->toGeoJSON();

            default:
                throw new Exception('Unsupported geometry type: ' . $geom['type']);
        }
        
    }

    /**
     * Fixes a polygon geometry.
     *
     * @param Polygon $polygon The input polygon.
     * @param bool $force_north_pole If true, force the joined segments to enclose the north pole.
     * @param bool $force_south_pole If true, force the joined segments to enclose the south pole.
     * @param bool $fix_winding If true, reverse the polygon's coordinates if it is wound clockwise.
     * @param bool $great_circle If true, compute meridian crossings on the sphere rather than using 2D geometry.
     *
     * @return Polygon|MultiPolygon The fixed polygon, as a single polygon or a multi-polygon if it was split.
     * @throws InvalidArgumentException If input is invalid.
     */
    private function fixPolygon(
        Polygon $polygon,
        bool $force_north_pole = false,
        bool $force_south_pole = false,
        bool $fix_winding = true,
        bool $great_circle = true
    ): Polygon|MultiPolygon {


        if ($force_north_pole || $force_south_pole) {
            $fix_winding = false;
        }

        $polygons = $this->fixPolygonToList(
            $polygon,
            $force_north_pole,
            $force_south_pole,
            $fix_winding,
            $great_circle
        );

        if (count($polygons) === 1) {
            $polygon = $polygons[0];
            if (Polygon::isCCW($polygon->getExteriorRing())) {
                return $polygon;
            } else {
                $polygon->setInteriorRings($polygon->getExteriorRing()); 
                $polygon->setExteriorRing([
                    [-180, 90],
                    [-180, -90],
                    [180, -90],
                    [180, 90]
                ]);
                return $polygon;
            }
        } else {
            return new MultiPolygon($polygons);
        }
    }

    /**
     * Fixes a MultiPolygon.
     *
     * @param object $multi_polygon The multi-polygon geometry object
     * @param bool $force_north_pole Force joined segments to enclose the north pole
     * @param bool $force_south_pole Force joined segments to enclose the south pole
     * @param bool $fix_winding Reverse coordinates if the polygon is wound clockwise
     * @param bool $great_circle Compute meridian crossings on the sphere
     * @return object The fixed multi-polygon
     * @throws Exception If input is not a valid MultiPolygon
     */
    private function fixMultiPolygon(
        $multi_polygon,
        bool $force_north_pole = false,
        bool $force_south_pole = false,
        bool $fix_winding = true,
        bool $great_circle = true
    ) {
        if ($multi_polygon->getType() !== 'MultiPolygon') {
            throw new Exception('Input geometry is not a MultiPolygon');
        }

        $polygons = [];
        foreach ($multi_polygon->getPolygons() as $polygon) {
            $fixed_polygons = $this->fixPolygonToList(
                $polygon,
                $force_north_pole,
                $force_south_pole,
                $fix_winding,
                $great_circle
            );
            $polygons = array_merge($polygons, $fixed_polygons);
        }
        
        // Assume `create_multi_polygon` is a function to create a MultiPolygon from an array of polygons
        return new MultiPolygon($polygons);
    }

    private function fixLineString(LineString $line_string, bool $great_circle): LineString|MultiLineString {
        /**
         * Fixes a LineString geometry.
         *
         * @param LineString $line_string The input line string.
         * @param bool $great_circle Compute meridian crossings on the sphere rather than using 2D geometry.
         * 
         * @return LineString|MultiLineString The fixed line string, either as a single line string or a multi-line string if split.
         */
        $segments = $this->segment($line_string->getCoordinates(), $great_circle);

        if (empty($segments)) {
            return $line_string;
        } else {
            $linestrings = [];
            for ($i = 0, $ii = count($segments); $i < $ii; $i++) {
                $linestrings[] = new LineString([
                    'type' => 'LineString',
                    'coordinates' => $segments[$i]
                ]);
            }
            return new MultiLineString($linestrings);
        }
    }

    private function fixMultiLineString(MultiLineString $multi_line_string, bool $great_circle): MultiLineString {
        /**
         * Fixes a MultiLineString geometry.
         *
         * @param MultiLineString $multi_line_string The input multi-line string.
         * @param bool $great_circle Compute meridian crossings on the sphere rather than using 2D geometry.
         * 
         * @return MultiLineString The fixed multi-line string.
         */
        $line_strings = [];

        foreach ($multi_line_string->getLineStrings() as $line_string) {
            $fixed = $this->fixLineString($line_string, $great_circle);

            if ($fixed instanceof LineString) {
                $line_strings[] = $fixed;
            } else {
                $line_strings = array_merge($line_strings, $fixed->getLineStrings());
            }
        }

        return new MultiLineString($line_strings);
    }

    private function fixPolygonToList(
        Polygon $polygon,
        bool $forceNorthPole = false,
        bool $forceSouthPole = false,
        bool $fixWinding = true,
        bool $greatCircle = true
    ): array {

        $exterior = $this->normalize($polygon->getExteriorRing());
        $segments = $this->segment($exterior, $greatCircle);
        if (empty($segments)) {

            $polygon->setExteriorRing($exterior);
            if ($fixWinding && !$polygon->checkOrientation()) {
                error_log('Warning: Fixing winding order because the polygon extends over both poles');
                $polygon->correctOrientation();
            }
            return [$polygon];
        }
        else {
            $interiors = [];
            $interiorRings = $polygon->getInteriorRings();
            for ($i = 0, $ii = count($interiorRings); $i < $ii; $i++) {
                $interiorSegments = $this->segment($interiorRings[$i], $greatCircle);
                if (!empty($interiorSegments)) {
                    if ($fixWinding) {
                        $unwrappedLinearRing = new LinearRing(array_map(
                            function ($coord) {
                                return [fmod($coord[0] + 360, 360), $coord[1]];
                            },
                            $interiorRings[$i]
                        ));
                        if ($unwrappedLinearRing->isCCW()) {
                            error_log('Warning: Fixing winding order because the polygon extends over both poles');
                            $interiorSegments = $this->segment(array_reverse($interiorRings[$i]), $greatCircle);
                        }
                    }
                    $segments = array_merge($segments, $interiorSegments);
                } else {
                    $interiors[] = $interiorRings[$i];
                }
            }
        }

        $segments = $this->extendOverPoles(
            $segments,
            $forceNorthPole,
            $forceSouthPole,
            $fixWinding
        );

        $polygons = $this->buildPolygons($segments);
        assert(!empty($polygons));

        foreach ($polygons as $i => $poly) {
            foreach ($interiors as $j => $interior) {
                if ($poly->contains(new LinearRing($interior))) {
                    unset($interiors[$j]);
                    $polygonInteriors = $poly->getInteriorRings();
                    $polygonInteriors[] = $interior;
                    $polygons[$i]->setExteriorRing($poly->getExteriorRing());
                    $polygons[$i]->setInteriorRings($polygonInteriors);
                }
            }
        }

        assert(empty($interiors));
        return $polygons;
    }


    /**
     * Segment a set of coordinates at the antimeridian.
     * 
     * [IMPORTANT] This function differs from the original implementation in that it first test if the
     * polygon split or not the antimeridian based on the bbox order. To do so, it takes the asumption that
     * the first coordinates is the most western point of the polygon. From this it computes the bbox and applies
     * the GeoJSON rule on antimeridian crossing (https://datatracker.ietf.org/doc/html/rfc7946#section-5.2)
     *    
     *          "Consider a set of point Features within the Fiji archipelago,
     *          straddling the antimeridian between 16 degrees S and 20 degrees S.
     *          The southwest corner of the box containing these Features is at 20
     *          degrees S and 177 degrees E, and the northwest corner is at 16
     *          degrees S and 178 degrees W.  The antimeridian-spanning GeoJSON
     *          bounding box for this FeatureCollection is
     *          
     *          "bbox": [177.0, -20.0, -178.0, -16.0]
     *          
     *          and covers 5 degrees of longitude.
     *          
     *          The complementary bounding box for the same latitude band, not
     *          crossing the antimeridian, is
     *          
     *          "bbox": [-178.0, -20.0, 177.0, -16.0]
     *          
     *          and covers 355 degrees of longitude.
     *          
     *          The latitude of the northeast corner is always greater than the
     *          latitude of the southwest corner, but bounding boxes that cross the
     *          antimeridian have a northeast corner longitude that is less than the
     *          longitude of the southwest corner."
     * 
     * 
     * @param array $coords The coordinates to segment.
     * @param bool $greatCircle Whether to use great circle calculations.
     */
    private function segment(array $coords, bool $greatCircle): array {
        $segment = [];
        $segments = [];

        $westernCoords = $coords[0];
        $easternCoords = $this->getEasternmostCoordinate($coords);

        if ($westernCoords[0] < $easternCoords[0]) {
            // No antimeridian crossing
            return [];
        }

        for ($i = 0; $i < count($coords) - 1; $i++) {
            $start = $coords[$i];
            $end = $coords[$i + 1];

            $segment[] = $start;

            if (($end[0] - $start[0] > 180) && ($end[0] - $start[0] != 360)) { // Left crossing
                $latitude = $this->crossingLatitude($start, $end, $greatCircle);
                $segment[] = [-180, $latitude];
                $segments[] = $segment;
                $segment = [[180, $latitude]];
            } elseif (($start[0] - $end[0] > 180) && ($start[0] - $end[0] != 360)) { // Right crossing
                $latitude = $this->crossingLatitude($end, $start, $greatCircle);
                $segment[] = [180, $latitude];
                $segments[] = $segment;
                $segment = [[-180, $latitude]];
            }
        }

        if (empty($segments)) {
            // No antimeridian crossings
            return [];
        } elseif ($coords[count($coords) - 1] == $segments[0][0]) {
            // Join polygons
            $segments[0] = array_merge($segment, $segments[0]);
        } else {
            $segment[] = $coords[count($coords) - 1];
            $segments[] = $segment;
        }

        return $segments;
    }

    /**
     * Returns the easternmost coordinate in an array of coordinates.
     * 
     * @param array $coordinates The array of coordinates.
     * @return array|null The easternmost coordinate.
     */
    private function getEasternmostCoordinate($coordinates) {
        if (empty($coordinates)) {
            return null; // Return null if the array is empty
        }

        // Sort the array by longitude in descending order
        usort($coordinates, function($a, $b) {
            return $b[0] <=> $a[0]; // Compare longitude values
        });

        // Return the first coordinate (easternmost)
        return $coordinates[0];
    }

    private function buildPolygons(array &$segments): array {
        if (empty($segments)) {
            return [];
        }

        $segment = array_pop($segments);
        $isRight = end($segment)[0] === 180;
        $candidates = [];

        if ($this->isSelfClosing($segment)) {
            // Self-closing segments might end up joining up with themselves.
            $candidates[] = [null, $segment[0][1]];
        }

        foreach ($segments as $i => $s) {
            // Is the start of $s on the same side as the end of $segment?
            if ($s[0][0] === end($segment)[0]) {
                if (
                    ($isRight && $s[0][1] > end($segment)[1] && 
                    (!$this->isSelfClosing($s) || end($s)[1] < $segment[0][1])) ||
                    (!$isRight && $s[0][1] < end($segment)[1] && 
                    (!$this->isSelfClosing($s) || end($s)[1] > $segment[0][1]))
                ) {
                    $candidates[] = [$i, $s[0][1]];
                }
            }
        }

        // Sort the candidates by latitude
        usort($candidates, function ($a, $b) use ($isRight) {
            if ($isRight) {
                return $b[1] <=> $a[1];
            } else {
                return $a[1] <=> $b[1];
            }
        });

        $index = $candidates[0][0] ?? null;

        if ($index !== null) {
            // Join the segments, then re-add them to the list and recurse.
            $segment = array_merge($segment, array_splice($segments, $index, 1)[0]);
            $segments[] = $segment;
            return $this->buildPolygons($segments);
        } else {
            // Build the rest of the polygons without this segment.
            $polygons = $this->buildPolygons($segments);

            // Check if all points in the segment are identical
            $isIdentical = true;
            foreach ($segment as $point) {
                if ($point !== $segment[0]) {
                    $isIdentical = false;
                    break;
                }
            }

            if (!$isIdentical) {
                $polygons[] = new Polygon($segment);
            }

            return $polygons;
        }
    }

    private function isSelfClosing(array $segment): bool {
        $isRight = $segment[count($segment) - 1][0] == 180;

        return $segment[0][0] == $segment[count($segment) - 1][0] && (
            ($isRight && $segment[0][1] > $segment[count($segment) - 1][1]) ||
            (!$isRight && $segment[0][1] < $segment[count($segment) - 1][1])
        );
    }

    private function normalize(array $coords): array {
        $original = $coords;
        $allAreOnAntimeridian = true;
        foreach ($coords as $i => &$point) {
            $longitude = $point[0];
            $latitude = $point[1];
            
            // Check if the longitude is close to 180
            if ($this->isClose($longitude, 180)) {
                if (abs($latitude) != 90 && $this->isClose($coords[($i - 1 + count($coords)) % count($coords)][0], -180)) {
                    $point[0] = -180;
                } else {
                    $point[0] = 180;
                }
            }
            // Check if the longitude is close to -180
            elseif ($this->isClose($longitude, -180)) {
                if (abs($latitude) != 90 && $this->isClose($coords[($i - 1 + count($coords)) % count($coords)][0], 180)) {
                    $point[0] = 180;
                } else {
                    $point[0] = -180;
                }
            } 
            // Normalize the longitude to be within -180 and 180
            else {
                $point[0] = $this->modulo($longitude + 180, 360) - 180;
                $allAreOnAntimeridian = false;
            }

            // If the point has more than 2 elements, preserve additional dimensions
            if (count($point) > 2) {
                $point = array_merge([$point[0], $point[1]], array_slice($point, 2));
            }
        }
        unset($point); // Unset reference after foreach

        if ($allAreOnAntimeridian) {
            return $original;
        } else {
            return $coords;
        }
    }

    /**
     * Helper function to determine if two numbers are close within a small tolerance.
     *
     * @param float $a First number.
     * @param float $b Second number.
     * @param float $tol Tolerance (default is 1e-9).
     * @return bool True if the numbers are close, false otherwise.
     */
    private function isClose(float $a, float $b, float $tol = 1e-9): bool {
        return abs($a - $b) < $tol;
    }

    /**
     * Convert spherical degrees to Cartesian coordinates.
     *
     * @param array $point An array [longitude, latitude].
     * @return array An array [x, y, z].
     */
    private function sphericalDegreesToCartesian(array $point): array {
        [$lon, $lat] = array_map('deg2rad', $point);
        return [
            cos($lon) * cos($lat),
            sin($lon) * cos($lat),
            sin($lat)
        ];
    }

    /**
     * Calculate the latitude of the crossing point on a great circle.
     *
     * @param array $start An array [longitude, latitude].
     * @param array $end An array [longitude, latitude].
     * @return float The crossing latitude.
     */
    private function crossingLatitudeGreatCircle(array $start, array $end): float {
        $p1 = $this->sphericalDegreesToCartesian($start);
        $p2 = $this->sphericalDegreesToCartesian($end);

        // Cross product of the two vectors.
        $n1 = $this->crossProduct($p1, $p2);

        // Meridian plane unit vector.
        $n2 = [0, -1, 0];

        // Intersection of both planes.
        $intersection = $this->crossProduct($n1, $n2);
        $norm = sqrt(array_sum(array_map(fn($x) => $x ** 2, $intersection)));

        // Normalize the intersection vector.
        $intersection = array_map(fn($x) => $x / $norm, $intersection);

        // Return the latitude (arcsin of z-coordinate).
        return round(rad2deg(asin($intersection[2])), $this->roundPrecision);
    }

    /**
     * Calculate the latitude of the crossing point using a flat approximation.
     *
     * @param array $start An array [longitude, latitude].
     * @param array $end An array [longitude, latitude].
     * @return float The crossing latitude.
     */
    private function crossingLatitudeFlat(array $start, array $end): float {
        $latitudeDelta = $end[1] - $start[1];

        if ($end[0] > 0) {
            return round(
                $start[1] + (180.0 - $start[0]) * $latitudeDelta / ($end[0] + 360.0 - $start[0]),
                $this->roundPrecision
            );
        } else {
            return round(
                $start[1] + ($start[0] + 180.0) * $latitudeDelta / ($start[0] + 360.0 - $end[0]),
                $this->roundPrecision
            );
        }
    }

    /**
     * Calculate the crossing latitude for a given start and end point.
     *
     * @param array $start An array [longitude, latitude].
     * @param array $end An array [longitude, latitude].
     * @param bool $greatCircle Whether to use great circle calculations.
     * @return float The crossing latitude.
     */
    private function crossingLatitude(array $start, array $end, bool $greatCircle): float {
        if (abs($start[0]) == 180) {
            return $start[1];
        } elseif (abs($end[0]) == 180) {
            return $end[1];
        }

        if ($greatCircle) {
            return $this->crossingLatitudeGreatCircle($start, $end);
        }
        return $this->crossingLatitudeFlat($start, $end);
    }

    /**
     * Calculate the cross product of two 3D vectors.
     *
     * @param array $v1 The first vector [x, y, z].
     * @param array $v2 The second vector [x, y, z].
     * @return array The cross product [x, y, z].
     */
    private function crossProduct(array $v1, array $v2): array {
        return [
            $v1[1] * $v2[2] - $v1[2] * $v2[1],
            $v1[2] * $v2[0] - $v1[0] * $v2[2],
            $v1[0] * $v2[1] - $v1[1] * $v2[0]
        ];
    }

    private function extendOverPoles(array $segments, bool $forceNorthPole, bool $forceSouthPole, bool $fixWinding): array {
        $leftStart = null;
        $rightStart = null;
        $leftEnd = null;
        $rightEnd = null;

        foreach ($segments as $i => $segment) {
            if ($segment[0][0] === -180 && 
                ($leftStart === null || $segment[0][1] < $leftStart->latitude)) {
                $leftStart = new IndexAndLatitude($i, $segment[0][1]);
            } elseif ($segment[0][0] === 180 && 
                    ($rightStart === null || $segment[0][1] > $rightStart->latitude)) {
                $rightStart = new IndexAndLatitude($i, $segment[0][1]);
            }
            if ($segment[count($segment) - 1][0] === -180 && 
                ($leftEnd === null || $segment[count($segment) - 1][1] < $leftEnd->latitude)) {
                $leftEnd = new IndexAndLatitude($i, $segment[count($segment) - 1][1]);
            } elseif ($segment[count($segment) - 1][0] === 180 && 
                    ($rightEnd === null || $segment[count($segment) - 1][1] > $rightEnd->latitude)) {
                $rightEnd = new IndexAndLatitude($i, $segment[count($segment) - 1][1]);
            }
        }

        $isOverNorthPole = false;
        $isOverSouthPole = false;
        $originalSegments = json_decode(json_encode($segments), true); // Deep copy

        if ($leftEnd !== null) {
            if (
                ($forceNorthPole && !$forceSouthPole) &&
                $rightEnd === null &&
                ($leftStart === null || $leftEnd->latitude > $leftStart->latitude)
            ) {
                $isOverNorthPole = true;
                $segments[$leftEnd->index][] = [-180, 90];
                $segments[$leftEnd->index][] = [180, 90];
                $segments[$leftEnd->index] = array_reverse($segments[$leftEnd->index]);
            } elseif (
                $forceSouthPole || 
                $leftStart === null || 
                $leftEnd->latitude < $leftStart->latitude
            ) {
                $isOverSouthPole = true;
                $segments[$leftEnd->index][] = [-180, -90];
                $segments[$leftEnd->index][] = [180, -90];
            }
        }

        if ($rightEnd !== null) {
            if (
                ($forceSouthPole && !$forceNorthPole) &&
                ($rightStart === null || $rightEnd->latitude < $rightStart->latitude)
            ) {
                $isOverSouthPole = true;
                $segments[$rightEnd->index][] = [180, -90];
                $segments[$rightEnd->index][] = [-180, -90];
                $segments[$rightEnd->index] = array_reverse($segments[$rightEnd->index]);
            } elseif (
                $forceNorthPole || 
                $rightStart === null || 
                $rightEnd->latitude > $rightStart->latitude
            ) {
                $isOverNorthPole = true;
                $segments[$rightEnd->index][] = [180, 90];
                $segments[$rightEnd->index][] = [-180, 90];
            }
        }

        if ($fixWinding && $isOverNorthPole && $isOverSouthPole) {
            if ($forceNorthPole || $forceSouthPole) {
                throw new Exception('Invalid state: Both poles are forced while fixing winding is enabled');
            }

            error_log('Warning: Fixing winding order because the polygon extends over both poles');
            foreach ($originalSegments as &$segment) {
                $segment = array_reverse($segment);
            }
            return $originalSegments;
        }

        return $segments;
    }

    /**
     * Modulo function that retains decimal precision.
     *
     * @param float $dividend The number to be divided.
     * @param float $divisor The number by which the dividend is divided.
     * @return float The remainder after division.
     */
    function modulo(float $dividend, float $divisor): float
    {
        if ($divisor == 0.0) {
            throw new InvalidArgumentException("Divisor cannot be zero.");
        }

        $remainder = fmod($dividend, $divisor);

        // To ensure the remainder is always positive:
        if ($remainder < 0) {
            $remainder += abs($divisor);
        }

        return $remainder;
    }


}



