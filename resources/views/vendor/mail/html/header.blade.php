@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">

            @if (trim($slot) === 'Promise App')
                {{-- Logo PROMISE --}}
                <img src="{{ asset('assets/image/logo-promise.png') }}"
                     class="logo"
                     alt="Promise App"
                     style="height:40px; display:block; margin:0 auto;">
            @else
                {!! $slot !!}
            @endif

        </a>
    </td>
</tr>
