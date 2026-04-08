<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\BeverageStockEntry;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\OrderAudit;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalUsers = User::count();
        $todaySales = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total');
        $monthSales = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'completed')
            ->sum('total');

        $recentOrders = Order::with(['user', 'cashier'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('totalProducts', 'totalUsers', 'todaySales', 'monthSales', 'recentOrders'));
    }

    // Gestión de Usuarios
    public function users()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function createUser()
    {
        return view('admin.users.create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,cajero,mesero',
            'order_channel' => 'nullable|in:table,delivery',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'order_channel' => $request->input('role') === 'mesero'
                ? $request->input('order_channel', User::ORDER_CHANNEL_TABLE)
                : User::ORDER_CHANNEL_TABLE,
            'active' => true,
        ]);

        return redirect()->route('admin.users')->with('success', 'Usuario creado exitosamente');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,cajero,mesero',
            'order_channel' => 'nullable|in:table,delivery',
            'active' => 'boolean',
        ]);

        $data = $request->only(['name', 'email', 'role', 'active']);
        $data['order_channel'] = $request->input('role') === 'mesero'
            ? $request->input('order_channel', User::ORDER_CHANNEL_TABLE)
            : User::ORDER_CHANNEL_TABLE;

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Usuario actualizado exitosamente');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Usuario eliminado exitosamente');
    }

    // Gestión de Categorías
    public function categories()
    {
        $categories = Category::withCount('products')->latest()->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    public function tables()
    {
        $tables = DiningTable::withCount(['orders', 'activeOrders'])
            ->orderByRaw('COALESCE(zone, "")')
            ->orderBy('name')
            ->get();

        return view('admin.tables.index', compact('tables'));
    }

    public function storeTable(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tables,name',
            'zone' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        DiningTable::create([
            'name' => trim($request->name),
            'zone' => $request->filled('zone') ? trim($request->zone) : null,
            'capacity' => $request->filled('capacity') ? $request->capacity : null,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('admin.tables')->with('success', 'Mesa creada exitosamente');
    }

    public function updateTable(Request $request, $id)
    {
        $table = DiningTable::withCount('activeOrders')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tables,name,' . $table->id,
            'zone' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $active = $request->boolean('active');
        if (!$active && $table->active_orders_count > 0) {
            return redirect()->route('admin.tables')
                ->with('error', 'No puedes cerrar una mesa con pedidos activos.');
        }

        $table->update([
            'name' => trim($request->name),
            'zone' => $request->filled('zone') ? trim($request->zone) : null,
            'capacity' => $request->filled('capacity') ? $request->capacity : null,
            'active' => $active,
        ]);

        return redirect()->route('admin.tables')->with('success', 'Mesa actualizada exitosamente');
    }

    public function deleteTable($id)
    {
        $table = DiningTable::withCount('orders')->findOrFail($id);

        if ($table->orders_count > 0) {
            return redirect()->route('admin.tables')
                ->with('error', 'No puedes eliminar una mesa que ya tiene pedidos asociados.');
        }

        $table->delete();

        return redirect()->route('admin.tables')->with('success', 'Mesa eliminada exitosamente');
    }

    public function updateTableReservation(Request $request, $id)
    {
        $table = DiningTable::findOrFail($id);

        $request->validate([
            'reservation_name' => 'nullable|string|max:255',
            'reservation_at' => 'nullable|date',
            'reservation_notes' => 'nullable|string',
        ]);

        $hasReservationData = $request->filled('reservation_name') || $request->filled('reservation_at') || $request->filled('reservation_notes');

        if ($hasReservationData && (!$request->filled('reservation_name') || !$request->filled('reservation_at'))) {
            return redirect()->route('admin.tables')
                ->with('error', 'Debes indicar nombre y fecha/hora para registrar una reserva.');
        }

        $table->update([
            'reservation_name' => $hasReservationData ? trim((string) $request->reservation_name) : null,
            'reservation_at' => $hasReservationData ? $request->reservation_at : null,
            'reservation_notes' => $hasReservationData ? $request->reservation_notes : null,
        ]);

        return redirect()->route('admin.tables')->with('success', $hasReservationData ? 'Reserva actualizada exitosamente' : 'Reserva eliminada exitosamente');
    }

    public function tableActivity($id)
    {
        $table = DiningTable::findOrFail($id);

        $orders = Order::with(['user', 'cashier'])
            ->where('table_id', $table->id)
            ->latest()
            ->take(20)
            ->get()
            ->map(function (Order $order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'table_name' => $order->table_number,
                    'waiter' => $order->user?->name ?? '-',
                    'cashier' => $order->cashier?->name ?? '-',
                    'total' => number_format((float) $order->total, 2, '.', ''),
                    'created_at' => $order->created_at?->format('d/m/Y H:i'),
                ];
            })
            ->values();

        $movements = OrderAudit::with('user')
            ->where('action', 'updated')
            ->latest('created_at')
            ->get()
            ->filter(function (OrderAudit $audit) use ($table) {
                $beforeTableId = (int) data_get($audit->meta, 'before.table_id', 0);
                $afterTableId = (int) data_get($audit->meta, 'after.table_id', 0);

                return $beforeTableId !== $afterTableId
                    && ($beforeTableId === $table->id || $afterTableId === $table->id);
            })
            ->take(20)
            ->map(function (OrderAudit $audit) use ($table) {
                $beforeTableId = (int) data_get($audit->meta, 'before.table_id', 0);
                $afterTableId = (int) data_get($audit->meta, 'after.table_id', 0);
                $beforeTableName = data_get($audit->meta, 'before.table_number', '-');
                $afterTableName = data_get($audit->meta, 'after.table_number', '-');

                if ($beforeTableId === $table->id && $afterTableId !== $table->id) {
                    $direction = 'Salida';
                    $description = 'El pedido salió de esta mesa hacia ' . $afterTableName;
                } elseif ($afterTableId === $table->id && $beforeTableId !== $table->id) {
                    $direction = 'Entrada';
                    $description = 'El pedido llegó a esta mesa desde ' . $beforeTableName;
                } else {
                    $direction = 'Cambio';
                    $description = 'Cambio interno de mesa';
                }

                return [
                    'order_id' => $audit->order_id,
                    'direction' => $direction,
                    'description' => $description,
                    'user' => $audit->user?->name ?? '-',
                    'created_at' => $audit->created_at?->format('d/m/Y H:i'),
                ];
            })
            ->values();

        return response()->json([
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
            ],
            'orders' => $orders,
            'movements' => $movements,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $this->makeUniqueCategoryCode($request->name),
        ]);

        return redirect()->route('admin.categories')->with('success', 'Categoría creada exitosamente');
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'code' => $this->makeUniqueCategoryCode($request->name, $category->id),
        ]);

        return redirect()->route('admin.categories')->with('success', 'Categoría actualizada exitosamente');
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una categoría con productos asociados');
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Categoría eliminada exitosamente');
    }

    // Gestión de Productos
    public function products()
    {
        $products = Product::with('category')->latest()->paginate(15);
        $categories = Category::where('active', true)->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    public function beverageWarehouse()
    {
        $beverageProducts = $this->beverageProductsQuery()
            ->with('latestBeverageEntry')
            ->orderBy('name')
            ->get();

        $recentMovements = BeverageStockEntry::with(['product', 'user'])
            ->whereHas('product.category', function ($query) {
                $query->where('code', Category::CODE_BEVERAGES);
            })
            ->latest()
            ->take(50)
            ->get();

        $totalBeverageUnits = (int) $beverageProducts->sum('stock');
        $inventorySaleValue = (float) $beverageProducts->sum(function (Product $product) {
            return (float) $product->price * (int) $product->stock;
        });
        $entryUnits = (int) $recentMovements->where('movement_type', 'entry')->sum('total_units');
        $exitUnits = (int) $recentMovements->where('movement_type', 'exit')->sum('total_units');

        return view('admin.warehouse.beverages', [
            'beverageProducts' => $beverageProducts,
            'recentMovements' => $recentMovements,
            'totalBeverageUnits' => $totalBeverageUnits,
            'inventorySaleValue' => $inventorySaleValue,
            'entryUnits' => $entryUnits,
            'exitUnits' => $exitUnits,
        ]);
    }

    public function storeBeverageEntry(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'entry_type' => 'required|in:unit,box',
            'quantity' => 'required|integer|min:1',
            'purchase_price' => 'required|numeric|min:0',
            'units_per_box' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = Product::with('category')->findOrFail($request->product_id);

        if (!($product->category?->isBeverages() ?? false)) {
            return redirect()->route('admin.warehouse.beverages')
                ->with('error', 'El almacén solo acepta productos de la categoría Bebidas.');
        }

        $entryType = $request->input('entry_type');
        $quantity = (int) $request->input('quantity');
        $purchasePrice = round((float) $request->input('purchase_price'), 2);
        $unitsPerBox = $entryType === 'box' ? (int) $request->input('units_per_box', 0) : null;

        if ($entryType === 'box' && $unitsPerBox <= 0) {
            return redirect()->route('admin.warehouse.beverages')
                ->withInput()
                ->with('error', 'Debes indicar cuántas unidades contiene cada caja.');
        }

        $totalUnits = $entryType === 'box'
            ? $quantity * $unitsPerBox
            : $quantity;

        $unitCost = $entryType === 'box'
            ? round($purchasePrice / $unitsPerBox, 2)
            : $purchasePrice;

        $totalCost = round($purchasePrice * $quantity, 2);

        $updatedStock = DB::transaction(function () use ($product, $request, $entryType, $quantity, $unitsPerBox, $totalUnits, $purchasePrice, $unitCost, $totalCost) {
            $lockedProduct = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            BeverageStockEntry::create([
                'product_id' => $lockedProduct->id,
                'user_id' => Auth::id(),
                'movement_type' => 'entry',
                'entry_type' => $entryType,
                'quantity' => $quantity,
                'units_per_box' => $unitsPerBox,
                'total_units' => $totalUnits,
                'purchase_price' => $purchasePrice,
                'unit_cost' => $unitCost,
                'total_cost' => $totalCost,
                'notes' => $request->filled('notes') ? trim((string) $request->input('notes')) : null,
            ]);

            $lockedProduct->stock = (int) $lockedProduct->stock + $totalUnits;
            $lockedProduct->save();

            return (int) $lockedProduct->stock;
        });

        return redirect()->route('admin.warehouse.beverages')
            ->with('success', 'Entrada de almacén registrada exitosamente. Stock actual: ' . $updatedStock . ' unidades.');
    }

    public function storeBeverageExit(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'entry_type' => 'required|in:unit,box',
            'quantity' => 'required|integer|min:1',
            'units_per_box' => 'nullable|integer|min:1',
            'notes' => 'required|string|max:1000',
        ]);

        $product = Product::with('category')->findOrFail($request->product_id);

        if (!($product->category?->isBeverages() ?? false)) {
            return redirect()->route('admin.warehouse.beverages')
                ->with('error', 'El almacén solo acepta productos de la categoría Bebidas.');
        }

        $entryType = $request->input('entry_type');
        $quantity = (int) $request->input('quantity');
        $unitsPerBox = $entryType === 'box' ? (int) $request->input('units_per_box', 0) : null;

        if ($entryType === 'box' && $unitsPerBox <= 0) {
            return redirect()->route('admin.warehouse.beverages')
                ->withInput()
                ->with('error', 'Debes indicar cuántas unidades contiene cada caja para registrar la salida.');
        }

        $totalUnits = $entryType === 'box'
            ? $quantity * $unitsPerBox
            : $quantity;

        if ((int) $product->stock < $totalUnits) {
            return redirect()->route('admin.warehouse.beverages')
                ->withInput()
                ->with('error', 'No hay suficiente stock para registrar esa salida.');
        }

        $updatedStock = DB::transaction(function () use ($product, $request, $entryType, $quantity, $unitsPerBox, $totalUnits) {
            $lockedProduct = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            BeverageStockEntry::create([
                'product_id' => $lockedProduct->id,
                'user_id' => Auth::id(),
                'movement_type' => 'exit',
                'entry_type' => $entryType,
                'quantity' => $quantity,
                'units_per_box' => $unitsPerBox,
                'total_units' => $totalUnits,
                'purchase_price' => 0,
                'unit_cost' => 0,
                'total_cost' => 0,
                'notes' => trim((string) $request->input('notes')),
            ]);

            $lockedProduct->stock = max(0, (int) $lockedProduct->stock - $totalUnits);
            $lockedProduct->save();

            return (int) $lockedProduct->stock;
        });

        return redirect()->route('admin.warehouse.beverages')
            ->with('success', 'Salida de almacén registrada exitosamente. Stock actual: ' . $updatedStock . ' unidades.');
    }

    public function updateBeverageEntryPurchasePrice(Request $request, BeverageStockEntry $entry)
    {
        $request->validate([
            'purchase_price' => 'required|numeric|min:0',
        ]);

        $entry->load('product.category');

        if (
            $entry->movement_type !== 'entry'
            || !($entry->product?->category?->isBeverages() ?? false)
        ) {
            return redirect()->route('admin.warehouse.beverages')
                ->with('error', 'Solo puedes editar precios de compras registradas en el almacén de bebidas.');
        }

        $purchasePrice = round((float) $request->input('purchase_price'), 2);
        $unitsPerBox = (int) ($entry->units_per_box ?? 0);
        $quantity = (int) $entry->quantity;

        $unitCost = $entry->entry_type === 'box'
            ? ($unitsPerBox > 0 ? round($purchasePrice / $unitsPerBox, 2) : 0)
            : $purchasePrice;

        $totalCost = round($purchasePrice * $quantity, 2);

        $entry->update([
            'purchase_price' => $purchasePrice,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
        ]);

        return redirect()->route('admin.warehouse.beverages')
            ->with('success', 'Precio de la última compra actualizado exitosamente.');
    }

    public function beverageWarehousePrint()
    {
        $beverageProducts = $this->beverageProductsQuery()
            ->orderBy('name')
            ->get();

        $movements = BeverageStockEntry::with(['product', 'user'])
            ->whereHas('product.category', function ($query) {
                $query->where('code', Category::CODE_BEVERAGES);
            })
            ->latest()
            ->get();

        return view('admin.warehouse.beverages-print', [
            'beverageProducts' => $beverageProducts,
            'movements' => $movements,
            'totalBeverageUnits' => (int) $beverageProducts->sum('stock'),
            'entryUnits' => (int) $movements->where('movement_type', 'entry')->sum('total_units'),
            'exitUnits' => (int) $movements->where('movement_type', 'exit')->sum('total_units'),
        ]);
    }

    public function createProduct()
    {
        $categories = Category::where('active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function storeProduct(Request $request)
    {
        $request->validate($this->productValidationRules(), $this->productValidationMessages());

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                return redirect()->back()->withInput()->with('error', 'La imagen no se pudo subir. Intenta nuevamente.');
            }
            $data['image'] = $this->storeProductImage($file);
        }

        Product::create($data);

        return redirect()->route('admin.products')->with('success', 'Producto creado exitosamente');
    }

    public function editProduct($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate($this->productValidationRules(), $this->productValidationMessages());

        $data = $request->except('image');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                return redirect()->back()->withInput()->with('error', 'La imagen no se pudo subir. Intenta nuevamente.');
            }
            // Eliminar imagen anterior
            if ($product->image) {
                if (Str::startsWith($product->image, 'uploads/')) {
                    File::delete(public_path($product->image));
                } else {
                    Storage::disk('public')->delete($product->image);
                }
            }
            $data['image'] = $this->storeProductImage($file);
        }

        $product->update($data);

        return redirect()->route('admin.products')->with('success', 'Producto actualizado exitosamente');
    }

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            if (Str::startsWith($product->image, 'uploads/')) {
                File::delete(public_path($product->image));
            } else {
                Storage::disk('public')->delete($product->image);
            }
        }

        $product->delete();

        return redirect()->route('admin.products')->with('success', 'Producto eliminado exitosamente');
    }

    private function storeProductImage($file): string
    {
        $dir = public_path('uploads/products');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = Str::uuid()->toString() . '.' . $ext;
        $file->move($dir, $filename);
        return 'uploads/products/' . $filename;
    }

    private function productValidationRules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'image' => 'nullable|image|max:10240',
        ];
    }

    private function productValidationMessages(): array
    {
        return [
            'image.image' => 'La imagen debe ser un archivo JPG, PNG, GIF o WEBP.',
            'image.max' => 'La imagen no debe superar los 10 MB.',
            'image.uploaded' => 'No se pudo subir la imagen. Verifica que pese menos de 10 MB e inténtalo de nuevo.',
        ];
    }

    private function beverageProductsQuery()
    {
        return Product::query()
            ->with('category')
            ->whereHas('category', function ($query) {
                $query->where('code', Category::CODE_BEVERAGES);
            });
    }

    private function makeUniqueCategoryCode(string $name, ?int $ignoreId = null): string
    {
        $baseCode = Str::slug($name, '_');
        $baseCode = $baseCode !== '' ? $baseCode : 'categoria';
        $candidate = $baseCode;
        $suffix = 2;

        while (
            Category::query()
                ->where('code', $candidate)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $candidate = $baseCode . '_' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    // Reportes
    public function reports(Request $request)
    {
        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier', 'details.product']);

        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $orders = $query->latest('completed_at')->paginate(20);
        $totalSales = $query->sum('total');
        $totalOrders = $query->count();
        $beverageGainReport = $this->buildBeverageGainReport(
            $request->input('date_from'),
            $request->input('date_to')
        );

        // Productos más vendidos
        $topProducts = Product::select('products.*')
            ->join('order_details', 'products.id', '=', 'order_details.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->selectRaw('SUM(order_details.quantity) as total_sold')
            ->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $audits = OrderAudit::with(['order', 'user'])->latest()->take(30)->get();

        return view('admin.reports', compact('orders', 'totalSales', 'totalOrders', 'topProducts', 'audits', 'beverageGainReport'));
    }

    private function buildBeverageGainReport(?string $dateFrom, ?string $dateTo): array
    {
        $salesQuery = OrderDetail::query()
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'completed')
            ->where('categories.code', Category::CODE_BEVERAGES);

        if ($dateFrom) {
            $salesQuery->whereDate('orders.completed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $salesQuery->whereDate('orders.completed_at', '<=', $dateTo);
        }

        $salesByProduct = $salesQuery
            ->selectRaw('products.id as product_id, products.name as product_name, SUM(order_details.quantity) as units_sold, SUM(order_details.subtotal) as revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->get();

        $costQuery = BeverageStockEntry::query()
            ->selectRaw('product_id, SUM(total_units) as purchased_units, SUM(total_cost) as purchased_cost')
            ->where('movement_type', 'entry')
            ->whereHas('product.category', function ($query) {
                $query->where('code', Category::CODE_BEVERAGES);
            });

        if ($dateTo) {
            $costQuery->whereDate('created_at', '<=', $dateTo);
        }

        $costByProduct = $costQuery
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $products = $salesByProduct->map(function ($row) use ($costByProduct) {
            $unitsSold = (int) $row->units_sold;
            $revenue = round((float) $row->revenue, 2);
            $costRow = $costByProduct->get($row->product_id);
            $purchasedUnits = (int) ($costRow->purchased_units ?? 0);
            $purchasedCost = round((float) ($costRow->purchased_cost ?? 0), 2);
            $averageSalePrice = $unitsSold > 0
                ? round($revenue / $unitsSold, 2)
                : 0.0;

            $averageUnitCost = $purchasedUnits > 0
                ? round($purchasedCost / $purchasedUnits, 2)
                : null;

            $estimatedCost = $averageUnitCost !== null
                ? round($unitsSold * $averageUnitCost, 2)
                : null;

            $grossProfit = $estimatedCost !== null
                ? round($revenue - $estimatedCost, 2)
                : null;

            $margin = ($grossProfit !== null && $revenue > 0)
                ? round(($grossProfit / $revenue) * 100, 2)
                : null;

            return (object) [
                'product_id' => (int) $row->product_id,
                'product_name' => $row->product_name,
                'units_sold' => $unitsSold,
                'revenue' => $revenue,
                'average_sale_price' => $averageSalePrice,
                'average_unit_cost' => $averageUnitCost,
                'estimated_cost' => $estimatedCost,
                'gross_profit' => $grossProfit,
                'margin' => $margin,
                'has_cost_data' => $averageUnitCost !== null,
            ];
        });

        $coveredProducts = $products->where('has_cost_data', true);
        $uncoveredProducts = $products->where('has_cost_data', false);
        $coveredRevenue = round((float) $coveredProducts->sum('revenue'), 2);
        $totalRevenue = round((float) $products->sum('revenue'), 2);
        $estimatedCost = round((float) $coveredProducts->sum('estimated_cost'), 2);
        $estimatedProfit = round((float) $coveredProducts->sum('gross_profit'), 2);
        $coveragePercent = $totalRevenue > 0
            ? round(($coveredRevenue / $totalRevenue) * 100, 2)
            : 100.0;

        return [
            'products' => $products,
            'summary' => [
                'total_units_sold' => (int) $products->sum('units_sold'),
                'total_revenue' => $totalRevenue,
                'covered_revenue' => $coveredRevenue,
                'estimated_cost' => $estimatedCost,
                'estimated_profit' => $estimatedProfit,
                'estimated_margin' => $coveredRevenue > 0
                    ? round(($estimatedProfit / $coveredRevenue) * 100, 2)
                    : null,
                'coverage_percent' => $coveragePercent,
                'missing_cost_products' => $uncoveredProducts->count(),
                'missing_cost_units' => (int) $uncoveredProducts->sum('units_sold'),
            ],
        ];
    }

    public function reportsBeverageGainPrint(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $beverageGainReport = $this->buildBeverageGainReport($dateFrom, $dateTo);

        return view('admin.reports-beverages-gain-print', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'beverageGainReport' => $beverageGainReport,
        ]);
    }

    public function reportsPrint(Request $request)
    {
        $type = $request->get('type', 'day');
        if (!in_array($type, ['day', 'month'], true)) {
            $type = 'day';
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if (!$dateFrom && !$dateTo) {
            if ($type === 'day') {
                $dateFrom = now()->toDateString();
                $dateTo = now()->toDateString();
            } else {
                $dateFrom = now()->startOfMonth()->toDateString();
                $dateTo = now()->endOfMonth()->toDateString();
            }
        }

        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier', 'details.product'])
            ->orderBy('completed_at');

        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }

        $orders = $query->get();
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();

        $grouped = $orders->groupBy(function ($order) use ($type) {
            $date = $order->completed_at ?? $order->created_at;
            return $type === 'month' ? $date->format('Y-m') : $date->format('Y-m-d');
        });

        return view('admin.reports-print', [
            'orders' => $orders,
            'grouped' => $grouped,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function reportsThermalPrint(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Order::where('status', 'completed')
            ->with(['details.product'])
            ->orderBy('completed_at');

        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }

        $orders = $query->get();

        $products = $orders
            ->flatMap(fn ($order) => $order->details)
            ->groupBy(fn ($detail) => $detail->product_id ?: 'deleted-' . $detail->id)
            ->map(function ($details) {
                $first = $details->first();

                return (object) [
                    'name' => $first->product->name ?? 'Producto eliminado',
                    'quantity' => $details->sum('quantity'),
                    'total' => $details->sum('subtotal'),
                ];
            })
            ->sortBy('name')
            ->values();

        return view('admin.reports-thermal-print', [
            'products' => $products,
            'ordersCount' => $orders->count(),
            'totalProducts' => $products->sum('quantity'),
            'grandTotal' => $products->sum('total'),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function reportsExport(Request $request)
    {
        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier', 'details.product']);

        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $orders = $query->latest('completed_at')->get();

        $headers = [
            'Pedido',
            'Fecha',
            'Mesa',
            'Mesero',
            'Cajero',
            'Items',
            'Total',
        ];

        $lines = [];
        $lines[] = $headers;

        foreach ($orders as $order) {
            $lines[] = [
                $order->display_number,
                ($order->completed_at ?? $order->created_at)->format('Y-m-d H:i'),
                $order->table_number,
                $order->user ? $order->user->name : '-',
                $order->cashier ? $order->cashier->name : '-',
                $order->details->sum('quantity'),
                number_format($order->total, 2, '.', ''),
            ];
        }

        $lines = array_map(function ($line) {
            return implode(',', array_map([$this, 'csvEscape'], $line));
        }, $lines);

        $content = implode("\n", $lines);
        $filename = 'reporte-ventas-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function reportsXlsx(Request $request)
    {
        if (!class_exists(\ZipArchive::class)) {
            return redirect()->route('admin.reports', $request->query())
                ->with('error', 'El servidor no tiene habilitada la extensión ZIP. Descarga CSV o habilita ZipArchive.');
        }

        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier', 'details.product']);

        if ($request->filled('date_from')) {
            $query->whereDate('completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('completed_at', '<=', $request->date_to);
        }

        $orders = $query->latest('completed_at')->get();

        $headers = [
            'Pedido',
            'Fecha',
            'Mesa',
            'Mesero',
            'Cajero',
            'Items',
            'Total',
        ];

        $rows = [];
        foreach ($orders as $order) {
            $rows[] = [
                $order->display_number,
                ($order->completed_at ?? $order->created_at)->format('Y-m-d H:i'),
                $order->table_number,
                $order->user ? $order->user->name : '-',
                $order->cashier ? $order->cashier->name : '-',
                $order->details->sum('quantity'),
                number_format($order->total, 2, '.', ''),
            ];
        }

        $path = $this->buildXlsx('Reporte Ventas', $headers, $rows);
        $filename = 'reporte-ventas-' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function reportsPdf(Request $request)
    {
        $type = $request->get('type', 'day');
        if (!in_array($type, ['day', 'month'], true)) {
            $type = 'day';
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        if (!$dateFrom && !$dateTo) {
            if ($type === 'day') {
                $dateFrom = now()->toDateString();
                $dateTo = now()->toDateString();
            } else {
                $dateFrom = now()->startOfMonth()->toDateString();
                $dateTo = now()->endOfMonth()->toDateString();
            }
        }

        $query = Order::where('status', 'completed')
            ->with(['user', 'cashier', 'details.product'])
            ->orderBy('completed_at');

        if ($dateFrom) {
            $query->whereDate('completed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('completed_at', '<=', $dateTo);
        }

        $orders = $query->get();
        $totalSales = $orders->sum('total');
        $totalOrders = $orders->count();

        $grouped = $orders->groupBy(function ($order) use ($type) {
            $date = $order->completed_at ?? $order->created_at;
            return $type === 'month' ? $date->format('Y-m') : $date->format('Y-m-d');
        });

        $pdf = Pdf::loadView('admin.reports-print', [
            'orders' => $orders,
            'grouped' => $grouped,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'type' => $type,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);

        return $pdf->download('reporte-ventas-' . now()->format('Ymd_His') . '.pdf');
    }

    public function topProducts(Request $request)
    {
        $query = Product::select('products.*')
            ->join('order_details', 'products.id', '=', 'order_details.product_id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->selectRaw('SUM(order_details.quantity) as total_sold')
            ->selectRaw('SUM(order_details.quantity * order_details.unit_price) as total_generated')
            ->with('category');

        if ($request->filled('date_from')) {
            $query->whereDate('orders.completed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('orders.completed_at', '<=', $request->date_to);
        }

        $topProducts = $query->groupBy('products.id')
            ->orderByDesc('total_sold')
            ->paginate(20);

        return view('admin.top-products', compact('topProducts'));
    }

    private function csvEscape($value): string
    {
        $value = (string) $value;
        $value = str_replace('"', '""', $value);
        return '"' . $value . '"';
    }

    private function buildXlsx(string $sheetName, array $headers, array $rows): string
    {
        $tmpDir = storage_path('app/exports');
        if (!File::exists($tmpDir)) {
            File::makeDirectory($tmpDir, 0755, true);
        }
        $filePath = $tmpDir . DIRECTORY_SEPARATOR . 'report_' . now()->format('Ymd_His') . '_' . Str::random(6) . '.xlsx';

        $sharedStrings = [];
        $stringIndex = [];

        $allRows = array_merge([$headers], $rows);
        foreach ($allRows as $row) {
            foreach ($row as $value) {
                $value = (string) $value;
                if (!array_key_exists($value, $stringIndex)) {
                    $stringIndex[$value] = count($sharedStrings);
                    $sharedStrings[] = $value;
                }
            }
        }

        $sheetXml = $this->buildSheetXml($allRows, $stringIndex);
        $sharedXml = $this->buildSharedStringsXml($sharedStrings);

        $zip = new \ZipArchive();
        $zip->open($filePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->addFromString('xl/sharedStrings.xml', $sharedXml);

        $zip->close();

        return $filePath;
    }

    private function buildSheetXml(array $rows, array $stringIndex): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';

        $rowNum = 1;
        foreach ($rows as $row) {
            $xml .= '<row r="' . $rowNum . '">';
            $col = 1;
            foreach ($row as $value) {
                $cellRef = $this->colLetter($col) . $rowNum;
                $idx = $stringIndex[(string) $value];
                $xml .= '<c r="' . $cellRef . '" t="s"><v>' . $idx . '</v></c>';
                $col++;
            }
            $xml .= '</row>';
            $rowNum++;
        }

        $xml .= '</sheetData></worksheet>';
        return $xml;
    }

    private function buildSharedStringsXml(array $strings): string
    {
        $count = count($strings);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $count . '" uniqueCount="' . $count . '">';
        foreach ($strings as $s) {
            $xml .= '<si><t>' . htmlspecialchars($s, ENT_XML1) . '</t></si>';
        }
        $xml .= '</sst>';
        return $xml;
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>' .
            '</Types>';
    }

    private function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
    }

    private function workbookXml(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" ' .
            'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets><sheet name="' . htmlspecialchars($sheetName, ENT_XML1) . '" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';
    }

    private function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
            '</Relationships>';
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index = intdiv($index - 1, 26);
        }
        return $letter;
    }
}
