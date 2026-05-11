@if ($paginator->hasPages())
    {{ $paginator->appends(request()->except('page'))->links('pagination::admin-bootstrap-5') }}
@endif