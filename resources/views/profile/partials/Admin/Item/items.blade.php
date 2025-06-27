<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Item Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'update')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Item Updated Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Item Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Sorry! Item Not Found</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'hasItem')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>This Item Cannot Be Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Items']" :urls="[$user . '/items']" />

    <div class="row">
        <div class="col-12">
            <div class=" mb-4 border-0">
                <div class="card-heade  py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-boxes-stacked me-2 text-info"></i>
                            Items
                        </h5>
                        <a class="btn btn-sm btn-success" href="{{ url($user . '/items/create') }}">
                            <i class="fas fa-plus me-1"></i>
                            Create Item
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($items) > 0)
                            <table class="table align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold ps-4">Item Name</th>
                                        @if ($user === 'admin')
                                            <th class="fw-bold text-center pe-4">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td class="ps-4">
                                                <span class="fw-semibold">{{ $item->item_name }}</span>
                                            </td>
                                            @if ($user === 'admin')
                                                <td class="text-center pe-4">
                                                    <x-actions
                                                        editUrl="{{ url($user . '/items/' . $item->id . '/edit') }}"
                                                        deleteUrl="{{ url($user . '/items/' . $item->id) }}"
                                                        userType="{{ $user }}"
                                                        deleteMessage="Are you sure you want to delete this Item?" />
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class=" alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Items Found</h4>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($items->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $items->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        });
    </script>
</x-app-layout>
