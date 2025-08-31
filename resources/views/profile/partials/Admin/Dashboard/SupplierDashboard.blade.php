<x-app-layout>


    <style>
        #messageContainer {
            position: fixed;
            bottom: 5%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 999999999;
        }

        .badge-soft {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }

        .dashboard-card {
            border-radius: 20px;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }


        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .search-container {
            background: #f8f9fc;
            border-radius: 15px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .search-container:focus-within {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .search-container .form-control {
            border: none;
            background: transparent;
            box-shadow: none;
        }

        .search-container .input-group-text {
            border: none;
            background: transparent;
        }

        .btn-modern {
            border-radius: 12px;
            font-weight: 600;
            padding: 12px 24px;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-modern.btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-modern.btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-modern.btn-outline-secondary {
            border: 2px solid #e3e6f0;
            color: #6c757d;
        }

        .btn-modern.btn-outline-secondary:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .table-modern {
            overflow: hidden;
        }

        .table-modern thead {
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecf3 100%);
        }

        .table-modern thead th {
            border: none;
            font-weight: 700;
            color: #5a5c69;
            padding: 20px 15px;
            font-size: 0.875rem;
        }

        .table-modern tbody tr {
            border: none;
            transition: all 0.3s ease;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fc;
            transform: scale(1.01);
        }

        .table-modern tbody td {
            border: none;
            padding: 20px 15px;
            vertical-align: middle;
        }

        .site-link {
            font-weight: 700;
            color: #5a5c69;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .site-link:hover {
            color: #667eea;
        }

        .amount-positive {
            color: #1cc88a;
            font-weight: 700;
        }

        .amount-negative {
            color: #e74a3b;
            font-weight: 700;
        }

        .amount-neutral {
            color: #36b9cc;
            font-weight: 700;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.75rem;
            border: none;
        }

        .status-progress {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #8b4513;
        }

        .status-completed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
        }

        .expand-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecf3 100%);
            color: #5a5c69;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .expand-btn:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .breakdown-container {
            background: linear-gradient(135deg, #f8f9fc 0%, #ffffff 100%);
            border-radius: 15px;
            margin: 10px 0;
        }

        .breakdown-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s ease;
        }

        .breakdown-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
        }

        .page-title {
            color: #5a5c69;
            font-weight: 800;
            margin-bottom: 2rem;
        }
    </style>


    <!-- Stats Overview -->
    <div class="row mb-5 g-4">
        <div class="col-md-4">
            <div class="dashboard-card primary h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 ">Total Suppliers</h6>
                        <h1 class="mb-0 fw-bold">{{ $suppliers->total() }}</h1>
                    </div>
                    <div class="icon-circle">
                        <i class="fas fa-building fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card secondary h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-2 ">Cleared Accounts</h6>
                        <h1 class="mb-0 fw-bold">
                            {{ $suppliers->filter(fn($s) => ($s->total_balance ?? 0) <= 0)->count() }}</h1>
                    </div>
                    <div class="icon-circle">
                        <i class="fas fa-lock fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="success h-100 p-4">
                <div class="d-flex flex-column gap-3">
                    <a class="btn btn-success btn-modern text-decoration-none d-flex align-items-center justify-content-center"
                        href="{{ url('/admin/dashboard') ?? '#' }}">
                        <i class="fas fa-exchange-alt me-2"></i> Switch Sites
                    </a>
                    <a class="btn btn-success btn-modern d-flex align-items-center justify-content-center"
                        href="{{ url('/admin/suppliers/create') }}">
                        <i class="fas fa-plus me-2"></i> Create Supplier
                    </a>
                </div>
            </div>
        </div>
    </div>



    <!-- 📊 Stats Overview -->
    <div class="row">
        <div class="col-12">
            <div class="table-modern">
                <!-- Search Filters -->
                <div class="p-4 bg-white">
                    <form method="GET" action="{{ url()->current() ?? '#' }}">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-8">
                                <div class="search-container input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search for customers or sites..."
                                        value="{{ request('search') ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-modern btn-success w-100">Search</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{ url()->current() ?? '#' }}"
                                    class="btn btn-modern btn-outline-primary w-100">Reset</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 📋 Supplier Table -->
                <div class="table-responsive">
                    <table class="table table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Contact</th>
                                <th>Address</th>
                                <th class="text-end">Paid</th>
                                <th class="text-end">Due</th>
                                <th class="text-end">Balance</th>
                                <th class="text-end">Return</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($suppliers ?? [] as $supplier)
                                @php
                                    $status = ($supplier->total_balance ?? 0) <= 0 ? 'Cleared' : 'Pending';
                                    $badgeClass = $status === 'Cleared' ? 'status-completed' : 'status-progress';
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ url('/admin/suppliers/' . base64_encode($supplier->id)) }}"
                                            class="site-link">
                                            {{ $supplier->name }}
                                        </a>
                                        <div class="text-muted small mt-1">
                                            @if ($supplier->is_raw_material_provider)
                                                🏗 Raw Materials
                                            @endif
                                            @if ($supplier->is_workforce_provider)
                                                👷 Workforce
                                            @endif
                                        </div>
                                    </td>
                                    <td class="fw-semibold">{{ $supplier->contact_no }}</td>
                                    <td class="text-muted">{{ $supplier->address }}</td>
                                    <td class="text-end amount-positive">
                                        {{ number_format($supplier->total_paid ?? 0, 2) }}</td>
                                    <td class="text-end amount-negative">
                                        {{ number_format($supplier->total_due ?? 0, 2) }}</td>
                                    <td class="text-end amount-neutral">
                                        {{ number_format($supplier->total_balance ?? 0, 2) }}
                                    </td>
                                    <td class="text-end text-primary">
                                        {{ number_format($supplier->total_return ?? 0, 2) }}</td>
                                    <td class="text-center">
                                        <button class="expand-btn" data-bs-toggle="collapse"
                                            data-bs-target="#breakdown-{{ $supplier->id }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Balance Breakdown -->
                                <tr class="collapse" id="breakdown-{{ $supplier->id }}">
                                    <td colspan="9" class="p-0">
                                        <div class="breakdown-container p-4">
                                            <div class="row g-3">
                                                @if (isset($supplier->balance_breakdown))
                                                    @foreach ($supplier->balance_breakdown as $key => $value)
                                                        <div class="col-md-2">
                                                            <div class="breakdown-card p-3 text-center">
                                                                <small class="text-muted text-uppercase d-block mb-1">
                                                                    {{ ucfirst($key) }}
                                                                </small>
                                                                <div class="fw-bold">{{ number_format($value, 2) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fs-1 mb-3 d-block text-muted"></i>
                                        <h5>No suppliers found</h5>
                                        <p class="mb-0">Try adjusting your search criteria</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- 📑 Pagination -->
                <div class="mt-3">
                    {{ $suppliers->withQueryString()->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>
    </div>



    <div id="messageContainer"></div>
</x-app-layout>
