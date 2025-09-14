@extends('layouts.admin')
@section('page-title')
{{ __('Invoice Create') }}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
@endsection
<!-- @push('script-page')
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script>
    var selector = "body";
    if ($(selector + " .repeater").length) {
        var $dragAndDrop = $("body .repeater tbody").sortable({
            handle: '.sort-handler'
        });
        var $repeater = $(selector + ' .repeater').repeater({
            initEmpty: false,
            defaultValues: {
                'status': 1
            },
            show: function() {
                $(this).slideDown();
                var file_uploads = $(this).find('input.multi');
                if (file_uploads.length) {
                    $(this).find('input.multi').MultiFile({
                        max: 3,
                        accept: 'png|jpg|jpeg',
                        max_size: 2048
                    });
                }
                JsSearchBox();
            },
            hide: function(deleteElement) {
                if (confirm('Are you sure you want to delete this element?')) {
                    $(this).slideUp(deleteElement);
                    $(this).remove();

                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.subTotal').html(subTotal.toFixed(2));
                    $('.totalAmount').html(subTotal.toFixed(2));
                }
            },
            ready: function(setIndexes) {

                $dragAndDrop.on('drop', setIndexes);
            },
            isFirstItemUndeletable: true
        });
        var value = $(selector + " .repeater").attr('data-value');
        if (typeof value != 'undefined' && value.length != 0) {
            value = JSON.parse(value);
            $repeater.setList(value);
        }
    }

    $(document).on('change', '#customer', function() {
        $('#customer_detail').removeClass('d-none');
        $('#customer_detail').addClass('d-block');
        $('#customer-box').removeClass('d-block');
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
                    $('#customer-box').addClass('d-block');
                    $('#customer_detail').removeClass('d-block');
                    $('#customer_detail').addClass('d-none');
                }
            },

        });
    });

    $(document).on('click', '#remove', function() {
        $('#customer-box').removeClass('d-none');
        $('#customer-box').addClass('d-block');
        $('#customer_detail').removeClass('d-block');
        $('#customer_detail').addClass('d-none');
    })

    $(document).on('change', '.item', function() {

        var iteams_id = $(this).val();
        var url = $(this).data('url');
        var el = $(this);

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
                row.find('.pro_description').val(item.product.description);

                var taxes = '';
                var tax = [];
                var totalItemTaxRate = 0;

                if (item.taxes == 0) {
                    taxes += '-';
                } else {
                    for (var i = 0; i < item.taxes.length; i++) {
                        taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name +
                            ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                        tax.push(item.taxes[i].id);
                        totalItemTaxRate += parseFloat(item.taxes[i].rate);
                    }
                }
                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product.sale_price * 1));
                row.find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));
                row.find('.itemTaxRate').val(totalItemTaxRate.toFixed(2));
                row.find('.taxes').html(taxes);
                row.find('.tax').val(tax);
                row.find('.unit').html(item.unit);
                row.find('.discount').val(0);

                // Calculate amount including tax
                var quantity = 1;
                var price = parseFloat(item.product.sale_price);
                var discount = 0;
                var amountBeforeTax = (quantity * price) - discount;
                var amountWithTax = amountBeforeTax + itemTaxPrice;
                row.find('.amount').val(amountWithTax.toFixed(2));

                // Trigger calculation for this row
                row.find('.price').trigger('change');
            },
        });
    });

    $(document).on('keyup', '.quantity', function() {
        var quntityTotalTaxPrice = 0;

        var el = $(this).parent().parent().parent().parent();

        var quantity = $(this).val();
        var price = $(el.find('.price')).val();
        var discount = $(el.find('.discount')).val();
        if (discount.length <= 0) {
            discount = 0;
        }

        var totalItemPrice = (quantity * price) - discount;

        var amount = (totalItemPrice);


        var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
        var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
        $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

        $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

        var totalItemTaxPrice = 0;
        var itemTaxPriceInput = $('.itemTaxPrice');
        for (var j = 0; j < itemTaxPriceInput.length; j++) {
            totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
        }


        var totalItemPrice = 0;
        var inputs_quantity = $(".quantity");

        var priceInput = $('.price');
        for (var j = 0; j < priceInput.length; j++) {
            totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
        }

        var inputs = $(".amount");

        var subTotal = 0;
        for (var i = 0; i < inputs.length; i++) {
            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
        }

        $('.subTotal').html(totalItemPrice.toFixed(2));
        $('.totalTax').html(totalItemTaxPrice.toFixed(2));

        $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

    })

    $(document).on('keyup change', '.price', function() {
        var el = $(this).closest('tr');
        var price = parseFloat(el.find('.price').val()) || 0;
        var quantity = parseFloat(el.find('.quantity').val()) || 0;
        var discount = parseFloat(el.find('.discount').val()) || 0;
        var totalItemPrice = (quantity * price) - discount;

        var totalItemTaxRate = parseFloat(el.find('.itemTaxRate').val()) || 0;
        var itemTaxPrice = (totalItemTaxRate / 100) * totalItemPrice;
        el.find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));

        // Update amount field (input)
        el.find('.amount').val((totalItemPrice + itemTaxPrice).toFixed(2));

        // Update totals
        var subTotal = 0,
            totalTax = 0,
            totalDiscount = 0;
        $('.repeater tbody tr[data-repeater-item]').each(function() {
            var qty = parseFloat($(this).find('.quantity').val()) || 0;
            var prc = parseFloat($(this).find('.price').val()) || 0;
            var disc = parseFloat($(this).find('.discount').val()) || 0;
            var taxRate = parseFloat($(this).find('.itemTaxRate').val()) || 0;
            var itemTotal = (qty * prc) - disc;
            var tax = (taxRate / 100) * itemTotal;
            subTotal += (qty * prc);
            totalDiscount += disc;
            totalTax += tax;
        });
        var totalAmount = subTotal - totalDiscount + totalTax;
        $('.subTotal').text(subTotal.toFixed(2));
        $('.totalDiscount').text(totalDiscount.toFixed(2));
        $('.totalTax').text(totalTax.toFixed(2));
        $('.totalAmount').text(totalAmount.toFixed(2));
    })

    $(document).on('keyup change', '.discount', function() {
        var el = $(this).parent().parent().parent();
        var discount = $(this).val();
        if (discount.length <= 0) {
            discount = 0;
        }

        var price = $(el.find('.price')).val();
        var quantity = $(el.find('.quantity')).val();
        var totalItemPrice = (quantity * price) - discount;


        var amount = (totalItemPrice);


        var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
        var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
        $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

        $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

        var totalItemTaxPrice = 0;
        var itemTaxPriceInput = $('.itemTaxPrice');
        for (var j = 0; j < itemTaxPriceInput.length; j++) {
            totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
        }


        var totalItemPrice = 0;
        var inputs_quantity = $(".quantity");

        var priceInput = $('.price');
        for (var j = 0; j < priceInput.length; j++) {
            totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
        }

        var inputs = $(".amount");

        var subTotal = 0;
        for (var i = 0; i < inputs.length; i++) {
            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
        }


        var totalItemDiscountPrice = 0;
        var itemDiscountPriceInput = $('.discount');

        for (var k = 0; k < itemDiscountPriceInput.length; k++) {
            if (!isNaN(parseFloat(itemDiscountPriceInput[k].value))) {
                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
            }
        }


        $('.subTotal').html(totalItemPrice.toFixed(2));
        $('.totalTax').html(totalItemTaxPrice.toFixed(2));

        $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
        $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));




    })

    $(document).on('change', '.item', function() {
        $('.item option').prop('hidden', false);
        $('.item :selected').each(function() {
            var id = $(this).val();
            $(".item option[value=" + id + "]").prop("hidden", true);
        });
    });

    $(document).on('click', '[data-repeater-create]', function() {
        $('.item option').prop('hidden', false);
        $('.item :selected').each(function() {
            var id = $(this).val();
            $(".item option[value=" + id + "]").prop("hidden", true);
        });
    })

    var customerId = '{{ $customerId }}';
    if (customerId > 0) {
        $('#customer').val(customerId).change();
    }
</script>
<script>
    $(document).on('click', '[data-repeater-delete]', function() {
        $(".price").change();
        $(".discount").change();

        $('.item option').prop('hidden', false);
        $('.item :selected').each(function() {
            var id = $(this).val();
            $(".item option[value=" + id + "]").prop("hidden", true);
        });
    });
    JsSearchBox();
</script>

@endpush -->

@push('script-page')
<script src="{{ asset('js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
<script src="{{ asset('js/jquery-searchbox.js') }}"></script>
<script>
    // Main Calculation Function
    function calculateRowTotals(changedElement) {
        let row = $(changedElement).closest('[data-repeater-item]');
        let quantity = parseFloat(row.find('.quantity').val()) || 0;
        let price = parseFloat(row.find('.price').val()) || 0;
        let discount = parseFloat(row.find('.discount').val()) || 0;
        let amountInput = row.find('.amount');
        let priceInput = row.find('.price');
        let itemTaxRate = parseFloat(row.find('.itemTaxRate').val()) || 0;

        // If the Amount field was changed, do a "reverse" calculation to find the price
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

        // Always update grand totals after any row change
        updateGrandTotals();
    }

    // Grand totals calculation
    function updateGrandTotals() {
        var subTotal = 0;
        var totalDiscount = 0;
        var totalTax = 0;

        $(".repeater tbody tr[data-repeater-item]").each(function() {
            let quantity = parseFloat($(this).find('.quantity').val()) || 0;
            let price = parseFloat($(this).find('.price').val()) || 0;
            let discount = parseFloat($(this).find('.discount').val()) || 0;

            let itemTotalBeforeTax = (quantity * price) - discount;
            let itemTaxRate = parseFloat($(this).find('.itemTaxRate').val()) || 0;
            let itemTaxPrice = (itemTotalBeforeTax * itemTaxRate) / 100;

            subTotal += (quantity * price);
            totalDiscount += discount;
            totalTax += itemTaxPrice;
        });

        var totalAmount = (subTotal - totalDiscount + totalTax);

        $('.subTotal').text(subTotal.toFixed(2));
        $('.totalDiscount').text(totalDiscount.toFixed(2));
        $('.totalTax').text(totalTax.toFixed(2));
        $('.totalAmount').text(totalAmount.toFixed(2));
    }

    // Event Handlers
    $(document).on('keyup change', '.quantity, .price, .discount, .itemTax', function() {
        calculateRowTotals(this);
    });
    $(document).on('keyup change', '.amount', function(e) {
        if (e.originalEvent) {
            calculateRowTotals(this);
        }
    });

    // AJAX item selection
    $(document).on('change', '.item', function() {
        var iteams_id = $(this).val();
        var url = $(this).data('url');
        var el = $(this);

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
                row.find('.pro_description').val(item.product.description);

                var taxes = '';
                var tax = [];
                var totalItemTaxRate = 0;

                if (item.taxes == 0) {
                    taxes += '-';
                } else {
                    for (var i = 0; i < item.taxes.length; i++) {
                        taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name +
                            ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                        tax.push(item.taxes[i].id);
                        totalItemTaxRate += parseFloat(item.taxes[i].rate);
                    }
                }
                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product.sale_price * 1));
                row.find('.itemTaxPrice').val(itemTaxPrice.toFixed(2));
                row.find('.itemTaxRate').val(totalItemTaxRate.toFixed(2));
                row.find('.taxes').html(taxes);
                row.find('.tax').val(tax);
                row.find('.unit').html(item.unit);
                row.find('.discount').val(0);

                // Calculate amount including tax
                var quantity = 1;
                var price = parseFloat(item.product.sale_price);
                var discount = 0;
                var amountBeforeTax = (quantity * price) - discount;
                var amountWithTax = amountBeforeTax + itemTaxPrice;
                row.find('.amount').val(amountWithTax.toFixed(2));

                // Trigger calculation for this row
                row.find('.price').trigger('change');
            },
        });
    });

    // Repeater delete handler
    $(document).on('click', '[data-repeater-delete]', function() {
        setTimeout(function() {
            updateGrandTotals();
        }, 100); // Wait for row to be removed
    });

    // Initial setup
    $(document).ready(function() {
        JsSearchBox();
        updateGrandTotals();
    });
</script>
@endpush
@section('content')
<div class="row">
    {{ Form::open(['url' => 'invoice','class'=>'w-100 needs-validation','novalidate']) }}
    <div class="col-12">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="form-group" id="customer-box">
                            {{ Form::label('customer_id', __('Customer'), ['class' => 'form-label']) }}<x-required></x-required>
                            {{ Form::select('customer_id', $customers, $customerId, ['class' => 'form-control select', 'id' => 'customer', 'data-url' => route('invoice.customer'), 'required' => 'required']) }}
                            <div class="text-xs mt-1">
                                {{ __('Create customer here.') }} <a href="{{ route('customer.index') }}"><b>{{ __('Create customer') }}</b></a>
                            </div>
                        </div>

                        <div id="customer_detail" class="d-none">
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <div class="form-icon-user">
                                        {{ Form::date('issue_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<x-required></x-required>
                                    <div class="form-icon-user">
                                        {{ Form::date('due_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('invoice_number', __('Invoice Number'), ['class' => 'form-label']) }}
                                    <div class="form-icon-user">
                                        <input type="text" class="form-control" value="{{ $invoice_number }}"
                                            readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                    <div class="form-icon-user">
                                        <span><i class="ti ti-joint"></i></span>
                                        {{ Form::text('ref_number', '', ['class' => 'form-control', 'placeholder'=>__('Enter Ref Number')]) }}
                                    </div>
                                </div>
                            </div>
                            @if (!$customFields->isEmpty())
                            <div class="col-md-6">
                                <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                    @include('customFields.formBuilder')
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <h5 class="h4 d-inline-block font-weight-400 mb-4">{{ __('Product & Services') }}</h5>
        <div class="card repeater">
            <div class="item-section py-4">
                <div class="row justify-content-between align-items-center">
                    <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                        <a href="javascript:void(0)" data-repeater-create="" class="btn btn-primary mr-2"
                            data-toggle="modal" data-target="#add-bank">
                            <i class="ti ti-plus"></i> {{ __('Add item') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body table-border-style mt-2">
                <div class="table-responsive">
                    <table class="table  mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                        <thead>
                            <tr>
                                <th>{{ __('Items') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Price') }} </th>
                                <th>{{ __('Discount') }}</th>
                                <th>{{ __('Tax') }} (%)</th>
                                <th class="text-end">{{ __('Amount') }} <br>
                                    <!-- <small class="text-danger font-weight-bold">{{ __('before tax & discount') }}</small> -->
                                </th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody class="ui-sortable" data-repeater-list="items" id="sortable-table">
                            <tr data-repeater-item>

                                <td width="25%" class="form-group pt-0 flex-nowrap">


                                    <select name="item" class="form-control item" data-url="{{ route('invoice.product') }}" required="required">
                                        <option value="">{{__('Select Item')}}</option>
                                        @foreach ($product_services as $product)
                                        <option
                                            value="{{ $product->id }}"
                                            title="Sell Price: {{ Auth::user()->priceFormat($product->sale_price) }} | Purchase Price: {{ Auth::user()->priceFormat($product->purchase_price) }} | Stock: {{ $product->quantity }}">
                                            {{ $product->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <div class="form-group price-input input-group search-form flex-nowrap">
                                        {{ Form::number('quantity', '', ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty'), 'required' => 'required']) }}
                                        <span class="unit input-group-text bg-transparent"></span>
                                    </div>
                                </td>


                                <td>
                                    <div class="form-group price-input input-group search-form flex-nowrap">
                                        {{ Form::number('price', '', ['class' => 'form-control price', 'step' => '0.01', 'required' => 'required', 'placeholder' => __('Price'), 'required' => 'required']) }}
                                        <span
                                            class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                    </div>
                                </td>



                                <td>
                                    <div class="form-group price-input input-group search-form flex-nowrap">
                                        {{ Form::number('discount', '', ['class' => 'form-control discount', 'required' => 'required', 'placeholder' => __('Discount')]) }}
                                        <span
                                            class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                    </div>
                                </td>

                                <td>
                                    <div class="form-group">
                                        <div class="input-group colorpickerinput flex-nowrap">
                                            <div class="taxes"></div>
                                            {{ Form::hidden('tax', '', ['class' => 'form-control tax text-dark']) }}
                                            {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                            {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                        </div>
                                    </div>
                                </td>

                                <!-- <td class="text-end amount">0.00</td> -->

                                <td>
                                    <div class="form-group price-input input-group search-form flex-nowrap">
                                        {{ Form::number('amount', '', ['class' => 'form-control amount', 'step' => '0.01', 'required' => 'required', 'placeholder' => __('Amount')]) }}
                                        <span class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-btn ms-2 float-end mb-3" data-repeater-delete>
                                        <a href="#" class=" mx-3 btn btn-sm d-inline-flex align-items-center m-2 p-2 bg-danger"
                                            data-bs-toggle="tooltip" data-bs-original-title="{{__('Delete')}}" title="{{__('Delete')}}">
                                            <i class="ti ti-trash text-white"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <div class="form-group">
                                        {{ Form::textarea('description', null, ['class' => 'form-control pro_description', 'rows' => '3', 'placeholder' => __('Description')]) }}
                                    </div>
                                </td>
                                <td colspan="5"></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                </td>
                                <td class="text-end subTotal">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalTax">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="blue-text"><strong>{{ __('Total Amount') }}
                                        ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalAmount blue-text"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('invoice.index') }}';"
            class="btn btn-light me-2">
        <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
    </div>
    {{ Form::close() }}

</div>





@endsection