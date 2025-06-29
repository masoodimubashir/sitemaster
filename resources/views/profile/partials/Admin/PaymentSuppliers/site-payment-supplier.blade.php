<x-app-layout>

    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp

    @if ($user === 'admin')
        <x-breadcrumb :names="['Dashboard', $site->site_name, ' Back']"
        :urls="[
            'admin/dashboard',
            'admin/sites/' . base64_encode($site->id),
            'admin/sites/' . base64_encode($site->id),
        ]" />
    @else
        <x-breadcrumb :names="['Sites', 'View ' . $site->site_name, 'View ' . $site->site_name . ' Payments']" :urls="['user/dashboard', 'user/sites/' . base64_encode($site->id), 'user/sites/payments/' . $site->id]" />
    @endif

    <div class="row">

        <!-- Site Info Cards -->
        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10">
                            <i class="fas fa-building text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Site Name</h6>
                            <h5 class="mb-0">{{ ucwords($site->site_name) }}</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10">
                            <i class="fas fa-map-marker-alt text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Location</h6>
                            <h5 class="mb-0">{{ ucwords($site->location) }}</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="bg-opacity-10">
                            <i class="fas fa-percent text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Service Charge</h6>
                            <h5 class="mb-0">{{ $site->service_charge }}%</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10">
                            <i class="fas fa-user-tie text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Site Owner</h6>
                            <h5 class="mb-0">{{ ucwords($site->site_owner_name) }}</h5>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-opacity-10">
                            <i class="fa-solid fa-phone text-info fs-3 p-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Contact</h6>
                            <h5 class="mb-0">
                                <a href="tel:{{ $site->contact_no }}" class="text-decoration-none">
                                    {{ $site->contact_no }}
                                </a>
                            </h5>
                        </div>
                    </div>

                    @if ($payments && $payments->count() > 0)
                        <div class="d-flex align-items-center">
                            <div class="bg-opacity-10">
                                <i class="fas fa-money-bill text-info fs-3 p-2"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Payments</h6>
                                <h5 class="mb-0">{{ Number::currency($payments->sum('amount'), 'INR') }}</h5>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="col-lg-12 grid-margin stretch-card mt-3">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="fw-bold bg-info text-white">Date</th>
                                <th class="fw-bold bg-info text-white">Screenshot</th>
                                <th class="fw-bold bg-info text-white">Supplier</th>
                                <th class="fw-bold bg-info text-white">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($payments && $payments->count() > 0)
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            @if ($payment->screenshot)
                                                <a href="{{ asset('storage/' . $payment->screenshot) }}"
                                                    data-fancybox="gallery"
                                                    data-caption="Payment - {{ $payment->created_at->format('d M Y') }}">
                                                    <img src="{{ asset('storage/' . $payment->screenshot) }}"
                                                        alt="Payment Receipt"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ ucwords($payment->supplier->name ?? 'N/A') }}</td>
                                        <td class="fw-bold text-success">
                                            {{ Number::currency($payment->amount, 'INR') }}
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-danger fw-bold py-4">
                                        No payment records found
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if ($payments && $payments->hasPages())
                    <div class="p-3 border-top">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center mb-0">
                                @if ($payments->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">&laquo;</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $payments->previousPageUrl() }}"
                                            rel="prev">&laquo;</a>
                                    </li>
                                @endif

                                @foreach ($payments->getUrlRange(1, $payments->lastPage()) as $page => $url)
                                    @if ($page == $payments->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                @if ($payments->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $payments->nextPageUrl() }}"
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

    <!-- Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Screenshot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="previewImage" src="" class="img-fluid" alt="Preview">
                </div>
            </div>
        </div>
    </div>

    <script>
        function showImagePreview(src) {
            document.getElementById('previewImage').src = src;
        }
    </script>

</x-app-layout>
