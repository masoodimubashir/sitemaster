<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Wastas']" :urls="[$user . '/wasta']" />

    <!-- Flash Messages Container -->
    <div id="messageContainer" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; max-width: 400px;">
        @if (session('status') === 'create')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Wasta Created Successfully</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'update' || session('status') === 'verify')
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>Wasta Updated</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'delete')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Wasta Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('status') === 'error' || session('status') === 'hasPaymentRecords')
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>Wasta Cannot Be Deleted</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-12">
            <div class="mb-4 border-0">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user-friends me-2 text-info"></i> Wastas
                    </h5>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#create-wasta-modal">
                        <i class="fas fa-plus me-1"></i> Create Wasta
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive rounded">
                        @if ($wastas->count())
                            <table class="table align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Contact No</th>
                                        <th>Created At</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($wastas as $wasta)
                                        <tr>
                                            <td>{{ $wasta->wasta_name }}</td>
                                            <td>{{ $wasta->price }}</td>
                                            <td>{{ $wasta->contact_no ?? '-' }}</td>
                                            <td>{{ $wasta->created_at->format('d-M-Y') }}</td>
                                            <td class="text-center">
                                                <x-actions editUrl="{{ url($user . '/wasta/' . $wasta->id . '/edit') }}"
                                                    deleteUrl="{{ url($user . '/wasta/' . $wasta->id) }}"
                                                    userType="{{ $user }}"
                                                    deleteMessage="Are you sure you want to delete this Wasta?" />

                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="alert-light text-center py-5">
                                <h4 class="text-muted">No Wasta Found</h4>
                            </div>
                        @endif
                    </div>
                    {{ $wastas->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="create-wasta-modal" tabindex="-1">
        <div class="modal-dialog">
            <form id="createWastaForm" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Create Wasta</h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <!-- Within the Create Modal -->
                    <div class="modal-body">
                        <label>Name</label>
                        <input type="text" name="wasta_name" class="form-control">
                        <div class="text-danger small error-message" data-error-for="wasta_name"></div>

                        <!-- Add this block for Price -->
                        <label class="mt-2">Price</label>
                        <input type="number" name="price" class="form-control" step="0.01">
                        <div class="text-danger small error-message" data-error-for="price"></div>

                        <label class="mt-2">Contact No</label>
                        <input type="text" name="contact_no" class="form-control">
                        <div class="text-danger small error-message" data-error-for="contact_no"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" id="saveWastaBtn" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

   

    @push('scripts')
        <script>
            $(function() {

                // CREATE
                $('#createWastaForm').submit(function(e) {
                    e.preventDefault();
                    let form = $(this);
                    let btn = $('#saveWastaBtn');
                    form.find('.error-message').text('');
                    btn.prop('disabled', true).text('Saving...');

                    $.ajax({
                        url: "{{ url($user . '/wasta') }}",
                        method: "POST",
                        data: form.serialize(),
                        success: function(res) {
                            if (res.status) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                $.each(errors, function(k, v) {
                                    form.find(`[data-error-for="${k}"]`).text(v[0]);
                                });
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false).text('Save');
                        }
                    });
                });

                // OPEN EDIT MODAL
                $('.editWastaBtn').click(function() {
                    let id = $(this).data('id');
                    let name = $(this).data('name');
                    let contact = $(this).data('contact');

                    let form = $('#editWastaForm');
                    form.attr('action', "{{ url($user . '/wasta') }}/" + id);
                    form.find('[name="wasta_name"]').val(name);
                    form.find('[name="contact_no"]').val(contact);

                    $('#edit-wasta-modal').modal('show');
                });

                // UPDATE
                $('#editWastaForm').submit(function(e) {
                    e.preventDefault();
                    let form = $(this);
                    let btn = $('#updateWastaBtn');
                    form.find('.error-message').text('');
                    btn.prop('disabled', true).text('Updating...');

                    $.ajax({
                        url: form.attr('action'),
                        method: "POST",
                        data: form.serialize(),
                        success: function(res) {
                            if (res.status) {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                $.each(errors, function(k, v) {
                                    form.find(`[data-error-for="${k}"]`).text(v[0]);
                                });
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false).text('Update');
                        }
                    });
                });

            });
        </script>
    @endpush

</x-app-layout>
