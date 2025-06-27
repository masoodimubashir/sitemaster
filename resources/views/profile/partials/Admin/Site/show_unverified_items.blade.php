<x-app-layout>
    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;"></div>

    <x-breadcrumb :names="['Verify Items']" :urls="['admin/item-verification']" />

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-check me-2 text-info"></i>
                            Item Verification
                        </h5>

                    </div>
                </div>

                <div class="card-body">
                    <!-- Filter Section -->
                    <div class=" border-0 mb-3">
                        <form action="{{ url()->current() }}" method="GET" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="site_id" class="form-label">Site</label>
                                    <select class="form-select text-black auto-submit" name="site_id" id="site_id">
                                        <option value="all" {{ request('site_id') === 'all' ? 'selected' : '' }}>
                                            All Sites
                                        </option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site->id }}"
                                                {{ request('site_id') == $site->id ? 'selected' : '' }}>
                                                {{ ucwords($site->site_name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="phase" class="form-label">Phase</label>
                                    <select class="form-select text-black auto-submit" name="phase" id="phase">
                                        <option value="all" {{ request('phase') === 'all' ? 'selected' : '' }}>
                                            All Phases
                                        </option>
                                        @foreach ($phases as $phase)
                                            <option value="{{ $phase }}"
                                                {{ request('phase') == $phase ? 'selected' : '' }}>
                                                {{ ucwords($phase) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="supplier" class="form-label">Supplier</label>
                                    <div class="input-group">
                                        <select class="form-select text-black auto-submit" name="supplier"
                                            id="supplier">
                                            <option value="all"
                                                {{ request('supplier') === 'all' ? 'selected' : '' }}>
                                                All Suppliers
                                            </option>
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier }}"
                                                    {{ request('supplier') == $supplier ? 'selected' : '' }}>
                                                    {{ ucwords($supplier) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>


                    </div>

                    <div class="table-responsive rounded">
                        @if (count($paginatedData))
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold">Date | Time</th>
                                        <th class="fw-bold">Supplier Name</th>
                                        <th class="fw-bold">Phase</th>
                                        <th class="fw-bold">Site Name</th>
                                        <th class="fw-bold">Type</th>
                                        <th class="fw-bold">Information</th>
                                        <th class="fw-bold">Status</th>
                                        @if (auth()->user()->role_name === 'admin')
                                            <th class="fw-bold text-center">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paginatedData as $data)
                                        <tr>
                                            <td>{{ $data['created_at']->format('d-M-y') }}</td>
                                            <td>{{ ucwords($data['supplier']) }}</td>
                                            <td>{{ ucwords($data['phase']) }}</td>
                                            <td>{{ ucwords($data['site']) }}</td>
                                            <td>{{ $data['category'] }}</td>
                                            <td>{{ ucwords($data['description']) }}</td>
                                            <td>
                                                @if ($data['verified_by_admin'] === 1)
                                                    <span class="badge bg-success">Verified</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            @if (auth()->user()->role_name === 'admin')
                                                <td class="text-center">
                                                    @if ($data['verified_by_admin'] === 0)
                                                        <a href="#" class="verify-link btn btn-sm btn-danger"
                                                            data-id="{{ $data['id'] }}"
                                                            data-category="{{ $data['category'] }}" data-verified="1"
                                                            data-bs-toggle="tooltip" title="Verify Item">
                                                            <i class="fas fa-check-circle"></i> Verify
                                                        </a>
                                                    @else
                                                        <a href="#"
                                                            class="verify-link btn btn-sm btn-outline-danger"
                                                            data-id="{{ $data['id'] }}"
                                                            data-category="{{ $data['category'] }}" data-verified="0"
                                                            data-bs-toggle="tooltip" title="Mark as Unverified">
                                                            <i class="fas fa-times-circle"></i> Unverify
                                                        </a>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Items Found</h4>

                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($paginatedData->hasPages())
                        <div class="d-flex justify-content-end mt-4">
                            <nav aria-label="Page navigation">
                                {{ $paginatedData->onEachSide(1)->links('pagination::bootstrap-5') }}
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

            // Verification toggle
            $(document).on('click', '.verify-link', function(e) {
                e.preventDefault();

                const link = $(this);
                const id = link.data('id');
                const verified = link.data('verified');
                const category = link.data('category');
                const spinner = $(
                    '<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>'
                );

                // Show loading state
                link.prop('disabled', true);
                link.append(spinner);

                // Mapping categories to their verification URLs
                const categoryUrlMap = {
                    'Daily Expense': 'verify/expenses',
                    'Raw Material': 'verify/materials',
                    'Square Footage Bill': 'verify/square-footage',
                    'Attendance': 'verify/attendance'
                };

                // Get the appropriate URL based on the category
                const url = categoryUrlMap[category];

                if (!url) {
                    console.error('Invalid category:', category);
                    return;
                }

                $.ajax({
                    url: `{{ url('admin') }}/${url}/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        verified: verified,
                    },
                    success: function(response) {
                        // Reload the page to reflect changes
                        window.location.reload();
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        $('#messageContainer').html(`
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <div>An error occurred. Please try again.</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `);
                    },
                    complete: function() {
                        link.prop('disabled', false);
                        spinner.remove();
                    }
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit when any filter changes
            document.querySelectorAll('.auto-submit').forEach(select => {
                select.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Reset form functionality
            document.getElementById('resetFilters').addEventListener('click', function() {
                // Reset all select elements to 'all'
                document.querySelectorAll('.auto-submit').forEach(select => {
                    select.value = 'all';
                });

                // Submit the form to reset all filters
                document.getElementById('filterForm').submit();
            });
        });
    </script>
</x-app-layout>
