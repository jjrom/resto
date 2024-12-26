<?php

class IndexAndLatitude {
    public int $index;
    public float $latitude;

    public function __construct(int $index, float $latitude) {
        $this->index = $index;
        $this->latitude = $latitude;
    }
}
