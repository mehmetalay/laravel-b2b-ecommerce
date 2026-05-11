@foreach ($categories as $category)
    <li class="{{ $category->children->count() ? 'sub-dropdown-hover' : '' }}">
        <a class="dropdown-item" href="{{ route('product.list', [$category->slug]) }}">
            {{ $category->name }}
        </a>

        @if ($category->children->count())
            <ul class="sub-menu">
                <x-category-menu :categories="$category->children" />
            </ul>
        @endif
    </li>
@endforeach