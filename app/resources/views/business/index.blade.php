@extends('layouts.app')
@section('title', 'Business Memory')
@section('subtitle', 'Manage what the AI knows about your business')

@section('content')
<div class="grid grid-cols-3 gap-6">
    {{-- ── Left: Add New Entry ─────────────────────────────── --}}
    <div>
        <div class="card p-5 sticky top-24">
            <h2 class="font-semibold text-white mb-4"><svg class="w-5 h-5 mr-1.5 inline text-brand-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add Entry</h2>
            <form action="{{ route('business.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Category</label>
                    <select name="category" required class="w-full rounded-xl px-3 py-2.5 text-sm mb-2" onchange="toggleCustomCategory(this.value, 'custom-cat-input')">
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                        @endforeach
                        <option value="custom">+ Custom Category...</option>
                    </select>
                    <input type="text" name="custom_category" id="custom-cat-input" placeholder="Enter custom category name" class="w-full rounded-xl px-3 py-2.5 text-sm hidden" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Key / Title</label>
                    <input type="text" name="key" required placeholder="e.g. Delivery Policy"
                        class="w-full rounded-xl px-3 py-2.5 text-sm" />
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1.5">Value / Description</label>
                    <textarea name="value" rows="4" required placeholder="Describe this item in detail..."
                        class="w-full rounded-xl px-3 py-2.5 text-sm resize-none"></textarea>
                </div>
                <button type="submit" class="w-full btn-primary py-2.5 rounded-xl text-sm font-semibold text-white">
                    Add to Memory
                </button>
            </form>
        </div>
    </div>

    {{-- ── Right: Existing Entries ──────────────────────────── --}}
    <div class="col-span-2 space-y-5">
        @php
            $catIcons = [
                'services'=>'<svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
                'pricing'=>'<svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                'menu'=>'<svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>',
                'faqs'=>'<svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                'hours'=>'<svg class="w-5 h-5 text-cyan-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                'contact'=>'<svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>'
            ];
            $catColors = ['services'=>'#3b82f6','pricing'=>'#10b981','menu'=>'#f59e0b','faqs'=>'#8b5cf6','hours'=>'#06b6d4','contact'=>'#ec4899'];
        @endphp

        @forelse ($categories as $cat)
        @if(isset($grouped[$cat]) && $grouped[$cat]->count())
        <div class="card overflow-hidden">
            <div class="px-5 py-3 flex items-center gap-2 border-b" style="border-color:rgba(255,255,255,0.05);background:rgba(255,255,255,0.02);">
                <span class="text-base">{!! $catIcons[$cat] ?? '<svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' !!}</span>
                <h3 class="font-semibold text-sm text-white">{{ ucfirst($cat) }}</h3>
                <span class="ml-auto text-xs text-slate-600">{{ $grouped[$cat]->count() }} entries</span>
            </div>

            <div class="divide-y" style="divide-color:rgba(255,255,255,0.04);">
                @foreach ($grouped[$cat] as $item)
                <div class="px-5 py-3 group" id="item-{{ $item->id }}">
                    {{-- View Mode --}}
                    <div class="view-mode-{{ $item->id }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium text-white">{{ $item->key }}</span>
                                    @if(!$item->active)
                                    <span class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(239,68,68,0.15);color:#f87171;">Inactive</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 leading-relaxed">{{ $item->value }}</p>
                            </div>
                            <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                                <button onclick="toggleEdit({{ $item->id }})"
                                    class="px-2.5 py-1 rounded-lg text-xs border border-slate-700 text-slate-400 hover:border-brand-500 hover:text-brand-400 transition-all">
                                    Edit
                                </button>
                                <button onclick="toggleActive({{ $item->id }}, this)"
                                    class="px-2.5 py-1 rounded-lg text-xs border transition-all
                                    {{ $item->active ? 'border-slate-700 text-slate-400 hover:border-amber-600 hover:text-amber-400' : 'border-emerald-800 text-emerald-400 hover:border-emerald-600' }}">
                                    {{ $item->active ? 'Disable' : 'Enable' }}
                                </button>
                                <form action="{{ route('business.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Delete this entry?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs border border-slate-700 text-red-400 hover:border-red-700 transition-all">✕</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Edit Mode --}}
                    <div class="edit-mode-{{ $item->id }} hidden">
                        <form action="{{ route('business.update', $item->id) }}" method="POST" class="space-y-2">
                            @csrf @method('PUT')
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <select name="category" class="w-full rounded-lg px-3 py-2 text-xs mb-2" onchange="toggleCustomCategory(this.value, 'edit-custom-cat-{{ $item->id }}')">
                                        @foreach ($categories as $cat2)
                                        <option value="{{ $cat2 }}" {{ $item->category === $cat2 ? 'selected' : '' }}>{{ ucfirst($cat2) }}</option>
                                        @endforeach
                                        <option value="custom">+ Custom...</option>
                                    </select>
                                    <input type="text" name="custom_category" id="edit-custom-cat-{{ $item->id }}" placeholder="Custom category" class="w-full rounded-lg px-3 py-2 text-xs hidden" />
                                </div>
                                <input type="text" name="key" value="{{ $item->key }}" class="rounded-lg px-3 py-2 text-xs" />
                            </div>
                            <textarea name="value" rows="2" class="w-full rounded-lg px-3 py-2 text-xs resize-none">{{ $item->value }}</textarea>
                            <div class="flex gap-2">
                                <button type="submit" class="btn-primary px-3 py-1.5 rounded-lg text-xs text-white font-medium">Save</button>
                                <button type="button" onclick="toggleEdit({{ $item->id }})" class="px-3 py-1.5 rounded-lg text-xs border border-slate-700 text-slate-400">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @empty
        <div class="card flex flex-col items-center justify-center py-24 text-slate-600">
            <svg class="w-12 h-12 mb-3 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
            <p class="text-sm">No business memory yet</p>
            <p class="text-xs mt-1">Add your services, pricing, FAQs using the form on the left</p>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleEdit(id) {
    document.querySelector('.view-mode-' + id).classList.toggle('hidden');
    document.querySelector('.edit-mode-' + id).classList.toggle('hidden');
}

async function toggleActive(id, btn) {
    const r = await fetch(`/business/${id}/toggle`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
    });
    const d = await r.json();
    btn.textContent = d.active ? 'Disable' : 'Enable';
    location.reload();
}

function toggleCustomCategory(value, inputId) {
    const input = document.getElementById(inputId);
    if (value === 'custom') {
        input.classList.remove('hidden');
        input.required = true;
    } else {
        input.classList.add('hidden');
        input.required = false;
        input.value = '';
    }
}
</script>
@endpush
