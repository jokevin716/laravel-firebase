<?php

namespace App\Http\Controllers;

use App\Services\MarketstackService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private MarketstackService $marketstackService;

    public function __construct(MarketstackService $marketstackService) {
        $this->marketstackService = $marketstackService;
        $this->middleware('auth');
    }

    public function index() {
        try {
            $stockData = $this->marketstackService->getMultipleStocks(['AAPL', 'GOOGL', 'MSFT'], 30);
            return view('dashboard', compact('stockData'));
        }
        catch(\Exception $e) {
            return view('dashboard', ['stockData' => null, 'error' => $e->getMessage()]);
        }
    }

    public function getStockData(Request $request) {
        $symbols = $request->input('symbols', 'AAPL,GOOGL,MSFT');
        $symbolArray = explode(',', $symbols);

        try {
            $data = $this->marketstackService->getMultipleStocks($symbolArray, 30);
            return response()->json($data);
        }
        catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
