@extends('layouts.app')
@section('title', 'Basma — Votre assistante Bosten')
@section('content')
    <div class="container py-4" style="max-width: 700px">
        <h2>💬 Parlez à Basma</h2>
        <div id="chat-box" class="border rounded p-3 mb-3" style="height:400px; overflow-y:auto; background:#f9f9f9">
            @foreach ($history as $conv)
                <div class="text-end mb-2"><span class="badge bg-success px-3 py-2">{{ $conv->message }}</span></div>
                <div class="mb-2"><span class="badge bg-secondary px-3 py-2 text-start">{{ $conv->reply }}</span></div>
            @endforeach
            <div id="messages"></div>
        </div>
        <form id="chat-form" class="d-flex gap-2">
            @csrf
            <input type="text" id="chat-input" class="form-control" placeholder="Posez votre question...">
            <button class="btn btn-success">Envoyer</button>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if (!msg) return;

            const box = document.getElementById('messages');
            box.innerHTML +=
                `<div class="text-end mb-2"><span class="badge bg-success px-3 py-2">${msg}</span></div>`;
            input.value = '';

            const res = await fetch('{{ route('chatbot.ask') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name=_token]').value
                },
                body: JSON.stringify({
                    message: msg
                })
            });
            const data = await res.json();
            box.innerHTML +=
                `<div class="mb-2"><span class="badge bg-secondary px-3 py-2">${data.reply}</span></div>`;
            document.getElementById('chat-box').scrollTop = 99999;
        });
    </script>
@endsection
