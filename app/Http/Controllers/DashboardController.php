<?php

namespace App\Http\Controllers;

use App\Models\Debts;
use App\Models\Deliveries;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrders;
use App\Models\Sales;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function admin()
    {
        $purchaseOrders = PurchaseOrders::whereHas('user', function ($query) {
            $query->where('parent_id', auth()->id());
        })
            ->where(function ($query) {
                $query->whereNotIn('status', ['Approved', 'Completed'])
                    ->orWhereDate('updated_at', '>=', now()->toDateString());
            })
            ->with(['branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate widget metrics
        $widgetOmset = Sales::where('create_by', auth()->id())->sum('grand_total');

        $omsetBulanIni = Sales::where('create_by', auth()->id())->whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('grand_total');
        $omsetBulanLalu = Sales::where('create_by', auth()->id())->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year)->sum('grand_total');
        $omsetTrendPercent = 0;
        if ($omsetBulanLalu > 0) {
            $omsetTrendPercent = (($omsetBulanIni - $omsetBulanLalu) / $omsetBulanLalu) * 100;
        } elseif ($omsetBulanIni > 0) {
            $omsetTrendPercent = 100.0;
        }

        $hutangSupplier = Debts::whereNotNull('supplier_id')->sum('remaining_amount');
        $hutangCabang = Debts::where('debtor_type', 'branch')->where('creditor_type', 'branch')->sum('remaining_amount');
        $userBranchId = auth()->user()->branch_id;
        $totalSku = $userBranchId
            ? Product::whereHas('productStocks', function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            })->count()
            : Product::count();
        $totalStok = $userBranchId
            ? (ProductStock::where('branch_id', $userBranchId)->sum('stock') ?? 0)
            : (ProductStock::sum('stock') ?? 0);

        // Calculate dynamic monthly sales trend for the last 7 months
        $startDate = now()->subMonths(6)->startOfMonth();
        $monthlySalesData = Sales::where('create_by', auth()->id())
            ->where('date', '>=', $startDate)
            ->select('grand_total', 'date')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = (int) $date->format('m');
            $yearNum = (int) $date->format('Y');

            $chartLabels[] = $indonesianMonths[$monthNum];

            $sum = $monthlySalesData->filter(function ($sale) use ($monthNum, $yearNum) {
                return $sale->date->year === $yearNum && $sale->date->month === $monthNum;
            })->sum('grand_total');

            $chartValues[] = (float) $sum;
        }

        return view('admin.dashboard', compact(
            'purchaseOrders',
            'widgetOmset',
            'omsetTrendPercent',
            'hutangSupplier',
            'hutangCabang',
            'totalSku',
            'totalStok',
            'chartLabels',
            'chartValues'
        ));
    }

    public function cabang(Request $request)
    {
        $pusatBranchId = auth()->user()->parent->branch_id ?? 'BRC-001';
        $branchId = auth()->user()->branch_id;

        $products = Product::with([
            'wholesalePrices' => function ($query) use ($pusatBranchId) {
                $query->where('branch_id', $pusatBranchId);
            },
            'branchPrices' => function ($query) use ($pusatBranchId) {
                $query->where('branch_id', $pusatBranchId);
            },
            'productStocks' => function ($query) use ($pusatBranchId) {
                $query->where('branch_id', $pusatBranchId);
            },
        ])->orderBy('name')->get();
        $deliveries = Deliveries::where('created_by', '!=', auth()->id())
            ->whereHas('sale', function ($query) {
                $query->where('branch_id', auth()->user()->branch_id);
            })->with(['sale.salesItems'])->orderBy('created_at', 'desc')->get();

        // Fetch PO requests from outlets belonging to this branch
        $outletPurchaseOrders = PurchaseOrders::whereHas('outlet', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })->with(['outlet', 'user', 'sale'])->orderBy('created_at', 'desc')->get();

        $selectedPoId = $request->query('outlet_po_id');
        $selectedPo = $selectedPoId ? $outletPurchaseOrders->firstWhere('id', $selectedPoId) : null;
        $selectedPoNotes = $selectedPo ? json_decode($selectedPo->notes, true) : null;

        // Calculate dynamic dashboard metrics
        $omsetPenjualan = Sales::where('branch_id', $branchId)->where('create_by', auth()->id())->sum('grand_total');
        $omsetHariIni = Sales::where('branch_id', $branchId)->where('create_by', auth()->id())->whereDate('date', now()->toDateString())->sum('grand_total');

        $hutangPusat = Debts::where('debtor_type', 'branch')
            ->where('debtor_branch_id', $branchId)
            ->where('creditor_type', 'branch')
            ->sum('remaining_amount');
        $hutangPusatCount = Debts::where('debtor_type', 'branch')
            ->where('debtor_branch_id', $branchId)
            ->where('creditor_type', 'branch')
            ->where('remaining_amount', '>', 0)
            ->count();

        $totalProduk = ProductStock::where('branch_id', $branchId)->count();
        $totalStok = ProductStock::where('branch_id', $branchId)->sum('stock') ?? 0;

        // Prepare chart data (rolling last 6 months)
        $startDate = now()->subMonths(5)->startOfMonth();
        $monthlySalesData = Sales::where('branch_id', $branchId)
            ->where('create_by', auth()->id())
            ->where('date', '>=', $startDate)
            ->select('grand_total', 'date')
            ->get();

        $chartLabels = [];
        $chartValues = [];
        $indonesianMonths = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthNum = (int) $date->format('m');
            $yearNum = (int) $date->format('Y');

            $chartLabels[] = $indonesianMonths[$monthNum];

            $sum = $monthlySalesData->filter(function ($sale) use ($monthNum, $yearNum) {
                return $sale->date->year === $yearNum && $sale->date->month === $monthNum;
            })->sum('grand_total');

            $chartValues[] = (float) $sum;
        }

        return view('cabang.dashboard', compact(
            'products',
            'deliveries',
            'outletPurchaseOrders',
            'selectedPo',
            'selectedPoNotes',
            'omsetPenjualan',
            'omsetHariIni',
            'hutangPusat',
            'hutangPusatCount',
            'totalProduk',
            'totalStok',
            'chartLabels',
            'chartValues'
        ));
    }

    public function outlet()
    {
        $outletId = auth()->user()->outlet_id;
        $branchId = auth()->user()->branch_id ?? 'BRC-001';

        $deliveries = collect();
        $totalBelanja = 0;
        $totalOrder = 0;
        $chartLabels = [];
        $chartValues = [];

        if ($outletId) {
            $deliveries = Deliveries::whereHas('sale', function ($query) use ($outletId) {
                $query->where('outlet_id', $outletId);
            })->with(['sale.salesItems'])->orderBy('created_at', 'desc')->get();

            $purchaseOrders = PurchaseOrders::where('outlet_id', $outletId)->get();
            $totalOrder = $purchaseOrders->count();
            $totalBelanja = $purchaseOrders->filter(function ($po) {
                return ! in_array($po->status, ['Rejected', 'Draft']);
            })->sum(function ($po) {
                $notes = json_decode($po->notes, true);

                return (float) ($notes['grand_total'] ?? 0);
            });

            // Prepare chart data (rolling last 6 months)
            $indonesianMonths = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
            ];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthNum = (int) $date->format('m');
                $yearNum = (int) $date->format('Y');

                $chartLabels[] = $indonesianMonths[$monthNum];

                $sum = $purchaseOrders->filter(function ($po) use ($monthNum, $yearNum) {
                    return $po->created_at->year === $yearNum && $po->created_at->month === $monthNum && ! in_array($po->status, ['Rejected', 'Draft']);
                })->sum(function ($po) {
                    $notes = json_decode($po->notes, true);

                    return (float) ($notes['grand_total'] ?? 0);
                });

                $chartValues[] = (float) $sum;
            }
        }

        $products = Product::with([
            'wholesalePrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'branchPrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'productStocks' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
        ])->orderBy('name')->get();

        return view('outlet.dashboard', compact('deliveries', 'products', 'totalBelanja', 'totalOrder', 'chartLabels', 'chartValues'));
    }

    public function outletOrder()
    {
        $outletId = auth()->user()->outlet_id;
        $branchId = auth()->user()->branch_id ?? 'BRC-001';

        $purchaseOrders = PurchaseOrders::where('outlet_id', $outletId)
            ->with(['branch', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $products = Product::with([
            'wholesalePrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'branchPrices' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
            'productStocks' => function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            },
        ])->orderBy('name')->get();

        return view('outlet.order', compact('purchaseOrders', 'products'));
    }

    public function outletHistory()
    {
        $outletId = auth()->user()->outlet_id;
        $purchaseOrders = PurchaseOrders::where('outlet_id', $outletId)
            ->with(['branch', 'user', 'sale.debt'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('outlet.history', compact('purchaseOrders'));
    }
}
