@extends('layouts.admin')
@section('title', 'Gestion des commandes')
@section('breadcrumb')
    <li class="breadcrumb-item active">Commandes</li>
@endsection

@section('content')
<h4 class="fw-bold mb-4">📦 Toutes les commandes</h4>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td class="fw-bold">{{ $order->id }}</td>
                    <td>{{ $order->user->name }}</td>
                    <td>{{ number_format($order->total, 2) }} TND</td>
                    <td>
                        <form method="POST"
                              action="{{ route('admin.orders.updateStatus', $order) }}">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select form-select-sm"
                                    onchange="this.form.submit()" style="width:140px">
                                @foreach(['pending','confirmed','shipped','delivered','cancelled'] as $s)
                                    <option value="{{ $s }}"
                                        {{ $order->status === $s ? 'selected' : '' }}>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('orders.show', $order) }}"
                           class="btn btn-sm btn-outline-success">
                            Voir
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $orders->links() }}</div>
@endsection
