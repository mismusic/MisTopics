<?php

namespace App\Imports;

use App\Models\Topic;
use Maatwebsite\Excel\Concerns\ToModel;

class TopicsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Topic([
            'title' => $row[0],
            'content' => $row[1],
            'category_id' => $row[2],
            'user_id' => $row[3],
        ]);
    }
}
