<table width="100%" style="border-bottom: 1px solid #e8e0d8; padding-bottom: 8px; margin-bottom: 4px;">
    <tr>
        <td width="50%" style="vertical-align: middle;">
            @if ($logoPath)
                <img src="{{ $logoPath }}" alt="Al Shaheen" style="height: 30px; width: auto;">
            @else
                <span style="font-size: 11pt; font-weight: bold; color: #28414e;">Al Shaheen</span>
            @endif
        </td>
        <td width="50%" style="vertical-align: middle; text-align: {{ $locale === 'ar' ? 'left' : 'right' }};">
            <span style="font-size: 8pt; color: #5a6a72; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $locale === 'ar' ? 'منصة الشاهين الإعلامية' : 'Al Shaheen Media' }}
                @if (! empty($documentLabel))
                    — {{ $documentLabel }}
                @endif
            </span>
        </td>
    </tr>
</table>
