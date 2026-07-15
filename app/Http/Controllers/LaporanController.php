<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\ProductStock;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function admin()
    {
        $totalOmset = Sales::where('create_by', auth()->id())->sum('grand_total');
        $totalKeuntungan = SalesItem::whereHas('sale', function ($query) {
            $query->where('create_by', auth()->id());
        })->sum(DB::raw('(price - cost) * qty')) - Sales::where('create_by', auth()->id())->sum('discount');
        $barangTerjual = SalesItem::whereHas('sale', function ($query) {
            $query->where('create_by', auth()->id());
        })->sum('qty');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('j M');
            $chartValues[] = (float) Sales::where('create_by', auth()->id())
                ->whereDate('date', $date->toDateString())
                ->sum('grand_total');
        }

        $produkTerlaris = SalesItem::whereHas('sale', function ($query) {
            $query->where('create_by', auth()->id());
        })->select('product_name', DB::raw('SUM(qty) as total_terjual'), DB::raw('SUM(subtotal) as total_omset'))
            ->groupBy('product_name')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $transaksiTerakhir = Sales::where('create_by', auth()->id())
            ->with('branch')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('admin.laporan', compact(
            'totalOmset',
            'totalKeuntungan',
            'barangTerjual',
            'chartLabels',
            'chartValues',
            'produkTerlaris',
            'transaksiTerakhir'
        ));
    }

    public function adminCabang(Request $request)
    {
        $tab = $request->query('tab', 'manajemen');

        $wilayahs = Wilayah::orderBy('name')->get();

        // Cascading Branch Filter - Exclude admin branches
        $branchesQuery = Branch::whereDoesntHave('users', function ($q) {
            $q->whereHas('role', function ($r) {
                $r->where('name', 'admin');
            });
        });
        if ($request->filled('wilayah_id')) {
            $branchesQuery->where('wilayah_id', $request->wilayah_id);
        }
        $branches = $branchesQuery->orderBy('name')->get();

        // Handle filter reset if branch does not belong to selected wilayah or is an admin branch
        if ($request->filled('wilayah_id') && $request->filled('branch_id')) {
            $branchExists = Branch::where('id', $request->branch_id)
                ->where('wilayah_id', $request->wilayah_id)
                ->whereDoesntHave('users', function ($q) {
                    $q->whereHas('role', function ($r) {
                        $r->where('name', 'admin');
                    });
                })
                ->exists();
            if (! $branchExists) {
                $request->merge(['branch_id' => null]);
            }
        }

        $categories = Category::orderBy('name')->get();

        $regions = [];
        $stocks = collect();
        $transactions = collect();

        if ($tab === 'manajemen') {
            $regions = Wilayah::with(['branches' => function ($q) {
                $q->withCount('outlets');
            }])->orderBy('name')->get();
        } elseif ($tab === 'stok') {
            $stocksQuery = ProductStock::with(['product.category', 'branch.wilayah'])
                ->whereHas('branch', function ($q) {
                    $q->whereDoesntHave('users', function ($qu) {
                        $qu->whereHas('role', function ($r) {
                            $r->where('name', 'admin');
                        });
                    });
                });

            if ($request->filled('wilayah_id')) {
                $stocksQuery->whereHas('branch', function ($q) use ($request) {
                    $q->where('wilayah_id', $request->wilayah_id);
                });
            }
            if ($request->filled('branch_id')) {
                $stocksQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('category_id')) {
                $stocksQuery->whereHas('product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $stocks = $stocksQuery->orderBy('stock', 'desc')->get();
        } elseif ($tab === 'transaksi') {
            $transactionsQuery = Sales::with(['branch.wilayah', 'outlet', 'salesItems.product'])
                ->whereNotNull('outlet_id')
                ->whereHas('branch', function ($q) {
                    $q->whereDoesntHave('users', function ($qu) {
                        $qu->whereHas('role', function ($r) {
                            $r->where('name', 'admin');
                        });
                    });
                });

            if ($request->filled('wilayah_id')) {
                $transactionsQuery->whereHas('branch', function ($q) use ($request) {
                    $q->where('wilayah_id', $request->wilayah_id);
                });
            }
            if ($request->filled('branch_id')) {
                $transactionsQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('category_id')) {
                $transactionsQuery->whereHas('salesItems.product', function ($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }

            $transactions = $transactionsQuery->orderBy('date', 'desc')->get();
        }

        return view('admin.laporan-cabang', compact(
            'tab',
            'wilayahs',
            'branches',
            'categories',
            'regions',
            'stocks',
            'transactions'
        ));
    }

    public function cabang()
    {
        $branchId = auth()->user()->branch_id;

        $totalOmset = Sales::where('branch_id', $branchId)
            ->whereNotNull('outlet_id')
            ->where('create_by', auth()->id())
            ->sum('grand_total');

        $totalKeuntungan = SalesItem::whereHas('sale', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id());
        })->sum(DB::raw('(price - cost) * qty')) - Sales::where('branch_id', $branchId)->whereNotNull('outlet_id')->where('create_by', auth()->id())->sum('discount');

        $barangTerjual = SalesItem::whereHas('sale', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id());
        })->sum('qty');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('j M');
            $chartValues[] = (float) Sales::where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id())
                ->whereDate('date', $date->toDateString())
                ->sum('grand_total');
        }

        $produkTerlaris = SalesItem::whereHas('sale', function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                ->whereNotNull('outlet_id')
                ->where('create_by', auth()->id());
        })->select('product_name', DB::raw('SUM(qty) as total_terjual'), DB::raw('SUM(subtotal) as total_omset'))
            ->groupBy('product_name')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        $transaksiTerakhir = Sales::where('branch_id', $branchId)
            ->whereNotNull('outlet_id')
            ->where('create_by', auth()->id())
            ->with('outlet')
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('cabang.laporan', compact(
            'totalOmset',
            'totalKeuntungan',
            'barangTerjual',
            'chartLabels',
            'chartValues',
            'produkTerlaris',
            'transaksiTerakhir'
        ));
    }
}
