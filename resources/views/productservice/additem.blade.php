@php
$plan = \App\Models\Utility::getChatGPTSettings();
@endphp

@extends('layouts.admin')

@section('page-title')
{{ __('Add Item') }}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('productservice.index') }}">{{ __('Products & Services') }}</a></li>
<li class="breadcrumb-item">{{ __('Add Item') }}</li>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        // Script to toggle the quantity field based on product/service type
        $(document).on('click', 'input[name="type"]', function() {
            var type = $(this).val();
            if (type === 'Product') {
                // Show quantity field and make it required
                $('.quantity').removeClass('d-none').addClass('d-block');
                $('input[name="quantity"]').prop('required', true);
            } else if (type === 'Service') {
                // Hide quantity field, clear its value, and remove required attribute
                $('.quantity').addClass('d-none').removeClass('d-block');
                $('input[name="quantity"]').val('').prop('required', false);
            }
        });

        // ✨ FINAL SCRIPT: Handles AJAX creation for BOTH Location and Category ✨
        $(document).on('submit', '#commonModal form', function(e) {
            var form = $(this);
            var formAction = form.attr('action');

            // Check if the form is for creating a product location OR a product category
            if (formAction.includes('productlocation') || formAction.includes('product-category')) {

                // Exclude any other forms within the modal that might be caught by the selector
                if (formAction.includes('getaccount')) {
                    return;
                }

                e.preventDefault(); // Stop the default page reload
                var url = formAction;
                var formData = form.serialize();

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // If a new category was created
                            if (response.category) {
                                var newCategory = response.category;
                                var newOption = new Option(newCategory.name, newCategory.id, true, true);
                                $('#category_id').append(newOption).trigger('change');
                            }
                            // If a new location was created
                            if (response.location) {
                                var newLocation = response.location;
                                var newOption = new Option(newLocation.name, newLocation.id, true, true);
                                $('#product_location').append(newOption).trigger('change');
                            }

                            $('#commonModal').modal('hide');
                            // You can add a success notification here, e.g.,
                            // show_toastr('Success', response.message, 'success');
                        }
                    },
                    error: function(xhr) {
                        // You can add error handling here to show validation messages
                        console.error("AJAX Error:", xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                {{ Form::open(['url' => 'productservice', 'class' => 'needs-validation ', 'novalidate']) }}

                <div class="row">
                    @if (isset($plan->enable_chatgpt) && $plan->enable_chatgpt == 'on')
                    <div class="col-12 text-end">
                        <a href="#" data-size="md" data-ajax-popup-over="true"
                            data-url="{{ route('generate', ['product & service']) }}" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="{{ __('Generate') }}" data-title="{{ __('Generate content with AI') }}"
                            class="btn btn-primary btn-sm">
                            <i class="fas fa-robot"></i>
                            {{ __('Generate with AI') }}
                        </a>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block form-label">{{ __('Type') }}</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input type" id="customRadio5" name="type" value="Product" checked="checked">
                                            <label class="custom-control-label form-label" for="customRadio5">{{ __('Product') }}</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-check-inline">
                                            <input type="radio" class="form-check-input type" id="customRadio6" name="type" value="Service">
                                            <label class="custom-control-label form-label" for="customRadio6">{{ __('Service') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required />
                                {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('purchase_price', __('Purchase Price'), ['class' => 'form-label']) }}<x-required />
                                {{ Form::number('purchase_price', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter Purchase Price')]) }}
                            </div>
                        </div>
                        <div class="form-group col-md-3">
                            {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}<x-required />

                            {{-- ✨ 1. ID ADDED HERE ✨ --}}
                            {{ Form::select('category_id', $category, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'category_id']) }}

                            <div class="text-xs mt-1">
                                {{ __('Create category here.') }}
                                <a href="#" data-size="lg" data-url="{{ route('product-category.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Category')}}">
                                    <b>{{ __('Create category') }}</b>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3 quantity d-block">
                            {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}<x-required />
                            {{ Form::text('quantity', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Quantity')]) }}
                        </div>
                        <div class="form-group col-md-3">
                            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}<x-required />
                            {{ Form::select('unit_id', $unit, null, ['class' => 'form-control select', 'required' => 'required']) }}
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('sku', __('SKU'), ['class' => 'form-label']) }}
                                {{ Form::text('sku', '', ['class' => 'form-control', 'placeholder' => __('Enter SKU')]) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('sale_price', __('Sale Price'), ['class' => 'form-label']) }}
                                {{ Form::number('sale_price', '', ['class' => 'form-control', 'step' => '0.01', 'placeholder' => __('Enter Sale Price')]) }}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Description')]) !!}
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            {{ Form::label('productlocation_id', __('Product Location'), ['class' => 'form-label']) }}<x-required />

                            {{-- ✨ 2. ID ADDED HERE ✨ --}}
                            {{ Form::select('productlocation_id', $productlocations, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'product_location']) }}

                            <div class="text-xs mt-1">
                                {{ __('Create Product Location here.') }}
                                <a href="#" data-size="lg" data-url="{{ route('productservice.addproductlocation') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Product Location')}}">
                                    <b>{{ __('Create Product Location') }}</b>
                                </a>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('sale_chartaccount_id', __('Income Account'), ['class' => 'form-label']) }}
                            <select name="sale_chartaccount_id" class="form-control" required="required">
                                @foreach ($incomeChartAccounts as $key => $chartAccount)
                                <option value="{{ $key }}" class="subAccount" {{ isset($defaultIncomeAccount) && $key == $defaultIncomeAccount ? 'selected' : '' }}>
                                    {{ $chartAccount }}
                                </option>
                                @foreach ($incomeSubAccounts as $subAccount)
                                @if ($key == $subAccount['account'])
                                <option value="{{ $subAccount['id'] }}" class="ms-5" {{ isset($defaultIncomeAccount) && $subAccount['id'] == $defaultIncomeAccount ? 'selected' : '' }}>
                                    &nbsp; &nbsp;&nbsp; {{ $subAccount['name'] }}
                                </option>
                                @endif
                                @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('expense_chartaccount_id', __('Expense Account'), ['class' => 'form-label']) }}
                            <select name="expense_chartaccount_id" class="form-control" required="required">
                                @foreach ($expenseChartAccounts as $key => $chartAccount)
                                <option value="{{ $key }}" class="subAccount" {{ isset($defaultExpenseAccount) && $key == $defaultExpenseAccount ? 'selected' : '' }}>
                                    {{ $chartAccount }}
                                </option>
                                @foreach ($expenseSubAccounts as $subAccount)
                                @if ($key == $subAccount['account'])
                                <option value="{{ $subAccount['id'] }}" class="ms-5" {{ isset($defaultExpenseAccount) && $subAccount['id'] == $defaultExpenseAccount ? 'selected' : '' }}>
                                    &nbsp; &nbsp;&nbsp; {{ $subAccount['name'] }}
                                </option>
                                @endif
                                @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if (!$customFields->isEmpty())
                    <div class="col-md-6">
                        @include('customFields.formBuilder')
                    </div>
                    @endif

                </div>
            </div>

            <div class="card-footer text-end">
                <a href="{{ route('productservice.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
            </div>

            {{ Form::close() }}

        </div>
    </div>
</div>
@endsection