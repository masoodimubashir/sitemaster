<x-app-layout>


    <div class="row">


        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h3 class="text-info">Edit Daily Expense</h3>

                    @if (session('message'))
                        <p class="card-description">
                            {{ session('message') }}

                        </p>
                    @endif

                    <form method="POST" action="{{ route('daily-expenses.update', [$dialy_expense->id]) }}"
                        class="forms-sample material-form">

                        @csrf
                        @method('PUT')

                        <!-- Wager Name -->
                        <div class="form-group">
                            <input id="item_name" type="text" name="item_name"
                                value="{{ $dialy_expense->item_name }}" />
                            <label for="item_name" class="control-label">Item
                                Name</label><i class="bar"></i>
                            @error('item_name')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <!-- Price -->
                        <div class="form-group">
                            <input id="price" type="number" name="price" value="{{ $dialy_expense->price }}" />
                            <label for="price" class="control-label">Price</label><i class="bar"></i>
                            @error('price')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="col-12 mt-3">

                            <input class="form-control" type="file" id="formFile" name="bill_photo" value="{{ $dialy_expense->bill_photo }}">
                            @error('bill_photo')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror

                        </div>

                        <!-- Select Phase -->
                        <div class=" col-md-6 mt-3">
                            <input id="phase_id" type="hidden" name="phase_id" placeholder="Phase"
                                value="{{ $dialy_expense->phase_id }}" />
                            @error('phase_id')
                                <x-input-error :messages="$message" class="mt-2" />
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">

                            <div class="button-container">

                                <a class=" btn btn-info"
                                    href="{{ route('sites.show', [base64_encode($dialy_expense->phase->site->id)]) }}"><span>Back</span></a>

                                <button class="btn btn-info"><span>Update Billing</span></button>

                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
