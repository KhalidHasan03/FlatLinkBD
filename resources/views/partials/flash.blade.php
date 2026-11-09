@if (session('success'))
    <div class="container">
        <div class="alert alert-success" data-dismissible>
            <span>{{ session('success') }}</span>
            <button type="button" class="alert-close" data-dismiss>&times;</button>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="container">
        <div class="alert alert-error" data-dismissible>
            <span>{{ session('error') }}</span>
            <button type="button" class="alert-close" data-dismiss>&times;</button>
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="container">
        <div class="alert alert-error" data-dismissible>
            <div>
                <strong>Please fix the following:</strong>
                <ul class="alert-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="alert-close" data-dismiss>&times;</button>
        </div>
    </div>
@endif
