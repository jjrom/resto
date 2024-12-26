<?php

class LinearRing
{
    private array $coordinates;

    /**
     * Constructor for LinearRing
     *
     * @param array $coordinates List of coordinates as [[x, y], [x, y], ...]
     * @throws InvalidArgumentException if the coordinates are invalid.
     */
    public function __construct(array $coordinates)
    {
        if (!$this->isValidLinearRing($coordinates)) {
            throw new InvalidArgumentException('Invalid LinearRing: must be closed and have at least 4 points.');
        }
        $this->coordinates = $coordinates;
    }

    /**
     * Get the coordinates of the LinearRing.
     *
     * @return array The list of coordinates.
     */
    public function getCoordinates(): array
    {
        return $this->coordinates;
    }

    /**
     * Check if the LinearRing is closed (first and last points are the same).
     *
     * @return bool True if closed, otherwise false.
     */
    public function isClosed(): bool
    {
        return $this->coordinates[0] === end($this->coordinates);
    }

    /**
     * Check if the LinearRing has a counter-clockwise orientation.
     *
     * @return bool True if counter-clockwise, otherwise false.
     */
    public function isCCW(): bool
    {
        $area = 0.0;
        $count = count($this->coordinates);

        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $this->coordinates[$i];
            $p2 = $this->coordinates[$i + 1];
            $area += ($p2[0] - $p1[0]) * ($p2[1] + $p1[1]);
        }

        return $area < 0; // Counter-clockwise if area is negative.
    }

    /**
     * Reverse the coordinates of the LinearRing.
     *
     * @return void
     */
    public function reverse(): void
    {
        $this->coordinates = array_reverse($this->coordinates);
    }

    /**
     * Validate that the coordinates form a valid LinearRing.
     *
     * @param array $coordinates The coordinates to validate.
     * @return bool True if valid, otherwise false.
     */
    private function isValidLinearRing(array $coordinates): bool
    {
        if (count($coordinates) < 4) {
            return false;
        }

        return $coordinates[0] === end($coordinates); // Must be closed.
    }

    /**
     * Normalize the coordinates of the LinearRing to ensure longitudes are within [-180, 180].
     *
     * @return void
     */
    public function normalize(): void
    {
        foreach ($this->coordinates as &$point) {
            $point[0] = (($point[0] + 180) % 360) - 180; // Normalize longitude.
        }
    }

    /**
     * Get the bounding box of the LinearRing.
     *
     * @return array Bounding box as [minX, minY, maxX, maxY].
     */
    public function getBoundingBox(): array
    {
        $minX = $minY = PHP_FLOAT_MAX;
        $maxX = $maxY = PHP_FLOAT_MIN;

        foreach ($this->coordinates as $point) {
            $minX = min($minX, $point[0]);
            $minY = min($minY, $point[1]);
            $maxX = max($maxX, $point[0]);
            $maxY = max($maxY, $point[1]);
        }

        return [$minX, $minY, $maxX, $maxY];
    }
}
