<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        @if (session('status') === 'update')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Supplier Updated Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Supplier Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Supplier Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Supplier Deleted Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'hasPaymnent')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Supplier cannot be deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Suppliers']" :urls="[$user . '/suppliers']" />

    <div class="row">
        <div class="col-12">
            <div class=" border-0">
                <div class="card-header border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-truck me-2 text-success"></i>
                            Suppliers
                        </h5>
                        <a class="btn btn-sm btn-success" href="{{ url($user . '/suppliers/create') }}">
                            <i class="fas fa-user-plus me-1"></i>
                            Create Supplier
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if ($suppliers && count($suppliers) > 0)
                            <table class="table  align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold ps-4">Name</th>
                                        <th class="fw-bold">Contact No</th>
                                        <th class="fw-bold">Address</th>
                                        <th class="fw-bold">Supplier Type</th>
                                        @if ($user === 'admin')
                                            <th class="fw-bold text-center pe-4">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($suppliers as $supplier)
                                        <tr>
                                            <td class="ps-4">
                                                <a href="{{ url($user . '/suppliers', [$supplier]) }}"
                                                    class="text-success fw-bold text-decoration-none">
                                                    {{ strtoupper($supplier->name) }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="tel:{{ $supplier->contact_no }}" class="text-decoration-none">
                                                    <i class="fas fa-phone me-1 text-muted"></i>
                                                    +91-{{ $supplier->contact_no }}
                                                </a>
                                            </td>
                                            <td>{{ ucfirst($supplier->address) }}</td>
                                            <td>
                                                @if ($supplier->is_raw_material_provider)
                                                    <span class="badge bg-info text-white me-1">Raw Material</span>
                                                @endif
                                                @if ($supplier->is_workforce_provider)
                                                    <span class="badge bg-warning text-white">Workforce</span>
                                                @endif
                                            </td>
                                            @if ($user === 'admin')
                                                <td class="text-center pe-4">
                                                    <x-actions
                                                        editUrl="{{ url($user . '/suppliers/' . $supplier->id . '/edit') }}"
                                                        deleteUrl="{{ url($user . '/suppliers/' . $supplier->id) }}"
                                                        userType="{{ $user }}"
                                                        deleteMessage="Are you sure you want to delete this Supplier?" />
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class=" alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-truck fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Suppliers Found</h4>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($suppliers && $suppliers->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $suppliers->onEachSide(1)->links('pagination::bootstrap-5') }}
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
