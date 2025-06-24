<x-app-layout>
    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Site Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'update' || session('status') === 'verify')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Site Verification Updated</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Site Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Site Cannot Be Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Sites']" :urls="['user/dashboard']" />

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            Site Management
                        </h5>
                        <a href="{{ url('user/sites/create') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-plus me-1"></i>
                            Create Site
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($sites))
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold">Status</th>
                                        <th class="fw-bold">Date</th>
                                        <th class="fw-bold">Site Name</th>
                                        <th class="fw-bold">Location</th>
                                        <th class="fw-bold">Contact No</th>
                                        <th class="fw-bold">Owner</th>
                                        <th class="fw-bold">Service Charge</th>
                                        <th class="fw-bold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sites as $site)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $site->is_on_going ? 'success' : 'danger' }}">
                                                    {{ $site->is_on_going ? 'Open' : 'Closed' }}
                                                </span>
                                            </td>
                                            <td>{{ $site->created_at->format('d-M-Y') }}</td>
                                            <td>
                                                @if ($site->is_on_going)
                                                    <a href="{{ url('user/sites/' . base64_encode($site->id)) }}" 
                                                       class="fw-bold text-primary text-decoration-none">
                                                        {{ ucfirst($site->site_name) }}
                                                    </a>
                                                @else
                                                    <span>{{ $site->site_name }}</span>
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($site->location) }}</td>
                                            <td>
                                                <a href="tel:{{ $site->contact_no }}" class="text-decoration-none">
                                                    <i class="fas fa-phone me-1 text-muted"></i>
                                                    +91-{{ $site->contact_no }}
                                                </a>
                                            </td>
                                            <td>{{ ucfirst($site->site_owner_name) }}</td>
                                            <td>{{ $site->service_charge }}%</td>
                                            <td class="text-center">
                                                <form action="{{ route('sites.update-on-going', $site->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('POST')
                                                    <button type="submit" 
                                                            class="btn btn-sm {{ $site->is_on_going ? 'btn-success' : 'btn-danger' }}"
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ $site->is_on_going ? 'Mark as Closed' : 'Mark as Open' }}">
                                                        <i class="fas {{ $site->is_on_going ? 'fa-check' : 'fa-times' }} me-1"></i>
                                                        {{ $site->is_on_going ? 'Verified' : 'Verify' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-map-marked-alt fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Sites Found</h4>
                                    <p class="text-muted mb-4">There are no site records available at the moment.</p>
                                    <a href="{{ url('user/sites/create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>
                                        Create New Site
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($sites->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $sites->onEachSide(1)->links('pagination::bootstrap-5') }}
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