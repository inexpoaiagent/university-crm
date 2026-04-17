@extends('layouts.portal')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">My Documents</h2>
    <table>
        <thead><tr><th>Type</th><th>File</th><th>Uploaded At</th><th>Status</th><th>Expiry</th><th>Action</th></tr></thead>
        <tbody>
        @foreach($documents as $d)
            <tr>
                <td>{{ $d->label }}</td>
                <td>
                    @if(!$d->is_missing)
                        <a href="{{ $d->file_url }}" target="_blank">{{ $d->file_name }}</a>
                    @else
                        <span class="footer-note">Missing</span>
                    @endif
                </td>
                <td>{{ $d->uploaded_at ?: '-' }}</td>
                <td>
                    @if($d->is_missing)
                        <span class="badge rejected">Missing</span>
                    @elseif($d->status === 'verified')
                        <span class="badge enrolled">Verified</span>
                    @else
                        <span class="badge applied">Uploaded</span>
                    @endif
                </td>
                <td>{{ $d->expiry_date ?: '-' }}</td>
                <td>
                    <form method="POST" action="/portal/documents" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="{{ $d->type }}">
                        <input type="file" name="file" required>
                        <input type="date" name="expiry_date">
                        <button>Upload</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
