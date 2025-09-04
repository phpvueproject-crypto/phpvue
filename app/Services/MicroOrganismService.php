<?php

namespace App\Services;

use App\Repositories\MicroOrganismRepository;

class MicroOrganismService {
    public function __construct(protected MicroOrganismRepository $repo) { }

    public function getFilteredMicroOrganisms(array $params) {
        return $this->repo->filterWithLocation($params);
    }
}
