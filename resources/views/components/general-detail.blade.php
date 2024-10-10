
 <div class="bg-light-subtle p-3 shadow-sm rounded mb-2">
     <p class="statistics-title text-info fw-bold  {{ $balance >= 0 || $balance === null  ? "text-info" : 'text-danger' }}">
         {{ $slot }}
     </p>
 </div>
