<?php

class MultiLineString {
    private $lineStrings; // Array of LineString objects

    /**
     * Constructor for the MultiLineString class.
     *
     * @param array $geoJsonGeometry A GeoJSON geometry object of type "MultiLineString".
     * @throws Exception If the GeoJSON geometry is invalid or not of type "MultiLineString".
     */
    public function __construct(array $geoJsonGeometryOrLineStringsArray) {
        
        if (array_is_list($geoJsonGeometryOrLineStringsArray)) {
            for ($i = 0, $ii = count($geoJsonGeometryOrLineStringsArray); $i < $ii; $i++) {
                if ( !is_a($geoJsonGeometryOrLineStringsArray[$i], 'LineString') ) {
                    throw new Exception('Invalid array of LineStrings: One entry is not a valid LineString');
                }
            }    
            $this->lineStrings = $geoJsonGeometryOrLineStringsArray;
        }
        else {
                
            if (!isset($geoJsonGeometryOrLineStringsArray['type']) || $geoJsonGeometryOrLineStringsArray['type'] !== 'MultiLineString') {
                throw new Exception('Invalid GeoJSON: Must be of type MultiLineString');
            }

            if (!isset($geoJsonGeometryOrLineStringsArray['coordinates']) || !is_array($geoJsonGeometryOrLineStringsArray['coordinates'])) {
                throw new Exception('Invalid GeoJSON: Missing or invalid coordinates field');
            }

            $this->lineStrings = [];

            foreach ($geoJsonGeometryOrLineStringsArray['coordinates'] as $coordinates) {
                $lineStringGeoJson = [
                    'type' => 'LineString',
                    'coordinates' => $coordinates
                ];
                $this->lineStrings[] = new LineString($lineStringGeoJson);
            }
        }
    }

    public function toGeoJSON(): array {
        $coordinates = [];
        for ($i = 0, $ii = count($this->lineStrings); $i < $ii; $i++) {
            $coordinates[] = $this->lineStrings[$i]->getCoordinates();
        }
        return [
            'type' => $this->getType(),
            'coordinates' => $coordinates
        ];
    }

    /**
     * Get all LineStrings in the MultiLineString.
     *
     * @return array An array of LineString objects.
     */
    public function getLineStrings(): array {
        return $this->lineStrings;
    }

    /**
     * Check if any line in the MultiLineString crosses the antimeridian.
     *
     * @return bool True if any line crosses the antimeridian.
     */
    public function isCoincidentToAntimeridian(): bool {
        foreach ($this->lineStrings as $lineString) {
            if ($lineString->isCoincidentToAntimeridian()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Correct the orientation of all LineStrings in the MultiLineString.
     * LineStrings don't have an orientation to correct, so this method does nothing.
     */
    public function correctOrientation(): void {
        // LineStrings do not have an orientation like Polygons, so nothing to correct
    }

    /**
     * Check the orientation of all LineStrings in the MultiLineString.
     * Since LineStrings do not have orientation, this always returns true.
     *
     * @return bool Always true for MultiLineString, as LineStrings do not have orientation.
     */
    public function checkOrientation(): bool {
        return true; // LineStrings don't have orientation like Polygons
    }

    /**
     * Get the type of the geometry (Polygon, MultiPolygon, LineString, MultiLineString).
     *
     * @return string The geometry type.
     */
    public function getType(): string {
        return 'MultiLineString';
    }
}
