<?php

namespace App\Admin\Extensions\Export;

use Encore\Admin\Grid\Exporters\ExcelExporter;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class TopicsExporter extends ExcelExporter implements WithMapping, WithEvents
{

    protected $fileName = '话题列表.xlsx';

    protected $columns = [
        'id' => 'ID',
        'title' => '标题',
        'content' => '内容',
        'category_id' => '分类名',
        'user_id' => '用户名',
        'read_count' => '阅读数',
        'reply_count' => '回复数',
        'created_at' => '创建时间',
        'updated_at' => '添加时间',
    ];

    public function map($topic): array
    {
        return [
            $topic->id,
            $topic->title,
            $topic->content,
            $topic->category->name,
            $topic->user->username,
            $topic->read_count,
            $topic->reply_count,
            $topic->created_at,
            $topic->updated_at,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $afterSheet) {
                $afterSheet->sheet->getDelegate()->getColumnDimension('A')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('B')->setWidth(50);
                $afterSheet->sheet->getDelegate()->getColumnDimension('C')->setWidth(80);
                $afterSheet->sheet->getDelegate()->getColumnDimension('D')->setWidth(15);
                $afterSheet->sheet->getDelegate()->getColumnDimension('E')->setWidth(20);
                $afterSheet->sheet->getDelegate()->getColumnDimension('F')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('G')->setWidth(10);
                $afterSheet->sheet->getDelegate()->getColumnDimension('H')->setWidth(20);
                $afterSheet->sheet->getDelegate()->getColumnDimension('I')->setWidth(20);

            },
        ];
    }

}