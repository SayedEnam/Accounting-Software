@extends('layouts.admin')
@section('page-title')
{{ __('Invoice Create') }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
@endsection
@push('script-page')
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>

<script>
    $(document).ready(function() {
        // Initialize the repeater
        if ($(".repeater").length) {
            var $repeater = $('.repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function() {
                    $(this).slideDown();
                    if (typeof JsSearchBox !== 'undefined') {
                        JsSearchBox();
                    }
                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();
                        updateGrandTotals();
                    }
                },
                isFirstItemUndeletable: true
            });
        }

        // AJAX for customer details
        $(document).on('change', '#customer', function() {
            $('#customer_detail').removeClass('d-none').addClass('d-block');
            $('#customer-box').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': id
                },
                cache: false,
                success: function(data) {
                    if (data != '') {
                        $('#customer_detail').html(data);
                    } else {
                        $('#customer-box').removeClass('d-none');
                        $('#customer_detail').removeClass('d-block').addClass('d-none');
                    }
                },
            });
        });

        $(document).on('click', '#remove', function() {
            $('#customer-box').removeClass('d-none');
            $('#customer_detail').removeClass('d-block').addClass('d-none');
        });

        // AJAX for product details
        $(document).on('change', '.item', function() {
            var iteams_id = $(this).val();
            var url = $(this).data('url');
            var el = $(this);
            if (!iteams_id) {
                var row = el.closest('[data-repeater-item]');
                row.find('.quantity, .price, .discount, .amount, .itemTaxRate').val('');
                row.find('.itemTax').val('');
                updateGrandTotals();
                return;
            }

            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id
                },
                cache: false,
                success: function(data) {
                    var item = JSON.parse(data);
                    var row = el.closest('[data-repeater-item]');
                    row.find('.quantity').val(1);
                    row.find('.price').val(item.product.sale_price);

                    // ✨ SIMPLIFIED: Tax is no longer automatically set. User will select it.
                    row.find('.itemTax').val(''); // Clear any previous tax selection
                    row.find('.itemTaxRate').val(0);

                    calculateRowTotals(row.find('.price'));

                    var $currentRow = el.closest('tr[data-repeater-item]');
                    var $lastRow = $('tbody[data-repeater-list="items"] tr[data-repeater-item]:last');
                    if ($currentRow.is($lastRow) && el.val()) {
                        $('[data-repeater-create]').click();
                    }
                },
            });
        });

        // ✨ NEW: Event Handler for Tax Dropdown Change
        $(document).on('change', '.itemTax', function() {
            var el = $(this);
            var row = el.closest('[data-repeater-item]');
            var selectedOption = el.find('option:selected');
            var taxRate = parseFloat(selectedOption.data('rate')) || 0;

            // Update the hidden input with the selected tax rate
            row.find('.itemTaxRate').val(taxRate.toFixed(2));

            // Trigger a recalculation of the row totals
            calculateRowTotals(el);
        });


        // Unified calculation function
        function calculateRowTotals(changedElement) {
            let row = $(changedElement).closest('[data-repeater-item]');
            let quantity = parseFloat(row.find('.quantity').val()) || 0;
            let price = parseFloat(row.find('.price').val()) || 0;
            let discount = parseFloat(row.find('.discount').val()) || 0;
            let amountInput = row.find('.amount');
            let priceInput = row.find('.price');
            let itemTaxRate = parseFloat(row.find('.itemTaxRate').val()) || 0;

            if ($(changedElement).hasClass('amount')) {
                let amount = parseFloat(amountInput.val()) || 0;
                if (quantity > 0) {
                    let priceBeforeTax = (amount / (1 + itemTaxRate / 100));
                    let newPrice = (priceBeforeTax + discount) / quantity;
                    priceInput.val(newPrice.toFixed(2));
                }
            } else {
                let totalItemPrice = (quantity * price) - discount;
                let itemTaxPrice = (itemTaxRate / 100) * totalItemPrice;
                amountInput.val((totalItemPrice + itemTaxPrice).toFixed(2));
                row.find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));
            }
            updateGrandTotals();
        }

        // Update all footer totals
        function updateGrandTotals() {
            var subTotal = 0;
            var totalDiscount = 0;
            var totalTax = 0;

            $(".repeater tbody tr").each(function() {
                let quantity = parseFloat($(this).find('.quantity').val()) || 0;
                let price = parseFloat($(this).find('.price').val()) || 0;
                let discount = parseFloat($(this).find('.discount').val()) || 0;

                subTotal += (quantity * price);
                totalDiscount += discount;
                totalTax += parseFloat($(this).find('.itemTaxPrice').val()) || 0;
            });

            var totalAmount = subTotal - totalDiscount + totalTax;

            $('.subTotal').text(subTotal.toFixed(2));
            $('.totalDiscount').text(totalDiscount.toFixed(2));
            $('.totalTax').text(totalTax.toFixed(2));
            $('.totalAmount').text(totalAmount.toFixed(2));
            updateDueAmount();
        }

        // Update received and due amounts
        function updateDueAmount() {
            var totalAmount = parseFloat($('.totalAmount').text()) || 0;
            var receivedAmount = parseFloat($('#received-amount-display').text()) || 0;
            var dueAmount = totalAmount - receivedAmount;
            $('#due-amount-display').text(dueAmount.toFixed(2));
        }

        // Update payment totals
        function updatePaymentTotals() {
            let totalPaid = 0;
            $('#payment-container .payment_amount').each(function() {
                totalPaid += parseFloat($(this).val()) || 0;
            });
            $('#received-amount-display').text(totalPaid.toFixed(2));
            updateDueAmount();
        }

        // Event Handlers
        $(document).on('keyup change', '.quantity, .price, .discount, .amount', function() {
            if ($(this).closest('[data-repeater-item]').length) {
                calculateRowTotals(this);
            }
        });

        $('#add-payment-row').on('click', function(e) {
            e.preventDefault();
            $('#payment-container').append($('#payment-row-template').html());
            if (typeof JsSearchBox !== 'undefined') {
                JsSearchBox();
            }
        });

        $('#payment-container').on('click', '.remove-payment-row', function(e) {
            e.preventDefault();
            $(this).closest('.payment-row').remove();
            updatePaymentTotals();
        });

        $('#payment-container').on('keyup change', '.payment_amount', function() {
            updatePaymentTotals();
        });

        // Initial setup
        var customerId = '{{ $customerId }}';
        if (customerId > 0) {
            $('#customer').val(customerId).change();
        }
        updateGrandTotals();
        updatePaymentTotals();
    });
</script>
{{-- ✨ END: UPDATED JAVASCRIPT LOGIC ✨ --}}


<script>
    $(document).on('click', '#submit-invoice', function(e) {
        // Get the current total amount from the display field
        var totalAmount = parseFloat($('.totalAmount').text()) || 0;

        // Check if the total amount is zero or less
        if (totalAmount <= 0) {
            // Prevent the form from submitting
            e.preventDefault();

            // Show an error message (you can customize this)
            alert('Error: Total Amount cannot be 0.00. Please add items to the invoice.');

            return false;
        }
    });
</script>

@endpush

@section('content')
<div class="row">
    {{ Form::open(['url' => 'invoice', 'class' => 'w-100 needs-validation', 'novalidate']) }}
    <div class="col-12">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="card">
            <div class="card-body">
                {{-- Customer and Invoice Details --}}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group" id="customer-box">
                            {{ Form::label('customer_id', __('Customer'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::select('customer_id', $customers, $customerId, ['class' => 'form-control select', 'id' => 'customer', 'data-url' => route('invoice.customer'), 'required' => 'required']) }}
                        </div>
                        <div id="customer_detail" class="d-none"></div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">{{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<x-required></x-required>{{ Form::date('issue_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">{{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<x-required></x-required>{{ Form::date('due_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">{{ Form::label('invoice_number', __('Invoice Number'), ['class' => 'form-label']) }}<input type="text" class="form-control" value="{{ $invoice_number }}" readonly></div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">{{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}{{ Form::text('ref_number', '', ['class' => 'form-control', 'placeholder' => __('Enter Ref Number')]) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Product & Services') }}</h5>
        <div class="card repeater">
            <div class="item-section py-4 px-4">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-12 d-flex align-items-center justify-content-end">
                        <a href="javascript:void(0)" data-repeater-create="" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="{{ __('Add Item') }}">
                            <i class="ti ti-plus">{{__('Add Item')}} </i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                        <thead>
                            <tr>
                                <th>{{ __('Items') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Price') }} </th>
                                <th>{{ __('Discount') }}</th>
                                <th>{{ __('Tax') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        {{-- ✨ START: UPDATED TABLE BODY ✨ --}}
                        <tbody class="ui-sortable" data-repeater-list="items">
                            <tr data-repeater-item>
                                <td width="25%">
                                    <select name="item" class="form-control item" data-url="{{ route('invoice.product') }}">
                                        <option value="">{{ __('Select Item') }}</option>
                                        @foreach ($product_services as $product)
                                        <option value="{{ $product->id }}"
                                            title="Stock: {{ $product->quantity }}">
                                            {{ $product->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="quantity" class="form-control quantity" placeholder="{{__('Qty')}}" min="1"></td>
                                <td><input type="number" name="price" class="form-control price" placeholder="{{__('Price')}}" step="0.01"></td>
                                <td><input type="number" name="discount" class="form-control discount" placeholder="{{__('Discount')}}" step="0.01"></td>
                                <!-- <td>
                                    <div class="taxes"></div>
                                    <input type="hidden" name="tax" class="form-control tax">
                                    <input type="hidden" name="itemTaxPrice" class="form-control itemTaxPrice">
                                    <input type="hidden" name="itemTaxRate" class="form-control itemTaxRate">
                                </td> -->

                                <td>
                                    {{-- ✨ START: This is the new Tax Dropdown ✨ --}}
                                    <select name="tax" class="form-control itemTax">
                                        <option value="">{{ __('Select Tax') }}</option>
                                        @foreach ($taxes as $tax)
                                        <option value="{{ $tax->id }}" data-rate="{{ $tax->rate }}">{{ $tax->name }} ({{ $tax->rate }}%)</option>
                                        @endforeach
                                    </select>
                                    {{-- Hidden fields to store calculation values --}}
                                    <input type="hidden" name="itemTaxPrice" class="form-control itemTaxPrice">
                                    <input type="hidden" name="itemTaxRate" class="form-control itemTaxRate">
                                    {{-- ✨ END: New Tax Dropdown ✨ --}}
                                </td>
                                <td class="text-end">
                                    <input type="number" name="amount" class="form-control amount" placeholder="{{__('Amount')}}" step="0.01">
                                </td>
                                <td>
                                    {{-- ✨ FIX: This button now correctly triggers the repeater's delete function --}}
                                    <a href="javascript:;" data-repeater-delete class="btn btn-sm btn-danger">
                                        <i class="ti ti-trash text-white"></i>
                                    </a>
                                </td>

                            </tr>
                        </tbody>
                        {{-- ✨ END: UPDATED TABLE BODY ✨ --}}
                        <tfoot>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end subTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalTax">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td class="blue-text"><strong>{{ __('Total Amount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end blue-text totalAmount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td class="text-danger"><strong>{{ __('Received') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end text-danger" id="received-amount-display">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4">&nbsp;</td>
                                <td class="text-success"><strong>{{ __('Due') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end text-success" id="due-amount-display">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Record Payments') }}</h5>
        <div class="card">
            <div class="card-body" id="payment-section">
                <div id="payment-container">
                    <div class="row align-items-center payment-row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">{{ Form::label('payment_date[]', __('Payment Date'), ['class' => 'form-label']) }}{{ Form::date('payment_date[]', date('Y-m-d'), ['class' => 'form-control']) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">{{ Form::label('payment_account_id[]', __('Payment Account'), ['class' => 'form-label']) }}
                                <x-required></x-required>{{ Form::select('payment_account_id[]', $accounts, null, ['class' => 'form-control select', 'placeholder' => __('Select Account'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">{{ Form::label('payment_amount[]', __('Amount'), ['class' => 'form-label']) }}<x-required></x-required>{{ Form::number('payment_amount[]', '', ['class' => 'form-control payment_amount', 'step' => '0.01', 'placeholder' => '0.00', 'required' => 'required']) }}</div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">{{ Form::label('payment_reference[]', __('Reference'), ['class' => 'form-label']) }}{{ Form::text('payment_reference[]', '', ['class' => 'form-control', 'placeholder' => __('e.g. Cheque No.')]) }}</div>
                        </div>
                    </div>
                </div>
                <a href="#" id="add-payment-row" class="btn btn-sm btn-primary mt-2"><i class="ti ti-plus"></i> {{ __('Add Another Payment') }}</a>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('invoice.index') }}';" class="btn btn-light me-2">
        <input type="submit" value="{{ __('Create & Save') }}" id="submit-invoice" class="btn btn-primary">
    </div>
    {{ Form::close() }}
</div>

<div id="payment-row-template" style="display: none;">
    <div class="row align-items-center payment-row mb-3">
        <div class="col-md-3">
            <div class="form-group">{{ Form::label('payment_date[]', __('Payment Date'), ['class' => 'form-label']) }}{{ Form::date('payment_date[]', date('Y-m-d'), ['class' => 'form-control']) }}</div>
        </div>
        <div class="col-md-3">
            <div class="form-group">{{ Form::label('payment_account_id[]', __('Payment Account'), ['class' => 'form-label']) }}{{ Form::select('payment_account_id[]', $accounts, null, ['class' => 'form-control select', 'placeholder' => __('Select Account')]) }}</div>
        </div>
        <div class="col-md-2">
            <div class="form-group">{{ Form::label('payment_amount[]', __('Amount'), ['class' => 'form-label']) }}{{ Form::number('payment_amount[]', '', ['class' => 'form-control payment_amount', 'step' => '0.01', 'placeholder' => '0.00']) }}</div>
        </div>
        <div class="col-md-3">
            <div class="form-group">{{ Form::label('payment_reference[]', __('Reference'), ['class' => 'form-label']) }}{{ Form::text('payment_reference[]', '', ['class' => 'form-control', 'placeholder' => __('e.g. Cheque No.')]) }}</div>
        </div>
        <div class="col-md-1 text-end"><a href="#" class="btn btn-sm btn-danger remove-payment-row" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a></div>
    </div>
</div>
@endsection