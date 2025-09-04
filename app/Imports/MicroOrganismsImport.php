<?php

namespace App\Imports;

use App\Models\AcceptanceGrade;
use App\Models\Location;
use App\Models\MicroOrganism;
use App\Models\PollutionCondition;
use App\Repositories\MicroOrganismRepository;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use TypeError;
use Validator;
use function app\getColor;

class MicroOrganismsImport implements SkipsEmptyRows, ToCollection, WithStartRow {
    private MicroOrganismRepository $microOrganisms;
    public Collection $data;

    public function __construct() {
        $this->microOrganisms = new MicroOrganismRepository();
    }

    public function startRow(): int {
        return 2;
    }

    /** @noinspection PhpParameterNameChangedDuringInheritanceInspection */
    /**
     * @throws \Throwable
     */
    public function collection(Collection $rows): void {
        $messages = [];
        foreach($rows as $key => $row) {
            $r = $key + 1;
            $messages["$key.0.exists"] = "第{$r}行的編號不存在系統裡！";
            $messages["$key.1.required"] = "第{$r}行的變動類型為必填！";
            $messages["$key.1.max"] = "第{$r}行的QR CODE請勿填寫超過16字！";
            $messages["$key.2.required"] = "第{$r}行的微生物類別為必填！";
            $messages["$key.2.in"] = "第{$r}行的微生物類別請填寫選單裡頭的項目！";
            $messages["$key.3.required"] = "第{$r}行的房間為必填！";
            $messages["$key.3.exists"] = "第{$r}行的請填寫選單裡頭的項目！";
            $messages["$key.4.required"] = "第{$r}行的位置代號為必填！";
            $messages["$key.4.exists"] = "第{$r}行的位置代號請填寫系統已存在的位置！";
            $messages["$key.5.required"] = "第{$r}行的測量值為必填！";
            $messages["$key.5.integer"] = "第{$r}行的測量值請填寫正整數！";
            $messages["$key.6.date_format"] = "第{$r}行的讀取時間格式不合法，請依據此格式2024-01-01 00:00:00！";
        }

        Validator::make($rows->toArray(), [
            '*.0' => ['nullable', 'exists:micro_organism,id'],
            '*.1' => ['nullable', 'max:16'],
            '*.2' => ['required', 'in:微粒子(0.5µm),微粒子(5µm),懸浮微生物,落下微生物,接觸微生物'],
            '*.3' => ['required', 'exists:room_environment,room_name'],
            '*.4' => ['required', 'exists:location,device_name'],
            '*.5' => ['required', 'integer'],
            '*.6' => ['nullable', 'date_format:Y-m-d H:i:s']
        ], $messages)->validate();

        DB::transaction(function() use ($rows) {
            $microOrganismIds = $rows->whereNotNull(0)->pluck(0);
            $microOrganisms = MicroOrganism::whereIn('id', $microOrganismIds)->get();
            $deviceNames = $rows->whereNotNull(4)->pluck(4);
            $locations = Location::whereIn('device_name', $deviceNames)->get();
            $microOrganismIds = [];
            $insertMicroOrganisms = collect();
            $updateMicroOrganisms = collect();
            $acceptanceGrades = AcceptanceGrade::whereIsDefault(0)->get();
            $pollutionConditions = PollutionCondition::all();
            foreach($rows as $row) {
                $microOrganism = $microOrganisms->where('id', $row[0])->first();
                if(!$microOrganism) {
                    $insertMode = true;
                    $microOrganism = new MicroOrganism();
                } else {
                    $insertMode = false;
                }
                $oldOrganismValue = $microOrganism->organism_value;
                $microOrganism->bar_code = trim($row[1]);
                $microOrganism->organism_kind = $this->microOrganisms->getOrganismKindIdByName(trim($row[2]));
                $microOrganism->room_name = trim($row[3]);
                $microOrganism->device_name = trim($row[4]);
                /** @var Location $location */
                $location = $locations->where('room', trim($row[3]))->where('device_name', trim($row[4]))->first();

                $microOrganism->location_id = $location->id;
                $microOrganism->organism_value = trim($row[5]);
                $microOrganism->Time = $this->transformDate($row[6]);
                $cleanlinessGrade = $location->roomEnvironment->regionMgmt->cleanliness_grade;
                $filterAcceptanceGrades = $acceptanceGrades->where('organism_kind', $microOrganism->organism_kind);
                $filterAcceptanceGrades = $filterAcceptanceGrades->where('grade', $cleanlinessGrade);
                /** @var AcceptanceGrade $acceptanceGrade */
                $acceptanceGrade = $filterAcceptanceGrades->first();
                if($acceptanceGrade) {
                    $microOrganism->color = getColor($pollutionConditions, $acceptanceGrade, $microOrganism->organism_value);
                    $microOrganism->score = ($microOrganism->organism_value / $acceptanceGrade->action) * 100;
                }
                $microOrganism->save();
                $microOrganismIds[] = $microOrganism->id;
                if($insertMode) {
                    $insertMicroOrganisms = $insertMicroOrganisms->push([
                        'id' => $microOrganism->id
                    ]);
                } else {
                    $updateMicroOrganisms = $updateMicroOrganisms->push([
                        'id'                 => $microOrganism->id,
                        'old_organism_value' => $oldOrganismValue
                    ]);
                }
            }

            $microOrganisms = MicroOrganism::whereIn('id', $microOrganismIds)->orderByDesc('id')->with('location.roomEnvironment')->get();
            $this->data = $microOrganisms;
            $this->microOrganisms->pushEvents($microOrganisms, $insertMicroOrganisms, $updateMicroOrganisms);
        }, 3);
    }

    public function transformDate($value, $format = 'Y-m-d H:i:s'): bool|Carbon {
        try {
            return Carbon::instance(Date::excelToDateTimeobject($value));
        } catch(TypeError|Exception) {
            return Carbon::createFromFormat($format, $value);
        }
    }
}
