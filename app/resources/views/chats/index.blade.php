@extends('layouts.app')
@section('title', 'Chats')
@section('subtitle', 'All conversations')

@section('content')
<div class="card overflow-hidden">
    <div class="px-6 py-4 flex items-center justify-between border-b" style="border-color:rgba(255,255,255,0.06);">
        <h2 class="font-semibold text-white">Conversations <span class="text-slate-500 font-normal text-sm">({{ count($conversations) }})</span></h2>
        <a href="{{ route('dashboard') }}" class="text-xs text-slate-500 hover:text-brand-400 transition-colors">← Dashboard</a>
    </div>

    @if(count($conversations) === 0)
    <div class="flex flex-col items-center justify-center py-24 text-slate-600">
        <svg class="w-12 h-12 mb-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <p class="text-sm">No conversations yet</p>
        <p class="text-xs mt-1">Messages will appear here when users write to your WhatsApp</p>
    </div>
    @else
    <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
        @foreach ($conversations as $conv)
        @php
            $isFlagged   = isset($flagged[$conv['phone']]);
            $isTakeover  = $isFlagged && $flagged[$conv['phone']]->human_takeover;
            $flagData    = $flagged[$conv['phone']] ?? null;
        @endphp
        <div class="flex items-center gap-4 px-6 py-4 hover:bg-white/[0.02] transition-colors group">
            {{-- Avatar --}}
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold text-white"
                 style="background:linear-gradient(135deg,#{{ substr(md5($conv['phone']),0,6) }},#{{ substr(md5($conv['phone'].'x'),0,6) }});">
                {{ strtoupper(substr($conv['phone'], -2)) }}
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-white text-sm">+{{ $conv['phone'] }}</span>
                    @if($isTakeover)
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium" style="background:rgba(236,72,153,0.15);color:#f472b6;"><svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Human</span>
                    @elseif($isFlagged)
                        <span class="px-1.5 py-0.5 rounded text-xs font-medium" style="background:rgba(239,68,68,0.15);color:#f87171;"><svg class="w-3 h-3 mr-1 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>Flagged</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 truncate flex items-center mt-0.5">
                    <svg class="w-3.5 h-3.5 mr-1.5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $conv['last_role'] === 'assistant' ? '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>' : '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>' !!}</svg> <span>{{ Str::limit($conv['last_message'], 70) }}</span>
                </p>
            </div>

            {{-- Meta --}}
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                <span class="text-xs text-slate-600">{{ \Carbon\Carbon::parse($conv['last_at'])->diffForHumans() }}</span>
                <span class="text-xs text-slate-700">{{ $conv['count'] }} msgs</span>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <a href="{{ route('chats.show', $conv['phone']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium btn-primary text-white">
                    View
                </a>
                <form action="{{ route('chats.takeover', $conv['phone']) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium border transition-all
                        {{ $isTakeover ? 'border-emerald-700 text-emerald-400 hover:border-emerald-500' : 'border-pink-800 text-pink-400 hover:border-pink-600' }}">
                        {!! $isTakeover ? '<svg class="w-3.5 h-3.5 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> AI On' : '<svg class="w-3.5 h-3.5 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Takeover' !!}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
