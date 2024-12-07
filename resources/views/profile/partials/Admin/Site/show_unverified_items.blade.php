<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            right: 45%;
            z-index: 9999999;
        }
    </style>

    <x-breadcrumb :names="['Ledger']" :urls="[$user . '/payments']"></x-breadcrumb>

    <div class="row">

        <div class="col-sm-12">

            <div class="home-tab">

                <div class="d-sm-flex align-items-center justify-content-between border-bottom">

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="workforce-tab" data-bs-toggle="tab" href="#workforce"
                                role="tab" aria-controls="workforce" aria-selected="true">Ledger</a>
                        </li>

                    </ul>

                </div>

                <div class="tab-content mt-3">

                    <div class="tab-pane fade show active" id="workforce" role="tabpanel"
                        aria-labelledby="workforce-tab">

                        <div class="table-responsive mt-4">

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-danger">
                                                <small>
                                                    <b>
                                                        ...
                                                    </b>
                                                </small>
                                                <h4 class="fw-bold">
                                                    ..
                                                </h4>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-warning">
                                                <small>
                                                    <b>
                                                        ...
                                                    </b>
                                                </small>
                                                <h4 class="fw-bold">
                                                    ...
                                                </h4>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-success">
                                                <small>
                                                    <b>
                                                        ...
                                                    </b>
                                                </small>
                                                <h4 class="fw-bold">
                                                    ...
                                                </h4>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">
                                                <small>
                                                    <b>
                                                        Ongoing Sites
                                                    </b>
                                                </small>
                                                <h4>
                                                    ...
                                                </h4>

                                            </div>
                                        </td>

                                        <td>
                                            <div class="p-3 d-flex flex-column gap-2 text-info fw-bold">
                                                <small>
                                                    <b>
                                                        ...
                                                    </b>
                                                </small>
                                                <h4>
                                                    ...
                                                </h4>

                                            </div>
                                        </td>

                                        <td colspan="4" style="background: #F4F5F7; border:none">

                                            <div class="row">

                                                <form class="col " action="{{ url($user . '/payments') }}"
                                                    method="GET" id="filterForm">
                                                    <select class="form-select form-select-sm bg-white text-black"
                                                        style="cursor: pointer" name="date_filter" id="date_filter"
                                                        onchange="document.getElementById('filterForm').submit();">
                                                        <option value="today"
                                                            {{ request('date_filter') === 'today' ? 'selected' : '' }}>
                                                            Today</option>
                                                        <option value="yesterday"
                                                            {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>
                                                            Yesterday</option>
                                                        <option value="this_week"
                                                            {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>
                                                            This Week</option>
                                                        <option value="this_month"
                                                            {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>
                                                            This Month</option>
                                                        <option value="this_year"
                                                            {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>
                                                            This Year</option>
                                                        <option value="lifetime"
                                                            {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>
                                                            All Data
                                                        </option>
                                                    </select>

                                                </form>

                                            </div>
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

                                                    @if ( $data['verified_by_admin'] === 0)
                                                        <a href="#"
                                                            class="verify-link ms-3   text-danger nav-link text-black"
                                                            data-id="{{ $data['id'] }}"
                                                            data-category="{{ $data['category'] }}" data-verified="1">
                                                            Verify
                                                        </a>
                                                    @else
                                                        <a href="#"
                                                            class="verify-link ms-3   text-info nav-link text-black"
                                                            data-id="{{ $data['id'] }}"
                                                            data-category="{{ $data['category'] }}" data-verified="0">
                                                            Verified
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td class="text-danger fw-bold text-center" colspan="8">No Records
                                                Awailable...</td>
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
                            location.reload();
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
                            location.reload();
                        });
                    }, 500);
                }
            });
        });
    </script>

</x-app-layout>
