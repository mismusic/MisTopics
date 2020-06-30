<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function index(Request $request)
    {
        $title = '首页';
        $topics = Topic::query()->with(['category', 'user'])->order($request->query('order'))->paginate(10);
        return view('pages.index', compact(['title', 'topics']));
    }
}
