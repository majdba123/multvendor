<?php

namespace App\Services\SubCategory;

use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class SubCategoryService
{
    public function getAll()
    {
        return SubCategory::all();
    }

    public function getById($id)
    {
        return SubCategory::findOrFail($id); // يرجع الخطأ 404 إذا لم يتم العثور
    }

    public function store(array $data)
    {
        if (isset($data['imag'])) {
            $imageFile = $data['imag'];
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = 'SubCategory/' . $imageName;
            $imageUrl = asset('storage/SubCategory/' . $imageName);
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));
            $data['imag'] = $imageUrl;
        }

        return SubCategory::create($data);
    }

    public function update(SubCategory $subcategory, array $data)
    {
        if (isset($data['imag'])) {
            $imageFile = $data['imag'];
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = 'SubCategory/' . $imageName;
            $imageUrl = asset('storage/SubCategory/' . $imageName);
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));
            $data['imag'] = $imageUrl;
        }
        $subcategory->update($data);
        return $subcategory;
    }

    public function delete(SubCategory $subcategory)
    {
        return $subcategory->delete();
    }


    public function get_by_category_id($id)
    {
        return SubCategory::where('category_id' ,$id)->get(); // يرجع الخطأ 404 إذا لم يتم العثور
    }
}
