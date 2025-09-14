<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\ProductServiceUnit;
use App\Models\Tax;
use App\Models\Vender;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductServiceExport;
use App\Imports\ProductServiceImport;
use App\Models\ChartOfAccount;
use App\Models\ProductLocation;

use Illuminate\Support\Facades\Validator; // Make sure this is imported at the top


class ProductServiceController extends Controller
{
    public function index(Request $request)
    {

        if (\Auth::user()->can('manage product & service')) {
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'product & service')->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            if (!empty($request->category)) {
                $productServices = ProductService::with(['unit', 'category', 'taxes', 'productLocation'])->where('created_by', '=', \Auth::user()->creatorId())->where('category_id', $request->category)->orderBy('created_at', 'desc')->get();
            } else {
                $productServices = ProductService::with(['unit', 'category', 'taxes', 'productLocation'])->where('created_by', '=', \Auth::user()->creatorId())->orderBy('created_at', 'desc')->get();
            }
            return view('productservice.index', compact('productServices', 'category'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create product & service')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
            $category     = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'product & service')->get()->pluck('name', 'id');
            $unit         = ProductServiceUnit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $tax          = Tax::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            // Fetch income chart of accounts
            $incomeChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name'), 'chart_of_accounts.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->where('chart_of_account_types.name', 'income')
                ->where('chart_of_accounts.parent', '=', 0)
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('code_name', 'id')
                ->prepend('Select Account', null);

            // Fetch income sub-accounts
            $incomeSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->where('chart_of_account_types.name', 'income')
                ->where('chart_of_accounts.parent', '!=', 0)
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->toArray();

            // Fetch expense chart of accounts
            $expenseChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name'), 'chart_of_accounts.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('code_name', 'id')
                ->prepend('Select Account', null);

            // Fetch expense sub-accounts
            $expenseSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                ->where('chart_of_accounts.parent', '!=', 0)
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->toArray();

            // Define default values
            $defaultIncomeAccount = 2178;
            $defaultExpenseAccount = 2187;

            return view('productservice.create', compact('category', 'unit', 'tax', 'customFields', 'incomeChartAccounts', 'incomeSubAccounts', 'expenseChartAccounts', 'expenseSubAccounts', 'defaultIncomeAccount', 'defaultExpenseAccount'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }



    // Add Item Function

    public function additem()
    {
        if (\Auth::user()->can('create product & service')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
            $category     = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'product & service')->get()->pluck('name', 'id');
            $unit         = ProductServiceUnit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $tax          = Tax::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            // product location
            $productlocations = ProductLocation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            // Fetch income chart of accounts
            $incomeChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name'), 'chart_of_accounts.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->where('chart_of_account_types.name', 'income')
                ->where('chart_of_accounts.parent', '=', 0)
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('code_name', 'id')
                ->prepend('Select Account', null);


            // Fetch income sub-accounts
            $incomeSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->where('chart_of_account_types.name', 'income')
                ->where('chart_of_accounts.parent', '!=', 0)
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->toArray();

            // Fetch expense chart of accounts
            $expenseChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name'), 'chart_of_accounts.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('code_name', 'id')
                ->prepend('Select Account', null);

            // Fetch expense sub-accounts
            $expenseSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                ->where('chart_of_accounts.parent', '!=', 0)
                ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                ->get()
                ->toArray();

            $defaultIncomeAccount = 2178; // Default income account ID for pre-selection
            $defaultExpenseAccount = 2187; // Default expense account ID for pre-selection

            return view('productservice.additem', compact('category', 'unit', 'tax', 'customFields', 'incomeChartAccounts', 'incomeSubAccounts', 'expenseChartAccounts', 'expenseSubAccounts', 'defaultIncomeAccount', 'defaultExpenseAccount', 'productlocations'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {

            $rules = [
                'name' => 'required',
                // 'sku' => 'required',
                'sku' => 'nullable|string', // Allow SKU to be nullable
                //'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category_id' => 'required',
                'productlocation_id' => 'required',
                'unit_id' => 'required',
                'type' => 'required',
                //'tax_id' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('productservice.index')->with('error', $messages->first());
            }

            $productService                 = new ProductService();
            $productService->name           = $request->name;
            $productService->description    = $request->description;
            $productService->sku            = $request->sku;
            //$productService->sale_price     = $request->sale_price;
            $productService->sale_price = $request->sale_price ?? 0.00;
            $productService->purchase_price = $request->purchase_price;
            $productService->tax_id         = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
            $productService->unit_id        = $request->unit_id;
            $productService->quantity       = $request->quantity ?? 0;
            $productService->type           = $request->type;
            $productService->sale_chartaccount_id       = $request->sale_chartaccount_id;
            $productService->expense_chartaccount_id    = $request->expense_chartaccount_id;
            $productService->category_id    = $request->category_id;
            $productService->productlocation_id = $request->productlocation_id;
            $productService->created_by     = \Auth::user()->creatorId();
            $productService->save();
            CustomField::saveData($productService, $request->customField);

            return redirect()->route('productservice.index')->with('success', __('Product successfully created.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit($id)
    {
        $productService = ProductService::find($id);

        if (\Auth::user()->can('edit product & service')) {
            if ($productService->created_by == \Auth::user()->creatorId()) {
                $category     = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 'product & service')->get()->pluck('name', 'id');

                $unit     = ProductServiceUnit::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $tax      = Tax::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');

                $productService->customField = CustomField::getData($productService, 'product');
                $customFields                = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'product')->get();
                $productService->tax_id      = explode(',', $productService->tax_id);

                // product location
                $productLocations = ProductLocation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');


                $incomeChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name'), 'chart_of_accounts.id')
                    ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                    ->where('chart_of_account_types.name', 'income')
                    ->where('chart_of_accounts.parent', '=', 0)
                    ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                    ->get()
                    ->pluck('code_name', 'id')
                    ->prepend('Select Account', 0);

                // Fetch income sub-accounts
                $incomeSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                    ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                    ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                    ->where('chart_of_account_types.name', 'income')
                    ->where('chart_of_accounts.parent', '!=', 0)
                    ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                    ->get()
                    ->toArray();

                // Fetch expense chart of accounts
                $expenseChartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name'), 'chart_of_accounts.id')
                    ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                    ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                    ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                    ->get()
                    ->pluck('code_name', 'id')
                    ->prepend('Select Account', '');

                // Fetch expense sub-accounts
                $expenseSubAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account')
                    ->leftJoin('chart_of_account_parents', 'chart_of_accounts.parent', '=', 'chart_of_account_parents.id')
                    ->leftJoin('chart_of_account_types', 'chart_of_account_types.id', '=', 'chart_of_accounts.type')
                    ->whereIn('chart_of_account_types.name', ['Expenses', 'Costs of Goods Sold'])
                    ->where('chart_of_accounts.parent', '!=', 0)
                    ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())
                    ->get()
                    ->toArray();

                return view('productservice.edit', compact('category', 'unit', 'tax', 'productService', 'customFields', 'incomeChartAccounts', 'incomeSubAccounts', 'expenseChartAccounts', 'expenseSubAccounts', 'productLocations'));
            } else {
                return response()->json(['error' => __('Permission denied.')], 401);
            }
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, $id)
    {
        if (\Auth::user()->can('edit product & service')) {
            $productService = ProductService::find($id);

            if ($productService->created_by == \Auth::user()->creatorId()) {

                $rules = [
                    'name' => 'required',
                    // 'sku' => 'required',
                    'sku' => 'nullable|string', // Allow SKU to be nullable
                    'sale_price' => 'nullable|numeric', // Make sale_price nullable
                    //'sale_price' => 'required|numeric',
                    'purchase_price' => 'required|numeric',
                    //'tax_id' => 'required',
                    'category_id' => 'required',
                    'productlocation_id' => 'required',
                    'unit_id' => 'required',
                    'type' => 'required',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('productservice.index')->with('error', $messages->first());
                }

                $productService->name           = $request->name;
                $productService->description    = $request->description;
                $productService->sku            = $request->sku;
                //$productService->sale_price     = $request->sale_price;
                $productService->sale_price = $request->sale_price ?? 0.00;
                $productService->purchase_price = $request->purchase_price;
                $productService->tax_id         = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
                $productService->unit_id        = $request->unit_id;
                $productService->quantity        = $request->quantity ?? 0;
                $productService->type           = $request->type;
                $productService->productlocation_id = $request->productlocation_id;
                $productService->sale_chartaccount_id       = $request->sale_chartaccount_id;
                $productService->expense_chartaccount_id    = $request->expense_chartaccount_id;
                $productService->category_id    = $request->category_id;
                $productService->created_by     = \Auth::user()->creatorId();
                $productService->save();
                CustomField::saveData($productService, $request->customField);

                return redirect()->route('productservice.index')->with('success', __('Product successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $productService = ProductService::find($id);
            if ($productService->created_by == \Auth::user()->creatorId()) {
                $productService->delete();

                return redirect()->route('productservice.index')->with('success', __('Product successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function export()
    {

        $name = 'product_service_' . date('Y-m-d i:h:s');
        $data = Excel::download(new ProductServiceExport(), $name . '.xlsx');

        return $data;
    }

    public function importFile()
    {
        return view('productservice.import');
    }

    public function import(Request $request)
    {
        $rules = [
            'file' => 'required|mimes:csv,txt,xls,xlsx',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $products     = (new ProductServiceImport)->toArray(request()->file('file'))[0];
        $totalProduct = count($products) - 1;
        $errorArray   = [];

        for ($i = 1; $i <= count($products) - 1; $i++) {
            $items = $products[$i];

            $taxNames = explode(',', $items[5]);
            $taxesData = [];

            foreach ($taxNames as $taxName) {
                $trimmedTax = trim($taxName);
                if (empty($trimmedTax)) {
                    continue;
                }

                $tax = Tax::where('name', $trimmedTax)->where('created_by', \Auth::user()->creatorId())->first();
                if ($tax) {
                    $taxesData[] = $tax->id;
                }
            }
            $taxData = implode(',', $taxesData);


            $category = ProductServiceCategory::where('name', $items[6])->first();
            $unit     = ProductServiceUnit::where('name', $items[7])->first();

            $productService =  new ProductService();
            $productService->name           = $items[0] ?? "";
            $productService->sku            = $items[1] ?? "";
            $productService->quantity       = $items[2] ?? "";
            // $productService->sale_price     = $items[3] ?? "";
            $productService->sale_price = !empty($items[3]) ? $items[3] : 0.00;
            $productService->purchase_price = $items[4] ?? "";
            $productService->tax_id         = $taxData  ?? "";
            $productService->category_id    = $category?->id ?? "";
            $productService->unit_id        = $unit?->id ?? "";
            $productService->type           = $items[8] ?? "";
            $productService->description    = $items[9] ?? "";
            $productService->created_by     = \Auth::user()->creatorId();

            if (empty($productService->name) || empty($productService->sku)) {
                $errorArray[] = $items;
            } else {
                $productService->save();
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalProduct . ' ' . 'record');

            foreach ($errorArray as $errorData) {
                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }



    // Get Product Location List
    public function productlocation(Request $request)
    {
        if (\Auth::user()->can('manage product & service')) {
            if (!empty($request->name)) {
                $productlocations = ProductLocation::where('created_by', \Auth::user()->creatorId())
                    ->where('name', $request->name)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } else {
                $productlocations = ProductLocation::where('created_by', \Auth::user()->creatorId())
                    ->orderBy('name', 'desc')
                    ->get();
            }

            return view('productservice.productlocation', compact('productlocations'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    // Add Product Location Function
    public function addproductlocation()
    {
        if (\Auth::user()->can('create product & service')) {

            $productlocations = ProductLocation::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $types = ProductLocation::$catTypes;

            return view('productservice.addproductlocation', compact('productlocations', 'types'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    // Store Product Location

    public function storeproductlocation(Request $request)
    {
        if (\Auth::user()->can('create product & service')) {

            // 1. Validate the request from the modal form
            $validator = Validator::make($request->all(), [
                'name' => 'required|unique:product_locations,name',
                'type' => 'required',
            ]);

            // 2. If validation fails, return errors as a JSON response
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422); // Use 422 status code for validation errors
            }

            // 3. If validation passes, create and save the new location
            $productLocation = new ProductLocation();
            $productLocation->name = $request->name;
            $productLocation->type = $request->type;
            $productLocation->created_by = \Auth::user()->creatorId();
            $productLocation->save();

            // 4. Return a success message and the new location data as a JSON response
            return response()->json([
                'success' => true,
                'message' => __('Product Location successfully created.'),
                'location' => $productLocation // This is crucial for the JS to update the dropdown
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.')
            ], 403); // Use 403 for permission denied
        }
    }

    // public function storeproductlocation(Request $request)
    // {
    //     if (\Auth::user()->can('create product & service')) {

    //         $rules = [
    //             'name' => 'required|unique:product_locations,name',
    //             'type' => 'required',
    //         ];

    //         $validator = \Validator::make($request->all(), $rules);

    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->route('productservice.productlocation')->with('error', $messages->first());
    //         }

    //         $productLocation                 = new ProductLocation();
    //         $productLocation->name           = $request->name;
    //         $productLocation->type           = $request->type;
    //         $productLocation->created_by     = \Auth::user()->creatorId();
    //         $productLocation->save();

    //         if (route('productservice.additem')) {
    //             return redirect()->route('productservice.additem')->with('success', __('Category successfully created.'));
    //         } elseif (route('productservice.productlocation')) {
    //             return redirect()->route('productservice.productlocation')->with('success', __('Product location successfully created.'));
    //         } else {
    //             return redirect()->back()->with('error', __('Permission denied.'));
    //         }
    //     }
    // }


    // edit Product Location
    public function editProductLocation($id)
    {
        if (\Auth::user()->can('edit product & service')) {
            $productLocation = ProductLocation::find($id);
            $types = ProductLocation::$catTypes;
            if ($productLocation) {
                return view('productservice.editproductlocation', compact('productLocation', 'types'));
            } else {
                return redirect()->back()->with('error', __('Product location not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    // Update Product Location
    public function updateproductlocation(Request $request, $id)
    {
        if (\Auth::user()->can('edit product & service')) {
            $productLocation = ProductLocation::find($id);
            if ($productLocation) {
                $rules = [
                    'name' => 'required|unique:product_locations,name,' . $productLocation->id,
                    'type' => 'required',
                ];

                $validator = \Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->route('productservice.productlocation')->with('error', $messages->first());
                }

                $productLocation->name = $request->name;
                $productLocation->type = $request->type;
                $productLocation->created_by = \Auth::user()->creatorId();
                $productLocation->save();

                return redirect()->route('productservice.productlocation')->with('success', __('Product location successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Product location not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    // Destroy Product Location
    public function destroyproductlocation($id)
    {
        if (\Auth::user()->can('delete product & service')) {
            $productLocation = ProductLocation::find($id);
            if ($productLocation) {
                $productLocation->delete();

                return redirect()->route('productservice.productlocation')->with('success', __('Product location successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Product location not found.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
