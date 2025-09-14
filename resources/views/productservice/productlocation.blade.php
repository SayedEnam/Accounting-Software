@extends('layouts.admin')
@section('page-title')
{{ __('Manage Product Location') }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Product Location') }}</li>
@endsection

@section('action-btn')
<div class="d-flex">

    <a href="#" data-size="lg" data-url="{{ route('productservice.addproductlocation') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Product Location')}}" class="btn btn-sm btn-primary">
        <i class="ti ti-plus"></i>
    </a>

</div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th> {{ __('Location') }}</th>
                                <th> {{ __('Type') }}</th>
                                <th width="10%"> {{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productlocations as $productlocation)
                            <tr>
                                <td class="font-style">{{ $productlocation->name }}</td>
                                <td class="font-style">
                                    {{ $productlocation->type ? $productlocation->type : '-' }}
                                </td>
                                <td class="Action">
                                    <span>
                                        @can('edit constant category')
                                        <div class="action-btn bg-info ms-2">
                                            <a href="#" class="mx-3 btn btn-sm align-items-center"
                                                data-url="{{ route('productlocation.edit', $productlocation->id) }}"
                                                data-ajax-popup="true" data-title="{{ __('Edit Product Location') }}"
                                                data-bs-toggle="tooltip" title="{{ __('Edit') }}"
                                                data-original-title="{{ __('Edit') }}">
                                                <i class="ti ti-pencil text-white"></i>
                                            </a>
                                        </div>
                                        @endcan
                                        @can('delete constant category')
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['productlocation.destroy', $productlocation->id], 'id' => 'delete-form-' . $productlocation->id]) !!}
                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                data-bs-toggle="tooltip" title="{{ __('Delete') }}"
                                                data-original-title="{{ __('Delete') }}"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $productlocation->id }}').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Form::close() !!}
                                        </div>
                                        @endcan
                                    </span>
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