<?php
namespace App\Services\Category;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class CategoryService
{
    public function create(array $data)
    {
        if (isset($data['imag'])) {
            $imageFile = $data['imag'];
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = 'categoryimage/' . $imageName;
            $imageUrl = asset('storage/categoryimage/' . $imageName);
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));
            $data['imag'] = $imageUrl;
        }

        return Category::create($data);
    }

    public function update(Category $category, array $data)
    {
        if (isset($data['imag'])) {
            $imageFile = $data['imag'];
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imagePath = 'categoryimage/' . $imageName;
            $imageUrl = asset('storage/categoryimage/' . $imageName);
            Storage::disk('public')->put($imagePath, file_get_contents($imageFile));
            $data['imag'] = $imageUrl;
        }
        $category->update($data);
        return $category;
    }

    public function delete(Category $category)
    {
        return $category->delete();
    }

    public function getAll()
    {
        // جلب جميع الفئات مع الـ sub_category الخاص بها
        return Category::with('sub_category')->get();
    }

    public function getById($id)
    {
        // جلب الفئة المحددة مع الـ sub_category الخاص بها
        return Category::with('sub_category')->findOrFail($id);
    }


}
