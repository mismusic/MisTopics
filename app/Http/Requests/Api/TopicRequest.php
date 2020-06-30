<?php

namespace App\Http\Requests\Api;


use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class TopicRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $routeName = Route::currentRouteName();
        if ($routeName === get_api_prefix() . 'topics.store') {
            return [
                'title' => ['required', 'between:1,255'],
                'content' => ['required', 'min:1'],
                'category_id' => ['required', 'exists:categories,id'],
            ];
        } else if ($routeName === get_api_prefix() . 'topics.reply_store') {
            return [
                'pid' => [
                    'required',
                    'integer',
                    Rule::exists('replies', 'id')->where('topic_id', $this->route('topic')->id),
                ],
                'content' => [
                    'required',
                    'string',
                    'min:1',
                ],
            ];
        }
    }
}
