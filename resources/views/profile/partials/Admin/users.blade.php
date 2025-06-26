<x-app-layout>

    @php
        $authUser = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <!-- Flash Messages Container (positioned fixed like in payments page) -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Site Engineer Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'update')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Site Engineer Updated Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Site Engineer Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Sorry! Site Engineer Not Found</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'hasPayment')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Sorry! Site Engineer Cannot Be Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <x-breadcrumb :names="['Site Engineer']" :urls="['admin/users']" />

    <div class="row">
        <div class="col-12">
            <div class=" mb-4 border-0">
                <div class="card-header border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-helmet-safety menu-icon fs-5 text-success"></i>

                            Site Engineers
                        </h5>
                        <a class="btn btn-sm btn-success" href="{{ url('admin/users/create') }}">
                            <i class="fas fa-user-plus me-1"></i>
                            Create Engineer
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if (count($users))
                            <table class="table  align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="fw-bold ps-4">Name</th>
                                        <th class="fw-bold">Username</th>
                                        <th class="fw-bold">Assigned Sites</th>
                                        <th class="fw-bold text-center pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td class="ps-4">{{ ucfirst($user->name) }}</td>
                                            <td>{{ $user->username }}</td>
                                            <td>
                                                @if ($user->sites->count() > 0)
                                                    <button class="btn btn-sm btn-outline-info text-black"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#sitesModal-{{ $user->id }}">
                                                        <i class="fas fa-eye me-1"></i>
                                                        View Sites ({{ $user->sites->count() }})
                                                    </button>
                                                @else
                                                    <span class="badge bg-secondary">No sites assigned</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <x-actions
                                                    editUrl="{{ url($authUser . '/edit-user/' . $user->id ) }}"
                                                    deleteUrl="{{ url($authUser . '/user/delete/' . $user->id) }}"
                                                    userType="{{ $authUser }}"
                                                    deleteMessage="Are you sure you want to delete this user?" />
                                            </td>
                                        </tr>

                                        <!-- Sites Modal for each user -->
                                        <div class="modal fade" id="sitesModal-{{ $user->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header text-black">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-map-marker-alt me-2"></i>
                                                            Sites Assigned to {{ ucwords($user->name) }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul class="list-group">
                                                            @foreach ($user->sites as $site)
                                                                <li
                                                                    class="list-group-item d-flex justify-content-between align-items-center">
                                                                    {{ ucwords($site->site_name) }}
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert-light text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-user-slash fa-4x text-muted mb-4"></i>
                                    <h4 class="text-muted">No Site Engineers Found</h4>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($users->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                {{ $users->onEachSide(1)->links('pagination::bootstrap-5') }}
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
