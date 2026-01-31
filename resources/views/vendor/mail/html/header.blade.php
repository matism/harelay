@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto;">
<tr>
<td style="padding-right: 12px; vertical-align: middle;">
<img src="https://harelay.com/favicon.png" width="40" height="40" alt="HARelay" style="vertical-align: middle;">
</td>
<td style="vertical-align: middle;">
<span style="font-size: 24px; font-weight: 700; color: #ffffff;">HARelay</span>
</td>
</tr>
</table>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
