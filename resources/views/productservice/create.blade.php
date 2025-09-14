@php
$plan = \App\Models\Utility::getChatGPTSettings();
@endphp
{{ Form::open(['url' => 'productservice', 'class'=>'needs-validation','novalidate']) }}
<div class="modal-body">
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

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<x-required />
                {{ Form::text('name', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Name')]) }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sku', __('SKU'), ['class' => 'form-label']) }}
                {{ Form::text('sku', '', ['class' => 'form-control', 'placeholder' => __('Enter SKU')]) }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('sale_price', __('Sale Price'), ['class' => 'form-label']) }}<x-required />
                {{ Form::number('sale_price', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter Sale Price')]) }}
            </div>
        </div>

        <!-- <div class="form-group col-md-6">
                        {{ Form::label('sale_chartaccount_id', __('Income Account'), ['class' => 'form-label']) }}<x-required />
                        {{ Form::select('sale_chartaccount_id', $incomeChartAccounts, null, ['class' => 'form-control select', 'required' => 'required']) }}
                        <select name="sale_chartaccount_id" class="form-control" required="required">
                            @foreach ($incomeChartAccounts as $key => $chartAccount)
                            <option value="{{ $key }}" {{ $key}} class="subAccount">{{ $chartAccount }}</option>
                            @foreach ($incomeSubAccounts as $subAccount)
                            @if ($key == $subAccount['account'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp; &nbsp;&nbsp; {{ $subAccount['name'] }}</option>
                            @endif
                            @endforeach
                            @endforeach
                        </select>
                    </div> -->

        <div class="form-group col-md-6">
            {{ Form::label('sale_chartaccount_id', __('Income Account'), ['class' => 'form-label']) }}<x-required />


            <select name="sale_chartaccount_id" class="form-control" required="required">
                @foreach ($incomeChartAccounts as $key => $chartAccount)
                <option
                    value="{{ $key }}"
                    class="subAccount"
                    {{-- This is the correct way to set a default value --}}
                    {{ isset($defaultIncomeAccount) && $key == $defaultIncomeAccount ? 'selected' : '' }}>
                    {{ $chartAccount }}
                </option>

                @foreach ($incomeSubAccounts as $subAccount)
                @if ($key == $subAccount['account'])
                <option
                    value="{{ $subAccount['id'] }}"
                    class="ms-5"
                    {{-- Also check the sub-account for the default value --}}
                    {{ isset($defaultIncomeAccount) && $subAccount['id'] == $defaultIncomeAccount ? 'selected' : '' }}>
                    &nbsp; &nbsp;&nbsp; {{ $subAccount['name'] }}
                </option>
                @endif
                @endforeach
                @endforeach
            </select>
        </div>

        {{-- TEMPORARY DEBUG CODE TO FIND IDs --}}
        <!-- <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin-bottom: 20px;">
                        <p><strong>Available Income Accounts (ID => Name):</strong></p>
                        <pre>{{ print_r($incomeChartAccounts->all()) }}</pre>
                    </div> -->

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('purchase_price', __('Purchase Price'), ['class' => 'form-label']) }}<x-required />
                {{ Form::number('purchase_price', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'placeholder' => __('Enter Purchase Price')]) }}
            </div>
        </div>

        <!-- <div class="form-group col-md-6">
                        {{ Form::label('expense_chartaccount_id', __('Expense Account'), ['class' => 'form-label']) }}<x-required />
                        <select name="expense_chartaccount_id" class="form-control" required="required">
                            @foreach ($expenseChartAccounts as $key => $chartAccount)
                            <option value="{{ $key }}" class="subAccount">{{ $chartAccount }}</option>
                            @foreach ($expenseSubAccounts as $subAccount)
                            @if ($key == $subAccount['account'])
                            <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp; &nbsp;&nbsp; {{ $subAccount['name'] }}</option>
                            @endif
                            @endforeach
                            @endforeach
                        </select>
                    </div> -->

        <div class="form-group col-md-6">
            {{ Form::label('expense_chartaccount_id', __('Expense Account'), ['class' => 'form-label']) }}<x-required />

            <select name="expense_chartaccount_id" class="form-control" required="required">
                @foreach ($expenseChartAccounts as $key => $chartAccount)
                <option
                    value="{{ $key }}"
                    class="subAccount"
                    {{-- This check sets the default for parent accounts --}}
                    {{ isset($defaultExpenseAccount) && $key == $defaultExpenseAccount ? 'selected' : '' }}>
                    {{ $chartAccount }}
                </option>

                @foreach ($expenseSubAccounts as $subAccount)
                @if ($key == $subAccount['account'])
                <option
                    value="{{ $subAccount['id'] }}"
                    class="ms-5"
                    {{-- This check sets the default for sub-accounts --}}
                    {{ isset($defaultExpenseAccount) && $subAccount['id'] == $defaultExpenseAccount ? 'selected' : '' }}>
                    &nbsp; &nbsp;&nbsp; {{ $subAccount['name'] }}
                </option>
                @endif
                @endforeach
                @endforeach
            </select>
        </div>

        {{-- TEMPORARY DEBUG CODE TO FIND IDs --}}
        <!-- <div style="background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin-bottom: 20px;">
                        <p><strong>Available Income Accounts (ID => Name):</strong></p>
                        <pre>{{ print_r($expenseChartAccounts->all()) }}</pre>
                    </div> -->

        <div class="form-group col-md-6">
            {{ Form::label('tax_id', __('Tax'), ['class' => 'form-label']) }}<x-required />
            {{ Form::select('tax_id[]', $tax, null, ['class' => 'form-control select2', 'id' => 'choices-multiple1', 'multiple', 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}<x-required />
            {{ Form::select('category_id', $category, null, ['class' => 'form-control select', 'required' => 'required']) }}
            <div class=" text-xs  mt-1">
                {{ __('Create category here.') }}
                <!-- <a href="{{ route('product-category.index') }}"><b>{{ __('Create category') }}</b></a> -->

                <a href="#" data-size="lg" data-url="{{ route('product-category.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Product & Service')}}">
                    <b>{{ __('Create category') }}</b>
                </a>

            </div>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}<x-required />
            {{ Form::select('unit_id', $unit, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6 quantity d-block">
            {{ Form::label('quantity', __('Quantity'), ['class' => 'form-label']) }}<x-required />
            {{ Form::text('quantity', null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Enter Quantity')]) }}
        </div>

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

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => '2', 'placeholder' => __('Enter Description')]) !!}
        </div>

        @if (!$customFields->isEmpty())
        <div class="col-md-6">
            @include('customFields.formBuilder')
        </div>
        @endif

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).on('click', 'input[name="type"]', function() {
        var type = $(this).val(); // Get the selected type value
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
</script>