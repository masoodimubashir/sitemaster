<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Suppliers' ,  $supplier->name, ' Back']"
        :urls="[$user . '/suppliers/' . $supplier->id, $user . '/suppliers/' . $supplier->id, $user . '/suppliers/' . $supplier->id]" />

    <div class="row">

        <div class="table-responsive mt-4">

            @if (count($payments))
                <table class="table table-bordered">
                    <thead>
                        <tr>

                            <td class="bg-info fw-bold text-white">Date</td>
                            <td class="bg-info fw-bold text-white">Amount</td>
                            <td class="bg-info fw-bold text-white">Supplier Name</td>
                            <td class="bg-info fw-bold text-white">Site Owner Name</td>
                            <td class="bg-info fw-bold text-white">Action</td>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $pay)
                            <tr>
                                <td>{{ $pay->created_at }}</td>
                                <td>{{ $pay->amount }}</td>
                                <td>{{ $pay->supplier->name }}</td>
                                <td>{{ $pay->site->site_owner_name ?? '--' }}</td>
                                <td>

                                    @if ($pay->verified_by_admin)
                                        <a href="#" class="verify-link ms-3 badge badge-info nav-link text-black"
                                            data-name="pay" data-id="{{ $pay->id }}" data-verified="0">
                                            Verified
                                        </a>
                                    @else
                                        <a href="#"
                                            class="verify-link ms-3 badge badge-danger nav-link text-black"
                                            data-name="pay" data-id="{{ $pay->id }}" data-verified="1">
                                            Verify
                                        </a>
                                    @endif
                                </td>

                            </tr>
                        @endforeach

                    </tbody>


                </table>
            @else
                <table class="table table-bordered">
                    <thead></thead>
                    <tbody>
                        <tr>
                            <td class="text-center text-danger fw-bold">No Records Available Yet</td>
                        </tr>
                    </tbody>
                </table>

            @endif

        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </div>




    <script>
        $(document).ready(function() {
            $('.verify-link').on('click', function(e) {
                e.preventDefault();

                const $link = $(this);
                const recordId = $link.data('id');
                const verifiedStatus = $link.data('verified');
                const recordName = $link.data('name');

                $.ajax({
                    url: '{{ url($user . '/verify-payments') }}',
                    type: 'PUT',
                    data: {
                        id: recordId,
                        verified: verifiedStatus,
                        name: recordName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Update link attributes and appearance
                            if (verifiedStatus === 1) {
                                $link.removeClass('badge-danger')
                                    .addClass('badge-info')
                                    .text('Verified')
                                    .data('verified', 0);
                            } else {
                                $link.removeClass('badge-info')
                                    .addClass('badge-danger')
                                    .text('Verify')
                                    .data('verified', 1);
                            }

                            // Optional: Show success notification
                            toastr.success(response.message);
                        }
                    },
                    error: function(xhr) {
                        // Handle error
                        toastr.error('Unable to update verification status');
                        console.error(xhr.responseJSON);
                    }
                });
            });
        });
    </script>



</x-app-layout>
