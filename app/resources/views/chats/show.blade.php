@extends('layouts.app')
@section('title', '+' . $phone)
@section('subtitle', 'Chat history & controls')

@section('content')
<div class="flex gap-5">
    {{-- ── Chat Window ─────────────────────────────────────── --}}
    <div class="flex-1 card flex flex-col" style="height:calc(100vh - 140px);">
        {{-- Chat Header --}}
        <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:rgba(255,255,255,0.06);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white"
                     style="background:linear-gradient(135deg,#{{ substr(md5($phone),0,6) }},#{{ substr(md5($phone.'x'),0,6) }});">
                    {{ strtoupper(substr($phone,-2)) }}
                </div>
                <div>
                    <div class="font-medium text-white text-sm">+{{ $phone }}</div>
                    <div class="text-xs text-slate-500">{{ count($messages) }} messages</div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Human Takeover Toggle --}}
                <form action="{{ route('chats.takeover', $phone) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition-all
                        {{ $flagged?->human_takeover ? 'border-emerald-700 text-emerald-400 hover:border-emerald-500' : 'border-pink-800 text-pink-400 hover:border-pink-600' }}">
                        {!! $flagged?->human_takeover ? '<svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> Enable AI' : '<svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Take Over' !!}
                    </button>
                </form>
                {{-- Clear Memory --}}
                <form action="{{ route('chats.clear-memory', $phone) }}" method="POST"
                      onsubmit="return confirm('Clear all memory for this user?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-lg text-sm font-medium border border-slate-700 text-slate-400 hover:border-red-700 hover:text-red-400 transition-all">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg> Clear Memory
                    </button>
                </form>
                <a href="{{ route('chats.index') }}" class="px-4 py-2 rounded-lg text-sm text-slate-500 hover:text-white transition-colors">← Back</a>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-3" id="chat-messages">
            @forelse ($messages as $msg)
            <div class="flex {{ $msg->role === 'assistant' ? 'justify-start' : 'justify-end' }} fade-in">
                <div class="max-w-xs lg:max-w-md xl:max-w-lg">
                    @if($msg->role === 'assistant')
                    <div class="flex items-end gap-2">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0 mb-1 text-white"
                             style="background:linear-gradient(135deg,#059669,#10b981);"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                        <div>
                            <div class="px-4 py-2.5 rounded-2xl rounded-bl-sm text-sm text-slate-200"
                                 style="background:#1a2740;border:1px solid rgba(255,255,255,0.06);">
                                {{ $msg->content }}
                            </div>
                            <div class="text-xs text-slate-700 mt-1 ml-1">{{ $msg->created_at->format('h:i A') }}</div>
                        </div>
                    </div>
                    @else
                    <div class="flex items-end gap-2 flex-row-reverse">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs flex-shrink-0 mb-1 text-white font-bold"
                             style="background:linear-gradient(135deg,#{{ substr(md5($phone),0,6) }},#{{ substr(md5($phone.'x'),0,6) }});">
                            {{ strtoupper(substr($phone,-1)) }}
                        </div>
                        <div>
                            <div class="px-4 py-2.5 rounded-2xl rounded-br-sm text-sm text-white"
                                 style="background:linear-gradient(135deg,#1d4ed8,#2563eb);">
                                {{ $msg->content }}
                            </div>
                            <div class="text-xs text-slate-700 mt-1 mr-1 text-right">{{ $msg->created_at->format('h:i A') }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center h-full text-slate-600">
                <svg class="w-12 h-12 mb-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <p class="text-sm">No messages yet</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── Sidebar: User Memory ─────────────────────────────── --}}
    <div class="w-72 space-y-4">
        {{-- Status Card --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-white mb-3">Conversation Status</h3>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">AI Replies</span>
                    <span class="{{ $flagged?->human_takeover ? 'text-pink-400' : 'text-emerald-400' }} font-medium">
                        {!! $flagged?->human_takeover ? '<svg class="w-3.5 h-3.5 mr-1 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Paused' : '<svg class="w-3.5 h-3.5 mr-1 inline" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Active' !!}
                    </span>
                </div>
                @if($flagged)
                <div class="flex justify-between">
                    <span class="text-slate-500">Flag Reason</span>
                    <span class="text-amber-400">{{ $flagged->reason ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Flag Status</span>
                    <span class="text-slate-300">{{ ucfirst($flagged->status) }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Long-term Memory --}}
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-white mb-3"><svg class="w-4 h-4 mr-1.5 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg> User Memory</h3>
            @if($memory?->summary)
                <p class="text-xs text-slate-400 leading-relaxed">{{ $memory->summary }}</p>
                <p class="text-xs text-slate-700 mt-3">Updated {{ $memory->last_activity_at?->diffForHumans() }}</p>
            @else
                <p class="text-xs text-slate-600">No summary yet. Generated automatically after 10 messages.</p>
            @endif
        </div>

        {{-- Flagged Message --}}
        @if($flagged?->last_message)
        <div class="card p-5" style="border-color:rgba(239,68,68,0.2);">
            <h3 class="text-sm font-semibold text-red-400 mb-3"><svg class="w-4 h-4 mr-1.5 inline text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg> Flagged Message</h3>
            <p class="text-xs text-slate-400 leading-relaxed">{{ $flagged->last_message }}</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-scroll to bottom of chat
    const chatEl = document.getElementById('chat-messages');
    if (chatEl) chatEl.scrollTop = chatEl.scrollHeight;
</script>
@endpush
