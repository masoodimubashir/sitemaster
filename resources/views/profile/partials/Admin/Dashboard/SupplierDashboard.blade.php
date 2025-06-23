<x-app-layout>
    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }
    </style>

    <div class="row">
        <!-- Stats Overview -->
        <div class="row mb-4">
            <!-- Statistics Cards -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1 text-white">Active Suppliers</h6>
                                        <h2 class="mb-0">{{ $suppliers->count() }}</h2>
                                    </div>
                                    <div class="icon-shape bg-white text-primary rounded-circle p-3">
                                        <i class="fas fa-truck fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-secondary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title mb-1 text-white">Total</h6>
                                        @php
                                            $totalOwed = 0;
                                            foreach ($suppliers as $supplier) {
                                                $supplierBaseAmount = 
                                                    ($supplier->total_material_billing ?? 0) +
                                                    ($supplier->total_site_expenses_from_payments ?? 0) +
                                                    ($supplier->total_square_footage ?? 0) +
                                                    ($supplier->total_daily_wagers ?? 0);
                                                
                                                $supplierServicePercentage = $supplier->service_charge ?? 0;
                                                $supplierServiceAmount = ($supplierBaseAmount * $supplierServicePercentage) / 100;
                                                $supplierTotalCost = $supplierBaseAmount + $supplierServiceAmount;
                                                
                                                $supplierPaid = $supplier->total_income_payments ?? 0;
                                                $totalOwed += max(0, $supplierTotalCost - $supplierPaid);
                                            }
                                        @endphp
                                        <h2 class="mb-0">₹{{ number_format($totalOwed, 2) }}</h2>
                                    </div>
                                    <div class="icon-shape bg-white text-danger rounded-circle p-3">
                                        <i class="fa-solid fa-indian-rupee-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-4 mt-3 mt-md-0">
                <div class="d-flex flex-column h-100 gap-2">
                    <a class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center"
                        href="{{ url('/admin/dashboard') }}">
                        <i class="fas fa-inbox me-2"></i> Switch Sites
                    </a>
                    <a href="{{ url('/admin/suppliers/create') }}" 
                       class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center">
                        <i class="fas fa-plus me-2"></i> Create Supplier
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Search and Filters -->
                    <div class="p-3">
                        <form method="GET" action="{{ url()->current() }}">
                            <div class="row g-2">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search for suppliers..."
                                            value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Search</button>
                                </div>
                                <div class="col-md-2">
                                    <a href="{{ url()->current() }}"
                                        class="btn btn-sm btn-outline-secondary text-black w-100">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Suppliers List -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Supplier Name</th>
                                    <th>Contact</th>
                                    <th>Created</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suppliers as $supplier)
                                    @php
                                        $supplierBaseAmount = 
                                            ($supplier->total_material_billing ?? 0) +
                                            ($supplier->total_site_expenses_from_payments ?? 0) +
                                            ($supplier->total_square_footage ?? 0) +
                                            ($supplier->total_daily_wagers ?? 0);
                                        
                                        $supplierServicePercentage = $supplier->service_charge ?? 0;
                                        $supplierServiceAmount = ($supplierBaseAmount * $supplierServicePercentage) / 100;
                                        $supplierTotalCost = $supplierBaseAmount + $supplierServiceAmount;
                                        
                                        $supplierPaid = $supplier->total_income_payments ?? 0;
                                        $supplierBalance = $supplierTotalCost - $supplierPaid;
                                    @endphp

                                    <tr>
                                        <td>
                                            <a href="{{ url('/admin/suppliers/' . $supplier->id) }}"
                                                class="fw-bold text-decoration-none">
                                                {{ $supplier->name }}
                                            </a>
                                        </td>
                                        <td>{{ $supplier->contact_no }}</td>
                                        <td>{{ $supplier->created_at->diffForHumans() }}</td>
                                        <td class="text-end text-success fw-bold">₹{{ number_format($supplierPaid, 2) }}</td>
                                        <td class="text-end {{ $supplierBalance >= 0 ? 'text-danger' : 'text-success' }} fw-bold">
                                            ₹{{ number_format(abs($supplierBalance), 2) }}
                                            <small class="d-block text-muted">{{ $supplierBalance >= 0 ? 'Due' : 'Advance' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($suppliers->hasPages())
                        <div class="p-3 border-top">
                            <nav aria-label="Page navigation">
                                <ul class="pagination justify-content-center mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($suppliers->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">&laquo;</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $suppliers->previousPageUrl() }}"
                                                rel="prev">&laquo;</a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($suppliers->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
                                        @if ($page == $suppliers->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link"
                                                    href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($suppliers->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $suppliers->nextPageUrl() }}"
                                                rel="next">&raquo;</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">&raquo;</span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div id="messageContainer"></div>
</x-app-layout>