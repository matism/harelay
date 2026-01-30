@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 auto;">
<tr>
<td style="padding-right: 12px; vertical-align: middle;">
<svg viewBox="0 0 48 48" width="40" height="40" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M24 4L4 20V44H18V30H30V44H44V20L24 4Z" fill="#22d3ee" opacity="0.2"/>
<path d="M24 4L4 20V44H18V30H30V44H44V20L24 4Z" stroke="#22d3ee" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<circle cx="24" cy="24" r="3" fill="#22d3ee"/>
<circle cx="16" cy="24" r="2" fill="#22d3ee" opacity="0.6"/>
<circle cx="32" cy="24" r="2" fill="#22d3ee" opacity="0.6"/>
<path d="M18 24H22M26 24H30" stroke="#22d3ee" stroke-width="2" stroke-linecap="round"/>
</svg>
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
