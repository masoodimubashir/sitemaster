@props([
    'editUrl' => null,
    'deleteUrl' => null,
    'itemId' => null,
    'userType' => null,
    'deleteMessage' => 'Are you sure you want to delete this item?',
    'editTooltip' => 'Edit',
    'deleteTooltip' => 'Delete',
    'editIcon' => 'fas fa-edit',
    'deleteIcon' => 'fas fa-trash-alt',
])

<div class="d-flex justify-content-center gap-2">
    <!-- Edit Icon -->
    <a href="{{ $editUrl }}" class="btn btn-sm btn-link text-primary p-0" data-bs-toggle="tooltip"
        title="{{ $editTooltip }}">
        <i class="{{ $editIcon }}"></i>
    </a>

    <!-- Delete Icon (only shown for admin) -->
    @if ($userType === 'admin')
        <form action="{{ $deleteUrl }}" method="POST" class="d-inline delete-form">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-link text-danger p-0" data-bs-toggle="tooltip"
                title="{{ $deleteTooltip }}" onclick="confirmDelete(this, '{{ $deleteMessage }}')">
                <i class="{{ $deleteIcon }}"></i>
            </button>
        </form>
    @endif
</div>

@once
    @push('scripts')
        <script>
            function confirmDelete(button, message) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.closest('form').submit();
                    }
                });
            }
        </script>
    @endpush
@endonce
