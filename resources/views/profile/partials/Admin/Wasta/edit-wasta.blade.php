<x-app-layout>

    @php
        $user = auth()->user()->role_name === 'admin' ? 'admin' : 'user';
    @endphp

    <x-breadcrumb :names="['Wastas', 'Edit ' . $wasta->wasta_name]" :urls="[$user . '/wasta', $user . '/wasta/' . $wasta->id . '/edit']" />

    {{-- Success Messages --}}
    @if (session('status') === 'update')
        <x-success-message message="Wasta updated successfully!" />
    @endif

    {{-- Error Messages --}}
    @if (session('status') === 'error')
        <div class="alert alert-danger">
            {{ session('message') }}
        </div>
    @endif


    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <form method="POST" action="{{ url($user . '/wasta', $wasta->id) }}"
                        class="forms-sample material-form">
                        @csrf
                        @method('PUT')

                        {{-- Wasta Name --}}
                        <div class="form-group">
                            <input type="text" 
                                   name="wasta_name"
                                   value="{{ old('wasta_name', $wasta->wasta_name) }}" 
                                   class="@error('wasta_name') is-invalid @enderror" />
                            <label class="control-label">Wasta Name</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('wasta_name')" class="mt-2" />
                        </div>

                        {{-- Contact Number --}}
                        <div class="form-group">
                            <input type="text" 
                                   name="contact_no"
                                   value="{{ old('contact_no', $wasta->contact_no) }}" 
                                   class="@error('contact_no') is-invalid @enderror" />
                            <label class="control-label">Contact Number</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('contact_no')" class="mt-2" />
                        </div>

                        {{-- Price --}}
                        <div class="form-group">
                            <input type="number" 
                                   name="price" 
                                   value="{{ old('price', $wasta->price) }}" 
                                   min="1"
                                   class="@error('price') is-invalid @enderror" />
                            <label class="control-label">Price</label><i class="bar"></i>
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="btn btn-success">
                            <span>Save</span>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>