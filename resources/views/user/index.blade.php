@extends('layouts.admin')
@php
$profile = asset(Storage::url('uploads/avatar/'));
@endphp
@section('page-title')
@if (\Auth::user()->type == 'super admin')
{{ __('Manage Companies') }}
@else
{{ __('Manage User') }}
@endif
@endsection
@section('breadcrumb')
<li class="breadcrumb-item">
    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
</li>
@if (\Auth::user()->type == 'super admin')
<li class="breadcrumb-item">{{ __('Companies') }}</li>
@else
<li class="breadcrumb-item">{{ __('User') }}</li>
@endif
@endsection
@push('script-page')
@endpush

@section('action-btn')
<div class="d-flex">
    <a href="#" data-size="md" data-url="{{ route('users.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"
        title="{{\Auth::user()->type == 'super admin' ?  __('New Company')  : __('New User') }}" class="btn btn-sm btn-primary me-2">
        <i class="ti ti-plus"></i>
    </a>
    @if (\Auth::user()->type == 'company')
    <a href="{{ route('userlogs.index') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
        title="{{ __('User Log') }}">
        <i class="ti ti-user-check"></i>
    </a>
    @endif
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-xxl-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('COMPANY NAME') }}</th>
                                @if (\Auth::user()->type == 'super admin')
                                <th>{{ __('PLAN') }}</th>
                                <th>{{ __('USERS / CUSTOMERS / VENDORS') }}</th>
                                @else
                                <th>{{ __('ROLE') }}</th>
                                @endif
                                <th>{{ __('LAST LOGIN') }}</th>
                                <th class="text-end" width="250px">{{ __('ACTION') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img width="40" height="40" src="{{ !empty($user->avatar) ? asset(Storage::url('uploads/avatar/' . $user->avatar)) : asset(Storage::url('uploads/avatar/avatar.png')) }}" class="avatar rounded-circle me-3">
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <small class="text-muted">{{ $user->email }}</small></br>
                                            <small class="text-muted">{{ $user->country_code }} {{ $user->mobile_number }}</small>
                                        </div>
                                    </div>
                                </td>
                                @if (\Auth::user()->type == 'super admin')
                                <td>{{ !empty($user->currentPlan) ? $user->currentPlan->name : '' }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <small>{{__('Users')}}: {{ $user->totalCompanyUser($user->id) }}</small>
                                        <small>{{__('Customers')}}: {{ $user->totalCompanyCustomer($user->id) }}</small>
                                        <small>{{__('Vendors')}}: {{ $user->totalCompanyVender($user->id) }}</small>
                                    </div>
                                </td>
                                @else
                                <td>
                                    <div class="badge bg-primary p-2 px-3 rounded">
                                        {{ ucfirst($user->type) }}
                                    </div>
                                </td>
                                @endif
                                <td>
                                    {{ !empty($user->last_login_at) ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : '-' }}
                                </td>
                                <td class="text-end">
                                    @if ($user->is_active == 1 && $user->is_disable == 1)
                                    <div class="d-flex justify-content-end">
                                        @can('edit user')
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-url="{{ route('users.edit', $user->id) }}" data-ajax-popup="true" data-size="md" data-bs-original-title="{{\Auth::user()->type == 'super admin' ?  __('Edit Company')  : __('Edit User') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan

                                        @if (Auth::user()->type == 'super admin')
                                        <div class="action-btn bg-success ms-2">
                                            <a href="{{ route('login.with.company', $user->id) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Login As Company') }}">
                                                <i class="ti ti-replace text-white"></i>
                                            </a>
                                        </div>
                                        @endif

                                        <div class="action-btn bg-warning ms-2">
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{__('Reset Password')}}" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}" data-ajax-popup="true" data-size="md">
                                                <i class="ti ti-key text-white"></i>
                                            </a>
                                        </div>

                                        @if ($user->is_enable_login == 1)
                                        <div class="action-btn bg-danger ms-2">
                                            <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Login Disable') }}">
                                                <i class="ti ti-lock-off text-white"></i>
                                            </a>
                                        </div>
                                        @elseif ($user->is_enable_login == 0 && $user->password == null)
                                        <div class="action-btn bg-success ms-2">
                                            <a href="#" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}" data-ajax-popup="true" data-size="md" class="mx-3 btn btn-sm d-inline-flex align-items-center login_enable" data-bs-toggle="tooltip" title="{{ __('Login Enable') }}" data-title="{{ __('New Password') }}">
                                                <i class="ti ti-lock text-white"></i>
                                            </a>
                                        </div>
                                        @else
                                        <div class="action-btn bg-success ms-2">
                                            <a href="{{ route('users.login', \Crypt::encrypt($user->id)) }}" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Login Enable') }}">
                                                <i class="ti ti-lock text-white"></i>
                                            </a>
                                        </div>
                                        @endif

                                        @can('delete user')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'id' => 'delete-form-' . $user->id]) !!}
                                            <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                        @endcan
                                    </div>
                                    @else
                                    <a href="#" class="action-item text-lg"><i class="ti ti-lock"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection