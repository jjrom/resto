<?php

class LineString {
    private $coordinates; // Array of coordinates representing the line

    /**
     * Constructor for the LineString class.
     *
     * @param array $geoJsonGeometry A GeoJSON geometry object of type "LineString".
     * @throws Exception If the GeoJSON geometry is invalid or not of type "LineString".
     */
    public function __construct(array $geoJsonGeometry) {
        if (!isset($geoJsonGeometry['type']) || $geoJsonGeometry['type'] !== 'LineString') {
            throw new Exception('Invalid GeoJSON: Must be of type LineString');
        }

        if (!isset($geoJsonGeometry['coordinates']) || !is_array($geoJsonGeometry['coordinates'])) {
            throw new Exception('Invalid GeoJSON: Missing or invalid coordinates field');
        }

        $this->coordinates = $geoJsonGeometry['coordinates'];

        // A valid LineString must have at least two coordinates
        if (count($this->coordinates) < 2) {
            throw new Exception('Invalid GeoJSON: LineString must have at least two coordinates');
        }
    }

    public function toGeoJSON(): array {
        return [
            'type' => $this->getType(),
            'coordinates' => $this->getCoordinates()
        ];
    }

    /**
     * Get the coordinates of the LineString.
     *
     * @return array An array of coordinates representing the line.
     */
    public function getCoordinates(): array {
        return $this->coordinates;
    }

    /**
     * Check if the LineString crosses the antimeridian.
     *
     * @return bool True if any segment of the LineString crosses the antimeridian.
     */
    public function isCoincidentToAntimeridian(): bool {
        for ($i = 0; $i < count($this->coordinates) - 1; $i++) {
            $start = $this->coordinates[$i];
            $end = $this->coordinates[$i + 1];

            if (abs($start[0]) == 180 && $start[0] == $end[0]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check the orientation of the LineString.
     *
     * @return bool Always returns true since LineStrings have no "orientation" like polygons.
     */
    public function checkOrientation(): bool {
        return true; // LineStrings don't have the concept of orientation (clockwise or counterclockwise)
    }

    /**
     * Correct the orientation of the LineString.
     * Since LineStrings do not have orientation to correct, this method does nothing.
     */
    public function correctOrientation(): void {
        // LineStrings don't have orientation like Polygons, so there's nothing to correct
    }

    /**
     * Check if the LineString is "counter clockwise".
     * LineStrings don't have a defined clockwise or counterclockwise orientation.
     * This method is here for consistency, but it will always return false for LineStrings.
     *
     * @return bool False for LineString, as orientation doesn't apply.
     */
    public function isCCW(): bool {
        return false;
    }

    /**
     * Get the type of the geometry (Polygon, MultiPolygon, LineString, MultiLineString).
     *
     * @return string The geometry type.
     */
    public function getType(): string {
        return 'LineString';
    }

}
