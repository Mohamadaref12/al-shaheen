<table width="100%" style="border-top: 1px solid #e8e0d8; padding-top: 6px; font-size: 8pt; color: #7a8790;">
    <tr>
        <td width="20%" style="vertical-align: middle;">
            @if ($logoPath)
                <img src="{{ $logoPath }}" alt="" style="height: 16px; width: auto; opacity: 0.85;">
            @endif
        </td>
        <td width="60%" style="vertical-align: middle; text-align: center;">
            {{ $locale === 'ar' ? 'تم التصدير من منصة الشاهين الإعلامية' : 'Exported from Al Shaheen Media' }}
            — {{ now()->format('Y-m-d H:i') }}
        </td>
        <td width="20%" style="vertical-align: middle; text-align: {{ $locale === 'ar' ? 'left' : 'right' }};">
            {PAGENO} / {nbpg}
        </td>
    </tr>
</table>
