<x-app-layout>
    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }

        .header-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .header-icon {
            background-color: #3b82f6;
            color: white;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .tab-container {
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .tab {
            display: inline-block;
            padding: 10px 0;
            margin-right: 30px;
            cursor: pointer;
        }

        .tab.active {
            border-bottom: 2px solid #3b82f6;
            color: #3b82f6;
            font-weight: 500;
        }

        .badge {
            background-color: #e5e7eb;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 12px;
            margin-left: 5px;
        }

        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .filter-label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }

        .filter-input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .summary-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            flex: 1;
            padding: 15px;
            border-radius: 8px;
        }

        .summary-card.gave {
            background-color: #fee2e2;
        }

        .summary-card.got {
            background-color: #d1fae5;
        }

        .summary-card.balance {
            background-color: #e0e7ff;
        }

        .summary-amount {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 14px;
        }

        .gave-text {
            color: #dc2626;
        }

        .got-text {
            color: #059669;
        }

        .balance-text {
            color: #4f46e5;
        }

        .report-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .report-table th {
            text-align: left;
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: 500;
            color: #4b5563;
        }

        .report-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        .report-table tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline {
            border: 1px solid #d1d5db;
            background-color: white;
            color: #374151;
        }
    </style>




    <div class="header-container">


        <div class="header-icon">
            <i class="menu-icon fa fa-building"></i>
        </div>
        <h1 class="text-xl font-semibold">Site Report</h1>
        <div class="ms-auto action-buttons d-flex gap-2">
            <!-- Dropdown Menu -->
            <div class="dropdown">
                <button class="btn btn-outline dropdown-toggle " type="button" id="dropdownMenuButton"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Make Entry
                </button>

                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">


                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button" href="#phase">
                            Create Phase
                        </a>
                    </li>


                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button"
                            href="#modal-construction-billings{{ $id }}">
                            Construction
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button"
                            href="#modal-square-footage-bills{{ $id }}">
                            Contractor
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button"
                            href="#modal-daily-expenses{{ $id }}">
                            Expenditure
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" data-bs-toggle="modal" role="button"
                            href="#modal-daily-wager{{ $id }}">
                            Wager
                        </a>
                    </li>
                </ul>
            </div>
            <form action="{{ url($user . '/ledger/report') }}" method="GET">
                <input type="hidden" name="site_id" value="{{ request('site_id', 'all') }}">
                <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                <input type="hidden" name="supplier_id" value="{{ request('supplier_id', 'all') }}">
                <input type="hidden" name="wager_id" value="{{ request('wager_id', 'all') }}">
                <button type="submit" class="btn btn-outline">
                    <i class="far fa-file-pdf"></i> Download PDF
                </button>
            </form>

            <a href="{{ url('admin/sites/details/' . base64_encode($id)) }}" class="btn btn-outline">
                <i class="fas fa-eye"></i> View Site Detail
            </a>


        </div>
    </div>




    <form class="d-flex flex-column flex-md-row gap-2 w-100" action="{{ url($user . '/sites/' . $id) }}" method="GET"
        id="filterForm">



        <!-- Supplier Select -->
        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm" name="supplier_id"
            onchange="document.getElementById('filterForm').submit();">
            <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>All Suppliers</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier['supplier_id'] }}"
                    {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>
                    {{ $supplier['supplier'] }}
                </option>
            @endforeach
        </select>



        <!-- Period Select -->
        <select style="cursor: pointer" class="bg-white text-black form-select form-select-sm" name="date_filter"
            id="date_filter" onchange="document.getElementById('filterForm').submit();">
            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
            <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday
            </option>
            <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week
            </option>
            <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month
            </option>
            <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year
            </option>
            <option value="lifetime" {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>All Data
            </option>
        </select>

        <!-- Start Date -->
        <input type="date" name="start_date" class="form-control form-control-sm bg-white text-black"
            value="{{ request('start_date') }}" onchange="document.getElementById('filterForm').submit();">

        <!-- End Date -->
        <input type="date" name="end_date" class="form-control form-control-sm bg-white text-black"
            value="{{ request('end_date') }}" onchange="document.getElementById('filterForm').submit();">
    </form>



    <div class="mt-4">

        <div class="summary-cards">

            <div class="summary-card gave">
                <div class="summary-amount gave-text">₹{{ number_format($total_balance) }}</div>
                <div class="summary-label gave-text">Total Balance</div>
            </div>

            <div class="summary-card gave">
                <div class="summary-amount gave-text">₹{{ number_format($total_due) }}</div>
                <div class="summary-label gave-text">Total Due</div>
            </div>

            <div class="summary-card balance">
                <div class="summary-amount balance-text">₹{{ number_format($effective_balance) }}</div>
                <div class="summary-label balance-text">Effective Balance</div>
            </div>

            <div class="summary-card got">
                <div class="summary-amount got-text">₹{{ number_format($total_paid) }}</div>
                <div class="summary-label got-text">Total Paid</div>
            </div>
        </div>



        <div class="card">
            <div class="table-responsive mt-4">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>Customer Name</th>
                            <th>DETAILS</th>
                            <th style="text-align: right;">Debit</th>
                            <th style="text-align: right;">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($paginatedLedgers))
                            @foreach ($paginatedLedgers as $key => $ledger)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($ledger['created_at'])->format('d M Y') }}</td>
                                    <td>{{ ucwords($ledger['supplier']) }}</td>
                                    <td>
                                        {{ ucwords($ledger['description']) }}
                                        <div class="text-sm text-gray-500">
                                            {{ ucwords($ledger['phase']) }} / {{ $ledger['category'] }}
                                        </div>
                                    </td>
                                    <td style="text-align: right;" class="gave-text">
                                        @if ($ledger['debit'] > 0)
                                            ₹{{ number_format($ledger['debit']) }}
                                        @else
                                            ₹0
                                        @endif
                                    </td>
                                    <td style="text-align: right;" class="got-text">
                                        @if ($ledger['credit'] > 0)
                                            ₹{{ number_format($ledger['credit']) }}
                                        @else
                                            ₹0
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">No records available</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $paginatedLedgers->links() }}
        </div>
    </div>


    {{-- Are The Models Are Here --}}
    <div id="phase" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
        tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <form class="forms-sample material-form" id="phaseForm">
                        @csrf

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="text" name="phase_name" id="phase_name" />
                            <label for="phase_name" class="control-label">Phase Name</label>
                            <i class="bar"></i>
                            <x-input-error :messages="$errors->get('phase_name')" class="mt-2" />
                        </div>

                        {{-- Site --}}
                        <div class="form-group">
                            <input type="hidden" name="site_id" value="{{ $id }}" />
                            <x-input-error :messages="$errors->get('site_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                Create Phase
                            </x-primary-button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="messageContainer"></div>


    <script>
        $(document).ready(function() {


            // Model Ajax Functions


            $('form[id="phaseForm"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();


                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url('admin/phase') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        form[0].reset();
                        messageContainer.append(`
                             <div  class="alert align-items-center text-white bg-success border-0" role="alert" >
                                 <div class="d-flex">
                                    <div class="toast-body">
                                        <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                    </div>
                                </div>
                            </div> `);
                        // Auto-hide success message after 3 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                            location.reload();
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) { // Validation errors
                            messageContainer.append(`
                    <div class="alert alert-danger mt-3 alert-dismissible fade show  " role="alert">
                    ${response.responseJSON.errors}

                    </div>`)

                        } else {
                            messageContainer.append(`
                    <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                        An unexpected error occurred. Please try again later.

                    </div>
                `);
                        }
                        // Auto-hide error message after 5 seconds
                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');

                        }, 2000);
                    }
                });
            });


        });



        function resetForm() {
            document.querySelector('select[name="site_id"]').value = 'all';
            document.querySelector('select[name="date_filter"]').value = 'today';
            document.querySelector('select[name="supplier_id"]').value = 'all';
            document.querySelector('select[name="wager_id"]').value = 'all';

            // Redirect to specific site
            window.location.href = "{{ url($user . '/sites/' . base64_encode($id)) }}";
        }
    </script>
</x-app-layout>
