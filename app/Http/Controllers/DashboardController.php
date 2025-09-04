<?php

namespace App\Http\Controllers;

use App\Models\ChartColor;
use App\Models\Location;
use App\Models\MicroOrganism;
use App\Services\MicroOrganismService;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Mccarlosen\LaravelMpdf\LaravelMpdf;
use Mpdf\MpdfException;
use PDF;
use Storage;
use Symfony\Component\HttpFoundation\Response;
use function app\randomColor;

class DashboardController extends Controller {
    public function __construct(protected MicroOrganismService $service) { }

    public function chart1(Request $request): array {
        $filters = $request->only(['organism_kinds', 'room_names', 'start_date', 'end_date']);
        $microOrganisms = $this->service->getFilteredMicroOrganisms($filters);

        $locations = $microOrganisms->pluck('location')->unique('id');
        $labels = [];
        $dataByOrganismKind = [];
        $organismKinds = $filters['organism_kinds'] ?? [];
        /** @var Location $location */
        foreach($locations as $location) {
            $labels[] = $location->device_name;
            foreach($organismKinds as $organismKind) {
                $dataByOrganismKind[$organismKind][$location->id] = $microOrganisms->where(function(MicroOrganism $microOrganism) use ($organismKind, $location) {
                    return $microOrganism->organism_kind == $organismKind && $microOrganism->location_id == $location->id;
                })->values()->sum('organism_value');
            }
        }
        $chartColors = ChartColor::whereIn('label', [
            'suspended',
            'falling',
            'contact',
            'microparticle_dot_5',
            'microparticle_5'
        ])->get();
        $datasets = collect($dataByOrganismKind)->map(function($r, $organismKind) use ($chartColors, $labels) {
            $organismKindNames = [
                'suspended'           => '懸浮微生物',
                'falling'             => '落下微生物',
                'contact'             => '接觸微生物',
                'microparticle_dot_5' => '0.5µm',
                'microparticle_5'     => '5µm'
            ];
            $organismKindName = $organismKindNames[$organismKind];

            $chartColor = $this->saveColor($chartColors, $organismKind);
            return [
                'label'           => $organismKindName,
                'data'            => collect($r)->values()->sortBy(function($r, $i) use ($labels) {
                    return $labels[$i];
                })->values()->all(),
                'backgroundColor' => $chartColor->color,
                'chartColorId'    => $chartColor->id,
                'borderColor'     => '#000000'
            ];
        })->values()->toArray();

        $cultureResult = [
            'labels'   => collect($labels)->sort()->values()->all(),
            'datasets' => $datasets
        ];

        return [
            'status' => 0,
            'data'   => [
                'cultureResult' => $cultureResult
            ]
        ];
    }

    public function chart2(Request $request): array {
        $filters = $request->only(['organism_kinds', 'room_names', 'start_date', 'end_date']);
        $microOrganisms = $this->service->getFilteredMicroOrganisms($filters);

        /** @var Collection $microOrganisms */
        $dates = $microOrganisms->map(function(MicroOrganism $microOrganism) {
            return $microOrganism->Time->format('Y-m-d');
        })->unique()->sort()->values();

        $locations = $microOrganisms->pluck('location')->unique('id');
        $labels = [];
        $data = [];
        /** @var Location $location */
        foreach($locations as $location) {
            $labels[] = $location->device_name;
            foreach($dates as $date) {
                $data[$date][$location->id] = $microOrganisms->where(function(MicroOrganism $microOrganism) use ($date, $location) {
                    return $microOrganism->Time->format('Y-m-d') == $date && $microOrganism->location_id == $location->id;
                })->values()->sum('organism_value');
            }
        }
        $chartColors = ChartColor::whereIn('label', collect($dates)->map(function($date) {
            $date = new Carbon($date);
            return $date->format('m/d');
        }))->get();

        $datasets = collect($data)->map(function($r, $date) use ($chartColors, $labels) {
            $date = new Carbon($date);
            $date = $date->format('m/d');
            $chartColor = $this->saveColor($chartColors, $date);

            return [
                'label'           => $date,
                'data'            => collect($r)->values()->sortBy(function($r, $i) use ($labels) {
                    return $labels[$i];
                })->values()->all(),
                'backgroundColor' => $chartColor->color,
                'chartColorId'    => $chartColor->id,
                'borderColor'     => '#000000'
            ];
        })->values()->toArray();

        $informationResult = [
            'labels'   => collect($labels)->sort()->values()->all(),
            'datasets' => $datasets
        ];

        return [
            'status' => 0,
            'data'   => [
                'informationResult' => $informationResult
            ]
        ];
    }

    private function saveColor(Collection $chartColors, $label): ChartColor {
        /** @var ChartColor $chartColor */
        $chartColor = $chartColors->where('label', $label)->first();
        if(!$chartColor) {
            $color = '#' . randomColor();
            $chartColor = new ChartColor();
            $chartColor->label = $label;
            $chartColor->color = $color;
            $chartColor->save();
        }

        return $chartColor;
    }

    /**
     * @throws MpdfException
     */
    public function downloadChart(Request $request): ?Response {
        $filename = $request->input('filename');
        $chartName = $request->input('chart_name');
        $file = Storage::get("charts/$filename");
        $data = [
            'chart' => base64_encode($file)
        ];

        /** @var LaravelMpdf $pdf */
        $pdf = PDF::loadView('pdf', $data, [], [
            'title' => $chartName
        ]);
        File::delete(storage_path("app/charts/$filename"));
        return $pdf->download($chartName . '.pdf');
    }

    public function uploadChart(Request $request): array {
        $time = time();
        $filename = "chart_{$time}.png";
        $request->file('file')->storeAs('charts', $filename);

        return [
            'status' => 0,
            'data'   => [
                'filename' => $filename
            ]
        ];
    }
}
