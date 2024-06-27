<?php
namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use File;
// use Intervention\Image\Facades\Image as Image;

trait ImageUploadTraits {

    /**
     * @param Request $request
     * @return $this|false|string
     */
    private function verifyAndUpload($destination, $attribute , $width, $height): string{
        if (!File::isDirectory(base_path().'/public/uploads/'.$destination)){
            File::makeDirectory(base_path().'/public/uploads/'.$destination, 0777, true, true);
        }
        $file_name = time() . '-' . $attribute->getClientOriginalName();
        $path = 'uploads/'. $destination .'/' .$file_name;
        $attribute->move(public_path('uploads/' . $destination .'/'), $file_name);
        return $path;

    }

    private function updateImage($destination, $new_attribute, $old_attribute , $width, $height): string
    {
        if (!File::isDirectory(base_path().'/public/uploads/'.$destination)){
            File::makeDirectory(base_path().'/public/uploads/'.$destination, 0777, true, true);
        }
        $file_name = time() . '-' . $new_attribute->getClientOriginalName();
        $path = 'uploads/'. $destination .'/' .$file_name;
        $new_attribute->move(public_path('uploads/' . $destination .'/'), $file_name);
        File::delete($old_attribute);
        return $path;

    }

    private function deleteImage($old_attribute): string
    {
        File::delete($old_attribute);
        return 'success';
    }
}
