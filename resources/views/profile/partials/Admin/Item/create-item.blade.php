<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Items', 'Create Item']" :urls="[$user . '/items', $user . '/items/create']" />
    
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                   

                    <form method="POST" action="{{ url($user . '/items') }}" class="forms-sample material-form">
                        @csrf

                        <div id="items-container">
                            @forelse(old('items', [['item_name' => '']]) as $index => $item)
                                <div class="item-row mb-4 position-relative">
                                    <div class="form-group mb-0">
                                        <input type="text" 
                                               id="items-{{ $index }}-item_name"
                                               name="items[{{ $index }}][item_name]" 
                                               value="{{ $item['item_name'] }}"
                                               class="form-control-lg" />
                                        <label for="items-{{ $index }}-item_name" class="control-label">Item Name</label>
                                        <i class="bar"></i>
                                    </div>

                                    @if ($index > 0)
                                        <button type="button" class="btn btn-sm btn-icon remove-item-row position-absolute top-0 end-0 mt-1 me-2">
                                            <i class="fas fa-times text-danger"></i>
                                        </button>
                                    @endif

                                    <x-input-error :messages="$errors->get('items.' . $index . '.item_name')" class="mt-2 fw-bold" />
                                </div>
                            @empty
                                <div class="item-row mb-4">
                                    <div class="form-group mb-0">
                                        <input type="text" 
                                               id="items-0-item_name" 
                                               name="items[0][item_name]"
                                               class="form-control-lg" />
                                        <label for="items-0-item_name" class="control-label">Item Name</label>
                                        <i class="bar"></i>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" id="add-item-row" class="btn btn-outline-success btn-sm btn-icon-text">
                                <i class="fas fa-plus"></i> 
                            </button>
                            
                            <button type="submit" class="btn btn-sm btn-success btn-icon-text">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .material-form .form-control-lg {
            padding: 0.5rem 0;
            font-size: 1rem;
            border: none;
            border-bottom: 1px solid #ced4da;
            border-radius: 0;
            background-color: transparent;
        }
        
        .material-form .form-control-lg:focus {
            box-shadow: none;
            border-color: #3f6ad8;
        }
        
        .item-row {
            position: relative;
            padding-right: 40px;
        }
        
        .remove-item-row {
            background: none;
            border: none;
            padding: 0;
            font-size: 1.25rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .remove-item-row:hover {
            opacity: 1;
        }
        
        .control-label {
            position: absolute;
            top: 0;
            left: 0;
            transition: all 0.2s;
            pointer-events: none;
            color: #6c757d;
        }
        
        .material-form input:focus ~ .control-label,
        .material-form input:not(:placeholder-shown) ~ .control-label {
            transform: translateY(-1.25rem) scale(0.85);
            color: #3f6ad8;
        }
        
        .bar {
            position: relative;
            display: block;
            width: 100%;
        }
        
        .bar:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #3f6ad8;
            transition: width 0.3s;
        }
        
        .material-form input:focus ~ .bar:before {
            width: 100%;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('items-container');
            const addButton = document.getElementById('add-item-row');
            let itemCount = document.querySelectorAll('#items-container .item-row').length;

            // Add new item row
            addButton.addEventListener('click', function() {
                const newRow = document.createElement('div');
                newRow.classList.add('item-row', 'mb-4', 'position-relative');
                newRow.innerHTML = `
                    <div class="form-group mb-0">
                        <input type="text"
                               id="items-${itemCount}-item_name"
                               name="items[${itemCount}][item_name]"
                               class="form-control-lg"
                               placeholder=" "
                        />
                        <label for="items-${itemCount}-item_name" class="control-label">Item Name</label>
                        <i class="bar"></i>
                    </div>
                    <button type="button" class="btn btn-sm btn-icon remove-item-row position-absolute top-0 end-0 mt-1 me-2">
                        <i class="fas fa-times text-danger"></i>
                    </button>
                `;

                container.appendChild(newRow);
                itemCount++;
            });

            // Remove item row
            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-item-row')) {
                    const row = e.target.closest('.item-row');
                    if (document.querySelectorAll('.item-row').length > 1) {
                        row.remove();
                    }
                }
            });
        });
    </script>
</x-app-layout>