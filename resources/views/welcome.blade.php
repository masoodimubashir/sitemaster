<x-guest-layout>

     <div class="container row g-4 ">
            <h1 class="text-info fw-bold display-1">Welcome</h1>
            <p class="fw-bold">Please select your role to sign in:</p>
            <div class="row g-2">
                <a href="{{ route('login') }}" class="btn btn-primary  col-md-3 me-2"> Admin</a>
                <a href="{{ route('login') }}" class="btn btn-success  col-md-4 me-2"> Project Manager</a>
                <a href="{{ route('client.login') }}" class="btn btn-info col-md-3 me-2"> Client</a>
            </div>
    </div>



</x-guest-layout>
