<?php

namespace App\Admin\Extensions\Export;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class RepliesExporter extends ExcelExporter implements WithMapping, WithEvents
{

    protected $fileName = '回复列表.xlsx';

    protected $columns = [
        'id' => 'ID',
        'pid' => 'Pid',
        'content' => '内容',
        'user_id' => '用户名',
        'topic_id' => '话题id',
        'created_at' => '创建时间',
    ];

    public function map($row): array
    {
        return [
            $row->id,
            (string) $row->pid,
            $row->content,
            $row->user->username,
            $row->topic_id,
            $row->created_at,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $afterSheet) {
                $afterSheet->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('B')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('C')->setWidth(80);
                $afterSheet->sheet->getDelegate()->getColumnDimension('D')->setWidth(20);
                $afterSheet->sheet->getDelegate()->getColumnDimension('E')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('F')->setWidth(20);
            },
        ];
    }

}