@props(['url'])
<tr>
    <td class="header" style="padding:0; margin:0; text-align:center;">
        <a href="{{ $url }}"
           style="display:block; text-decoration:none; max-width:570px; margin:0 auto;">

            @if (trim($slot) === 'Promise App')
                {{-- Banner PROMISE tanpa gambar, lebar sama dengan body --}}
                <div style="
                    background-color:#0300a5;
                    color:#ffffff;
                    font-family: Arial, Helvetica, sans-serif;
                    text-align:center;
                    padding:12px 20px;
                    display:block;
                    border-radius:4px 4px 0 0;
                ">
                    <div style="font-weight:700; font-size:18px; letter-spacing:2px;">
                        ----- PROMISE -----
                    </div>
                    <div style="font-weight:600; font-size:13px; margin-top:3px;">
                        Project Management Integrated System Engineering
                    </div>
                    <div style="font-weight:600; font-size:13px; margin-top:3px;">
                        PT. Summit Adyawinsa Indonesia
                    </div>
                </div>
            @else
                {!! $slot !!}
            @endif

        </a>
    </td>
</tr>
