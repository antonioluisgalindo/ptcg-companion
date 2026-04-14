@extends('layouts.app')
@section('title', 'Roles & Permisos')

@section('content')
<h2 style="font-size:1.5rem;font-weight:700;color:var(--color-text-primary);margin-bottom:var(--spacing-xl);">Roles & Permisos</h2>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:var(--spacing-lg);">
    @foreach($roles as $role)
    <div class="card-custom">
        <div class="card-custom-header">
            <div>
                <h3 class="card-custom-title">{{ ucfirst($role->name) }}</h3>
                <p style="font-size:0.75rem;color:var(--color-text-muted);margin-top:2px;">{{ $role->users_count }} usuarios</p>
            </div>
            <span class="badge-custom badge-primary">{{ $role->permissions->count() }} permisos</span>
        </div>
        <div class="card-custom-body">
            <div style="display:flex;flex-wrap:wrap;gap:var(--spacing-xs);">
                @foreach($role->permissions as $perm)
                    <span class="badge-custom badge-secondary" style="font-size:0.65rem;font-family:monospace;">{{ $perm->name }}</span>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
