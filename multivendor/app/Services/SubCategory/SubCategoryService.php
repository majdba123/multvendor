<?php

namespace App\Services\SubCategory;

use App\Models\SubCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class SubCategoryService
{
    public function getAll()
    {
        return SubCategory::with('attribute')->get();
    }

    public function getById($id)
    {
        return SubCategory::with('attribute')->findOrFail($id);
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

        $subcategory = SubCategory::create($data);

        // Create attributes
        if (isset($data['attributes'])) {
            foreach ($data['attributes'] as $attributeData) {
                $subcategory->attribute()->create([
                    'name' => $attributeData['name'],
                    'sub_category_id' => $subcategory->id
                ]);
            }
        }

        return $subcategory->load('attribute');
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

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            foreach ($data['attributes'] as $attributeData) {
                $existingAttribute = $subcategory->attribute()
                    ->where('id', $attributeData['attribute_id'])
                    ->first();

                if ($existingAttribute) {
                    $existingAttribute->update(['name' => $attributeData['name']]);
                }
            }
        }

        return $subcategory->load('attribute');
    }

    public function delete(SubCategory $subcategory)
    {
        // Delete associated attributes first
        $subcategory->attribute()->delete();
        return $subcategory->delete();
    }

    public function get_by_category_id($id)
    {
        return SubCategory::with('attribute')->where('category_id', $id)->get();
    }
}
