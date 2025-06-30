<div class="row">
    <div class="col-auto col-12">
        <nav aria-label="breadcrumb" class="second">
            <ol class="breadcrumb bg-white lighten-6 first px-md-4">
                @foreach ($breadcrumbs as $index => $breadcrumb)
                    <li class="breadcrumb-item font-weight-bold">
                        <a class="black-text text-uppercase 
                            {{ request()->is(ltrim($breadcrumb['url'], '/')) || request()->fullUrl() === url($breadcrumb['url']) || $loop->last ? 'active-2' : '' }}"
                            href="{{ url($breadcrumb['url']) }}">
                            <span class="{{ $loop->last ? '' : 'mr-md-3 mr-2' }}">
                              
                                {{ $breadcrumb['name'] }}
                            </span>
                        </a>
                        @if (!$loop->last)
                            <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>
</div>


<style>
    .second .breadcrumb>li+li:before {
        content: "" !important;
    }


 

    .breadcrumb-item a {
        text-decoration: none;
        transition: color 0.15s ease-in-out;
    }

    .breadcrumb-item a:hover {
        text-decoration: none;
        color: #34B1AA !important;
    }

    .black-text {
        color: #212529 !important;
    }

    .text-uppercase {
        text-transform: uppercase !important;
    }

    .font-weight-bold {
        font-weight: 700 !important;
    }

    .active-2 {
        color: #34B1AA !important;
        font-weight: 800 !important;
    }

    .indigo.lighten-6 {
        background-color: #e8eaf6 !important;
    }


   

    .fa {
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: inherit;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .fa-home:before {
        content: "\f015";
    }

    .fa-angle-double-right:before {
        content: "\f101";
    }

    .mr-1 {
        margin-right: 0.25rem !important;
    }


    .mr-md-3 {
        margin-right: .5rem !important;
    }

    @media (max-width: 767.98px) {
        .mr-md-3 {
            margin-right: 0.5rem !important;
        }
    }

  




  
</style>
