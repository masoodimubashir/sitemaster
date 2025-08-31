    <x-app-layout>

        @php
            $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
        @endphp

        <style>
            #messageContainer {
                position: fixed;
                bottom: 2rem;
                right: 2rem;
                z-index: 9999;
            }

            .site-header {
                background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
                border-radius: 12px;
                color: white;
                padding: 2rem;
                margin-bottom: 2rem;
            }

            .site-header h1 {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .site-header .subtitle {
                opacity: 0.9;
                font-size: 1.1rem;
            }

            .site-header .stats {
                display: flex;
                gap: 2rem;
                margin-top: 1rem;
            }

            .site-header .stat-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .dashboard-tabs {
                margin-bottom: 2rem;
            }

            .nav-tabs {
                border-bottom: 2px solid #e9ecef;
            }

            .nav-tabs .nav-link {
                border: none;
                border-radius: 0;
                color: #6c757d;
                font-weight: 500;
                padding: 1rem 1.5rem;
                position: relative;
            }

            .nav-tabs .nav-link.active {
                background-color: transparent;
                color: #ff6b35;
                border-bottom: 3px solid #ff6b35;
            }

            .nav-tabs .nav-link:hover {
                border-color: transparent;
                color: #ff6b35;
            }

            .metric-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }

            .metric-card {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                border: 1px solid #f0f0f0;
            }

            .metric-card .icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1rem;
                font-size: 1.5rem;
            }

            .metric-card.total-budget .icon {
                background-color: rgba(255, 107, 53, 0.1);
                color: #ff6b35;
            }

            .metric-card.total-spent .icon {
                background-color: rgba(40, 167, 69, 0.1);
                color: #28a745;
            }

            .metric-card.remaining .icon {
                background-color: rgba(23, 162, 184, 0.1);
                color: #17a2b8;
            }

            .metric-card.utilization .icon {
                background-color: rgba(108, 117, 125, 0.1);
                color: #6c757d;
            }

            .metric-card .amount {
                font-size: 1.8rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .metric-card .label {
                color: #6c757d;
                font-size: 0.9rem;
                margin: 0;
            }

            .progress-section {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }

            .progress-section h5 {
                margin-bottom: 1rem;
                color: #495057;
                font-weight: 600;
            }

            .phase-item {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 1rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                border: 1px solid #f0f0f0;
                cursor: pointer;
                transition: all 0.3s ease;
                position: relative;
            }

            .phase-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
                border-color: #ff6b35;
            }

            .phase-item .phase-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1rem;
            }

            .phase-item .phase-title {
                font-weight: 600;
                color: #495057;
            }

            .phase-item .phase-status {
                padding: 0.25rem 0.75rem;
                border-radius: 20px;
                font-size: 0.8rem;
                font-weight: 500;
            }

            .phase-status.completed {
                background-color: #d4edda;
                color: #155724;
            }

            .phase-status.active {
                background-color: #fff3cd;
                color: #856404;
            }

            .phase-status.pending {
                background-color: #f8d7da;
                color: #721c24;
            }

            .phase-actions {
                position: absolute;
                top: 1rem;
                right: 1rem;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .phase-item:hover .phase-actions {
                opacity: 1;
            }

            .btn-orange {
                background-color: #ff6b35;
                border-color: #ff6b35;
                color: white;
            }

            .btn-orange:hover {
                background-color: #e55a2b;
                border-color: #e55a2b;
                color: white;
            }

            .btn-outline-orange {
                border-color: #ff6b35;
                color: #ff6b35;
            }

            .btn-outline-orange:hover {
                background-color: #ff6b35;
                border-color: #ff6b35;
                color: white;
            }

            .filters-section {
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            }

            .filter-card {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 0.75rem;
                margin-bottom: 1rem;
            }

            .filter-card h6 {
                margin-bottom: 0.5rem;
                color: #495057;
                font-weight: 600;
            }

            .phase-detail-tabs .nav-tabs {
                border-bottom: 1px solid #dee2e6;
                margin-bottom: 1.5rem;
            }

            .phase-detail-tabs .nav-link {
                border: none;
                color: #6c757d;
                padding: 0.75rem 1rem;
                margin-right: 1rem;
                border-radius: 6px;
            }

            .phase-detail-tabs .nav-link.active {
                background-color: #ff6b35;
                color: white;
            }

            .phase-detail-tabs .nav-link:hover {
                color: #ff6b35;
                background-color: rgba(255, 107, 53, 0.1);
            }
        </style>

        <style>
            /* Existing styles remain the same... */

            /* New styles for phase detail view */
            .phase-detail-view {
                display: none;
                background: white;
                border-radius: 12px;
                padding: 1.5rem;
                margin-bottom: 2rem;
                box-shadow: 0 2px 10px rgba(0,0,0,0.08);
                border: 1px solid #f0f0f0;
            }

            .phase-detail-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e9ecef;
            }

            .phase-detail-tabs .nav-tabs {
                border-bottom: 1px solid #dee2e6;
                margin-bottom: 1.5rem;
            }

            .phase-detail-tabs .nav-link {
                border: none;
                color: #6c757d;
                padding: 0.75rem 1rem;
                margin-right: 1rem;
                border-radius: 6px;
            }

            .phase-detail-tabs .nav-link.active {
                background-color: #ff6b35;
                color: white;
            }

            .phase-detail-tabs .nav-link:hover {
                color: #ff6b35;
                background-color: rgba(255, 107, 53, 0.1);
            }

            .tab-content-section {
                min-height: 300px;
            }

            .back-to-phases {
                cursor: pointer;
                color: #ff6b35;
                font-weight: 500;
            }

            .back-to-phases:hover {
                text-decoration: underline;
            }

            .add-entry-btn {
                margin-bottom: 1rem;
            }
        </style>


        <x-breadcrumb :names="['Sites', $site->site_name]" :urls="[$user . '/sites', $user . '/sites/' . base64_encode($site->id)]" />

        <!-- Site Header -->
        <div class="site-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1>{{ ucwords($site->site_name) }}</h1>
                    <p class="subtitle mb-0">Current Phase: Site Preparation</p>
                    <div class="stats mt-3">
                        <div class="stat-item">
                            <i class="fas fa-layer-group"></i>
                            <span>{{ count($phases) }} Phases</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <span>{{ count($site->users) }} Workers</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-chart-line"></i>
                            <span>{{ $total_paid > 0 ? number_format((($total_paid / ($total_paid + $total_due)) * 100), 1) : 0 }}% Complete</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#phase">
                        <i class="fas fa-plus me-1"></i> New Phase
                    </button>
                    <a href="{{ url($user . '/attendance/site/show/' . base64_encode($site->id)) }}" class="btn btn-outline-orange">
                        <i class="fas fa-external-link-alt me-2"></i> View Full Attendance
                    </a>
                    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#payment-supplier">
                        <i class="fas fa-money-check-alt me-1"></i> Record Payment
                    </button>
                    <form action="{{ url($user . '/ledger/report') }}" method="GET" class="d-inline">
                        <input type="hidden" name="site_id" value="{{ $site->id }}">
                        <input type="hidden" name="date_filter" value="{{ request('date_filter', 'today') }}">
                        <input type="hidden" name="supplier_id" value="{{ request('supplier_id', 'all') }}">
                        <input type="hidden" name="phase_id" value="{{ request('phase_id', 'all') }}">
                        @if(request('date_filter') === 'custom')
                            <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                        @endif
                        <button type="submit" class="btn btn-light btn-sm">
                            <i class="far fa-file-pdf me-1"></i> Export Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="dashboard-tabs">
            <ul class="nav nav-tabs" id="siteTabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="phases-tab" data-bs-toggle="tab" data-bs-target="#phases" type="button" role="tab">
                        <i class="fas fa-layer-group me-2"></i> Phases
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calculations-tab" data-bs-toggle="tab" data-bs-target="#calculations" type="button" role="tab">
                        <i class="fas fa-calculator me-2"></i> Calculations
                    </button>
                </li>


            </ul>
        </div>

        <!-- Tab Content -->
        <div class="tab-content" id="siteTabContent">







            <!-- Phases Tab -->
            <div class="tab-pane fade active" id="phases" role="tabpanel">

                <!-- Metric Cards -->
                <div class="metric-cards">
                    <div class="metric-card total-budget">
                        <div class="icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="amount">₹{{ number_format($total_paid + $total_due) }}</div>
                        <p class="label">Total Budget</p>
                    </div>

                    <div class="metric-card total-spent">
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="amount">₹{{ number_format($total_paid) }}</div>
                        <p class="label">Total Spent</p>
                    </div>

                    <div class="metric-card remaining">
                        <div class="icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="amount">₹{{ number_format($total_due) }}</div>
                        <p class="label">Remaining</p>
                    </div>

                    <div class="metric-card utilization">
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <div class="amount">{{ $total_paid > 0 ? number_format((($total_paid / ($total_paid + $total_due)) * 100), 1) : 0 }}%</div>
                        <p class="label">Utilization</p>
                    </div>
                </div>


                <!-- Filters -->
                <div class="filters-section">
                    <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Filter Data</h5>
                    <form action="{{ url()->current() }}" method="GET" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="filter-card">
                                    <h6><i class="fas fa-layer-group me-2"></i>Phase</h6>
                                    <select class="form-select form-select-sm" name="phase_id" id="phaseFilter">
                                        <option value="all" {{ request('phase_id') == 'all' ? 'selected' : '' }}>All Phases</option>
                                        @if (!empty($phases))
                                            @foreach ($phases as $phase)
                                                <option value="{{ $phase->id }}" {{ request('phase_id') == $phase->id ? 'selected' : '' }}>
                                                    {{ $phase->phase_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="filter-card">
                                    <h6><i class="fas fa-truck me-2"></i>Supplier</h6>
                                    <select class="form-select form-select-sm" name="supplier_id" id="supplierFilter">
                                        <option value="all" {{ request('supplier_id') == 'all' ? 'selected' : '' }}>All Suppliers</option>
                                        @if (!empty($suppliers))
                                            @foreach ($suppliers as $supplier)
                                                <option value="{{ $supplier['supplier_id'] }}" {{ request('supplier_id') == $supplier['supplier_id'] ? 'selected' : '' }}>
                                                    {{ $supplier['supplier_name'] }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="filter-card">
                                    <h6><i class="fas fa-calendar me-2"></i>Date Range</h6>
                                    <select class="form-select form-select-sm" name="date_filter" id="dateFilter">
                                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                                        <option value="yesterday" {{ request('date_filter') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                        <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                                        <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                                        <option value="this_year" {{ request('date_filter') === 'this_year' ? 'selected' : '' }}>This Year</option>
                                        <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                                        <option value="lifetime" {{ request('date_filter') === 'lifetime' ? 'selected' : '' }}>All Data</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="customDateRange" class="row g-3 mt-2" style="display: {{ request('date_filter') === 'custom' ? 'flex' : 'none' }};">
                            <div class="col-md-6">
                                <div class="filter-card">
                                    <h6><i class="fas fa-calendar-alt me-2"></i>Start Date</h6>
                                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="filter-card">
                                    <h6><i class="fas fa-calendar-alt me-2"></i>End Date</h6>
                                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-orange me-2" id="resetFilters">
                                <i class="fas fa-undo me-1"></i> Reset Filters
                            </button>
                            <button type="submit" class="btn btn-orange">
                                <i class="fas fa-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>


                <!-- Phase List View (default) -->
                <div id="phaseListView">


                    @if(!empty($phases) && count($phases) > 0)
                        <div class="row">
                            @foreach($phases as $phase)
                                <div class="col-lg-4 mb-4">
                                    <div class="phase-item position-relative" onclick="openPhaseDetail({{ $phase->id }}, '{{ $phase->phase_name }}')">
                                        <!-- Action Buttons -->
                                        <div class="phase-actions">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); editPhase({{ $phase->id }}, '{{ $phase->phase_name }}')" title="Edit Phase">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deletePhase({{ $phase->id }}, '{{ $phase->phase_name }}')" title="Delete Phase">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="phase-header">
                                            <span class="phase-title">{{ $phase->phase_name }}</span>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block"><i class="fas fa-calendar me-1"></i>Start: {{ \Carbon\Carbon::parse($phase->created_at)->format('d/m/Y') }}</small>
                                            <small class="text-muted"><i class="fas fa-rupee-sign me-1"></i>Budget: ₹{{ number_format(150000) }}</small>
                                        </div>
                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar" style="width: 100%; background-color: #ff6b35;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><i class="fas fa-user-tie me-1"></i>ABC Construction</small>
                                            <small class="text-success fw-semibold">₹{{ number_format(149500) }}</small>
                                        </div>

                                        <!-- Click indicator -->
                                        <div class="text-center mt-2">
                                            <small class="text-muted"><i class="fas fa-mouse-pointer me-1"></i>Click to view details</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-layer-group text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">No phases created yet</h5>
                            <p class="text-muted">Start by creating your first project phase</p>
                            <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#phase">
                                <i class="fas fa-plus me-2"></i> Create First Phase
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Phase Detail View (hidden by default) -->
                <div id="phaseDetailView" class="phase-detail-view">
                    <div class="phase-detail-header">
                        <div>
                        <span class="back-to-phases" onclick="showPhaseList()">
                            <i class="fas fa-arrow-left me-2"></i> Back to Phases
                        </span>
                            <h4 class="mt-2" id="phaseDetailTitle">Construction Data - <span id="phaseNameTitle"></span></h4>
                        </div>
                        <div class="phase-progress-badge">
                            <span class="badge bg-success" id="phaseProgressBadge">100% Complete</span>
                        </div>
                    </div>

                    <!-- Phase Detail Tabs -->
                    <div class="phase-detail-tabs">
                        <ul class="nav nav-tabs" id="phaseDetailTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials-content" type="button" role="tab">
                                    <i class="fas fa-boxes me-2"></i> Materials
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contractor-tab" data-bs-toggle="tab" data-bs-target="#contractor-content" type="button" role="tab">
                                    <i class="fas fa-user-tie me-2"></i> Contractor Billing
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-content" type="button" role="tab">
                                    <i class="fas fa-receipt me-2"></i> Expenses
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Phase Detail Tab Content -->
                    <div class="tab-content tab-content-section" id="phaseDetailTabContent">
                        <!-- Materials Tab -->
                        <div class="tab-pane fade show active" id="materials-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="mb-0">Material Entries</h6>
                                <button class="btn btn-orange btn-sm add-entry-btn" onclick="addMaterial()">
                                    <i class="fas fa-plus me-1"></i> Add Material
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody id="materialsTableBody">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No material entries found</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contractor Billing Tab -->
                        <div class="tab-pane fade" id="contractor-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="mb-0">Contractor Billing Entries</h6>
                                <button class="btn btn-orange btn-sm add-entry-btn" onclick="addContractorBilling()">
                                    <i class="fas fa-plus me-1"></i> Add Billing
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Contractor</th>
                                                <th>Work Description</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody id="contractorTableBody">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No contractor billing entries found</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Tab -->
                        <div class="tab-pane fade" id="expenses-content" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="mb-0">Expense Entries</h6>
                                <button class="btn btn-orange btn-sm add-entry-btn" onclick="addExpense()">
                                    <i class="fas fa-plus me-1"></i> Add Expense
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Item</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody id="expensesTableBody">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">No expense entries found</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Calculations Tab -->
            <div class="tab-pane fade" id="calculations" role="tabpanel">
                @if(!empty($phaseData) && count($phaseData) > 0)
                    <div class="accordion" id="phaseAccordion">
                        @foreach ($phaseData as $idx => $p)
                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-3">
                                <h2 class="accordion-header" id="heading{{ $idx }}">
                                    <button class="accordion-button {{ $idx !== 0 ? 'collapsed' : '' }} rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $idx }}" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $idx }}" style="background-color: #f8f9fa;">
                                        <div class="w-100">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-semibold">Phase: {{ $p['phase'] }}</span>
                                                <div class="text-end">
                                                    <div class="fw-bold text-primary">₹{{ number_format($p['phase_total']) }}</div>
                                                    <small class="text-muted">Total Cost</small>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    With Service Charge: ₹{{ number_format($p['phase_total_with_service_charge']) }} |
                                                    Paid: ₹{{ number_format($p['total_payment_amount']) }}
                                                </small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $idx }}" data-bs-parent="#phaseAccordion">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                {{--                                            <h6 class="mb-2 text-primary"><i class="fas fa-tools me-2"></i>Construction Materials (₹{{ number_format($p['construction_total_amount']) }})</h6>--}}
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead class="table-light">
                                                        <tr><th>Date</th><th>Item</th><th>Amount</th></tr>
                                                        </thead>
                                                        <tbody>
                                                        @forelse($p['construction_material_billings'] as $row)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($row['created_at'])->format('d M Y') }}</td>
                                                                <td>{{ $row['description'] }}</td>
                                                                <td class="fw-semibold">₹{{ number_format($row['debit']) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No materials found</td></tr>
                                                        @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                {{--                                            <h6 class="mb-2 text-warning"><i class="fas fa-ruler-combined me-2"></i>Square Footage (₹{{ number_format($p['square_footage_total_amount']) }})</h6>--}}
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead class="table-light">
                                                        <tr><th>Date</th><th>Work</th><th>Amount</th></tr>
                                                        </thead>
                                                        <tbody>
                                                        @forelse($p['square_footage_bills'] as $row)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($row['created_at'])->format('d M Y') }}</td>
                                                                <td>{{ $row['description'] }}</td>
                                                                <td class="fw-semibold">₹{{ number_format($row['debit']) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No square footage work found</td></tr>
                                                        @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                {{--                                            <h6 class="mb-2 text-danger"><i class="fas fa-receipt me-2"></i>Expenses (₹{{ number_format($p['daily_expenses_total_amount']) }})</h6>--}}
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead class="table-light">
                                                        <tr><th>Date</th><th>Item</th><th>Amount</th></tr>
                                                        </thead>
                                                        <tbody>
                                                        @forelse($p['daily_expenses'] as $row)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($row['created_at'])->format('d M Y') }}</td>
                                                                <td>{{ $row['description'] }}</td>
                                                                <td class="fw-semibold">₹{{ number_format($row['debit']) }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="3" class="text-center text-muted">No expenses found</td></tr>
                                                        @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                {{--                                            <h6 class="mb-2 text-success"><i class="fas fa-users me-2"></i>Labour / Wasta (₹{{ number_format($p['daily_labours_total_amount'] + $p['daily_wastas_total_amount']) }})</h6>--}}
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-striped">
                                                        <thead class="table-light">
                                                        <tr><th>Date</th><th>Type</th><th>Amount</th></tr>
                                                        </thead>
                                                        <tbody>
                                                        @forelse($p['daily_wastas'] as $row)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($row['created_at'])->format('d M Y') }}</td>
                                                                <td>Wasta: {{ $row['description'] }}</td>
                                                                <td class="fw-semibold">₹{{ number_format($row['debit']) }}</td>
                                                            </tr>
                                                        @empty
                                                        @endforelse
                                                        @forelse($p['daily_labours'] as $row)
                                                            <tr>
                                                                <td>{{ \Carbon\Carbon::parse($row['created_at'])->format('d M Y') }}</td>
                                                                <td>Labour: {{ $row['description'] }}</td>
                                                                <td class="fw-semibold">₹{{ number_format($row['debit']) }}</td>
                                                            </tr>
                                                        @empty
                                                        @endforelse
                                                        @if(empty($p['daily_wastas']) && empty($p['daily_labours']))
                                                            <tr><td colspan="3" class="text-center text-muted">No labour/wasta records found</td></tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calculator text-muted mb-3" style="font-size: 3rem;"></i>
                        <h5 class="text-muted">No phase calculations available</h5>
                        <p class="text-muted">Create phases and add expenses to see detailed calculations</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Phase Detail Modal -->
        <div id="phaseDetailModal" class="modal fade modal-lg" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: white;">
                        <h5 class="modal-title" id="phaseDetailTitle">
                            <i class="fas fa-hammer me-2"></i> Construction Data - <span id="phaseNameTitle"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Phase Detail Tabs -->
                        <div class="phase-detail-tabs">
                            <ul class="nav nav-tabs" id="phaseDetailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-content" type="button" role="tab">
                                        <i class="fas fa-receipt me-2"></i> Expenses
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="materials-tab" data-bs-toggle="tab" data-bs-target="#materials-content" type="button" role="tab">
                                        <i class="fas fa-boxes me-2"></i> Materials
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="progress-tab" data-bs-toggle="tab" data-bs-target="#progress-content" type="button" role="tab">
                                        <i class="fas fa-chart-line me-2"></i> Progress
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <!-- Phase Detail Tab Content -->
                        <div class="tab-content" id="phaseDetailTabContent">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-outline-orange">Save Draft</button>
                        <button type="button" class="btn btn-orange">Save Construction Data</button>
                    </div>
                </div>
            </div>
        </div>





        <div id="phase" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
             data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <form class="forms-sample material-form" id="phaseForm">
                            @csrf

                            Phase Name
                            <div class="form-group">
                                <input type="text" name="phase_name" id="phase_name" />
                                <label for="phase_name" class="control-label">Phase Name</label>
                                <i class="bar"></i>
                            </div>


                            Date
                            <div class="form-group">
                                <input type="date" name="created_at" id="created_at" />
                                <label for="created_at" class="control-label">Date</label>
                                <i class="bar"></i>
                            </div>


                            Site
                            <div class="form-group">
                                <input type="hidden" name="site_id" value="{{ $site->id }}" />
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    Create Phase
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Construction Material Form -->
        <div id="modal-construction-billings" class="modal fade" aria-hidden="true" data-bs-backdrop="static"
             data-bs-keyboard="false" aria-labelledby="exampleModalToggleLabel" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-body">

                        <form enctype="multipart/form-data" class="forms-sample material-form"
                              id="constructionBillingForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Amount -->
                                    <div class="form-group mb-3">
                                        <input type="number" name="amount" id="amount" step="0.01"
                                               min="0" />
                                        <label for="amount" class="control-label">Material Price</label>
                                        <i class="bar"></i>
                                        <p class="mt-1 text-danger" id="amount-error"></p>
                                    </div>
                                </div>



                                <div class="col-md-6">
                                    <!-- Unit -->
                                    <div class="form-group mb-3">
                                        <input type="number" name="unit_count" id="unit_count" min="1" />
                                        <label for="unit_count" class="control-label">Units</label>
                                        <i class="bar"></i>
                                        <p class="mt-1 text-danger" id="unit_count-error"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Item Name - Toggleable Field -->
                                <div class="col-12 mb-3">
                                    <div class="btn-group btn-group-sm mb-2" role="group">
                                        <button type="button" class="btn btn-outline-primary toggle-item-btn active"
                                                data-mode="select">View list</button>
                                        <button type="button" class="btn btn-outline-secondary toggle-item-btn"
                                                data-mode="custom">Enter custom</button>
                                    </div>

                                    <!-- Item Select (visible by default) -->
                                    <div id="item-select-container">
                                        <select class="form-select text-black form-select-sm" name="item_name"
                                                id="item_name">
                                            <option value="">Select Item</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item->item_name }}">{{ $item->item_name }}</option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-danger" id="item_name-error"></p>
                                    </div>



                                    <!-- Custom Input (hidden by default) -->
                                    <div id="custom-item-container" style="display: none;">
                                        <input type="text" class="form-control" name="custom_item_name"
                                               id="custom_item_name" placeholder="Enter item name">
                                        <p class="mt-1 text-danger" id="custom_item_name-error"></p>
                                    </div>


                                    Date
                                    <div class="form-group">
                                        <input type="date" name="created_at" id="created_at" />
                                        <label for="created_at" class="control-label">Date</label>
                                        <i class="bar"></i>
                                        <p class="mt-1 text-danger" id="created_at-error"></p>

                                    </div>
                                </div>

                                <!-- Supplier -->
                                <div class="col-md-6 mb-3">
                                    <select class="form-select text-black form-select-sm" name="supplier_id">
                                        <option value="">Select Supplier</option>
                                        @foreach ($supp as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-danger" id="supplier_id-error"></p>
                                </div>

                                <!-- Phase -->
                                <div class="col-md-6 mb-3">
                                    <select class="form-select text-black form-select-sm" name="phase_id">
                                        <option value="">Select Phase</option>
                                        @foreach ($phases as $phase)
                                            <option value="{{ $phase->id }}">{{ $phase->phase_name }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-danger" id="phase_id-error"></p>
                                </div>

                                <!-- Image Upload -->
                                <div class="col-12 mb-3">
                                    <input class="form-control form-control-md" id="image" type="file"
                                           name="image">
                                    <p class="mt-1 text-danger" id="image-error"></p>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Create Billing</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>


        {{--     Square Footage Bill Model--}}
        <div id="modal-square-footage-bills" class="modal fade" aria-hidden="true" data-bs-backdrop="static"
             data-bs-keyboard="false" aria-labelledby="exampleModalToggleLabel" tabindex="-1">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <div class="modal-body">

                        Create Square Footage Bills
                        <form id="squareFootageBills" enctype="multipart/form-data" class="forms-sample material-form">

                            @csrf

                            Date
                            <div class="form-group">
                                <input type="date" name="created_at" id="created_at" />
                                <label for="created_at" class="control-label">Date</label>
                                <i class="bar"></i>
                                <p class="mt-1 text-danger" id="created_at-error"></p>

                            </div>

                            <!-- Wager Name -->
                            <div class="form-group">
                                <input id="wager_name" type="text" name="wager_name" />
                                <label for="wager_name" class="control-label" />Work
                                Type</label><i class="bar"></i>
                                <p class="text-danger" id="wager_name-error"></p>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <input id="price" type="number" name="price" />
                                <label for="price" class="control-label" />Price</label><i class="bar"></i>
                                <p class="text-danger" id="price-error"></p>
                            </div>

                            <!-- Number Of Days -->
                            <div class="form-group">
                                <input id="multiplier" type="number" name="multiplier" />
                                <label for="multiplier" class="control-label">Multiplier</label><i class="bar"></i>

                                <p class="text-danger" id="multiplier-error"></p>
                            </div>

                            <div class="row">

                                <div class="col-md-4">
                                    <!-- Type -->
                                    <select class="form-select text-black form-select-sm" id="exampleFormControlSelect3"
                                            name="type" style="cursor: pointer">
                                        <option value="">Select Type</option>
                                        <option value="per_sqr_ft">Per Square Feet</option>
                                        <option value="per_unit">Per Unit</option>
                                        <option value="full_contract">Full Contract
                                        </option>
                                    </select>
                                    <p class="text-danger" id="type-error"></p>
                                </div>

                                <div class="col-md-4">
                                    <!-- Select Supplier -->
                                    <select class="form-select text-black form-select-sm" id="supplier_id"
                                            name="supplier_id" style="cursor: pointer">
                                        <option value="">Select Supplier</option>
                                        @foreach ($supp as $supplier)
                                            <option value="{{ $supplier->id }}">
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <p class="text-danger" id="supplier_id-error"></p>

                                </div>


                                <!-- Phases -->
                                <div class="col-md-4">
                                    <select class="form-select text-black form-select-sm" id="exampleFormControlSelect3"
                                            name="phase_id" style="cursor: pointer">
                                        <option value="">Select Phase</option>
                                        @foreach ($phases as $phase)
                                            <option value="{{ $phase->id }}">
                                                {{ $phase->phase_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class=" mt-1 text-danger" id="phase_id-error"></p>
                                </div>
                            </div>


                            <!-- Image -->
                            <div class="mt-3">
                                <label for="image">Item Bill</label>
                                <input class="form-control form-control-md" id="image" type="file"
                                       name="image_path">
                                <p class="text-danger" id="image_path-error"></p>

                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    {{ __('Create Bill') }}
                                </button>
                            </div>



                        </form>

                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Expense -->
        <div id="modal-daily-expenses" class="modal fade" aria-hidden="true" aria-labelledby="exampleModalToggleLabel"
             data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">

            Daily Expenses
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <form id="dailyExpenses" class="forms-sample material-form">

                            @csrf

                            Date
                            <div class="form-group">
                                <input type="date" name="created_at" id="created_at" />
                                <label for="created_at" class="control-label">Date</label>
                                <i class="bar"></i>
                                <p class="mt-1 text-danger" id="created_at-error"></p>

                            </div>

                            <!-- Wager Name -->
                            <div class="form-group">
                                <input id="item_name" type="text" name="item_name" />
                                <label for="item_name" class="control-label">Item
                                    Name</label><i class="bar"></i>
                                <p class="text-danger" id="date-error"></p>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <input id="price" type="number" name="price" />
                                <label for="price" class="control-label">Price</label><i class="bar"></i>
                                <p class="text-danger" id="description-error"></p>
                            </div>

                            <!-- sites -->
                            <div class="form-group">
                                <input id="site_id" type="hidden" name="site_id" value="{{ $site->id }}" />
                            </div>

                            <!-- Phases -->
                            <div class=" col-12">
                                <select class="form-select text-black form-select-sm" id="exampleFormControlSelect3"
                                        name="phase_id" style="cursor: pointer">
                                    <option value="">Select Phase
                                    </option>
                                    @foreach ($phases as $phase)
                                        <option value="{{ $phase->id }}">
                                            {{ $phase->phase_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class=" mt-1 text-danger" id="phase_id-error"></p>
                            </div>


                            <div class="col-12 mt-3">

                                <input class="form-control" type="file" id="formFile" name="bill_photo">

                                <p class="text-danger" id="category_id-error"></p>

                            </div>


                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    {{ __('Create Bill') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>


        {{--     Payment Modal--}}
        <div id="payment-supplier" class="modal fade" aria-hidden="true" tabindex="-1" data-bs-backdrop="static"
             data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <form id="payment_supplierForm" class="forms-sample material-form" enctype="multipart/form-data">
                            @csrf




                            <div class="d-flex align-items-center gap-2 mb-3 p-2 border-start border-3 border-primary">
                                <i class="bi bi-building text-primary"></i>
                                <div>
                                    <small class="text-muted d-block">Client</small>
                                    <strong>{{ $site->client->name }}</strong>
                                </div>
                            </div>

                            Date
                            <div class="form-group">
                                <input type="date" name="created_at" id="created_at" />
                                <label for="created_at" class="control-label">Date</label>
                                <i class="bar"></i>
                                <p class="mt-1 text-danger" id="created_at-error"></p>
                            </div>

                            Amount
                            <div class="form-group">
                                <input type="text" name="amount" class="form-control" />
                                <label class="control-label">Amount</label>
                                <i class="bar"></i>
                                <div class="invalid-feedback" id="amount-error"></div>
                            </div>

                            Site (hidden)
                            <input type="hidden" name="site_id" value="{{ $site->id }}" />

                            Select Payee
                            <div class="mb-3">
                                <select name="payment_initiator" id="payment_initiator" style="cursor: pointer"
                                        class="form-select text-black form-select-sm" onchange="togglePayOptions()">
                                    <option value="">Select Payee</option>
                                    <option value="1">Supplier</option>
                                    <option value="0">Admin</option>
                                </select>
                                <div class="invalid-feedback" id="payment_initiator-error"></div>
                            </div>

                            Supplier Options
                            <div id="supplierOptions" style="display: none;" class="mb-3">
                                <select name="supplier_id" id="supplier_id" class="form-select text-black"
                                        style="cursor: pointer">
                                    <option value="">Select Supplier</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier['supplier_id'] }}">{{ $supplier['supplier_name'] }}
                                        </option>
                                    @endforeach
                                    <div class="invalid-feedback" id="supplier_id-error"></div>
                                </select>

                                Screenshot Upload
                                <div class="mb-3">
                                    <label class="control-label mt-3">Upload Screenshot</label>
                                    <input class="form-control" id="image" type="file" name="screenshot">
                                    <div class="invalid-feedback" id="screenshot-error"></div>
                                </div>

                                Narration
                                <div>
                                    <label class="control-label mt-3">Narration</label>
                                    <textarea id="narration" class="form-control" name="narration"></textarea>
                                    <div class="invalid-feedback" id="narration-error"></div>
                                </div>
                            </div>

                            Admin Options (Shown when Admin is selected)
                            <div id="adminOptions" style="display: none;" class="mt-4">

                                <div class="row g-3 mt-2">
                                    Sent Radio Option
                                    <div class="col-auto">
                                        <label for="transaction_sent">
                                            <input type="radio" name="transaction_type" id="transaction_sent"
                                                   value="1"> Return To {{ $site->client->name }}
                                        </label>
                                    </div>
                                    Received Radio Option
                                    <div class="col-auto">
                                        <label for="transaction_received">
                                            <input type="radio" name="transaction_type" id="transaction_received"
                                                   value="0"> Received By Admin
                                        </label>
                                    </div>
                                </div>
                            </div>






                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Pay</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div id="messageContainer"></div>


        <!-- The rest of your code remains the same (modals, etc.) -->

        @push('scripts')
            <script>
                // Global variables
                let currentPhaseId = null;
                let currentPhaseName = '';

                // Initialize when document is ready
                document.addEventListener('DOMContentLoaded', function() {
                    initializeFormSubmissions();
                    initializeEventListeners();
                });

                function initializeFormSubmissions() {
                    // Phase Form
                    $('#phaseForm').on('submit', function(e) {
                        e.preventDefault();
                        submitForm($(this), '/admin/phases', 'Phase created successfully');
                    });

                    // Construction Billing Form
                    $('#constructionBillingForm').on('submit', function(e) {
                        e.preventDefault();
                        submitForm($(this), '/admin/construction-billings', 'Material added successfully');
                    });

                    // Square Footage Form
                    $('#squareFootageBills').on('submit', function(e) {
                        e.preventDefault();
                        submitForm($(this), '/admin/square-footage-bills', 'Contractor billing added successfully');
                    });

                    // Daily Expenses Form
                    $('#dailyExpenses').on('submit', function(e) {
                        e.preventDefault();
                        submitForm($(this), '/admin/daily-expenses', 'Expense added successfully');
                    });

                    // Payment Form
                    $('#payment_supplierForm').on('submit', function(e) {
                        e.preventDefault();
                        submitForm($(this), '/admin/payments', 'Payment recorded successfully');
                    });
                }

                function initializeEventListeners() {
                    // Toggle item input method
                    $('.toggle-item-btn').on('click', function() {
                        const mode = $(this).data('mode');
                        $('.toggle-item-btn').removeClass('active').removeClass('btn-primary').addClass('btn-outline-secondary');
                        $(this).addClass('active btn-primary').removeClass('btn-outline-secondary');

                        if (mode === 'custom') {
                            $('#item-select-container').hide();
                            $('#custom-item-container').show();
                        } else {
                            $('#item-select-container').show();
                            $('#custom-item-container').hide();
                        }
                    });

                    // Date filter toggle
                    $('#dateFilter').on('change', function() {
                        if ($(this).val() === 'custom') {
                            $('#customDateRange').show();
                        } else {
                            $('#customDateRange').hide();
                        }
                    });

                    // Reset filters
                    $('#resetFilters').on('click', function() {
                        $('#filterForm').find('select, input').val('');
                        $('#filterForm').submit();
                    });
                }

                function submitForm(form, url, successMessage) {
                    const formData = new FormData(form[0]);

                    // Show loading state
                    const submitBtn = form.find('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

                    // Clear previous errors
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback, .text-danger').html('');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            showMessage(successMessage, 'success');
                            form[0].reset();

                            // Close modal
                            form.closest('.modal').modal('hide');

                            // Reload page or update UI as needed
                            if (currentPhaseId) {
                                loadPhaseData(currentPhaseId);
                            } else {
                                location.reload(); // Reload page to see new data
                            }
                        },
                        error: function(xhr) {
                            const errors = xhr.responseJSON.errors;
                            if (errors) {
                                // Display validation errors
                                for (const field in errors) {
                                    const input = form.find('[name="' + field + '"]');
                                    const errorContainer = form.find('#' + field + '-error');

                                    if (input.length) {
                                        input.addClass('is-invalid');
                                    }
                                    if (errorContainer.length) {
                                        errorContainer.html(errors[field][0]);
                                    }
                                }
                            } else {
                                showMessage('An error occurred. Please try again.', 'error');
                            }
                        },
                        complete: function() {
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    });
                }

                function showMessage(message, type) {
                    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                    const messageHtml = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;

                    $('#messageContainer').html(messageHtml);

                    // Auto dismiss after 5 seconds
                    setTimeout(() => {
                        $('#messageContainer').empty();
                    }, 5000);
                }

                // Phase Management Functions
                function openPhaseDetail(phaseId, phaseName) {
                    currentPhaseId = phaseId;
                    currentPhaseName = phaseName;

                    // Update detail view title
                    document.getElementById('phaseNameTitle').textContent = phaseName;

                    // Show detail view, hide list view
                    document.getElementById('phaseListView').style.display = 'none';
                    document.getElementById('phaseDetailView').style.display = 'block';

                    // Load phase data
                    loadPhaseData(phaseId);
                }

                function showPhaseList() {
                    // Show list view, hide detail view
                    document.getElementById('phaseListView').style.display = 'block';
                    document.getElementById('phaseDetailView').style.display = 'none';

                    // Reset current phase
                    currentPhaseId = null;
                    currentPhaseName = '';
                }

                function loadPhaseData(phaseId) {
                    // Show loading state in all tables
                    document.getElementById('expensesTableBody').innerHTML = `
                <tr><td colspan="4" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
            `;
                    document.getElementById('materialsTableBody').innerHTML = `
                <tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
            `;
                    document.getElementById('contractorTableBody').innerHTML = `
                <tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
            `;

                    // Load phase data via AJAX
                    $.ajax({
                        url: window.location.href,
                        type: 'GET',
                        data: {
                            ajax_action: 'get_phase_data',
                            phase_id: phaseId
                        },
                        success: function(response) {
                            if (response.success) {
                                // Categorize the response data
                                const phaseLedgers = response.response || [];

                                const materials = phaseLedgers.filter(item => item.category === 'Material');
                                const expenses = phaseLedgers.filter(item =>
                                    item.category === 'Attendance' || item.category === 'Expense'
                                );
                                const contractorBillings = phaseLedgers.filter(item => item.category === 'SQFT');

                                populateExpensesTable(expenses);
                                populateMaterialsTable(materials);
                                populateContractorTable(contractorBillings);
                            } else {
                                throw new Error('Failed to load data');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading phase data:', error);
                            document.getElementById('expensesTableBody').innerHTML = `
                        <tr><td colspan="4" class="text-center text-danger">Failed to load data</td></tr>
                    `;
                            document.getElementById('materialsTableBody').innerHTML = `
                        <tr><td colspan="5" class="text-center text-danger">Failed to load data</td></tr>
                    `;
                            document.getElementById('contractorTableBody').innerHTML = `
                        <tr><td colspan="5" class="text-center text-danger">Failed to load data</td></tr>
                    `;
                        }
                    });
                }

                function populateExpensesTable(expenses) {
                    const tbody = document.getElementById('expensesTableBody');
                    if (!expenses || expenses.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No expense entries found</td></tr>';
                        return;
                    }

                    tbody.innerHTML = expenses.map(expense => `
                <tr>
                    <td>${new Date(expense.created_at).toLocaleDateString()}</td>
                    <td>${expense.description}</td>
                    <td>₹${expense.debit}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteExpense(${expense.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
                }

                function populateMaterialsTable(materials) {
                    const tbody = document.getElementById('materialsTableBody');
                    if (!materials || materials.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No material entries found</td></tr>';
                        return;
                    }

                    tbody.innerHTML = materials.map(material => `
                <tr>
                    <td>${new Date(material.created_at).toLocaleDateString()}</td>
                    <td>${material.description}</td>
                    <td>${material.unit_count || 1}</td>
                    <td>₹${material.debit}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMaterial(${material.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
                }

                function populateContractorTable(billings) {
                    const tbody = document.getElementById('contractorTableBody');
                    if (!billings || billings.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No contractor billing entries found</td></tr>';
                        return;
                    }

                    tbody.innerHTML = billings.map(billing => `
                <tr>
                    <td>${new Date(billing.created_at).toLocaleDateString()}</td>
                    <td>${billing.supplier || 'N/A'}</td>
                    <td>${billing.description}</td>
                    <td>₹${billing.debit}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteContractorBilling(${billing.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
                }

                function addExpense() {
                    // Reset the existing expense form
                    document.getElementById('dailyExpenses').reset();

                    // Set the phase_id in the form
                    const phaseSelect = document.querySelector('#dailyExpenses select[name="phase_id"]');
                    if (phaseSelect && currentPhaseId) {
                        phaseSelect.value = currentPhaseId;
                    }

                    // Show expense modal
                    const expenseModal = new bootstrap.Modal(document.getElementById('modal-daily-expenses'));
                    expenseModal.show();
                }

                function addMaterial() {
                    // Reset the existing material form
                    document.getElementById('constructionBillingForm').reset();

                    // Set the phase_id in the form
                    const phaseSelect = document.querySelector('#constructionBillingForm select[name="phase_id"]');
                    if (phaseSelect && currentPhaseId) {
                        phaseSelect.value = currentPhaseId;
                    }

                    // Show material modal
                    const materialModal = new bootstrap.Modal(document.getElementById('modal-construction-billings'));
                    materialModal.show();
                }

                function addContractorBilling() {
                    // Reset the existing contractor form
                    document.getElementById('squareFootageBills').reset();

                    // Set the phase_id in the form
                    const phaseSelect = document.querySelector('#squareFootageBills select[name="phase_id"]');
                    if (phaseSelect && currentPhaseId) {
                        phaseSelect.value = currentPhaseId;
                    }

                    // Show contractor modal
                    const contractorModal = new bootstrap.Modal(document.getElementById('modal-square-footage-bills'));
                    contractorModal.show();
                }

                function deleteExpense(expenseId) {
                    if (confirm('Are you sure you want to delete this expense?')) {
                        $.ajax({
                            url: `/admin/expenses/${expenseId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                showMessage('Expense deleted successfully', 'success');
                                loadPhaseData(currentPhaseId);
                            },
                            error: function() {
                                showMessage('Failed to delete expense', 'error');
                            }
                        });
                    }
                }

                function deleteMaterial(materialId) {
                    if (confirm('Are you sure you want to delete this material?')) {
                        $.ajax({
                            url: `/admin/materials/${materialId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                showMessage('Material deleted successfully', 'success');
                                loadPhaseData(currentPhaseId);
                            },
                            error: function() {
                                showMessage('Failed to delete material', 'error');
                            }
                        });
                    }
                }

                function deleteContractorBilling(billingId) {
                    if (confirm('Are you sure you want to delete this contractor billing?')) {
                        $.ajax({
                            url: `/admin/contractor-billings/${billingId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function() {
                                showMessage('Contractor billing deleted successfully', 'success');
                                loadPhaseData(currentPhaseId);
                            },
                            error: function() {
                                showMessage('Failed to delete contractor billing', 'error');
                            }
                        });
                    }
                }

                function togglePayOptions() {
                    const payee = document.getElementById('payment_initiator').value;
                    const supplierOptions = document.getElementById('supplierOptions');
                    const adminOptions = document.getElementById('adminOptions');

                    if (payee === '1') {
                        supplierOptions.style.display = 'block';
                        adminOptions.style.display = 'none';
                    } else if (payee === '0') {
                        supplierOptions.style.display = 'none';
                        adminOptions.style.display = 'block';
                    } else {
                        supplierOptions.style.display = 'none';
                        adminOptions.style.display = 'none';
                    }
                }
            </script>
        @endpush




    </x-app-layout>





