<!doctype html>
<html>
<head><meta charset="utf-8"><title>Advanced Report</title></head>
<body>
<h2>Advanced Report</h2>
<table border="1" cellspacing="0" cellpadding="6">
    <thead><tr><th>Name</th><th>Email</th><th>Stage</th><th>Country</th><th>Agent</th><th>Sub-Agent</th></tr></thead>
    <tbody>
    @foreach($rows as $s)
        <tr>
            <td>{{ $s->full_name }}</td>
            <td>{{ $s->email }}</td>
            <td>{{ $s->stage }}</td>
            <td>{{ $s->target_country }}</td>
            <td>{{ $s->agent?->name ?: '-' }}</td>
            <td>{{ $s->subAgent?->name ?: '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>

