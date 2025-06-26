<x-app-layout>
    <!-- Flash Messages Container -->
    <div id="messageContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999; max-width: 400px;"></div>

    <x-breadcrumb :names="['Phases']" :urls="['trash/phases']" />

    <div class="row">
        <div class="col-12">
            <div class=" border-0">
                <div class="card-header  py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-layer-group me-2"></i>
                            Deleted Phases
                        </h5>
                        @if($phases->isNotEmpty())
                        <div class="badge bg-light text-dark">
                            <i class="fas fa-trash-restore me-1"></i>
                            {{ $phases->total() }} deleted items
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        @if ($phases->isNotEmpty())
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="fw-semibold">#</th>
                                        <th class="fw-semibold">Phase Name</th>
                                        <th class="fw-semibold text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($phases as $phase)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="fw-semibold text-primary d-flex align-items-center">
                                                    <i class="fas fa-project-diagram me-2 text-muted"></i>
                                                    {{ ucfirst($phase->phase_name) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end gap-2">
                                                    <a href="{{ route('trash.restore', ['model_name' => 'phase', 'id' => $phase->id]) }}"
                                                       class="btn btn-sm btn-outline-success restore-btn"
                                                       data-bs-toggle="tooltip"
                                                       title="Restore Phase">
                                                        <i class="fas fa-history me-1"></i> Restore
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#permanentDeleteModal"
                                                            data-id="{{ $phase->id }}"
                                                            data-name="{{ $phase->phase_name }}"
                                                            data-type="phase">
                                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-layer-group fa-4x text-light mb-4"></i>
                                    <h4 class="text-muted">No Deleted Phases Found</h4>
                                    <a href="{{ url('/admin/phase') }}" class="btn btn-primary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to Phases
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($phases->hasPages())
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex justify-content-center">
                                {{ $phases->onEachSide(1)->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Permanent Delete Modal -->
    <div class="modal fade" id="permanentDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirm Permanent Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to permanently delete phase: <strong id="deleteItemName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone and all related data will be lost.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="permanentDeleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Delete Permanently
                        </button>
                    </form>
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

            // Permanent delete modal handler
            $('#permanentDeleteModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const name = button.data('name');
                const type = button.data('type');
                
                const modal = $(this);
                modal.find('#deleteItemName').text(name);
                modal.find('#permanentDeleteForm').attr('action', `/admin/trash/${type}/${id}/force-delete`);
            });

            // Restore button click handler
            $('.restore-btn').on('click', function(e) {
                e.preventDefault();
                const button = $(this);
                button.prop('disabled', true);
                button.append('<span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>');
                
                window.location.href = button.attr('href');
            });
        });
    </script>
</x-app-layout>