<div id="alert-container" class="position-fixed"
    style="bottom: 100px; left: 50%; z-index: 1050; transform: translate(-50%, 0);"
    onload="setTimeout(function() { document.getElementById('alert-container').style.display = 'none'; }, 3000);">
    <div id="alert-box" class="alert  alert-dismissible fade show shadow-sm border border-danger p-0" role="alert">
        <div class="row align-items-center g-0 bg-white">
            <div class="bg-danger col-auto p-3">
                <i class="fa fa-x text-white"></i>
            </div>
            <div class="col ps-5 pe-5">
                <strong class="text-danger"> {{ __($message) }}</strong>
            </div>
        </div>
    </div>
</div>

<script>
    // Automatically trigger the timeout when the element loads
    setTimeout(function() {
        document.getElementById('alert-container').style.display = 'none';
    }, 2000);
</script>
