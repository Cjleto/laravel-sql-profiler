@extends('layouts.app')

@section('content')
<div class="container">
    <h1>SQL Profiler Dashboard</h1>
    <h2>Query più lente</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tempo (ms)</th>
                <th>SQL</th>
                <th>Bindings</th>
                <th>Connection</th>
                <th>User</th>
                <th>Data/Ora</th>
            </tr>
        </thead>
        <tbody>
        @foreach($queries as $query)
            <tr>
                <td>{{ $query->time }}</td>
                <td><code>{{ $query->sql }}</code></td>
                <td><code>{{ $query->bindings }}</code></td>
                <td>{{ $query->connection }}</td>
                <td>{{ $query->user_id }}</td>
                <td>{{ $query->created_at }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Possibili N+1 Query</h2>
    @if($nPlusOne->isEmpty())
        <p>Nessun caso sospetto rilevato.</p>
    @else
        <ul>
        @foreach($nPlusOne as $sql => $group)
            <li>
                <strong>{{ $group->count() }} volte</strong>:
                <code>{{ $sql }}</code>
            </li>
        @endforeach
        </ul>
    @endif
</div>
@endsection
