@extends('layouts.reseller')
@section('title', 'Brand Settings')
@section('subtitle', 'Customize how your portal looks to clients')

@section('content')
<div x-data="brandingPanel()" class="space-y-6 max-w-2xl">

    {{-- Flash messages --}}
    <div x-show="success" class="p-3 rounded-lg bg-brand-500/10 border border-brand-500/30 text-brand-400 text-xs" x-text="success"></div>
    <div x-show="error" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-xs" x-text="error"></div>

    {{-- ── Logo & Favicon ──────────────────────────────────────── --}}
    <div class="card p-6 space-y-5">
        <h3 class="text-sm font-semibold text-white">Logo & Favicon</h3>
        <form id="branding-form" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div class="grid grid-cols-2 gap-5">
                {{-- Logo Upload --}}
                <div class="space-y-2">
                    <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider block">Brand Logo</label>
                    @if($reseller->logo_path)
                        <img src="{{ Storage::url($reseller->logo_path) }}" alt="Logo" class="h-12 object-contain rounded-lg border border-slate-700 p-2 bg-slate-900" />
                    @else
                        <div class="h-12 w-20 rounded-lg border border-dashed border-slate-700 bg-slate-900 flex items-center justify-center text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <label class="inline-block px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800 text-xs text-slate-300 cursor-pointer hover:border-slate-600 transition-colors">
                        Choose Logo
                        <input type="file" name="logo" accept="image/*" class="hidden" x-on:change="logoFile = $event.target.files[0]" />
                    </label>
                    <div x-show="logoFile" class="text-[10px] text-brand-400" x-text="logoFile?.name"></div>
                </div>

                {{-- Favicon Upload --}}
                <div class="space-y-2">
                    <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider block">Favicon</label>
                    @if($reseller->favicon_path)
                        <img src="{{ Storage::url($reseller->favicon_path) }}" alt="Favicon" class="h-12 w-12 object-contain rounded-lg border border-slate-700 p-2 bg-slate-900" />
                    @else
                        <div class="h-12 w-12 rounded-lg border border-dashed border-slate-700 bg-slate-900 flex items-center justify-center text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                    <label class="inline-block px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-800 text-xs text-slate-300 cursor-pointer hover:border-slate-600 transition-colors">
                        Choose Favicon
                        <input type="file" name="favicon" accept="image/*" class="hidden" x-on:change="faviconFile = $event.target.files[0]" />
                    </label>
                    <div x-show="faviconFile" class="text-[10px] text-brand-400" x-text="faviconFile?.name"></div>
                </div>
            </div>
        </form>
    </div>

    {{-- ── Brand Colors ────────────────────────────────────────── --}}
    <div class="card p-6 space-y-5">
        <h3 class="text-sm font-semibold text-white">Brand Colors</h3>
        <div class="grid grid-cols-2 gap-5">
            <div class="space-y-2">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider block">Primary Accent Color</label>
                <p class="text-[10px] text-slate-600">Used for buttons, active states, links, highlights</p>
                <div class="flex items-center gap-3">
                    <input x-model="primaryColor" type="color" class="w-12 h-10 rounded-lg border border-slate-700 cursor-pointer bg-transparent" />
                    <input x-model="primaryColor" type="text" placeholder="#10b981" class="flex-1 text-xs px-3 py-2 rounded-lg font-mono" />
                </div>
                <div class="h-6 w-full rounded-lg transition-all" :style="'background: ' + primaryColor"></div>
            </div>
            <div class="space-y-2">
                <label class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider block">Sidebar Background Color</label>
                <p class="text-[10px] text-slate-600">Used for the navigation sidebar background</p>
                <div class="flex items-center gap-3">
                    <input x-model="sidebarColor" type="color" class="w-12 h-10 rounded-lg border border-slate-700 cursor-pointer bg-transparent" />
                    <input x-model="sidebarColor" type="text" placeholder="#080f1e" class="flex-1 text-xs px-3 py-2 rounded-lg font-mono" />
                </div>
                <div class="h-6 w-full rounded-lg transition-all" :style="'background: ' + sidebarColor"></div>
            </div>
        </div>
    </div>



    {{-- ── Live Preview ─────────────────────────────────────────── --}}
    <div class="card p-5">
        <h3 class="text-xs font-semibold text-slate-400 mb-3 uppercase tracking-wider">Live Sidebar Preview</h3>
        <div class="rounded-xl overflow-hidden flex h-32 border border-slate-700">
            <div class="w-28 flex-shrink-0 flex flex-col p-2 gap-1.5" :style="'background:' + sidebarColor">
                <div class="flex items-center gap-1.5 px-1.5 mb-1">
                    <div class="w-4 h-4 rounded flex items-center justify-center text-white text-[8px] font-bold" :style="'background:' + primaryColor">B</div>
                    <span class="text-white text-[9px] font-bold" x-text="'{{ $reseller->name }}'.substring(0,10)">Brand</span>
                </div>
                <div class="px-1.5 py-0.5 rounded text-white text-[8px] font-semibold" :style="'background:' + primaryColor + '22; color:' + primaryColor">Dashboard</div>
                <div class="px-1.5 py-0.5 rounded text-slate-400 text-[8px]">Clients</div>
                <div class="px-1.5 py-0.5 rounded text-slate-400 text-[8px]">Branding</div>
            </div>
            <div class="flex-1 p-3" style="background: #070d1a;">
                <div class="h-3 bg-slate-800 rounded w-1/2 mb-2"></div>
                <div class="h-2 bg-slate-800 rounded w-3/4 mb-3"></div>
                <div class="flex gap-2">
                    <div class="h-8 flex-1 rounded-lg border" :style="'background:' + primaryColor + '15; border-color:' + primaryColor + '30'"></div>
                    <div class="h-8 flex-1 rounded-lg bg-slate-800"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Save Button ─────────────────────────────────────────── --}}
    <div class="flex justify-end">
        <button @click="saveBranding()"
                :disabled="saving"
                class="px-6 py-2.5 text-sm font-bold rounded-lg text-white transition-all hover:opacity-90 disabled:opacity-50 flex items-center gap-2 shadow-lg"
                style="background: {{ $reseller->primary_color }}">
            <div x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
            <span x-text="saving ? 'Saving...' : 'Save Branding'"></span>
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
function brandingPanel() {
    return {
        primaryColor: '{{ $reseller->primary_color ?? "#10b981" }}',
        sidebarColor: '{{ $reseller->sidebar_color ?? "#080f1e" }}',
        logoFile: null,
        faviconFile: null,
        saving: false,
        success: '',
        error: '',

        saveBranding() {
            this.saving = true; this.success = ''; this.error = '';
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const form  = new FormData();
            form.append('_token', token);
            form.append('primary_color', this.primaryColor);
            form.append('sidebar_color', this.sidebarColor);
            if (this.logoFile)    form.append('logo', this.logoFile);
            if (this.faviconFile) form.append('favicon', this.faviconFile);

            fetch('/reseller-admin/branding', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                body: form,
            }).then(r => r.json()).then(data => {
                this.saving = false;
                if (data.success) {
                    this.success = data.message;
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    this.error = data.message || 'Save failed.';
                }
            }).catch(() => { this.saving = false; this.error = 'Network error.'; });
        },
    };
}
</script>
@endpush
