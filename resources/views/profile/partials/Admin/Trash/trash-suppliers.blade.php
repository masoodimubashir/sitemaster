<x-app-layout>
    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

    <x-breadcrumb :names="['Suppliers']" :urls="['trash/suppliers']" />

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie me-2 text-info"></i>
                            Deleted Suppliers
                        </h5>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($suppliers))
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold">ID</th>
                                        <th class="fw-bold">Supplier Name</th>
                                        <th class="fw-bold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($suppliers as $key => $supplier)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ ucfirst($supplier->name) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('trash.restore', ['model_name' => 'supplier', 'id' => $supplier->id]) }}"
                                                   class="btn btn-sm btn-icon"
                                                   data-bs-toggle="tooltip"
                                                   title="Restore Supplier">
                                                    <i class="fas fa-history me-1"></i> Restore
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class=" alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-trash-alt fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Deleted Suppliers Found</h4>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($suppliers->hasPages())
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