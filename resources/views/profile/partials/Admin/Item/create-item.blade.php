<x-app-layout>


    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Items', 'Create Item']" :urls="[$user . '/items', $user . '/items/create']" />
    <div class="row">
        <div class="d-flex justify-content-end">
            <div class="form-group mt-3">
                <button type="button" id="add-item-row" class="btn btn-info btn-sm d-flex align-items-center">
                    Add Entry <i class="fa fa-plus ms-2"></i>
                </button>
            </div>
        </div>

        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ url($user . '/items') }}" class="forms-sample material-form">
                        @csrf

                        <div id="items-container">
                            @forelse(old('items', [['item_name' => '']]) as $index => $item)
                                <div class="form-group item-row mb-3">
                                    <div class="d-flex align-items-center">
                                        <input type="text" id="items-{{ $index }}-item_name"
                                            name="items[{{ $index }}][item_name]"
                                            value="{{ $item['item_name'] }}" class="w-100"
                                            style="width: 100% !important" />
                                        <label for="items-{{ $index }}-item_name" class="control-label">Item
                                            Name</label>
                                        <i class="bar"></i>

                                        @if ($index > 0)
                                            <div class="input-group-append ms-2">
                                                <i class="fa-regular fa-circle-xmark remove-item-row fs-3 text-danger"
                                                    style="cursor:pointer"></i>
                                            </div>
                                        @endif
                                    </div>

                                    @error('items.' . $index . '.item_name')
                                        <div class="invalid-feedback d-block mt-2 fw-bold">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            @empty
                                <div class="form-group item-row mb-3">
                                    <div class="d-flex align-items-center form-group w-100">
                                        <input type="text" id="items-0-item_name" name="items[0][item_name]"
                                            class="w-100" />
                                        <label for="items-0-item_name" class="control-label">Item Name</label>
                                        <i class="bar"></i>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <button type="submit" class="btn btn-info mt-3">
                            <span>Save Items</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCount = document.querySelectorAll('#items-container .item-row').length;
            const container = document.getElementById('items-container');
            const addButton = document.getElementById('add-item-row');

            addButton.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.classList.add('form-group', 'item-row', 'mb-3');
                newRow.innerHTML = `
            <div class="d-flex align-items-center form-group">
                <input type="text"
                       id="items-${itemCount}-item_name"
                       name="items[${itemCount}][item_name]"
                />
                <label for="items-${itemCount}-item_name" class="control-label">Item Name</label>
                <i class="bar"></i>

                <div class="input-group-append me-3">
                    <i class="fa-regular fa-circle-xmark remove-item-row fs-3 text-danger"
                       style="cursor:pointer"></i>
                </div>
            </div>
        `;

                // Add remove functionality to the new row
                const removeButton = newRow.querySelector('.remove-item-row');
                removeButton.addEventListener('click', function() {
                    container.removeChild(newRow);
                });

                container.appendChild(newRow);
                itemCount++;
            });

            // Delegate remove functionality for dynamically added remove buttons
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item-row')) {
                    e.target.closest('.item-row').remove();
                }
            });
        });
    </script>


</x-app-layout>
