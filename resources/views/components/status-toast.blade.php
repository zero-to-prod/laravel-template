@if(session('status'))
    <div class="toast toast-top toast-end z-50">
        <div role="alert" class="alert alert-success">
            <span>{{ session('status') }}</span>
        </div>
    </div>
@endif
