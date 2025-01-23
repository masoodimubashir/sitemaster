   <nav aria-label="breadcrumb " class="second ">
       <ol class="breadcrumb indigo lighten-6 first">
           @foreach ($breadcrumbs as $breadcrumb)
               <li class="breadcrumb-item font-weight-bold">
                   <a class="black-text text-uppercase {{ Request::is($breadcrumb['url']) ? ' active-2' : '' }}"
                       href="{{ url($breadcrumb['url']) }}">
                       <span class="fw-bold">{{ $breadcrumb['name'] }}</span>
                   </a>
                   @if (!$loop->last)
                       <i class="fa fa-angle-double-right" aria-hidden="true"></i>
                   @endif
               </li>
           @endforeach
       </ol>
   </nav>
