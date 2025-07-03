<form id="editPaymentForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="id" value="{{ $payment->id }}">

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" value="{{ $payment->amount }}" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="supplier_id" class="form-label">Supplier</label>
            <select class="form-select text-black" id="supplier_id" name="supplier_id" required>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $payment->supplier_id == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="site_id" class="form-label">Site</label>
            <select class="form-select text-black" id="site_id" name="site_id" required>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ $payment->site_id == $site->id ? 'selected' : '' }}>
                        {{ $site->site_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6 mb-3">
            <label for="screenshot" class="form-label">Payment Screenshot</label>
            <input type="file" class="form-control" id="screenshot" name="screenshot">
            @if($payment->screenshot)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $payment->screenshot) }}" style="max-width: 100px;" class="img-thumbnail">
                </div>
            @endif
        </div>

    </div>
</form>