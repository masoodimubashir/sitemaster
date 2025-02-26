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

        .accordion {
            margin: 0 auto;
        }

        /* Accordion item */
        .accordion-item {
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        /* Accordion header */
        .accordion-header {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 15px;
            text-align: left;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            display: block;
            outline: none;
            border-radius: 4px;
        }

        /* Accordion content */
        .accordion-content {
            display: none;
            /* Hidden by default */
            padding: 15px;
            background-color: #f1f1f1;
            border-top: 1px solid #ddd;
        }

        /* Transition for smooth expansion */
        .accordion-content {
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .accordion-content.show {
            display: block;
            max-height: 500px;
            /* Arbitrary large value */
        }
    </style>


    <x-breadcrumb :names="['Suppliers', $data['supplier']->name]" :urls="[$user . '/suppliers', $user . '/suppliers/' . $data['supplier']->id]" />


    <div class="row mb-4">

        <div class="col-12">

            <div class="d-flex flex-wrap justify-content-start gap-2">

                <button class="btn btn-info btns" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Make Payment
                </button>

                <a href="{{ url($user . '/supplier/payments', [$data['supplier']->id]) }}" class="btn btn-info btns"
                    data-modal="payment-supplier">
                    View Payments
                </a>

                <a href="{{ url($user . '/supplier/ledger', [$data['supplier']->id]) }}" class="btn btn-info btns"
                    data-modal="payment-supplier">
                    View Ledger
                </a>

                <a href="{{ url($user . '/supplier-payment/report', ['id' => base64_encode($data['supplier']->id)]) }}"
                    class="btn btn-info">
                    Generate Payment Report
                </a>

                @if ($user === 'admin')
                    <a href="{{ url($user . '/unverified-supplier-payments/' . $data['supplier']->id) }}"
                        class="btn btn-info">
                        Unverified Payments
                    </a>
                @endif

            </div>

        </div>

    </div>


    <div class="row g-4">
        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">

                    <div class="d-flex align-items-center  mb-3">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-user text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Supplier</h6>
                            {{ ucfirst($data['supplier']->name) }}

                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">

                            <i class="fa-solid fa-phone text-info fs-3  p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Contact</h6>
                            <h5 class="mb-0">

                                <a href="tel:+91-{{ $data['supplier']->contact_no }}"
                                    class="text-decoration-none">91-{{ ucfirst($data['supplier']->contact_no) }}</a>
                            </h5>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                
                <div class="card-body">

                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-map-marker-alt text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Location</h6>
                            {{ ucfirst($data['supplier']->address) }}

                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class=" bg-opacity-10 ">
                            <i class="fas fa-money-bill text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Debit</h6>
                            {{ Number::currency($data['totalDebit'] ?? 0, 'INR') }}
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class=" bg-{{ $data['balance'] >= 0 ? '' : '' }} fs-3 p-2">
                            <i class="fas fa-balance-scale text-{{ $data['balance'] >= 0 ? 'info' : 'danger' }}"></i>
                        </div>
                        <div>
                            <h6 class="text-{{ $data['balance'] >= 0 ? 'info' : 'danger' }} mb-1">Balance</h6>
                            <h5 class="mb-0 text-{{ $data['balance'] >= 0 ? 'info' : 'danger' }}">
                                {{ Number::currency($data['balance'], 'INR') }}
                            </h5>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class=" fs-3 p-2">
                            <i class="fas fa-credit-card text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Credit</h6>
                            {{ Number::currency($data['totalCredit'] ?? 0 , 'INR') }}

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (count($data) >= 0)

        <div class="row ">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card-body ">

                    <div class="table-responsive mt-4">

                        <table class="table table-bordered">

                            <thead>

                                <tr>

                                    <th class="bg-info fw-bold text-white">
                                        Date
                                    </th>

                                    <th class="bg-info fw-bold text-white"> Bill Proof</th>

                                    <th class="bg-info fw-bold text-white"> Item</th>


                                    <th class="bg-info fw-bold text-white">
                                        Total Amount
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                @if (count($data['data']) > 0)

                                    @foreach ($data['data'] as $d)
                                        <tr>

                                            <td>
                                                {{ $d['created_at'] }}
                                            </td>

                                            <td>

                                                @if ($d['image'] !== null)
                                                    <img src="{{ asset('storage/' . $d['image']) }}" alt="">
                                                @else
                                                    NA
                                                @endif

                                            </td>

                                            <td>
                                                {{ $d['item'] }}
                                            </td>


                                            <td>
                                                {{ $d['total_price'] }}
                                            </td>

                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="text-danger fw-bold text-center" colspan="7">No Records Found...
                                        </td>
                                    </tr>
                                @endif


                            </tbody>

                        </table>

                    </div>

                </div>


            </div>

        </div>

    @endif



    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-body">
                    <form id="payment_form" class="forms-sample material-form" enctype="multipart/form-data">

                        @csrf

                        {{-- Phase Name --}}
                        <div class="form-group">
                            <input type="number" min="0" name="amount" step="0.01" />
                            <label for="input" class="control-label">Amount</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>

                        <!-- Site -->
                        <div class="form-group">
                            <input type="hidden" name="supplier_id" value="{{ $data['supplier']->id }}" />
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        {{-- Select Payee Dropdown --}}
                        <select name="payment_initiator" id="payment_initiator"
                            class="form-select text-black form-select-sm" style="cursor: pointer"
                            onchange="togglePayOptions()">
                            <option value="" selected>Select Payee</option>
                            <option value="1">Supplier</option>
                            <option value="0">Admin</option>
                        </select>

                        {{-- Supplier Options (Shown when Supplier is selected) --}}
                        <div id="supplierOptions" style="display: none;" class="mt-3">
                            <select name="site_id" id="site_id" class="form-select text-black form-select-sm"
                                style="cursor: pointer">
                                <option for="site_id" value="">Select Site</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site['site_id'] }}">
                                        {{ $site['site_name'] }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- File Upload for Screenshot --}}
                            <div class="mt-3">
                                <input class="form-control form-control-md" id="image" type="file"
                                    name="screenshot">
                            </div>
                        </div>

                        {{-- Admin Options (Shown when Admin is selected) --}}
                        <div id="adminOptions" style="display: none;" class="mt-4">
                            <div class="row g-3">
                                {{-- Sent Radio Option --}}
                                <div class="col-auto">
                                    <label for="transaction_sent">
                                        <input type="radio" name="transaction_type" id="transaction_sent"
                                            value="1">
                                        Sent
                                    </label>
                                </div>
                                {{-- Received Radio Option --}}
                                <div class="col-auto">
                                    <label for="transaction_received">
                                        <input type="radio" name="transaction_type" id="transaction_received"
                                            value="0">
                                        Received
                                    </label>
                                </div>
                            </div>
                        </div>


                        {{-- Screenshot --}}


                        <div class="flex items-center justify-end mt-4">

                            <x-primary-button>
                                {{ __('Pay') }}
                            </x-primary-button>

                        </div>

                    </form>
                </div>

            </div>
        </div>
    </div>


    <div id="messageContainer">

    </div>

    <script>
        $(document).ready(function() {
            $('form[id="payment_form"]').on('submit', function(e) {
                e.preventDefault();

                const form = $(this);
                const formData = new FormData(form[0]);
                const messageContainer = $('#messageContainer');
                messageContainer.empty();

                $('.text-danger').remove();

                $.ajax({
                    url: '{{ url($user . '/supplier/payments') }}',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        messageContainer.append(`
                        <div class="alert align-items-center text-white bg-success border-0" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <strong><i class="fas fa-check-circle me-2"></i></strong>${response.message}
                                </div>
                            </div>
                        </div>
                    `);
                        form[0].reset();

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 2000);
                    },
                    error: function(response) {

                        if (response.status === 422) {

                            messageContainer.append(`
                            <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                ${response.responseJSON.errors}
                            </div>`)

                            location.reload();

                        } else {
                            messageContainer.append(`
                            <div class="alert alert-danger mt-3 alert-dismissible fade show" role="alert">
                                An unexpected error occurred. Please try again later.
                            </div>
                        `);
                        }

                        setTimeout(function() {
                            messageContainer.find('.alert').alert('close');
                        }, 2000);
                    }
                });
            });


        });

        function togglePayOptions() {
            const payTo = document.getElementById('payment_initiator').value; 
            const supplierOptions = document.getElementById('supplierOptions');
            const adminOptions = document.getElementById('adminOptions');

            if (payTo === "1") {
                supplierOptions.style.display = 'block'; 
                adminOptions.style.display = 'none'; 
            } else if (payTo === "0") {
                supplierOptions.style.display = 'none'; 
                adminOptions.style.display = 'block'; 
            } else {

                supplierOptions.style.display = 'none';
                adminOptions.style.display = 'none';
            }
        }
    </script>


</x-app-layout>
