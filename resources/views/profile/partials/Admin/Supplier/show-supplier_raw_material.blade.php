<x-app-layout>





    {{-- Modal --}}
    <style>
        /* Styles for the modal */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.7);

            /* Black w/ opacity */
        }

        /* .modal-content input[type="text"],
        .modal-content input[type="number"] {
            width: 100%;
            border: 0;
            outline: 1px solid #dee2e6;
            font-weight: 400;
            border-radius: 4px;
            display: block;
            background: none;
            font-size: 0.875rem;
            border-width: 0;
            border-color: transparent;
            line-height: 1.9;
            -webkit-transition: all 0.28s ease;
            transition: all 0.28s ease;
            box-shadow: none;
        } */

        /* Modal Content */
        .modal-content {
            background-color: #fefefe;
            margin: 50% auto;
            /* Centered on the screen */
            padding: 15px;
            width: 90%;
            /* Default width for mobile */
            max-width: 600px;
            /* Max width for larger screens */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }




        /* Responsive design */
        @media screen and (min-width: 768px) {
            .modal-content {
                margin: 10% auto;
                /* Adjust margins for larger screens */
                width: 80%;
                /* Slightly wider on larger screens */
            }
        }

        @media screen and (min-width: 992px) {
            .modal-content {
                margin: 15% auto;
                /* Further adjustment for very large screens */
                width: 60%;
                /* Even wider on very large screens */
            }
        }
    </style>


    {{-- Accordian --}}
    <style>
        /* Accordion container */
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



    @if ($supplier)


        <div class=" row ">

            <div class="col-md-3 ">
                <button class="btn btn-info btns btn-sm" data-modal="payment-supplier">
                    Make Payment
                </button>
            </div>

            <div class="col-md-3 ">

                <a href="{{ route('supplier-payments.edit', [$supplier->id]) }}" class="btn btn-info btns btn-sm"
                    data-modal="payment-supplier">
                    View Payments
                </a>

            </div>

            <div class="col-md-3 ">

                <a href="{{ route('suppliers.view-ledger', [$supplier->id]) }}" class="btn btn-info btns btn-sm"
                    data-modal="payment-supplier">
                    View Ledger
                </a>

            </div>

        </div>

        <div class="row mt-4">

            <div class=" col-12 col-md-4 mb-1">

                <x-general-detail>
                    <i class="fa fa-map-marker display-4 text-info me-2 fw-bold"></i>
                    {{ ucfirst($supplier->address) }}
                </x-general-detail>

                <x-general-detail>
                    <i class="fa fa-user-circle display-4 text-info me-2 fw-bold"></i>
                    {{ ucfirst($supplier->name) }}
                </x-general-detail>

                <x-general-detail>
                    <i class=" display-4 fa fa-credit-card text-info me-2 fw-bold"></i>
                    Credit
                    {{ Number::currency($grandTotal ?? 0, 'INR') }}
                </x-general-detail>

            </div>

            <div class=" col-12 col-md-4 mb-1">

                <x-general-detail>

                    {{ $supplier->is_raw_material_provider ? 'Raw Materialist' : '' }}

                </x-general-detail>

                <x-general-detail>
                    <i class=" display-4 fa fa-money text-info me-2 fw-bold"></i>
                    Debit {{ Number::currency($supplier->payment_suppliers_sum_amount ?? 0, 'INR') }}
                </x-general-detail>
            </div>

            <div class=" col-12 col-md-4 mb-1">

                <x-general-detail>
                    <i class="fa fa-mobile display-4 text-info me-2 fw-bold"></i>
                    <a href="tel:+91-{{ $supplier->contact_no }}">91-{{ ucfirst($supplier->contact_no) }}</a>
                </x-general-detail>
                @php
                    $balance = $grandTotal - $supplier->payment_suppliers_sum_amount;
                @endphp
                @if ($balance >= 0)
                    <x-general-detail :balance="$balance">
                        <i class=" display-4 fa fa-balance-scale  me-2 text-info  fw-bold"></i>
                        Balance
                        {{ Number::currency($balance ?? 0, 'INR') }}
                    </x-general-detail>
                @else
                    <x-general-detail :balance="$balance">
                        <i class=" display-4 fa fa-balance-scale  me-2 text-danger  fw-bold"></i>

                        Balance
                        {{ Number::currency($balance ?? 0, 'INR') }}
                    </x-general-detail>
                @endif
            </div>
        </div>




        <div class="row ">

            <div class="col-lg-12 grid-margin stretch-card">

                <div class="card-body ">

                    <div class="table-responsive mt-4">

                        <!-- Content for Construction Billing Material Tab -->
                        <table class="table table-bordered">

                            <thead>

                                <tr>

                                    <th class="bg-info fw-bold text-white">
                                        Date
                                    </th>

                                    <th class="bg-info fw-bold text-white"> Bill Proof </th>

                                    <th class="bg-info fw-bold text-white"> Item </th>

                                    <th class="bg-info fw-bold text-white">
                                        Site Name
                                    </th>

                                    <th class="bg-info fw-bold text-white">
                                        Site Owner
                                    </th>


                                    <th class="bg-info fw-bold text-white">
                                        Amount
                                    </th>

                                    {{-- <th class="bg-info fw-bold text-white">
                                        Total Amount
                                    </th> --}}

                                </tr>

                            </thead>

                            <tbody>

                                @foreach ($supplier->constructionMaterialBilling as $construction_material_billing)
                                    <tr>

                                        <td>
                                            {{ $construction_material_billing->created_at->format('d-M-Y') }}
                                        </td>

                                        <td>
                                            <img src="{{ asset($construction_material_billing->item_image_path) }}"alt=""
                                                class="w-20 h-20 rounded-full">
                                        </td>

                                        <td>
                                            {{ $construction_material_billing->item_name }}
                                        </td>


                                        <td>
                                            {{ $construction_material_billing->phase->site->site_name }}
                                        </td>

                                        <td>
                                            {{ $construction_material_billing->phase->site->site_owner_name }}
                                        </td>


                                        <td>
                                            {{ Number::currency($construction_material_billing->amount, 'INR') }}
                                        </td>

                                        {{-- <td>
                                            {{ Number::currency($construction_material_billing->total_amount, 'INR') }}
                                        </td> --}}

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                        {{-- Payment Supplier --}}
                        <div id="payment-supplier" class="modal">
                            <div class="modal-content">

                                <p>
                                    Supplier Payment
                                </p>

                                <form action="{{ route('supplier-payments.store') }}"
                                    class="forms-sample material-form" method="POST" enctype="multipart/form-data">

                                    @csrf

                                    {{-- Phase Name --}}
                                    <div class="form-group">
                                        <input type="number" min="0" name="amount" />
                                        <label for="input" class="control-label">Amount</label><i class="bar"></i>
                                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                    </div>

                                    <!-- Site -->

                                    <div class="mt-4">
                                        <select id="site_id" class="form-select form-select-sm" name="site_id">
                                            <option value="">Select Site</option>
                                            @foreach ($sites as $site)
                                                <option value="{{ $site->id }}">
                                                    {{ $site->id }} -
                                                    {{ $site->site_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('site_id')
                                            <x-input-error :messages="$site_id" class="mt-2" />
                                        @enderror
                                    </div>



                                    {{-- Supplier --}}

                                    <input type="hidden" name="supplier_id" value="{{ $supplier->id }}" />
                                    <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />

                                    @error('supplier_id')
                                        <x-input-error :messages="$message" class="mt-2" />
                                    @enderror


                                    <!-- Is Verified -->
                                    <div class="form-check">
                                        <label class="form-check-label mt-2">
                                            <input type="checkbox" class="form-check-input" name="is_verified">
                                            Verify
                                        </label>
                                        @error('is_verified')
                                            <x-input-error :messages="$message" class="mt-2" />
                                        @enderror
                                    </div>

                                    {{-- Screenshot --}}
                                    <div class="mt-3">
                                        <input class="form-control form-control-md" id="image" type="file"
                                            name="screenshot">
                                    </div>

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


            </div>

        </div>




    @endif






    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Get all buttons with class 'btn'
            const buttons = document.querySelectorAll('.btns');

            // Add click event listeners to each button
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    // Get the modal ID from the button's data attribute
                    const modalId = button.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);

                    // Show the modal
                    if (modal) {
                        modal.style.display = 'block';
                    }
                });
            });

            // Close modals when the close button is clicked
            const closeButtons = document.querySelectorAll('.modal .close');
            closeButtons.forEach(closeButton => {
                closeButton.addEventListener('click', () => {
                    const modal = closeButton.closest('.modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                });
            });

            // Close modals when clicking outside the modal content
            window.addEventListener('click', (event) => {
                if (event.target.classList.contains('modal')) {
                    event.target.style.display = 'none';
                }
            });
        });
    </script>

</x-app-layout>
