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
                    <div>Client Updated Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Sorry! Client Not Found</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Client Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Client Deleted Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Clients']" :urls="[$user . '/clients']" />

    <div class="row">
        <div class="col-12">
            <div class=" mb-4 border-0">
                <div class="card-header  border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2 text-primary text-success"></i>
                            Clients
                        </h5>
                        <a class="btn btn-sm btn-success" href="{{ url($user . '/clients/create') }}">
                            <i class="fas fa-user-plus me-1"></i>
                            Create Client
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($clients))
                            <table class="table  align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold ps-4">Name</th>
                                        <th class="fw-bold">Username / Number</th>
                                        <th class="fw-bold text-center pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($clients as $client)
                                        <tr>
                                            <td class="ps-4">{{ ucfirst($client->name) }}</td>
                                            <td>{{ $client->number }}</td>
                                            <td class="text-center">
                                                <x-actions
                                                    editUrl="{{ url($user . '/clients/' . base64_encode($client->id) . '/edit') }}"
                                                    deleteUrl="{{ url($user . '/clients/' . $client->id) }}"
                                                    userType="{{ $user }}"
                                                    deleteMessage="Are you sure you want to delete this client?" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-user-slash fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Clients Found</h4>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($clients->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $clients->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imageModel" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="imageModalLabel">Image Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid rounded">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

            // Image modal handler
            window.showImage = function(src) {
                $('#modalImage').attr('src', src);
                $('#imageModel').modal('show');
            };
        });
    </script>
</x-app-layout>
