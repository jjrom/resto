<?php

class MultiPolygon {
    private $polygons; // Array of Polygon objects

    /**
     * Constructor for the MultiPolygon class.
     *
     * @param array $geoJsonGeometryOrPolygonsArray A GeoJSON geometry object of type "MultiPolygon" or an array of Polygon objects.
     * @throws Exception If the GeoJSON geometry is invalid or not of type "MultiPolygon".
     */
    public function __construct(array $geoJsonGeometryOrPolygonsArray) {

        if (array_is_list($geoJsonGeometryOrPolygonsArray)) {
            for ($i = 0, $ii = count($geoJsonGeometryOrPolygonsArray); $i < $ii; $i++) {
                if ( !is_a($geoJsonGeometryOrPolygonsArray[$i], 'Polygon') ) {
                    throw new Exception('Invalid array of polygons: One entry is not a valid Polygon');
                }
            }    
            $this->polygons = $geoJsonGeometryOrPolygonsArray;
        }
        else {
            if (!isset($geoJsonGeometryOrPolygonsArray['type']) || $geoJsonGeometryOrPolygonsArray['type'] !== 'MultiPolygon') {
                throw new Exception('Invalid GeoJSON: Must be of type MultiPolygon');
            }
    
            if (!isset($geoJsonGeometryOrPolygonsArray['coordinates']) || !is_array($geoJsonGeometryOrPolygonsArray['coordinates'])) {
                throw new Exception('Invalid GeoJSON: Missing or invalid coordinates field');
            }
    
            $this->polygons = [];
    
            foreach ($geoJsonGeometryOrPolygonsArray['coordinates'] as $coordinates) {
                $polygonGeoJson = [
                    'type' => 'Polygon',
                    'coordinates' => $coordinates
                ];
                $this->polygons[] = new Polygon($polygonGeoJson);
            }
        }
        
    }

    public function toGeoJSON(): array {
        $coordinates = [];
        for ($i = 0, $ii = count($this->polygons); $i < $ii; $i++) {
            $coordinates[] = $this->polygons[$i]->getCoordinates();
        }
        return [
            'type' => $this->getType(),
            'coordinates' => $coordinates
        ];
    }

    /**
     * Get all polygons in the MultiPolygon.
     *
     * @return array An array of Polygon objects.
     */
    public function getPolygons(): array {
        return $this->polygons;
    }

    /**
     * Check if any polygon in the MultiPolygon crosses the antimeridian.
     *
     * @return bool True if any polygon crosses the antimeridian.
     */
    public function isCoincidentToAntimeridian(): bool {
        foreach ($this->polygons as $polygon) {
            if ($polygon->isCoincidentToAntimeridian()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Correct the orientation of all polygons in the MultiPolygon.
     */
    public function correctOrientation(): void {
        foreach ($this->polygons as $polygon) {
            $polygon->correctOrientation();
        }
    }

    /**
     * Check the orientation of all polygons in the MultiPolygon.
     *
     * @return bool True if all polygons are correctly oriented.
     */
    public function checkOrientation(): bool {
        foreach ($this->polygons as $polygon) {
            if (!$polygon->checkOrientation()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the type of the geometry (Polygon, MultiPolygon, LineString, MultiLineString).
     *
     * @return string The geometry type.
     */
    public function getType(): string {
        return 'MultiPolygon';
    }
}
