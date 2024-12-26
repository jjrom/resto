<?php

class Polygon {

    private $exteriorRing; // Array of coordinates representing the exterior ring
    private $interiorRings = []; // Array of arrays, each representing an interior ring

   /**
    * Determine if a ring is oriented clockwise.
    *
    * @param array $ring The ring to check.
    * @return bool True if the ring is clockwise, false otherwise.
    */
    public static function isClockwise(array $ring): bool {
       $sum = 0;
       $n = count($ring);

       for ($i = 0; $i < $n - 1; $i++) {
           $p1 = $ring[$i];
           $p2 = $ring[$i + 1];
           $sum += ($p2[0] - $p1[0]) * ($p2[1] + $p1[1]);
       }

       return $sum > 0;
   }

    public function __construct(array $geoJsonGeometryOrSegment) {
        
        if (array_is_list($geoJsonGeometryOrSegment)) {
            $geoJsonGeometryOrSegment[] = $geoJsonGeometryOrSegment[0];
            $this->exteriorRing = $geoJsonGeometryOrSegment;
        }
        else {

            if (!isset($geoJsonGeometryOrSegment['type']) || $geoJsonGeometryOrSegment['type'] !== 'Polygon') {
                throw new Exception('Invalid GeoJSON: Must be of type Polygon');
            }

            if (!isset($geoJsonGeometryOrSegment['coordinates']) || !is_array($geoJsonGeometryOrSegment['coordinates'])) {
                throw new Exception('Invalid GeoJSON: Missing or invalid coordinates field');
            }

            $coordinates = $geoJsonGeometryOrSegment['coordinates'];

            if (empty($coordinates[0]) || !$this->isValidRing($coordinates[0])) {
                throw new Exception('Invalid GeoJSON: Exterior ring must be a closed linear ring with at least 4 coordinates');
            }
            $this->exteriorRing = $coordinates[0];

            for ($i = 1; $i < count($coordinates); $i++) {
                if (!$this->isValidRing($coordinates[$i])) {
                    throw new Exception('Invalid GeoJSON: Interior ring at index $i must be a closed linear ring with at least 4 coordinates');
                }
                $this->interiorRings[] = $coordinates[$i];
            }
        }
    }

    public function toGeoJSON(): array {

        return [
            'type' => $this->getType(),
            'coordinates' => $this->getCoordinates()
        ];
    }

    public function getExteriorRing(): array {
        return $this->exteriorRing;
    }

    public function getInteriorRings(): array {
        return $this->interiorRings;
    }

    public function setExteriorRing($exteriorRing) {
        $this->exteriorRing = $exteriorRing;
    }

    public function setInteriorRings($interiorRings) {
        $this->interiorRings = $interiorRings;
    }

    private function isValidRing(array $ring): bool {
        return count($ring) >= 4 && $ring[0] === end($ring);
    }

    public function getCoordinates(): array {
        $coordinates = [
            $this->exteriorRing
        ];
        if ( !empty($this->interiorRings) ) {
            $coordinates[] = $this->interiorRings;
        }
        return $coordinates;
    }

    public function isCoincidentToAntimeridian(): bool {
        if ($this->checkRingCoincidence($this->exteriorRing)) {
            return true;
        }

        foreach ($this->interiorRings as $ring) {
            if ($this->checkRingCoincidence($ring)) {
                return true;
            }
        }

        return false;
    }

    private function checkRingCoincidence(array $ring): bool {
        for ($i = 0; $i < count($ring) - 1; $i++) {
            $start = $ring[$i];
            $end = $ring[$i + 1];

            if (abs($start[0]) == 180 && $start[0] == $end[0]) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the orientation of the rings is valid.
     *
     * @return bool True if the exterior ring is CCW and interior rings are CW.
     */
    public function checkOrientation(): bool {
        if (Polygon::isClockwise($this->exteriorRing)) {
            return false;
        }

        foreach ($this->interiorRings as $ring) {
            if (!Polygon::isClockwise($ring)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Correct the orientation of the rings.
     * Ensures the exterior ring is CCW and interior rings are CW.
     */
    public function correctOrientation(): void {
        if (Polygon::isClockwise($this->exteriorRing)) {
            $this->exteriorRing = array_reverse($this->exteriorRing);
        }

        foreach ($this->interiorRings as &$ring) {
            if (!Polygon::isClockwise($ring)) {
                $ring = array_reverse($ring);
            }
        }
    }

    /**
     * Checks if a given ring is contained within the polygon's exterior.
     *
     * @param LinearRing $ring The ring to check for containment.
     * @return bool True if the ring is contained within the polygon's exterior, false otherwise.
     */
    public function contains($ring): bool
    {
        // We assume the $ring is a LinearRing object and we check if all its coordinates are within the exterior ring.
        foreach ($ring->getCoordinates() as $point) {
            if (!$this->isPointInsideExterior($point)) {
                return false;  // Return false if any point of the interior ring is outside the exterior.
            }
        }
        return true;  // Return true if all points of the interior ring are inside the exterior.
    }

    /**
     * Get the type of the geometry (Polygon, MultiPolygon, LineString, MultiLineString).
     *
     * @return string The geometry type.
     */
    public function getType(): string {
        return 'Polygon';
    }

    /**
     * Helper function to check if a point is inside the polygon's exterior.
     * This is a basic implementation of point-in-polygon.
     *
     * @param array $point The point to check, given as an array [longitude, latitude].
     * @return bool True if the point is inside the exterior, false otherwise.
     */
    private function isPointInsideExterior(array $point): bool
    {
        // Using a simple ray-casting algorithm for point-in-polygon test
        $coords = $this->getExteriorRing();
        $x = $point[0];
        $y = $point[1];
        $inside = false;

        for ($i = 0, $j = count($coords) - 1; $i < count($coords); $j = $i++) {
            $xi = $coords[$i][0];
            $yi = $coords[$i][1];
            $xj = $coords[$j][0];
            $yj = $coords[$j][1];

            if (($yi > $y) != ($yj > $y) &&
                $x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi) {
                $inside = !$inside;
            }
        }

        return $inside;
    }

}
