@extends('layouts.app')

@section('title', 'Founder Admin Dashboard')
@section('subtitle', 'Global system oversight and tenant controls')

@section('content')
<div x-data="adminDashboard()" class="space-y-6">
    <!-- Top System Alert Bar -->
    <template x-if="alerts.length > 0">
        <div class="space-y-3">
            <template x-for="alert in alerts" :key="alert.message">
                <div class="flex items-center gap-3 p-4 rounded-xl border text-sm font-medium fade-in"
                     :class="{
                        'bg-red-500/10 border-red-500/30 text-red-400': alert.type === 'danger',
                        'bg-amber-500/10 border-amber-500/30 text-amber-400': alert.type === 'warning',
                        'bg-brand-500/10 border-brand-500/30 text-brand-400': alert.type === 'info'
                     }">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span x-text="alert.message"></span>
                </div>
            </template>
        </div>
    </template>

    <!-- Global AI Status Alert (Specifically highlighted) -->
    <div x-show="!globalAiEnabled" class="flex items-center justify-between p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm font-medium fade-in">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 animate-pulse text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <div>
                <span class="font-bold">Global AI Paused:</span> AI Auto-replies are disabled globally across all tenants. WhatsApp messages will still ingest but won't be replied to.
            </div>
        </div>
        <button @click="toggleGlobalAi()" class="px-3 py-1.5 bg-amber-500 text-slate-950 font-semibold rounded-lg hover:bg-amber-400 transition-colors text-xs">
            Turn ON Globally
        </button>
    </div>

    <!-- Main Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Tenants -->
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Total Tenants</span>
                <div class="text-3xl font-bold text-white mt-1" x-text="stats.total_tenants">0</div>
                <div class="text-[11px] text-slate-500 mt-2">Active business subdomains</div>
            </div>
            <div class="p-3.5 rounded-xl bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>

        <!-- Active Connections -->
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Active WHATSAPP</span>
                <div class="text-3xl font-bold text-white mt-1 flex items-baseline gap-1">
                    <span x-text="stats.active_connections">0</span>
                    <span class="text-slate-500 text-sm font-normal">/ <span x-text="stats.total_tenants">0</span></span>
                </div>
                <div class="flex items-center gap-1.5 mt-2">
                    <div class="w-2 h-2 rounded-full pulse-dot bg-brand-500" x-show="stats.active_connections > 0"></div>
                    <div class="w-2 h-2 rounded-full bg-slate-600" x-show="stats.active_connections === 0"></div>
                    <span class="text-[11px] text-slate-500">Connected bot sessions</span>
                </div>
            </div>
            <div class="p-3.5 rounded-xl bg-brand-500/10 text-brand-400 border border-brand-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
        </div>

        <!-- Messages Today -->
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Messages Today</span>
                <div class="text-3xl font-bold text-white mt-1" x-text="stats.messages_today">0</div>
                <div class="text-[11px] text-slate-500 mt-2">Inbound chat logs logged today</div>
            </div>
            <div class="p-3.5 rounded-xl bg-sky-500/10 text-sky-400 border border-sky-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                </svg>
            </div>
        </div>

        <!-- AI Replies Today -->
        <div class="card p-6 flex items-center justify-between">
            <div>
                <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">AI Auto-Replies</span>
                <div class="text-3xl font-bold text-white mt-1" x-text="stats.ai_replies_today">0</div>
                <div class="text-[11px] text-slate-500 mt-2 flex items-center gap-1">
                    <span class="text-brand-400" x-text="stats.messages_today > 0 ? Math.round((stats.ai_replies_today / stats.messages_today) * 100) + '%' : '0%'"></span>
                    <span>automation rate today</span>
                </div>
            </div>
            <div class="p-3.5 rounded-xl bg-purple-500/10 text-purple-400 border border-purple-500/20">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Side: Graph + Tenants Table -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Messages Graph -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white">System Inbound Load</h3>
                        <p class="text-xs text-slate-500">Incoming message frequency over the last 24 hours</p>
                    </div>
                </div>
                <div class="relative w-full h-48">
                    <canvas id="messagesChart"></canvas>
                </div>
            </div>

            <!-- Tenants List -->
            <div class="card overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Tenants Registry</h3>
                        <p class="text-xs text-slate-500">Manage business workspaces and toggle statuses</p>
                    </div>
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <div class="relative w-full sm:w-64">
                            <input type="text" 
                                   x-model="searchQuery" 
                                   placeholder="Search name or slug..." 
                                   autocomplete="off"
                                   class="w-full text-xs px-3.5 py-2 rounded-lg" />
                            <span class="absolute right-3 top-2.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                        </div>
                        <button @click="openProvisionWizard()" 
                                class="px-4 py-2 bg-brand-500 hover:bg-brand-400 text-slate-950 font-bold rounded-lg transition-all text-xs flex items-center gap-1.5 shadow-md shadow-brand-500/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            <span>Provision Tenant</span>
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-800 text-xs">
                        <thead class="bg-slate-900/40 text-slate-400 font-semibold text-left">
                            <tr>
                                <th class="px-6 py-3">Tenant Details</th>
                                <th class="px-6 py-3">WhatsApp Connection</th>
                                <th class="px-6 py-3">Tenant AI Autopilot</th>
                                <th class="px-6 py-3 text-center">Messages (Today)</th>
                                <th class="px-6 py-3">Last Active</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 text-slate-300">
                            <template x-for="tenant in filteredTenants()" :key="tenant.id">
                                <tr class="hover:bg-slate-900/10 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div>
                                                <div class="font-medium text-white flex items-center gap-1.5">
                                                    <span x-text="tenant.name"></span>
                                                    <template x-if="tenant.status === 'suspended'">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] bg-red-500/15 border border-red-500/30 text-red-400 font-semibold uppercase tracking-wider">Suspended</span>
                                                    </template>
                                                </div>
                                                <div class="text-[10px] text-slate-500 mt-0.5" x-text="'ID: ' + tenant.id + ' | slug: ' + tenant.slug"></div>
                                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                                    <!-- Plan Badge -->
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider bg-slate-800 text-slate-300 border border-slate-700 capitalize"
                                                          :class="{
                                                              'bg-indigo-500/15 border-indigo-500/30 text-indigo-400': tenant.plan === 'automator',
                                                              'bg-slate-800 border-slate-700 text-slate-300': tenant.plan === 'starter'
                                                          }"
                                                          x-text="tenant.plan">
                                                    </span>

                                                    <!-- Subscription Status Badge -->
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider bg-slate-800 border border-slate-700"
                                                          :class="{
                                                              'text-brand-400 border-brand-500/30 bg-brand-500/5': tenant.subscription_status === 'active',
                                                              'text-amber-400 border-amber-500/30 bg-amber-500/5': tenant.subscription_status === 'trialing',
                                                              'text-red-400 border-red-500/30 bg-red-500/5': ['expired', 'cancelled', 'past_due'].includes(tenant.subscription_status)
                                                          }"
                                                          x-text="tenant.subscription_status">
                                                    </span>

                                                    <!-- Support Add-on Badge -->
                                                    <template x-if="tenant.has_support_addon">
                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold uppercase tracking-wider bg-purple-500/15 border border-purple-500/30 text-purple-400">
                                                            👨‍💼 Support Setup
                                                        </span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-1.5 h-1.5 rounded-full" 
                                                 :class="tenant.whatsapp_status === 'connected' ? 'bg-brand-500 pulse-dot' : 'bg-red-500'"></div>
                                            <span class="capitalize" :class="tenant.whatsapp_status === 'connected' ? 'text-brand-400' : 'text-red-400'" x-text="tenant.whatsapp_status"></span>
                                            <span class="text-[10px] text-slate-600 font-normal" x-show="tenant.whatsapp_status === 'connected'" x-text="'(' + tenant.health_score + '%)'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <!-- AI Status Badge / Inline Toggle -->
                                            <button @click="toggleTenantAi(tenant.id)"
                                                    class="relative inline-flex h-4 w-7 flex-shrink-0 items-center rounded-full transition-colors focus:outline-none"
                                                    :class="tenant.ai_enabled ? 'bg-brand-500' : 'bg-slate-700'">
                                                <span class="inline-block h-2.5 w-2.5 rounded-full bg-white transition-transform"
                                                      :class="tenant.ai_enabled ? 'translate-x-3.5' : 'translate-x-0.5'"></span>
                                            </button>
                                            <span x-text="tenant.ai_enabled ? 'ON' : 'OFF'" 
                                                  :class="tenant.ai_enabled ? 'text-brand-400 font-semibold' : 'text-slate-500'"></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-300 font-mono text-[10px]" x-text="tenant.messages_today">0</span>
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 font-mono text-[10px]" x-text="tenant.last_active"></td>
                                    <td class="px-6 py-4 text-right space-x-1.5">
                                        <!-- Login As Client -->
                                        <form method="POST" :action="'/admin/impersonate/' + tenant.id" class="inline-block m-0">
                                            @csrf
                                            <button type="submit" 
                                                    title="Login As Client"
                                                    class="p-1 px-2 rounded bg-indigo-500/10 border border-indigo-500/30 hover:bg-indigo-500/20 hover:text-indigo-300 transition-all text-[11px] inline-flex items-center gap-1 text-indigo-400">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                                </svg>
                                                <span>Login</span>
                                            </button>
                                        </form>

                                        <!-- Reboot WhatsApp -->
                                        <button @click="reconnectTenantBot(tenant.id)" 
                                                title="Reboot WhatsApp Socket"
                                                class="p-1 px-2 rounded bg-amber-500/10 border border-amber-500/30 hover:bg-amber-500/20 hover:text-amber-300 transition-all text-[11px] inline-flex items-center gap-1 text-amber-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                            </svg>
                                            <span>Reboot</span>
                                        </button>

                                        <!-- View Details -->
                                        <button @click="viewTenantDetails(tenant)" 
                                                title="View Details"
                                                class="p-1 px-2 rounded bg-slate-800 border border-slate-700 hover:border-slate-600 hover:text-white transition-all text-[11px] inline-flex items-center gap-1 text-slate-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span>Details</span>
                                        </button>
                                        
                                        <!-- Pause / Resume -->
                                        <button @click="togglePauseTenant(tenant.id)" 
                                                class="p-1 px-2 rounded border transition-all text-[11px] inline-flex items-center gap-1"
                                                :class="tenant.status === 'suspended' 
                                                    ? 'bg-brand-500/10 border-brand-500/30 text-brand-400 hover:bg-brand-500/20' 
                                                    : 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'">
                                            <!-- Play icon (for resume) -->
                                            <svg x-show="tenant.status === 'suspended'" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                            <!-- Pause icon (for pause) -->
                                            <svg x-show="tenant.status !== 'suspended'" class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                            </svg>
                                            <span x-text="tenant.status === 'suspended' ? 'Activate' : 'Suspend'"></span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            
                            <tr x-show="filteredTenants().length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No tenant organizations found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Side: Health Checks & Activity Feed -->
        <div class="space-y-6">
            <!-- Global Autopilot Control -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white">Global Auto-Reply Control</h3>
                        <p class="text-xs text-slate-500">Pause or resume all AI bot replies globally</p>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3.5 rounded-xl border transition-colors"
                     :class="globalAiEnabled ? 'bg-brand-500/5 border-brand-500/20' : 'bg-red-500/5 border-red-500/20'">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg" :class="globalAiEnabled ? 'bg-brand-500/10 text-brand-400' : 'bg-red-500/10 text-red-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-white" x-text="globalAiEnabled ? 'Active Autopilot' : 'Global Pause Active'"></div>
                            <div class="text-[10px] text-slate-500 mt-0.5" x-text="globalAiEnabled ? 'AI replying to messages' : 'Bot responses suspended' "></div>
                        </div>
                    </div>
                    
                    <button @click="toggleGlobalAi()"
                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none"
                            :class="globalAiEnabled ? 'bg-brand-500' : 'bg-slate-700'">
                        <span class="inline-block h-3.5 w-3.5 rounded-full bg-white transition-transform"
                              :class="globalAiEnabled ? 'translate-x-4.5' : 'translate-x-1'"></span>
                    </button>
                </div>
            </div>

            <!-- Latency & Health Checks -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-white">System Engine Health</h3>
                        <p class="text-xs text-slate-500">Live service response times and latency</p>
                    </div>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- SQLite Latency -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/40 border border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                            </span>
                            <div>
                                <span class="font-medium text-slate-300">Database Connection</span>
                                <div class="text-[10px] text-slate-500" x-text="health.laravel_status === 'working' ? 'Operational' : 'Checking...'"></div>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-indigo-400" x-text="health.db_latency">0.0 ms</span>
                    </div>

                    <!-- Node.js Bot Service -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-slate-900/40 border border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 rounded border" 
                                  :class="health.node_status === 'online' ? 'bg-brand-500/10 text-brand-400 border-brand-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                                </svg>
                            </span>
                            <div>
                                <span class="font-medium text-slate-300">Express Bot Engine</span>
                                <div class="text-[10px] text-slate-500" x-text="health.node_status === 'online' ? 'Online (Uptime: ' + health.node_uptime + ')' : 'Offline/Not Responding'"></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-mono font-bold" 
                                  :class="health.node_status === 'online' ? 'text-brand-400' : 'text-red-400'" 
                                  x-text="health.node_status === 'online' ? health.node_latency : 'Offline'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Logs Feed -->
            <div class="card p-6 flex flex-col h-[400px]">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-white">Live Activity Feed</h3>
                    <p class="text-xs text-slate-500">Real-time system events logging</p>
                </div>
                
                <!-- Logs Scroll Window -->
                <div class="flex-1 overflow-y-auto pr-1 space-y-3.5">
                    <template x-for="log in logs" :key="log.id">
                        <div class="p-3 rounded-lg bg-slate-900/30 border border-slate-800/60 flex items-start gap-2.5 hover:border-slate-700/80 transition-colors">
                            <!-- Left Event Icon Indicator -->
                            <div class="p-1 rounded text-xs flex-shrink-0 mt-0.5"
                                 :class="{
                                    'bg-brand-500/10 text-brand-400 border border-brand-500/20': log.type === 'ai_reply',
                                    'bg-sky-500/10 text-sky-400 border border-sky-500/20': log.type === 'message_received',
                                    'bg-red-500/10 text-red-400 border border-red-500/20': log.type === 'error' || log.type === 'rate_limit',
                                    'bg-amber-500/10 text-amber-400 border border-amber-500/20': log.type === 'human_takeover' || log.type === 'human_flag',
                                    'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20': !['ai_reply','message_received','error','rate_limit','human_takeover','human_flag'].includes(log.type)
                                 }">
                                <!-- Message Icon -->
                                <svg x-show="log.type === 'message_received'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                                <!-- Reply Sparkles Icon -->
                                <svg x-show="log.type === 'ai_reply'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 21l-.813-5.096L3 15l5.187-.813L9 9l.813 5.187L15 15l-5.187.813zM19.071 4.929l-.707.707M19.071 19.071l-.707-.707M4.929 19.071l.707-.707M4.929 4.929l.707.707"/>
                                </svg>
                                <!-- Warning/Shield Icon -->
                                <svg x-show="['error','rate_limit','human_takeover','human_flag'].includes(log.type)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <!-- Gear / System Icon -->
                                <svg x-show="!['ai_reply','message_received','error','rate_limit','human_takeover','human_flag'].includes(log.type)" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                </svg>
                            </div>
                            <!-- Log Content -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1.5">
                                    <span class="font-bold text-white tracking-wide truncate" x-text="log.tenant_name"></span>
                                    <span class="text-[9px] text-slate-500 font-mono flex-shrink-0" x-text="log.time"></span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-0.5 line-clamp-2" x-text="log.description"></div>
                                <template x-if="log.phone">
                                    <span class="inline-block mt-1 text-[9px] font-mono px-1 rounded bg-slate-800 text-slate-500" x-text="'+' + log.phone"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="logs.length === 0" class="text-center py-12 text-slate-500 text-xs">
                        No activity logs gathered yet.
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Tenant Details Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-all"
         style="display: none;"
         x-transition>
        
        <div @click.away="showModal = false" 
             class="card w-full max-w-lg overflow-hidden shadow-2xl border border-slate-800 max-h-[90vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white" x-text="selectedTenant ? selectedTenant.name : ''"></h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="selectedTenant ? 'Workspace Slug: ' + selectedTenant.slug : ''"></p>
                </div>
                <button @click="showModal = false" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg bg-slate-800/40 hover:bg-slate-800 border border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-300">
                <!-- Status Indicators -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80">
                        <span class="text-slate-500 block uppercase tracking-wider text-[10px]">Workspace Status</span>
                        <div class="mt-1.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :class="selectedTenant && selectedTenant.status === 'active' ? 'bg-brand-500' : 'bg-red-500'"></span>
                            <span class="font-bold text-white capitalize" x-text="selectedTenant ? selectedTenant.status : ''"></span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800/80">
                        <span class="text-slate-500 block uppercase tracking-wider text-[10px]">WhatsApp Client</span>
                        <div class="mt-1.5 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full" :class="selectedTenant && selectedTenant.whatsapp_status === 'connected' ? 'bg-brand-500 pulse-dot' : 'bg-red-500'"></span>
                            <span class="font-bold text-white capitalize" x-text="selectedTenant ? selectedTenant.whatsapp_status : ''"></span>
                            <span class="text-slate-500" x-show="selectedTenant && selectedTenant.whatsapp_status === 'connected'" x-text="'(' + selectedTenant.health_score + '%)'"></span>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="space-y-3">
                    <h4 class="font-semibold text-white uppercase text-[10px] tracking-wider text-slate-400">Activity Overview</h4>
                    
                    <div class="divide-y divide-slate-800 border-y border-slate-800">
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">Database ID</span>
                            <span class="font-bold text-white" x-text="selectedTenant ? selectedTenant.id : ''"></span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">Subscription Plan</span>
                            <span class="font-bold capitalize text-white" 
                                  :class="selectedTenant && selectedTenant.plan === 'automator' ? 'text-indigo-400' : 'text-slate-300'"
                                  x-text="selectedTenant ? selectedTenant.plan : ''"></span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">Subscription Status</span>
                            <span class="font-bold capitalize" 
                                  :class="{
                                      'text-brand-400': selectedTenant && selectedTenant.subscription_status === 'active',
                                      'text-amber-400': selectedTenant && selectedTenant.subscription_status === 'trialing',
                                      'text-red-400': selectedTenant && ['expired', 'cancelled', 'past_due'].includes(selectedTenant.subscription_status)
                                  }"
                                  x-text="selectedTenant ? selectedTenant.subscription_status : ''"></span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">Onboarding Support</span>
                            <span class="font-bold" 
                                  :class="selectedTenant && selectedTenant.has_support_addon ? 'text-purple-400' : 'text-slate-500'"
                                  x-text="selectedTenant && selectedTenant.has_support_addon ? '👨‍💼 Active' : 'None'"></span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">Messages Today</span>
                            <span class="font-bold text-white" x-text="selectedTenant ? selectedTenant.messages_today : '0'"></span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">AI Auto-pilot Active</span>
                            <span class="font-bold" :class="selectedTenant && selectedTenant.ai_enabled ? 'text-brand-400' : 'text-slate-500'" x-text="selectedTenant && selectedTenant.ai_enabled ? 'ON' : 'OFF'"></span>
                        </div>
                        <div class="py-2.5 flex justify-between">
                            <span class="text-slate-400">Last activity detected</span>
                            <span class="font-bold text-slate-200" x-text="selectedTenant ? selectedTenant.last_active : ''"></span>
                        </div>
                    </div>
                </div>

                <!-- Fast Actions -->
                <div class="space-y-3.5 pt-2">
                    <h4 class="font-semibold text-white uppercase text-[10px] tracking-wider text-slate-400">Admin Actions</h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Toggle AI -->
                        <button @click="toggleTenantAi(selectedTenant.id); showModal = false;" 
                                class="p-2.5 rounded-lg border text-center font-medium transition-all text-xs flex items-center justify-center gap-2"
                                :class="selectedTenant && selectedTenant.ai_enabled 
                                    ? 'bg-slate-800 border-slate-700 hover:bg-slate-700 text-slate-300' 
                                    : 'bg-brand-500/10 border-brand-500/30 text-brand-400 hover:bg-brand-500/20'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span x-text="selectedTenant && selectedTenant.ai_enabled ? 'Pause AI' : 'Resume AI'"></span>
                        </button>

                        <!-- Toggle Pause -->
                        <button @click="togglePauseTenant(selectedTenant.id); showModal = false;" 
                                class="p-2.5 rounded-lg border text-center font-medium transition-all text-xs flex items-center justify-center gap-2"
                                :class="selectedTenant && selectedTenant.status === 'suspended' 
                                    ? 'bg-brand-500/10 border-brand-500/30 text-brand-400 hover:bg-brand-500/20' 
                                    : 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            <span x-text="selectedTenant && selectedTenant.status === 'suspended' ? 'Activate' : 'Suspend'"></span>
                        </button>

                        <!-- Login As Client -->
                        <form method="POST" :action="'/admin/impersonate/' + selectedTenant.id" class="m-0 col-span-1" x-show="selectedTenant">
                            @csrf
                            <button type="submit" 
                                    class="w-full p-2.5 rounded-lg border text-center font-medium transition-all text-xs flex items-center justify-center gap-2 bg-indigo-500/10 border-indigo-500/30 text-indigo-400 hover:bg-indigo-500/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <span>Login as Client</span>
                            </button>
                        </form>

                        <!-- Reboot WhatsApp -->
                        <button @click="reconnectTenantBot(selectedTenant.id); showModal = false;" 
                                class="p-2.5 rounded-lg border text-center font-medium transition-all text-xs flex items-center justify-center gap-2 bg-amber-500/10 border-amber-500/30 text-amber-400 hover:bg-amber-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            <span>Reboot WhatsApp</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="p-6 border-t border-slate-800 bg-slate-900/30 text-right">
                <button @click="showModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 text-white rounded-lg transition-colors text-xs font-semibold">
                    Close Overlay
                </button>
            </div>
        </div>
    </div>

    <!-- Tenant Provisioning Wizard Modal -->
    <div x-show="showProvisionWizard" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-all"
         style="display: none;"
         x-transition>
        
        <form @submit.prevent
              @click.away="if(!isProvisioning) showProvisionWizard = false" 
              class="card w-full max-w-xl overflow-hidden shadow-2xl border border-slate-800 max-h-[95vh] flex flex-col"
              autocomplete="off">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">Manual Tenant Provisioning</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Wizard step <span x-text="wizardStep"></span> of 3</p>
                </div>
                <button x-show="!isProvisioning" @click="showProvisionWizard = false" class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg bg-slate-800/40 hover:bg-slate-800 border border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="h-1 bg-slate-800 w-full relative">
                <div class="h-full bg-brand-500 transition-all duration-300"
                     :style="'width: ' + (wizardStep * 33.33) + '%'"></div>
            </div>

            <!-- Modal Body (Form Content) -->
            <div class="p-6 overflow-y-auto flex-1 space-y-4 text-xs">
                <!-- Errors -->
                <div x-show="provisionError" class="p-3.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 font-medium fade-in" style="display: none;">
                    <span x-text="provisionError"></span>
                </div>

                <!-- STEP 1: Workspace Settings -->
                <div x-show="wizardStep === 1" class="space-y-4 fade-in">
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Tenant Name</label>
                        <input type="text" x-model="provisionForm.tenant_name" placeholder="e.g. Acme Corporation" 
                               class="w-full text-xs px-3.5 py-2.5 rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Workspace Slug (Subdomain)</label>
                        <div class="flex items-stretch">
                            <input type="text" x-model="provisionForm.slug" placeholder="e.g. acme" 
                                   @input="provisionForm.slug = provisionForm.slug.toLowerCase().replace(/[^a-z0-9\-]/g, '')"
                                   class="flex-1 text-xs px-3.5 py-2.5 rounded-l-lg border-r-0" />
                            <span class="px-3.5 py-2.5 rounded-r-lg border border-slate-700 bg-slate-900 text-slate-500 flex items-center select-none font-mono">
                                .ichatup.com
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 mt-1">Only lowercase alphanumeric characters and hyphens allowed.</p>
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Account Type</label>
                        <div class="grid grid-cols-2 gap-3.5">
                            <!-- Business Card -->
                            <div @click="provisionForm.account_type = 'business'" 
                                 class="p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                                 :class="provisionForm.account_type === 'business' ? 'border-brand-500 bg-brand-500/5 text-white' : 'border-slate-800 hover:border-slate-700 text-slate-400'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="p-2 rounded-lg" :class="provisionForm.account_type === 'business' ? 'bg-brand-500/10 text-brand-400' : 'bg-slate-900 text-slate-500'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                                        </svg>
                                    </span>
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center" 
                                         :class="provisionForm.account_type === 'business' ? 'border-brand-500 bg-brand-500' : 'border-slate-700'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-950" x-show="provisionForm.account_type === 'business'"></div>
                                    </div>
                                </div>
                                <div>
                                    <span class="font-bold block text-sm">Business Account</span>
                                    <span class="text-[10px] text-slate-500 mt-1 block">For companies, shops, and business automation workflows.</span>
                                </div>
                            </div>
                            
                            <!-- Personal Card -->
                            <div @click="provisionForm.account_type = 'personal'" 
                                 class="p-4 rounded-xl border cursor-pointer transition-all flex flex-col justify-between"
                                 :class="provisionForm.account_type === 'personal' ? 'border-brand-500 bg-brand-500/5 text-white' : 'border-slate-800 hover:border-slate-700 text-slate-400'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="p-2 rounded-lg" :class="provisionForm.account_type === 'personal' ? 'bg-brand-500/10 text-brand-400' : 'bg-slate-900 text-slate-500'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                    </span>
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center" 
                                         :class="provisionForm.account_type === 'personal' ? 'border-brand-500 bg-brand-500' : 'border-slate-700'">
                                        <div class="w-1.5 h-1.5 rounded-full bg-slate-950" x-show="provisionForm.account_type === 'personal'"></div>
                                    </div>
                                </div>
                                <div>
                                    <span class="font-bold block text-sm">Personal Brand</span>
                                    <span class="text-[10px] text-slate-500 mt-1 block">For influencers, coaches, and individual creators.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Owner/Administrator Settings -->
                <div x-show="wizardStep === 2" class="space-y-4 fade-in">
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Administrator Name</label>
                        <input type="text" x-model="provisionForm.user_name" placeholder="e.g. John Doe" 
                               class="w-full text-xs px-3.5 py-2.5 rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-slate-400 mb-1.5 font-medium">Email Address</label>
                        <input type="email" x-model="provisionForm.email" placeholder="e.g. admin@acme.com" 
                               autocomplete="off"
                               class="w-full text-xs px-3.5 py-2.5 rounded-lg" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-400 mb-1.5 font-medium">Password</label>
                            <input type="password" x-model="provisionForm.password" placeholder="Min. 8 characters" 
                                   autocomplete="new-password"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-lg" />
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1.5 font-medium">Confirm Password</label>
                            <input type="password" x-model="provisionForm.password_confirmation" placeholder="Repeat password" 
                                   autocomplete="new-password"
                                   class="w-full text-xs px-3.5 py-2.5 rounded-lg" />
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Setup Options & Onboarding Defaults -->
                <div x-show="wizardStep === 3" class="space-y-4 fade-in">
                    <!-- Pre-complete onboarding card toggle -->
                    <div @click="provisionForm.pre_complete_onboarding = !provisionForm.pre_complete_onboarding" 
                         class="p-4 rounded-xl border cursor-pointer transition-all flex items-start gap-3.5"
                         :class="provisionForm.pre_complete_onboarding ? 'border-brand-500/40 bg-brand-500/5' : 'border-slate-800 bg-slate-900/10 hover:border-slate-700'">
                        <div class="mt-0.5">
                            <input type="checkbox" x-model="provisionForm.pre_complete_onboarding" class="accent-brand-500 rounded cursor-pointer" @click.stop />
                        </div>
                        <div>
                            <span class="font-bold text-white block">Pre-complete Onboarding & Seed Defaults</span>
                            <span class="text-[10px] text-slate-400 mt-1 block leading-relaxed">
                                Auto-generate ready-to-use chatbot templates, fill default system prompts, enable the autopilot engine, and bypass the onboarding wizard so the client starts with a ready-to-run setup.
                            </span>
                        </div>
                    </div>

                    <!-- Review Panel -->
                    <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/40 space-y-3.5">
                        <span class="font-bold text-white uppercase text-[10px] tracking-wider text-slate-400 block">Review Provision Details</span>
                        
                        <div class="grid grid-cols-2 gap-x-6 gap-y-2 border-t border-slate-800 pt-3 text-[11px]">
                            <div class="space-y-1">
                                <span class="text-slate-500 block">Tenant Name</span>
                                <span class="font-bold text-slate-300" x-text="provisionForm.tenant_name || 'N/A'"></span>
                            </div>
                            <div class="space-y-1">
                                <span class="text-slate-500 block">Workspace Slug</span>
                                <span class="font-bold text-slate-300" x-text="provisionForm.slug ? provisionForm.slug + '.ichatup.com' : 'N/A'"></span>
                            </div>
                            <div class="space-y-1 mt-2">
                                <span class="text-slate-500 block">Account Type</span>
                                <span class="font-bold text-slate-300 capitalize" x-text="provisionForm.account_type"></span>
                            </div>
                            <div class="space-y-1 mt-2">
                                <span class="text-slate-500 block">Admin Username</span>
                                <span class="font-bold text-slate-300" x-text="provisionForm.user_name || 'N/A'"></span>
                            </div>
                            <div class="col-span-2 space-y-1 mt-2">
                                <span class="text-slate-500 block">Admin Email</span>
                                <span class="font-bold text-slate-300" x-text="provisionForm.email || 'N/A'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-6 border-t border-slate-800 bg-slate-900/30 flex items-center justify-between">
                <div>
                    <!-- Back Button -->
                    <button x-show="wizardStep > 1 && !isProvisioning" 
                            @click="wizardStep--" 
                            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 text-white rounded-lg transition-colors text-xs font-semibold">
                        Back
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <button x-show="!isProvisioning" 
                            @click="showProvisionWizard = false" 
                            class="px-4 py-2 bg-slate-950 text-slate-400 hover:text-white rounded-lg transition-colors text-xs font-semibold">
                        Cancel
                    </button>
                    
                    <!-- Next/Submit Button -->
                    <button @click="nextWizardStep()" 
                            :disabled="isProvisioning"
                            class="px-4 py-2 bg-brand-500 hover:bg-brand-400 text-slate-950 font-bold rounded-lg transition-colors text-xs flex items-center gap-1.5 disabled:opacity-50">
                        <span x-show="!isProvisioning" x-text="wizardStep === 3 ? 'Provision Workspace' : 'Continue'"></span>
                        <span x-show="isProvisioning" class="flex items-center gap-1.5">
                            <!-- Spinner Icon -->
                            <svg class="animate-spin -ml-1 mr-1.5 h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Creating Workspace...</span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Action Toast Notification -->
    <div x-show="toast.show" 
         x-transition
         class="fixed bottom-5 right-5 z-50 p-4 rounded-xl border text-xs font-semibold shadow-2xl flex items-center gap-2.5 max-w-sm fade-in"
         :class="{
            'bg-brand-500/10 border-brand-500/30 text-brand-400': toast.type === 'success',
            'bg-red-500/10 border-red-500/30 text-red-400': toast.type === 'error',
            'bg-amber-500/10 border-amber-500/30 text-amber-400': toast.type === 'warning'
         }"
         style="display: none;">
        <span x-text="toast.type === 'success' ? '✓' : '✕'" class="text-sm font-bold"></span>
        <span x-text="toast.message"></span>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function adminDashboard() {
        return {
            stats: {
                total_tenants: 0,
                active_connections: 0,
                messages_today: 0,
                ai_replies_today: 0,
                chart: []
            },
            tenants: [],
            logs: [],
            health: {
                laravel_status: 'checking',
                db_latency: '...',
                node_status: 'checking',
                node_uptime: '...',
                node_latency: '...'
            },
            searchQuery: '',
            selectedTenant: null,
            showModal: false,
            globalAiEnabled: {{ $globalAiEnabled ? 'true' : 'false' }},
            alerts: [],
            chartInstance: null,

            // Provisioning Wizard & Action toast states
            showProvisionWizard: false,
            wizardStep: 1,
            provisionForm: {
                tenant_name: '',
                slug: '',
                account_type: 'business',
                user_name: '',
                email: '',
                password: '',
                password_confirmation: '',
                pre_complete_onboarding: true
            },
            isProvisioning: false,
            provisionError: '',
            toast: {
                show: false,
                message: '',
                type: 'success'
            },

            init() {
                this.fetchStats();
                this.fetchTenants();
                this.fetchActivity();
                this.fetchHealth();

                // Periodic updates
                setInterval(() => this.fetchStats(), 10000);
                setInterval(() => this.fetchTenants(), 10000);
                setInterval(() => this.fetchActivity(), 5000);
                setInterval(() => this.fetchHealth(), 10000);
            },

            async fetchStats() {
                try {
                    let res = await fetch('{{ route('admin.stats') }}');
                    let data = await res.json();
                    if (data.success) {
                        this.stats = data.stats;
                        this.updateChart(data.stats.chart);
                        this.generateAlerts();
                    }
                } catch (e) {
                    console.error("Failed fetching admin stats:", e);
                }
            },

            async fetchTenants() {
                try {
                    let res = await fetch('{{ route('admin.tenants.list') }}');
                    let data = await res.json();
                    if (data.success) {
                        this.tenants = data.tenants;
                        // Keep selectedTenant reactive if details modal is open
                        if (this.selectedTenant) {
                            let match = this.tenants.find(t => t.id === this.selectedTenant.id);
                            if (match) this.selectedTenant = match;
                        }
                        this.generateAlerts();
                    }
                } catch (e) {
                    console.error("Failed fetching tenants list:", e);
                }
            },

            async fetchActivity() {
                try {
                    let res = await fetch('{{ route('admin.activity') }}');
                    let data = await res.json();
                    if (data.success) {
                        this.logs = data.logs;
                    }
                } catch (e) {
                    console.error("Failed fetching activity feed:", e);
                }
            },

            async fetchHealth() {
                try {
                    let res = await fetch('{{ route('admin.system-health') }}');
                    let data = await res.json();
                    if (data.success) {
                        this.health = data.health;
                        this.globalAiEnabled = data.health.global_ai_enabled;
                        this.generateAlerts();
                    }
                } catch (e) {
                    console.error("Failed fetching latency checks:", e);
                }
            },

            async toggleGlobalAi() {
                try {
                    let res = await fetch('{{ route('admin.toggle-ai') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.globalAiEnabled = data.global_ai_enabled;
                        this.fetchHealth();
                    }
                } catch (e) {
                    console.error("Failed toggling global AI status:", e);
                }
            },

            async toggleTenantAi(tenantId) {
                try {
                    let res = await fetch('{{ route('admin.toggle-ai') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ tenant_id: tenantId })
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.fetchTenants();
                    }
                } catch (e) {
                    console.error("Failed toggling tenant AI:", e);
                }
            },

            async togglePauseTenant(tenantId) {
                try {
                    let res = await fetch('{{ route('admin.pause-tenant') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ tenant_id: tenantId })
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.fetchTenants();
                    }
                } catch (e) {
                    console.error("Failed suspending tenant:", e);
                }
            },

            viewTenantDetails(tenant) {
                this.selectedTenant = tenant;
                this.showModal = true;
            },

            filteredTenants() {
                if (!this.searchQuery) return this.tenants;
                const query = this.searchQuery.toLowerCase();
                return this.tenants.filter(t => 
                    t.name.toLowerCase().includes(query) || 
                    t.slug.toLowerCase().includes(query)
                );
            },

            showToast(message, type = 'success') {
                this.toast.message = message;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => {
                    this.toast.show = false;
                }, 4000);
            },

            openProvisionWizard() {
                this.provisionForm = {
                    tenant_name: '',
                    slug: '',
                    account_type: 'business',
                    user_name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    pre_complete_onboarding: true
                };
                this.provisionError = '';
                this.wizardStep = 1;
                this.showProvisionWizard = true;
            },

            nextWizardStep() {
                if (this.wizardStep < 3) {
                    if (this.wizardStep === 1) {
                        if (!this.provisionForm.tenant_name.trim()) {
                            this.provisionError = 'Tenant name is required.';
                            return;
                        }
                        if (!this.provisionForm.slug.trim()) {
                            this.provisionError = 'Slug is required.';
                            return;
                        }
                        if (!/^[a-z0-9\-]+$/i.test(this.provisionForm.slug)) {
                            this.provisionError = 'Slug can only contain letters, numbers, and hyphens.';
                            return;
                        }
                    } else if (this.wizardStep === 2) {
                        if (!this.provisionForm.user_name.trim()) {
                            this.provisionError = 'User name is required.';
                            return;
                        }
                        if (!this.provisionForm.email.trim()) {
                            this.provisionError = 'Email address is required.';
                            return;
                        }
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.provisionForm.email)) {
                            this.provisionError = 'Please enter a valid email address.';
                            return;
                        }
                        if (this.provisionForm.password.length < 8) {
                            this.provisionError = 'Password must be at least 8 characters long.';
                            return;
                        }
                        if (this.provisionForm.password !== this.provisionForm.password_confirmation) {
                            this.provisionError = 'Passwords do not match.';
                            return;
                        }
                    }
                    this.provisionError = '';
                    this.wizardStep++;
                } else {
                    this.submitProvisionForm();
                }
            },

            async submitProvisionForm() {
                this.isProvisioning = true;
                this.provisionError = '';
                try {
                    let res = await fetch('{{ route('admin.tenants.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(this.provisionForm)
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.showProvisionWizard = false;
                        this.fetchTenants();
                        this.fetchStats();
                        this.showToast(data.message, 'success');
                    } else {
                        this.provisionError = data.message || 'Provisioning failed.';
                    }
                } catch (e) {
                    console.error("Failed submitting provisioning form:", e);
                    this.provisionError = 'An error occurred during provisioning.';
                } finally {
                    this.isProvisioning = false;
                }
            },

            async reconnectTenantBot(tenantId) {
                if (!confirm('Are you sure you want to reboot this tenant\'s WhatsApp connection? This will restart their session on the bot server.')) {
                    return;
                }
                try {
                    let res = await fetch('{{ route('admin.reconnect-tenant') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ tenant_id: tenantId })
                    });
                    let data = await res.json();
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.fetchTenants();
                    } else {
                        this.showToast(data.message || 'Failed to reconnect.', 'error');
                    }
                } catch (e) {
                    console.error("Failed rebooting socket connection:", e);
                    this.showToast('An error occurred while attempting to reconnect.', 'error');
                }
            },

            generateAlerts() {
                let alertList = [];
                if (!this.globalAiEnabled) {
                    alertList.push({
                        type: 'warning',
                        message: 'Global AI is turned OFF. AI replies are disabled across all tenants.'
                    });
                }
                if (this.health.node_status === 'offline') {
                    alertList.push({
                        type: 'danger',
                        message: 'Node.js bot server is offline! WhatsApp connections cannot be maintained.'
                    });
                }
                // Check if any tenant has disconnected WhatsApp status
                let disconnectedCount = this.tenants.filter(t => t.whatsapp_status === 'disconnected').length;
                if (disconnectedCount > 0) {
                    alertList.push({
                        type: 'info',
                        message: `${disconnectedCount} tenant(s) have disconnected WhatsApp clients.`
                    });
                }
                // Alert if messages spike: messages today > 1000
                if (this.stats.messages_today > 1000) {
                    alertList.push({
                        type: 'warning',
                        message: 'High message volume spike detected today!'
                    });
                }
                this.alerts = alertList;
            },

            updateChart(chartData) {
                if (!chartData || chartData.length === 0) return;
                const ctx = document.getElementById('messagesChart').getContext('2d');
                const labels = chartData.map(c => c.label);
                const counts = chartData.map(c => c.count);

                if (this.chartInstance) {
                    this.chartInstance.data.labels = labels;
                    this.chartInstance.data.datasets[0].data = counts;
                    this.chartInstance.update();
                } else {
                    this.chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Messages',
                                data: counts,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.1)',
                                fill: true,
                                tension: 0.3,
                                borderWidth: 2,
                                pointRadius: 0,
                                pointHoverRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false,
                                    backgroundColor: '#0d1627',
                                    titleColor: '#94a3b8',
                                    bodyColor: '#f1f5f9',
                                    borderColor: 'rgba(255,255,255,0.08)',
                                    borderWidth: 1
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { color: '#64748b', font: { size: 9 } }
                                },
                                y: {
                                    border: { dash: [4, 4] },
                                    grid: { color: 'rgba(255,255,255,0.05)' },
                                    ticks: { color: '#64748b', font: { size: 9 }, precision: 0 }
                                }
                            }
                        }
                    });
                }
            }
        }
    }
</script>
@endpush
