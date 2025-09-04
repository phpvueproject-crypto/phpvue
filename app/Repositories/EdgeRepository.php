<?php

namespace App\Repositories;

class EdgeRepository {
    public function getName($startVertexName, $endVertexName): string {
        return "({$startVertexName},{$endVertexName})";
    }
}
