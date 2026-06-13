<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\DocumentType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class TaxonomyLivewire extends Component
{
    use AuthorizesRequests;

    public string $categoryName = '';
    public string $categoryDescription = '';
    public ?int $editCategoryId = null;

    public string $typeName = '';
    public string $typeDescription = '';
    public ?int $editTypeId = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    public function saveCategory(): void
    {
        $this->authorize('create', Category::class);
        $this->validate(['categoryName' => 'required|string|max:150']);

        $slug = Str::slug($this->categoryName) ?: 'category-'.time();

        if ($this->editCategoryId) {
            $category = Category::findOrFail($this->editCategoryId);
            $this->authorize('update', $category);
            $category->update([
                'name' => $this->categoryName,
                'description' => $this->categoryDescription ?: null,
            ]);
            toastr()->success(__('archive.msg_category_updated'));
        } else {
            Category::create([
                'name' => $this->categoryName,
                'slug' => $slug,
                'description' => $this->categoryDescription ?: null,
                'is_active' => true,
            ]);
            toastr()->success(__('archive.msg_category_created'));
        }

        $this->resetCategoryForm();
    }

    public function editCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);
        $this->editCategoryId = $id;
        $this->categoryName = $category->name;
        $this->categoryDescription = $category->description ?? '';
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);

        if ($category->files()->exists()) {
            toastr()->error(__('archive.msg_category_has_files'));

            return;
        }

        $category->delete();
        toastr()->success(__('archive.msg_category_deleted'));
        $this->resetCategoryForm();
    }

    public function saveDocumentType(): void
    {
        $this->authorize('create', DocumentType::class);
        $this->validate(['typeName' => 'required|string|max:150']);

        $slug = Str::slug($this->typeName) ?: 'type-'.time();

        if ($this->editTypeId) {
            $type = DocumentType::findOrFail($this->editTypeId);
            $this->authorize('update', $type);
            $type->update([
                'name' => $this->typeName,
                'description' => $this->typeDescription ?: null,
            ]);
            toastr()->success(__('archive.msg_type_updated'));
        } else {
            DocumentType::create([
                'name' => $this->typeName,
                'slug' => $slug,
                'description' => $this->typeDescription ?: null,
                'is_active' => true,
            ]);
            toastr()->success(__('archive.msg_type_created'));
        }

        $this->resetTypeForm();
    }

    public function editDocumentType(int $id): void
    {
        $type = DocumentType::findOrFail($id);
        $this->authorize('update', $type);
        $this->editTypeId = $id;
        $this->typeName = $type->name;
        $this->typeDescription = $type->description ?? '';
    }

    public function deleteDocumentType(int $id): void
    {
        $type = DocumentType::findOrFail($id);
        $this->authorize('delete', $type);

        if ($type->files()->exists()) {
            toastr()->error(__('archive.msg_type_has_files'));

            return;
        }

        $type->delete();
        toastr()->success(__('archive.msg_type_deleted'));
        $this->resetTypeForm();
    }

    public function resetCategoryForm(): void
    {
        $this->reset(['categoryName', 'categoryDescription', 'editCategoryId']);
    }

    public function resetTypeForm(): void
    {
        $this->reset(['typeName', 'typeDescription', 'editTypeId']);
    }

    public function render()
    {
        return view('livewire.taxonomy-livewire', [
            'categories' => Category::withCount('files')->orderBy('name')->get(),
            'documentTypes' => DocumentType::withCount('files')->orderBy('name')->get(),
        ]);
    }
}
