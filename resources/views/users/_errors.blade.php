<div class="alert alert-error" role="alert">
    <x-icon name="alert" :size="18" />
    <ul>@foreach($errors->getBag('user')->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
