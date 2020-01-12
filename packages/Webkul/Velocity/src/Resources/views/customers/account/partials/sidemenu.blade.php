<div class="customer-sidebar row no-margin no-padding">
    <div class="account-details col-12">
        <div class="customer-name col-12 text-uppercase">
            {{ substr(auth('customer')->user()->first_name, 0, 1) }}
        </div>
        <div class="col-12 customer-name-text text-capitalize text-break">{{ auth('customer')->user()->first_name . ' ' . auth('customer')->user()->last_name}}</div>
        <div class="customer-email col-12 text-break">{{ auth('customer')->user()->email }}</div>
    </div>

    @foreach ($menu->items as $menuItem)
        <ul type="none" class="navigation">
            @foreach ($menuItem['children'] as $index => $subMenuItem)
                <li class="{{ $menu->getActive($subMenuItem) }}">
                    <a class="unset fw6 full-width" href="{{ $subMenuItem['url'] }}">
                        <i class="icon {{ $index }} text-down-3"></i>
                        <span>{{ trans($subMenuItem['name']) }}<span>
                        <i class="rango-arrow-right pull-right text-down-3"></i>
                    </a>
                </li>
            @endforeach
        </ul>
    @endforeach
</div>

@push('css')
    <style type="text/css">
        .general-container {
            margin-bottom: 0px;
            min-height: 100vh;
        }
    </style>
@endpush
