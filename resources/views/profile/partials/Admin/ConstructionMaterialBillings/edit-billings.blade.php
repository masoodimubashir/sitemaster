<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="[
        'View ' . $construction_material_billing->phase->site->site_name,
        'Edit ' . $construction_material_billing->item_name,
    ]" :urls="[
        'admin/sites/' . base64_encode($construction_material_billing->phase->site->id),
        'admin/construction-material-billings/' . base64_encode($construction_material_billing->id),
    ]" />

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="forms-sample material-form"
                        action="{{ url($user . '/construction-material-billings', [$construction_material_billing->id]) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')


                        <!-- Item Name Toggle -->
                        @php
                            $itemExistsInList = $items->contains(
                                'item_name',
                                $construction_material_billing->item_name,
                            );
                        @endphp


                        {{-- Date --}}
                        <div class="form-group">
                            <input type="date" name="created_at" id="created_at"
                                value="{{ $construction_material_billing->created_at ? $construction_material_billing->created_at->format('Y-m-d') : '' }}" />
                            <label for="created_at" class="control-label">Date</label>
                            <i class="bar"></i>
                            <p class="mt-1 text-danger" id="created_at-error"></p>
                        </div>



                        <div class="mb-4">
                            <div class="btn-group btn-group-sm mb-2" role="group">
                                <button type="button"
                                    class="btn btn-outline-primary toggle-item-btn {{ $itemExistsInList ? 'active' : '' }}"
                                    data-mode="select">
                                    Select from list
                                </button>
                                <button type="button"
                                    class="btn btn-outline-secondary toggle-item-btn {{ !$itemExistsInList ? 'active' : '' }}"
                                    data-mode="custom">
                                    Enter custom
                                </button>
                            </div>

                            <!-- Item Select -->
                            <div id="item-select-container" style="{{ !$itemExistsInList ? 'display: none;' : '' }}">
                                <select class="form-select text-black form-select-sm" name="item_name" id="item_name">
                                    <option value="">Select Item</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->item_name }}"
                                            {{ $construction_material_billing->item_name === $item->item_name ? 'selected' : '' }}>
                                            {{ $item->item_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('item_name')" class="mt-2" />
                            </div>

                            <!-- Custom Input -->
                            <div id="custom-item-container" style="{{ $itemExistsInList ? 'display: none;' : '' }}">
                                <input type="text" class="form-control" name="custom_item_name"
                                    value="{{ !$itemExistsInList ? $construction_material_billing->item_name : '' }}"
                                    placeholder="Enter item name">
                                <x-input-error :messages="$errors->get('custom_item_name')" class="mt-2" />
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="form-group">
                            <input type="number" name="amount" step="0.01"
                                value="{{ $construction_material_billing->amount }}" />
                            <label for="input" class="control-label">Amount</label>
                            <i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Unit -->
                        <div class="form-group mb-3">
                            <input type="number" name="unit_count" id="unit_count" min="1"
                                value="{{ $construction_material_billing->unit_count }}" />
                            <label for="unit_count" class="control-label">Units</label>
                            <i class="bar"></i>
                            <x-input-error :messages="$errors->get('unit_count')" class="mt-2" />
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" name="site_id"
                            value="{{ $construction_material_billing->phase->site->id }}" />
                        <input type="hidden" name="phase_id"
                            value="{{ $construction_material_billing->phase->id }}" />

                        <!-- Supplier -->
                        <div class="mt-4">
                            <label for="supplier_id" class="mb-1"
                                style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">
                                Select Supplier
                            </label>
                            <select id="supplier_id" class="form-select form-select-sm text-black" name="supplier_id">
                                @foreach ($suppliers as $supplier)
                                    <option
                                        {{ $construction_material_billing->supplier_id === $supplier->id ? 'selected' : '' }}
                                        value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        <!-- Image Upload -->
                        <div class="mt-4">
                            <label for="image" class="mb-1"
                                style="font-size: 0.8rem; color: rgba(17, 17, 17, 0.48);">
                                Item Bill
                            </label>
                            <input class="form-control form-control-md" id="image" type="file" name="image">
                            @if ($construction_material_billing->item_image_path)
                                <div class="mt-2">
                                    <small>Current image:</small>
                                    <a href="{{ asset('storage/' . $construction_material_billing->item_image_path) }}"
                                        target="_blank">
                                        View Image
                                    </a>
                                </div>
                            @endif
                            <x-input-error :messages="$errors->get('image')" class="mt-2" />
                        </div>

                        <button class="btn btn-success mt-4">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle between item selection modes
            const toggleButtons = document.querySelectorAll('.toggle-item-btn');
            const itemSelectContainer = document.getElementById('item-select-container');
            const customItemContainer = document.getElementById('custom-item-container');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const mode = this.getAttribute('data-mode');

                    // Update button states
                    toggleButtons.forEach(btn => {
                        btn.classList.remove('active', 'btn-primary');
                        btn.classList.add('btn-outline-secondary');
                    });
                    this.classList.add('active', 'btn-primary');
                    this.classList.remove('btn-outline-secondary');

                    // Toggle visibility
                    if (mode === 'select') {
                        itemSelectContainer.style.display = 'block';
                        customItemContainer.style.display = 'none';
                        customItemContainer.querySelector('input').value = '';
                    } else {
                        itemSelectContainer.style.display = 'none';
                        customItemContainer.style.display = 'block';
                        itemSelectContainer.querySelector('select').value = '';
                    }
                });
            });
        });
    </script>
</x-app-layout>
