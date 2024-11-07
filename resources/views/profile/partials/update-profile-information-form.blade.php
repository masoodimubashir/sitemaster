<section>

    @if (session('status') === 'name')
        <x-success-message message="Name Updated Successfully" />
    @endif

    <div class="row">

        <x-header>
          Update Name
        </x-header>

        <form method="POST" action="{{ route('profile.update') }}" class="forms-sample material-form">

            @csrf
            @method('patch')

            <div class="form-group">
                <input type="text" name="name" value="{{ auth()->user()->name }}" />
                <label for="input" class="control-label" value="{{ old('name') }}">Name</label><i
                    class="bar"></i>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <button class=" btn btn-info"><span>Save</span></button>

        </form>


    </div>




</section>
