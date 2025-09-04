<?php

namespace App\Exports;

use App\Models\MicroOrganism;
use App\Models\RoomEnvironment;
use Generator;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromGenerator;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MicroOrganismsExport implements FromGenerator, WithHeadings, WithEvents, ShouldAutoSize, WithColumnWidths {
    use Exportable;

    protected array $selects;
    private MicroOrganism|Builder|\Illuminate\Database\Query\Builder $microOrganisms;

    public function __construct(MicroOrganism|Builder|\Illuminate\Database\Query\Builder $microOrganisms) {
        $this->microOrganisms = $microOrganisms;
        $roomNames = RoomEnvironment::with('regionMgmt')->get()->sortBy(function(RoomEnvironment $roomEnvironment) {
            return $roomEnvironment->regionMgmt->region;
        })->values()->pluck('room_name')->all();
        $selects = [
            [
                'columns_name' => 'C',
                'options'      => [
                    '微粒子(0.5µm)',
                    '微粒子(5µm)',
                    '懸浮微生物',
                    '落下微生物',
                    '接觸微生物'
                ]
            ],
            [
                'columns_name' => 'D',
                'options'      => $roomNames
            ]
        ];
        $this->selects = $selects;
    }

    public function headings(): array {
        return [
            '編號',
            'QR CODE',
            '微生物類別',
            '房間',
            '位置代號',
            '測量值',
            '讀取時間',
            '建檔時間',
            '簽名：_______________  日期：_______________'
        ];
    }

    public function generator(): Generator {
        /** @var MicroOrganism $microOrganism */
        foreach($this->microOrganisms->cursor() as $microOrganism) {
            yield [
                $microOrganism->id,
                $microOrganism->bar_code,
                $microOrganism->organism_kind_name,
                $microOrganism->room_name,
                $microOrganism->device_name,
                $microOrganism->organism_value,
                $microOrganism->Time ? $microOrganism->Time->format('Y-m-d H:i:s') : '',
                $microOrganism->created_at ? $microOrganism->created_at->format('Y-m-d H:i:s') : '',
                null
            ];
        }
    }

    public function registerEvents(): array {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $count = $this->microOrganisms->count() + 1;
                $event->sheet->getDelegate()->getStyle("A1:I$count")->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle("A1:I$count")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle("A1:I$count")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                foreach($this->selects as $select) {
                    $drop_column = $select['columns_name'];
                    $options = $select['options'];
                    $validation = $event->sheet->getCell("{$drop_column}2")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setErrorTitle('驗證錯誤');
                    $validation->setError('請選擇選單的項目！');
                    $validation->setFormula1(sprintf('"%s"', implode(',', $options)));
                    for($i = 3; $i <= $count + 100; $i++) {
                        $event->sheet->getCell("$drop_column$i")->setDataValidation(clone $validation);
                    }
                }
            }
        ];
    }

    public function columnWidths(): array {
        return [
            'F' => 40,
        ];
    }
}
