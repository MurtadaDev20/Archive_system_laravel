<div>
    <div class="row g-4">
        {{-- التصنيفات --}}
        <div class="col-lg-6">
            <div class="archive-card">
                <div class="archive-card-header">
                    <h5><i class="bi bi-tags me-2"></i>{{ __('archive.manage_categories') }}</h5>
                </div>
                <div class="archive-card-body">
                    <form wire:submit.prevent="saveCategory" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input wire:model="categoryName" type="text" class="form-control form-control-sm" placeholder="{{ __('archive.category_name') }}">
                                @error('categoryName') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <input wire:model="categoryDescription" type="text" class="form-control form-control-sm" placeholder="{{ __('archive.description') }}">
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-archive-accent btn-sm w-100">{{ $editCategoryId ? __('archive.update') : __('archive.add') }}</button>
                                @if($editCategoryId)
                                    <button type="button" wire:click="resetCategoryForm" class="btn btn-outline-secondary btn-sm">{{ __('archive.cancel') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table archive-table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('archive.category') }}</th>
                                    <th>{{ __('archive.documents') }}</th>
                                    <th class="text-end">{{ __('archive.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr wire:key="cat-{{ $category->id }}">
                                        <td>
                                            <div class="fw-semibold">{{ $category->name }}</div>
                                            @if($category->description)<small class="text-archive-muted">{{ $category->description }}</small>@endif
                                        </td>
                                        <td>{{ $category->files_count }}</td>
                                        <td class="text-end">
                                            <button wire:click="editCategory({{ $category->id }})" class="btn btn-light btn-icon btn-sm"><i class="bi bi-pencil"></i></button>
                                            <button wire:click="deleteCategory({{ $category->id }})" wire:confirm="{{ __('archive.delete_category_confirm') }}" class="btn btn-outline-danger btn-icon btn-sm"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-archive-muted">{{ __('archive.no_categories') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- أنواع المستندات --}}
        <div class="col-lg-6">
            <div class="archive-card">
                <div class="archive-card-header">
                    <h5><i class="bi bi-file-earmark-ruled me-2"></i>{{ __('archive.manage_document_types') }}</h5>
                </div>
                <div class="archive-card-body">
                    <form wire:submit.prevent="saveDocumentType" class="mb-4">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input wire:model="typeName" type="text" class="form-control form-control-sm" placeholder="{{ __('archive.type_name') }}">
                                @error('typeName') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-5">
                                <input wire:model="typeDescription" type="text" class="form-control form-control-sm" placeholder="{{ __('archive.description') }}">
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-archive-accent btn-sm w-100">{{ $editTypeId ? __('archive.update') : __('archive.add') }}</button>
                                @if($editTypeId)
                                    <button type="button" wire:click="resetTypeForm" class="btn btn-outline-secondary btn-sm">{{ __('archive.cancel') }}</button>
                                @endif
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table archive-table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('archive.document_type') }}</th>
                                    <th>{{ __('archive.documents') }}</th>
                                    <th class="text-end">{{ __('archive.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($documentTypes as $type)
                                    <tr wire:key="type-{{ $type->id }}">
                                        <td>
                                            <div class="fw-semibold">{{ $type->name }}</div>
                                            @if($type->description)<small class="text-archive-muted">{{ $type->description }}</small>@endif
                                        </td>
                                        <td>{{ $type->files_count }}</td>
                                        <td class="text-end">
                                            <button wire:click="editDocumentType({{ $type->id }})" class="btn btn-light btn-icon btn-sm"><i class="bi bi-pencil"></i></button>
                                            <button wire:click="deleteDocumentType({{ $type->id }})" wire:confirm="{{ __('archive.delete_type_confirm') }}" class="btn btn-outline-danger btn-icon btn-sm"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-archive-muted">{{ __('archive.no_types') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
