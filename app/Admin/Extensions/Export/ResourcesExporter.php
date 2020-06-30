<?php

namespace App\Admin\Extensions\Export;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class ResourcesExporter extends ExcelExporter implements WithMapping, WithEvents
{

    protected $fileName = '资源列表.xlsx';

    protected $columns = [
        'id' => 'ID',
        'type' => '资源类型',
        'name' => '文件名',
        'original_name' => '文件源名称',
        'uri' => '资源地址',
        'user_id' => '用户名',
        'public' => '资源共享',
        'created_at' => '创建时间',
    ];

    public function map($row): array
    {
        // TODO: Implement map() method.
        return [
            $row->id,
            $row->type,
            $row->name,
            $row->original_name,
            $row->uri,
            $row->user->username,
            (string) $row->public,
            $row->created_at,
        ];
    }

    public function registerEvents(): array
    {
        // TODO: Implement registerEvents() method.
        return [
            AfterSheet::class => function (AfterSheet $afterSheet) {
                $afterSheet->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('B')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('C')->setWidth(35);
                $afterSheet->sheet->getDelegate()->getColumnDimension('D')->setWidth(30);
                $afterSheet->sheet->getDelegate()->getColumnDimension('E')->setWidth(50);
                $afterSheet->sheet->getDelegate()->getColumnDimension('F')->setWidth(20);
                $afterSheet->sheet->getDelegate()->getColumnDimension('G')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('H')->setWidth(20);
            },
        ];
    }

}