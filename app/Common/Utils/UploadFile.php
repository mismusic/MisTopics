<?php

namespace App\Common\Utils;


use Intervention\Image\Facades\Image;

class UploadFile
{

    public static function localStorage($file, string $folder, $identified, $type = 'image', $size = null)
    {
        // 1.获取文件信息
        $ext = $file->extension();  // 获取源文件的扩展名
        $path = $folder .'/'. date('Ym') . '/' . date('d');  // 文件路径
        $fileName = date('YmdHis') . $identified . random_int(10000000, 99999999) . '.' . $ext;  // 文件名
        $fullPath = storage_path('app/public') . '/' . $path;  // 文件完全路径
        // 2.保存文件到本地存储
        $file->move($fullPath, $fileName);
        // 如果资源类型为image，并且$size不等于null，就对图片进行剪切，缩放
        if ($type === 'image' && ! is_null($size)) {
            self::resize($fullPath . '/' . $fileName, $size);
        }
        // 返回对应的文件信息
        $utils = new Utils();
        return [
            'type' => $type,
            'fileUri' => $path . '/' . $fileName,
            'fileName' => $fileName,
            'fileOriginalName' => $file->getClientOriginalName(),  // 源文件名称
            'fileSize' => $utils->convertFileSize(filesize($fullPath . '/' . $fileName)),  // 上传的文件大小
        ];
    }

    public static function resize($filePath, $size)
    {
        $image = Image::make($filePath);  // 生成一个图片资源
        $image->fit($size, $size, function ($constraint) {
            $constraint->aspectRatio();  // 对图片进行等比例缩放
            $constraint->upsize();  // 防止图片进行放大
        });
        // 保存图片
        $image->save($filePath, 100);
    }

}