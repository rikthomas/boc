<h2>Daily Oxygen Usage Report - UCH and NHNN Site</h2>

<p><a href="http://uclh-icu.org.uk/vie/current/public/">Click here for further analytics</a></p>

<h3>UCH Summary</h3>

<table cellpadding="0" cellspacing="0" width="640" border="1">
    <tr>
        <th>Date</th>
        <th>Oxygen Usage (L/min)</th>
        <th>% of total capacity (5000 L/min)</th>
        <th>% change</th>
    </tr>
    @foreach ($data['uch'] as $result)
    <tr>
        <td align="center">{{ $result['date'] }}</td>
        <td align="center">{{ $result['reading'] }}</td>
        <td align="center">{{ $result['limit'] }}%</td>
        <td align="center">{{ $result['change'] }}%</td>
    </tr>
    @endforeach
</table>

<h3>NHNN Summary</h3>

<table cellpadding="0" cellspacing="0" width="640" border="1">
    <tr>
        <th>Date</th>
        <th>Oxygen Usage (L/min)</th>
        <th>% of total capacity (3000 L/min)</th>
        <th>% change</th>
    </tr>
    @foreach ($data['nhnn'] as $result)
    <tr>
        <td align="center">{{ $result['date'] }}</td>
        <td align="center">{{ $result['reading'] }}</td>
        <td align="center">{{ $result['limit'] }}%</td>
        <td align="center">{{ $result['change'] }}%</td>
    </tr>
    @endforeach
</table>