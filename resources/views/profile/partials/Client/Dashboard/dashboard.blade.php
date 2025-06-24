
<x-app-layout>


    <x-breadcrumb :names="['Sites']" :urls="['admin/sites']" />

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header  py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                            Sites
                        </h5>

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
                                                    <a href="{{ url('client/dashboard', [base64_encode($site->id)]) }}"
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
                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#create-site-modal">
                                        <i class="fas fa-plus me-1"></i>
                                        Create New Site
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($sites->hasPages())
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


</x-app-layout>
