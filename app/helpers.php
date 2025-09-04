<?php

namespace app;

use App\Models\AcceptanceGrade;
use App\Models\Location;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

function imgCoordToQuadrantCoordX($resolution, $imgX, $originX): float|int {
    return ($imgX * $resolution) + $originX;
}

function imgCoordToQuadrantCoordY($resolution, $imgY, $imgHeight, $originY): float|int {
    return (($imgHeight - $imgY) * $resolution) + $originY;
}

function quadrantCoordToImgCoordX($resolution, $x, $originX): float|int {
    return ($x - $originX) / $resolution;
}

function quadrantCoordToImgCoordY($resolution, $y, $imgHeight, $originY): float|int {
    return $imgHeight - (($y - $originY) / $resolution);
}

if(!function_exists('is_json')) {
    function is_json(?string $json_string = ""): bool {
        return is_string($json_string) && is_array(json_decode($json_string, true)) && (json_last_error(
            ) == JSON_ERROR_NONE) ? true : false;
    }
}

function randomColor(): string {
    mt_srand((double)microtime() * 1000000);
    $c = '';
    while(strlen($c) < 6) {
        $c .= sprintf("%02X", mt_rand(0, 255));
    }
    return $c;
}

function getColor(Collection $pollutionConditions, AcceptanceGrade $acceptanceGrade, $value): ?string {
    $pollutionConditions = $pollutionConditions->sortBy('id')->values();
    $pollutionCondition = null;
    $pollutionConditionNames = $pollutionConditions->pluck('name');
    $condition1 = false;
    foreach($pollutionConditionNames as $i => $pollutionConditionName) {
        if($i == 0) {
            $condition1 = true;
        } else {
            if($acceptanceGrade->{$pollutionConditionNames[$i - 1]}) {
                $condition1 = $value <= $acceptanceGrade->{$pollutionConditionNames[$i - 1]};
            }
        }
        if($i == count($pollutionConditionNames) - 1) {
            $condition2 = true;
        } else {
            if($acceptanceGrade->{$pollutionConditionName}) {
                $condition2 = $value > $acceptanceGrade->{$pollutionConditionName};
            } else {
                continue;
            }
        }
        if($condition1 && $condition2) {
            $pollutionCondition = $pollutionConditions->where('name', $pollutionConditionName)->first();
            break;
        }
    }
    return $pollutionCondition ? $pollutionCondition->color : null;
}

function getClosestLocation($targetX, $targetY, $mapId) {
    // 1. 获取所有点位
    $locations = Location::where('map_id', $mapId)->get();

    $closestLocation = null;
    $shortestDistance = PHP_INT_MAX;

    // 2. 遍历所有点位并计算距离
    foreach($locations as $location) {
        $distance = sqrt(pow($location->x - $targetX, 2) + pow($location->y - $targetY, 2));

        // 3. 如果距离小于一公尺且比当前最短距离更短，更新最近的点位
        if($distance < 1000 && $distance < $shortestDistance) {
            $shortestDistance = $distance;
            $closestLocation = $location;
        }
    }

    return $closestLocation;
}

function equalModel(Eloquent|Model $record1, Eloquent|Model $record2, $filterColumns = ['id']): bool {
    $record1 = $record1->toArray();
    $record2 = $record2->toArray();

// 排除不需要比較的欄位
    foreach($filterColumns as $filterColumn) {
        unset($record1[$filterColumn]);
        unset($record2[$filterColumn]);
    }

    // 對 JSON 欄位進行排序
    $record1 = normalizeAndSortJson($record1);
    $record2 = normalizeAndSortJson($record2);

// 對數組進行排序
    ksort($record1);
    ksort($record2);

// 比較哈希值
    if(hash('sha256', json_encode($record1)) === hash('sha256', json_encode($record2))) {
        return true;
    } else {
        return false;
    }
}

function normalizeAndSortJson($data) {
    if(is_array($data)) {
        foreach($data as $key => &$value) {
            if(is_numeric($value)) {
                // 將數值轉換為浮點數
                $value = (float)$value;
            } elseif(is_string($value) && isJson($value)) {
                // 對 JSON 字符串進行標準化處理
                $decodedJson = json_decode($value, true);
                if(json_last_error() === JSON_ERROR_NONE) {
                    $value = json_encode(normalizeAndSortJson($decodedJson));
                }
            } else {
                $value = normalizeAndSortJson($value);
            }
        }
        ksort($data);
    }
    return $data;
}

function isJson($string): bool {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function getCarbon($finished): ?Carbon {
    $finishedTime = $finished ?? null;
    if($finishedTime) {
        $finishedTime = Carbon::parse($finishedTime);
    }
    return $finishedTime;
}
