<?php

namespace App\Http\Controllers;

use App\Events\MicroOrganismCreated;
use App\Events\MicroOrganismDeleted;
use App\Events\MicroOrganismUpdated;
use App\Exports\MicroOrganismsExport;
use App\Imports\MicroOrganismsImport;
use App\Models\AcceptanceGrade;
use App\Models\Location;
use App\Models\MicroOrganism;
use App\Models\PollutionCondition;
use App\Models\RoomEnvironment;
use App\Repositories\MicroOrganismRepository;
use DB;
use Excel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use function app\getColor;

class MicroOrganismController extends Controller {
    private MicroOrganismRepository $microOrganisms;

    public function __construct(MicroOrganismRepository $microOrganisms) {
        $this->microOrganisms = $microOrganisms;
    }

    public function index(Request $request): array {
        $microOrganisms = $this->microOrganisms->query($request);
        $microOrganismsPagination = $microOrganisms->paginate(10);

        return [
            'status' => 0,
            'data'   => [
                'microOrganisms'   => $microOrganismsPagination->items(),
                'pagination'       => [
                    'last_page'    => $microOrganismsPagination->lastPage(),
                    'current_page' => $microOrganismsPagination->currentPage()
                ],
                'roomEnvironments' => RoomEnvironment::with([
                    'locations' => function(HasMany $query) {
                        $query->orderBy('device_name');
                    },
                    'regionMgmt'
                ])->get()->sortBy(function(RoomEnvironment $roomEnvironment) {
                    return $roomEnvironment->regionMgmt->region;
                })->values()
            ]
        ];
    }

    public function download(Request $request): BinaryFileResponse {
        $microOrganisms = $this->microOrganisms->query($request);
        $now = Carbon::now();
        return (new MicroOrganismsExport($microOrganisms))->download("微生物數據_{$now->format('YmdHis')}.xlsx");
    }

    public function storeBatch(Request $request): array {
        $import = new MicroOrganismsImport;
        Excel::import($import, $request->file('file'));

        return [
            'status' => 0,
            'data'   => [
                'microOrganisms' => $import->data
            ]
        ];
    }

    /**
     * @throws Throwable
     */
    public function update(Request $request): array {
        $processedIds = [];
        $insertedRecords = collect();
        $updatedRecords = collect();
        DB::transaction(function() use ($request, &$processedIds, &$insertedRecords, &$updatedRecords) {
            $inputMicroOrganisms = collect($request->input('micro_organisms'));
            $existingIds = $inputMicroOrganisms->pluck('id')->filter(fn($id) => $id > 0);
            $existingMicroOrganisms = MicroOrganism::whereIn('id', $existingIds)->get();
            $acceptanceGrades = AcceptanceGrade::whereIsDefault(0)->get()->groupBy(['organism_kind', 'grade']);
            $locationIds = $inputMicroOrganisms->pluck('location_id');
            $locations = Location::whereIn('id', $locationIds)->with([
                'vertex.regionMgmt',
                'roomEnvironment'
            ])->get()->keyBy('id');
            $pollutionConditions = PollutionCondition::all();

            foreach($inputMicroOrganisms as $item) {
                $id = $item['id'];
                $isNew = !$existingIds->has($id);
                $microOrganism = $existingMicroOrganisms->get($id) ?? new MicroOrganism();

                $locationId = $item['location_id'];
                /** @var Location $location */
                $location = $locations->get($locationId);
                if(!$location || !$location->roomEnvironment || !$location->roomEnvironment->regionMgmt) {
                    continue; // 資料不完整，略過這筆
                }

                $cleanlinessGrade = $location->roomEnvironment->regionMgmt->cleanliness_grade;
                $organismKind = $item['organism_kind'];

                /** @var AcceptanceGrade $acceptanceGrade */
                $acceptanceGrade = $acceptanceGrades[$organismKind][$cleanlinessGrade][0] ?? null;

                $oldValue = $microOrganism->organism_value;

                if($isNew) {
                    $microOrganism->Time = new Carbon($item['Time']);
                    $microOrganism->location_id = $locationId;
                    $microOrganism->device_name = $location->device_name;
                    $microOrganism->room_name = $location->roomEnvironment->room_name;
                    $microOrganism->organism_kind = $organismKind;
                    $microOrganism->bar_code = $item['bar_code'];
                }
                $microOrganism->organism_value = $item['organism_value'];
                $microOrganism->created_at = new Carbon($item['created_at']);

                if($acceptanceGrade) {
                    $microOrganism->color = getColor(
                        $pollutionConditions,
                        $acceptanceGrade,
                        $microOrganism->organism_value
                    );
                    $microOrganism->score = ($item['organism_value'] / $acceptanceGrade->action) * 100;
                }

                $microOrganism->save();
                $processedIds[] = $microOrganism->id;

                if($isNew) {
                    $insertedRecords = $insertedRecords->push([
                        'id' => $microOrganism->id
                    ]);
                } else {
                    $updatedRecords = $updatedRecords->push([
                        'id'                 => $microOrganism->id,
                        'old_organism_value' => $oldValue
                    ]);
                }
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        $microOrganisms = MicroOrganism::whereIn('id', $processedIds)->orderByDesc('id')->with(
            'location.roomEnvironment'
        )->get();
        $this->microOrganisms->pushEvents($microOrganisms, $insertedRecords, $updatedRecords);

        return [
            'status' => 0,
            'data'   => [
                'microOrganisms' => $microOrganisms
            ]
        ];
    }

    public function destroy($id): array {
        $microOrganism = MicroOrganism::findOrFail($id);
        $microOrganism->delete();
        $this->microOrganisms->refreshSeriousData();
        event(new MicroOrganismDeleted($microOrganism));

        return [
            'status' => 0
        ];
    }
}
