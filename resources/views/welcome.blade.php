<x-guest-layout>

     <div class="container row gap-2">
                <a href="{{ route('login') }}" class="btn btn-info  me-2">Sign In As Admin</a>
                <a href="{{ route('login') }}" class="btn btn-info me-2"> Sign In As Project Manager</a>
                <a href="{{ route('client.login') }}" class="btn btn-info  me-2"> Sign In As Client</a>
    </div>


    


</x-guest-layout>
