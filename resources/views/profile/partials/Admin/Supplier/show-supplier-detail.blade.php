<x-app-layout>

    @php

        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';

    @endphp



    <div class="row g-4">

        <div class="col-12 col-md-6">
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

        <div class="col-12 col-md-6">
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


    </div>

    @if (count($data) >= 0)
        <div class="row mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">

                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th class="fw-bold bg-info text-white">Date</th>
                                        <th class="fw-bold bg-info text-white">Bill Proof</th>
                                        <th class="fw-bold bg-info text-white">Type</th>
                                        <th class="fw-bold bg-info text-white">Item</th>
                                        <th class="fw-bold bg-info text-white">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data['data'] as $d)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($d['created_at'])->format('d-M-Y') }}</td>
                                            <td>
                                                @if ($d['image'])
                                                    <a href="{{ asset('storage/' . $d['image']) }}"
                                                        data-fancybox="gallery">
                                                        <img src="{{ asset('storage/' . $d['image']) }}"
                                                            alt="Bill proof" class="img-thumbnail"
                                                            style="width: 50px; height: 50px;">
                                                    </a>
                                                @else
                                                    <span class="text-muted">NA</span>
                                                @endif
                                            </td>
                                            <td>{{ $d['type'] }}</td>
                                            <td>{{ $d['item'] }}</td>
                                            <td
                                                class="text-end fw-bold {{ $d['transaction_type'] === 'debit' ? 'text-danger' : 'text-success' }}">
                                                ₹{{ number_format($d['total_price'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No transaction records found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($data['data']->hasPages())
                            <div class="p-3 border-top">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        {{-- Previous Page --}}
                                        @if ($data['data']->onFirstPage())
                                            <li class="page-item disabled">
                                                <span class="page-link">&laquo;</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $data['data']->previousPageUrl() }}"
                                                    rel="prev">&laquo;</a>
                                            </li>
                                        @endif

                                        {{-- Page Numbers --}}
                                        @foreach ($data['data']->getUrlRange(1, $data['data']->lastPage()) as $page => $url)
                                            @if ($page == $data['data']->currentPage())
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

                                        {{-- Next Page --}}
                                        @if ($data['data']->hasMorePages())
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $data['data']->nextPageUrl() }}"
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
    @endif



</x-app-layout>
