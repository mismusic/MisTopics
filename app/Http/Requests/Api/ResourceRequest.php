<?php

namespace App\Http\Requests\Api;


use App\Common\ApiReturnCode;
use App\Common\Utils\UploadFile;

class ResourceRequest extends Request
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
        return [
            'file' => [
                'required',
                'file',
                'mimes:' . UploadFile::UPLOAD_EXTENSION,
                function ($attribute, $value, $fail)
                {
                    $size = UploadFile::MAX_FILE_SIZE * (1024 ** 2);  // 把单位 M 转换为 B
                    if ($this->hasFile('file') && $this->file('file')->getSize() > $size)  // 当前上传文件的大小大于最大运行文件大小时，抛出错误
                    {
                        $fail(ApiReturnCode::getReturnMessage(ApiReturnCode::API_RETURN_CODE_FILE_MAX_NOT_ALLOWED));
                    }
                }
            ]
        ];
    }
}
