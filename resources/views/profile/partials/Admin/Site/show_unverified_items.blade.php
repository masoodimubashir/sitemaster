<x-app-layout>

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            right: 45%;
            z-index: 9999999;
        }
    </style>

    <x-breadcrumb :names="['Verify Items']" :urls="['admin/item-verification']"></x-breadcrumb>

    <div class="row">

        <div class="col-sm-12">


            <div class="table-responsive mt-4">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <td class="bg-transparent" colspan="4">
                                <form class="d-flex flex-column flex-md-row gap-2 w-100"
                                    action="{{ url('admin/item-verification') }}" method="GET" id="filterForm">

                                    <select style="cursor: pointer"
                                        class="bg-white text-black form-select form-select-sm mt-2" name="site_id">
                                        <option value="all" {{ request('site_id') === 'all' ? 'selected' : '' }}>
                                            All Sites
                                        </option>
                                        @foreach ($sites as $site)
                                            <option value="{{ $site['site_id'] }}"
                                                {{ request('site_id') == $site['site_id'] ? 'selected' : '' }}>
                                                {{ $site['site'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-info text-white mt-2">Filter</button>
                                        <button type="button" class="btn btn-success  text-white mt-2"
                                            onclick="resetForm()">Reset</button>
                                    </div>
                                </form>
                            </td>



                        </tr>
                        <tr>
                            <th class="bg-info text-white fw-bold ">Date | Time</th>
                            <th class="bg-info text-white fw-bold ">Supplier Name</th>
                            <th class="bg-info text-white fw-bold ">Phase</th>
                            <th class="bg-info text-white fw-bold ">Site Name</th>
                            <th class="bg-info text-white fw-bold ">Type</th>
                            <th class="bg-info text-white fw-bold">Information</th>
                            <th class="bg-info text-white fw-bold">Verify</th>

                        </tr>
                    </thead>
                    <tbody>
                        @if (count($paginatedData))


                            @foreach ($paginatedData as $key => $data)
                                @php
                                    // $balance =  $data['amount'] - $data['payment_amounts'];
                                @endphp
                                <tr>
                                    <td>
                                        {{ $data['created_at'] }}
                                    </td>
                                    <td>
                                        {{ ucwords($data['supplier']) }}
                                    </td>
                                    <td>
                                        {{ ucwords($data['phase']) }}
                                    </td>
                                    <td>
                                        {{ ucwords($data['site']) }}
                                    </td>
                                    <td>
                                        {{ $data['category'] }}
                                    </td>
                                    <td>
                                        {{ ucwords($data['description']) }}
                                    </td>
                                    <td>

                                        @if ($data['verified_by_admin'] === 0)
                                            <a href="#"
                                                class="verify-link ms-3   badge badge-danger text-black nav-link"
                                                data-id="{{ $data['id'] }}" data-category="{{ $data['category'] }}"
                                                data-verified="1">
                                                Verify
                                            </a>
                                        @else
                                            <a href="#"
                                                class="verify-link ms-3 badge badge-info text-black nav-link"
                                                data-id="{{ $data['id'] }}" data-category="{{ $data['category'] }}"
                                                data-verified="0">
                                                Verified
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="text-danger fw-bold text-center" colspan="8">No Records
                                    Available...</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            </div>

            <div class="mt-4">
                {{ $paginatedData->links() }}
            </div>

        </div>
    </div>

    <div id="messageContainer"> </div>

    <script>
        $(document).on('click', '.verify-link', function(e) {
            e.preventDefault();

            const link = $(this);
            const id = link.data('id');
            const verified = link.data('verified');
            const category = link.data('category');
            const messageContainer = $('#messageContainer');
            messageContainer.empty();

            // Mapping categories to their verification URLs
            const categoryUrlMap = {
                'Daily Expense': 'verify/expenses',
                // 'Daily Wager': 'verify/wagers',
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
                    verified: verified
                },
                success: function(response) {
                    // Toggle verification status
                    if (verified == 1) {
                        link.html('Verified');
                        link.data('verified', 0);
                        link.removeClass('badge-danger').addClass('badge-info');
                    } else {
                        link.html('Verify');
                        link.data('verified', 1);
                        link.removeClass('badge-info').addClass('badge-danger');
                    }

                    // Show success message
                    if (response.message) {
                        messageContainer.append(`
                    <div class="alert align-items-center text-white bg-success border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                            </div>
                        </div>
                    </div>
                `);
                    }

                    // Fade out message and reload page
                    setTimeout(function() {
                        messageContainer.find('.alert').fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 500);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    messageContainer.append(`
                <div class="alert align-items-center text-white bg-danger border-0" role="alert">
                    <div class="d-flex">
                        <div class="toast-body">
                            <strong><i class="fas fa-exclamation-circle me-2"></i></strong>An error occurred. Please try again.
                        </div>
                    </div>
                </div>
            `);

                    // Fade out error message and reload page
                    setTimeout(function() {
                        messageContainer.find('.alert').fadeOut('slow', function() {
                            $(this).remove();
                        });
                    }, 500);
                }
            });
        });
    </script>


    <script>
        function resetForm() {
            // Reset select fields to default values
            document.querySelector('select[name="site_id"]').value = 'all';

            window.location.href = "{{ url('admin/item-verification') }}";
        }
    </script>

</x-app-layout>
