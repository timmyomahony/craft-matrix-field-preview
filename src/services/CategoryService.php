<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;

use weareferal\matrixfieldpreview\records\CategoryRecord;


class CategoryService extends Component
{
    public function getById($id)
    {
        return CategoryRecord::findOne([
            'id' => $id
        ]); 
    }

    public function count()
    {
        return CategoryRecord::find()
            ->count();
    }

    public function getAll()
    {
        return CategoryRecord::find()
            ->orderBy(['sortOrder' => SORT_ASC])
            ->all();
            
    }

    public function save(CategoryRecord $category): bool {
        $isNew = ! $category->id;

        if (! $category->validate()) {
            Craft::info("Category not saved due to validation error", "matrix-field-preview");
            return false;
        }

        if ($isNew) {
            $category->sortOrder = $this->count() + 1;
        }

        $category->save();

        return true;
    }

    public function reorder(array $categoryIds): bool
    {
        foreach ($this->getAll() as $i => $category) {
            $category->sortOrder = array_search($category->id, $categoryIds);
            $category->save();
        }
        return true;
    }

    public function deleteById(int $categoryId) {
        if (!$categoryId) {
            return false;
        }

        $category = $group = $this->getById($categoryId);

        if (! $category) {
            return false;
        }

        $category->delete();

        return true;
    }
}
